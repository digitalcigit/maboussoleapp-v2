#!/bin/bash

# Configuration
REMOTE_USER="tcxtutmt"
REMOTE_HOST="146.88.232.123"
REMOTE_PORT="5022"
RELEASE_NAME="release-20241230-065107"
REMOTE_BASE_DIR="~/public_html"

# SSH connection string
SSH_CONNECTION="ssh -p ${REMOTE_PORT} ${REMOTE_USER}@${REMOTE_HOST}"

echo "Starting deployment process..."

# 1. Create necessary directories
echo "Creating directories..."
$SSH_CONNECTION "mkdir -p ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}"

# 2. Unzip the release
echo "Unzipping release..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && unzip -q ../${RELEASE_NAME}.zip"

# 3. Restore production .env
echo "Restoring production .env..."
$SSH_CONNECTION "cp ${REMOTE_BASE_DIR}/current/.env ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/.env"

# 4. Set proper permissions
echo "Setting permissions..."
$SSH_CONNECTION "chmod -R 755 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}"
$SSH_CONNECTION "chmod -R 775 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage"
$SSH_CONNECTION "chmod -R 775 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/bootstrap/cache"
$SSH_CONNECTION "find ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage -type f -exec chmod 664 {} \;"

# 5. Ensure storage structure
echo "Setting up storage structure..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && mkdir -p storage/framework/{sessions,views,cache}"
$SSH_CONNECTION "chmod -R 775 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage/framework"

# 6. Clear and optimize Laravel
echo "Optimizing Laravel..."
$SSH_CONNECTION "cd ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} && php artisan optimize:clear && php artisan optimize && php artisan view:cache && php artisan config:cache"

# 7. Update symlinks
echo "Updating symlinks..."
$SSH_CONNECTION "ln -nfs ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME} ${REMOTE_BASE_DIR}/current"
$SSH_CONNECTION "ln -nfs ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/public ${REMOTE_BASE_DIR}/public_html"

# 8. Check Laravel logs
echo "Checking Laravel logs..."
$SSH_CONNECTION "tail -n 50 ${REMOTE_BASE_DIR}/releases/${RELEASE_NAME}/storage/logs/laravel.log"

echo "Deployment completed successfully!"
