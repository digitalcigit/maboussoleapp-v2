#!/bin/bash

# Arrêt en cas d'erreur
set -e

echo "🚀 Déploiement en cours..."

# Variables d'environnement (seront définies dans GitHub Actions)
# SERVER_HOST sera défini dans les secrets GitHub
# SERVER_USER sera défini dans les secrets GitHub
# DEPLOY_PATH sera défini dans les secrets GitHub

# Mise à jour du code source
echo "📦 Mise à jour du code source..."
cd $DEPLOY_PATH
git pull origin main

# Installation/Mise à jour des dépendances
echo "📚 Installation des dépendances..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Optimisations Laravel
echo "⚡ Optimisation de l'application..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migration de la base de données
echo "🔄 Migration de la base de données..."
php artisan migrate --force

# Compilation des assets
echo "🎨 Compilation des assets..."
npm ci
npm run build

# Nettoyage du cache
echo "🧹 Nettoyage du cache..."
php artisan cache:clear

# Redémarrage des services si nécessaire
echo "🔄 Redémarrage des services..."
sudo supervisorctl restart all
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

echo "✅ Déploiement terminé avec succès!"
