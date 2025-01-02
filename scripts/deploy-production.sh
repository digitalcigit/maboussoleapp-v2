#!/bin/bash

# Configuration
REMOTE_USER="crmmaboussole"
REMOTE_HOST="crm-app.maboussole.net"
REMOTE_PORT="22"
RELEASE_NAME="release-$(date +%Y%m%d-%H%M%S)"
REMOTE_BASE_DIR="/var/www/laravel/crm-app.maboussole.net"
MAX_RELEASES=5

# SSH connection string
SSH_CONNECTION="ssh -p ${REMOTE_PORT} ${REMOTE_USER}@${REMOTE_HOST}"

echo "Starting deployment process..."

# 0. Check SSH connection
echo "Checking SSH connection..."
if ! $SSH_CONNECTION "echo 'SSH connection successful'"; then
    echo "Error: Unable to connect to remote server"
    exit 1
fi

# 1. Create necessary directories
echo "Creating directories..."
$SSH_CONNECTION "mkdir -p ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}"

# 2. Clone repository
echo "Cloning repository..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases && \
    git clone --depth 1 https://github.com/votre-repo/maboussoleapp-v2.git ${RELEASE_NAME}"

# 3. Install dependencies and build
echo "Installing dependencies and building assets..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    composer install --no-dev --optimize-autoloader && \
    npm ci && \
    npm run build"

# 4. Configure environment
echo "Setting up environment..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    cp ${REMOTE_BASE_DIR}/shared/.env .env && \
    php artisan key:generate --force && \
    php artisan storage:link"

# 5. Set proper permissions
echo "Setting permissions..."
$SSH_CONNECTION "sudo chown -R www-data:www-data ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}"
$SSH_CONNECTION "sudo chmod -R 755 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}"
$SSH_CONNECTION "sudo chmod -R 775 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage"
$SSH_CONNECTION "sudo chmod -R 775 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/bootstrap/cache"
$SSH_CONNECTION "sudo find ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage -type f -exec chmod 664 {} \;"

# 6. Database migrations
echo "Running database migrations..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    php artisan migrate --force"

# 7. Clear and optimize Laravel
echo "Optimizing Laravel..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    php artisan down --retry=60 && \
    php artisan optimize:clear && \
    php artisan optimize && \
    php artisan view:cache && \
    php artisan config:cache && \
    php artisan route:cache"

# 8. Update symlinks
echo "Updating symlinks..."
$SSH_CONNECTION "ln -nfs ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} ${REMOTE_BASE_DIR}/current"
$SSH_CONNECTION "ln -nfs ${REMOTE_BASE_DIR}/current/public ${REMOTE_BASE_DIR}/public_html"

# 9. Cleanup old releases
echo "Cleaning up old releases..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases && ls -t | tail -n +$((MAX_RELEASES + 1)) | xargs -r rm -rf"

# 10. Restart services if needed
echo "Restarting services..."
$SSH_CONNECTION "sudo systemctl reload php8.2-fpm"
$SSH_CONNECTION "sudo systemctl reload nginx"

# 11. Clear cache and bring application back online
echo "Finalizing deployment..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    php artisan cache:clear && \
    php artisan up"

# 12. Check application status
echo "Checking application status..."
$SSH_CONNECTION "curl -sI http://crm-app.maboussole.net | grep HTTP/"
$SSH_CONNECTION "tail -n 50 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage/logs/laravel.log"

echo "Deployment completed successfully!"
