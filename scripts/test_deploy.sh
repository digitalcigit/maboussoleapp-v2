#!/bin/bash

# Arrêt en cas d'erreur
set -e

echo "🚀 Test de déploiement en cours..."

# Variables d'environnement pour le test
export DEPLOY_PATH="/home/dcidev/test_deploy"
export APP_ENV="testing"

# Copier le fichier .env.testing vers .env pour le test
echo "🔧 Configuration de l'environnement de test..."
cp .env.testing .env

# Nettoyer l'environnement de test
echo "🧹 Nettoyage de l'environnement de test..."
rm -rf $DEPLOY_PATH/vendor
rm -rf $DEPLOY_PATH/node_modules
rm -rf $DEPLOY_PATH/public/build
rm -rf /home/dcidev/maboussoleapp-v2_full
rm -rf /home/dcidev/test_deploy/public_html

# Copier le projet dans l'environnement de test
echo "📂 Copie des fichiers du projet..."
rsync -av --exclude={'.git','node_modules','vendor'} /home/dcidev/CascadeProjects/maboussoleapp-v2/ $DEPLOY_PATH/

# Préparation de l'environnement de test
echo "🔧 Préparation de l'environnement de test..."
cd $DEPLOY_PATH

# Installation des dépendances de développement pour le test
echo "📚 Installation des dépendances de développement..."
composer install --no-interaction --prefer-dist

# Génération de la clé d'application
echo "🔑 Génération de la clé d'application..."
php artisan key:generate

# Exécution du script de déploiement
echo "🚀 Exécution du déploiement..."
./scripts/deploy.sh

# Vérification de l'installation
echo "✅ Vérification de l'installation..."
php artisan --version

# Exécution des tests
echo "🧪 Exécution des tests..."
php artisan test

echo "✅ Test de déploiement terminé!"
echo "📁 Vérifiez la structure dans: $DEPLOY_PATH"
