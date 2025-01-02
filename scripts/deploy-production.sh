#!/bin/bash

# Configuration
REMOTE_USER="${REMOTE_USER}"
REMOTE_HOST="${REMOTE_HOST}"
REMOTE_PORT="${REMOTE_PORT}"
RELEASE_NAME="release-$(date +%Y%m%d-%H%M%S)"
REMOTE_BASE_DIR="${REMOTE_BASE_DIR}"
MAX_RELEASES=5

# Vérification des variables requises
if [ -z "$REMOTE_USER" ] || [ -z "$REMOTE_HOST" ] || [ -z "$REMOTE_PORT" ] || [ -z "$REMOTE_BASE_DIR" ]; then
    echo "Error: Missing required environment variables"
    echo "Required variables: REMOTE_USER, REMOTE_HOST, REMOTE_PORT, REMOTE_BASE_DIR"
    exit 1
fi

echo "Starting deployment process..."
echo "Deploying to ${REMOTE_HOST}:${REMOTE_PORT} as ${REMOTE_USER}"
echo "Release: ${RELEASE_NAME}"
echo "Base directory: ${REMOTE_BASE_DIR}"

# Configuration SSH dans ~/.ssh/config
mkdir -p ~/.ssh
cat > ~/.ssh/config << EOF
Host deployment
    HostName ${REMOTE_HOST}
    User ${REMOTE_USER}
    Port ${REMOTE_PORT}
    StrictHostKeyChecking no
    UserKnownHostsFile=/dev/null
EOF
chmod 600 ~/.ssh/config

# Test de la connexion SSH
echo "Checking SSH connection..."
if ! ssh deployment "echo 'SSH connection successful'"; then
    echo "Error: Unable to connect to remote server"
    exit 1
fi

# 1. Create necessary directories
echo "Creating directories..."
ssh deployment "mkdir -p ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}"

# 2. Clone repository
echo "Cloning repository..."
ssh deployment "cd ${REMOTE_BASE_DIR}/releases && \
    git clone --depth 1 https://github.com/digitalcigit/maboussoleapp-v2.git ${RELEASE_NAME}"

# 3. Install dependencies and build
echo "Installing dependencies and building assets..."
ssh deployment "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    composer install --no-dev --optimize-autoloader && \
    npm ci && \
    npm run build"

# 4. Configure environment
echo "Setting up environment..."
ssh deployment "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    cp ${REMOTE_BASE_DIR}/shared/.env .env && \
    php artisan key:generate --force && \
    php artisan storage:link"

# 5. Set proper permissions
echo "Setting permissions..."
ssh deployment "sudo chown -R www-data:www-data ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}"
ssh deployment "sudo chmod -R 755 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}"
ssh deployment "sudo chmod -R 775 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage"
ssh deployment "sudo chmod -R 775 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/bootstrap/cache"
ssh deployment "sudo find ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage -type f -exec chmod 664 {} \;"

# 6. Database migrations
echo "Running database migrations..."
ssh deployment "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    php artisan migrate --force"

# 7. Clear and optimize Laravel
echo "Optimizing Laravel..."
ssh deployment "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    php artisan down --retry=60 && \
    php artisan optimize:clear && \
    php artisan optimize && \
    php artisan view:cache && \
    php artisan config:cache && \
    php artisan route:cache"

# 8. Update symlinks
echo "Updating symlinks..."
ssh deployment "ln -nfs ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} ${REMOTE_BASE_DIR}/current"
ssh deployment "ln -nfs ${REMOTE_BASE_DIR}/current/public ${REMOTE_BASE_DIR}/public_html"

# 9. Cleanup old releases
echo "Cleaning up old releases..."
ssh deployment "cd ${REMOTE_BASE_DIR}/releases && ls -t | tail -n +$((MAX_RELEASES + 1)) | xargs -r rm -rf"

# 10. Restart services if needed
echo "Restarting services..."
ssh deployment "sudo systemctl reload php8.2-fpm"
ssh deployment "sudo systemctl reload nginx"

# 11. Clear cache and bring application back online
echo "Finalizing deployment..."
ssh deployment "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && \
    php artisan cache:clear && \
    php artisan up"

# 12. Check application status
echo "Checking application status..."
ssh deployment "curl -sI http://crm-app.maboussole.net | grep HTTP/"
ssh deployment "tail -n 50 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage/logs/laravel.log"

echo "Deployment completed successfully!"
