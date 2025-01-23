#!/bin/bash

# Arrêt en cas d'erreur
set -e

# Couleurs pour une meilleure lisibilité
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les statuts
print_status() {
    if [ $2 -eq 0 ]; then
        echo -e "${GREEN}✓ $1${NC}"
    else
        echo -e "${RED}✗ $1${NC}"
        exit 1
    fi
}

echo -e "${YELLOW}🔍 Vérification des prérequis de déploiement...${NC}\n"

# 1. Vérification des secrets GitHub
echo "1️⃣ Vérification des secrets GitHub..."

check_secret() {
    local secret_name=$1
    local secret_value=$2
    if [ -z "$secret_value" ]; then
        echo -e "${RED}✗ $secret_name n'est pas configuré${NC}"
        return 1
    else
        echo -e "${GREEN}✓ $secret_name est configuré${NC}"
        return 0
    fi
}

check_secret "SSH_PRIVATE_KEY" "$SSH_PRIVATE_KEY"
check_secret "SERVER_HOST" "$SERVER_HOST"
check_secret "SERVER_USER" "$SERVER_USER"
check_secret "SERVER_PORT" "$SERVER_PORT"
check_secret "DEPLOY_PATH" "$DEPLOY_PATH"

echo ""

# 2. Vérification SSH
echo "2️⃣ Test de la connexion SSH..."

# Création du répertoire .ssh si nécessaire
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Ajout de la clé privée temporairement pour le test
if [ ! -z "$SSH_PRIVATE_KEY" ]; then
    echo "$SSH_PRIVATE_KEY" > ~/.ssh/deploy_key
    chmod 600 ~/.ssh/deploy_key
    
    # Test de la connexion SSH
    ssh -i ~/.ssh/deploy_key -p "$SERVER_PORT" -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_HOST" "echo 'SSH connection successful'" &> /dev/null
    print_status "Connexion SSH" $?
    
    # Nettoyage
    rm ~/.ssh/deploy_key
else
    echo -e "${RED}✗ Impossible de tester la connexion SSH : SSH_PRIVATE_KEY manquante${NC}"
    exit 1
fi

echo ""

# 3. Vérification des permissions du dossier de déploiement
echo "3️⃣ Vérification des permissions..."

ssh -p "$SERVER_PORT" "$SERVER_USER@$SERVER_HOST" "test -w $DEPLOY_PATH" &> /dev/null
print_status "Permissions en écriture sur $DEPLOY_PATH" $?

# 4. Vérification des dépendances locales
echo -e "\n4️⃣ Vérification des dépendances locales..."

# Git
command -v git >/dev/null 2>&1
print_status "Git installé" $?

# PHP
command -v php >/dev/null 2>&1
print_status "PHP installé" $?

# Composer
command -v composer >/dev/null 2>&1
print_status "Composer installé" $?

# Node/NPM
command -v node >/dev/null 2>&1
print_status "Node.js installé" $?
command -v npm >/dev/null 2>&1
print_status "NPM installé" $?

# 5. Vérification de l'espace disque
echo -e "\n5️⃣ Vérification de l'espace disque sur le serveur..."

ssh -p "$SERVER_PORT" "$SERVER_USER@$SERVER_HOST" "df -h $DEPLOY_PATH | tail -n 1 | awk '{print \$5}' | sed 's/%//'" | {
    read usage
    if [ "$usage" -gt 90 ]; then
        echo -e "${RED}✗ Espace disque critique : $usage% utilisé${NC}"
        exit 1
    else
        echo -e "${GREEN}✓ Espace disque suffisant : $usage% utilisé${NC}"
    fi
}

echo -e "\n${GREEN}✅ Toutes les vérifications sont terminées avec succès !${NC}"
