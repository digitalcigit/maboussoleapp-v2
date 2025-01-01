#!/bin/bash

# Arrêt en cas d'erreur
set -e

echo "🚀 Préparation de la release..."

# Créer le dossier de release
RELEASE_DIR="releases"
RELEASE_NAME="release-$(date '+%Y%m%d-%H%M%S')"
RELEASE_PATH="$RELEASE_DIR/$RELEASE_NAME"

mkdir -p "$RELEASE_PATH"

# Copier les fichiers sources
echo "📦 Copie des fichiers sources..."
rsync -av --progress ./ "$RELEASE_PATH" \
    --exclude '.git' \
    --exclude 'node_modules' \
    --exclude 'vendor' \
    --exclude 'releases'

# Copier le fichier .env
cp .env "$RELEASE_PATH/.env"

# Installer les dépendances de production
echo "📚 Installation des dépendances PHP..."
cd "$RELEASE_PATH"
composer install --no-dev --optimize-autoloader

echo "📦 Installation des dépendances Node.js..."
npm ci
npm run build

# Nettoyer les fichiers inutiles
echo "🧹 Nettoyage..."
rm -rf node_modules
rm -rf tests

# Créer l'archive
echo "📦 Création de l'archive..."
cd ..
zip -r "${RELEASE_NAME}.zip" "$RELEASE_NAME"

echo "✅ Release préparée avec succès!"
echo "📁 Archive disponible dans: $RELEASE_DIR/${RELEASE_NAME}.zip"
