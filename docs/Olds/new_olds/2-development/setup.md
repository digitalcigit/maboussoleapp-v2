# Guide d'Installation - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Ce guide détaille les étapes nécessaires pour mettre en place un environnement de développement pour MaBoussole CRM v2.

## Prérequis

### Système
- PHP 8.2 ou supérieur
- Composer 2.x
- Node.js 16.x ou supérieur
- MySQL 8.0 ou supérieur
- Git

### Extensions PHP Requises
```bash
- pdo_mysql
- mbstring
- xml
- curl
- gd
- zip
```

## Installation

### 1. Clonage du Projet
```bash
git clone [repository-url]
cd maboussoleapp-v2
```

### 2. Dépendances PHP
```bash
composer install
```

### 3. Dépendances JavaScript
```bash
npm install
```

### 4. Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Base de Données
```bash
# Configuration .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maboussole_crm
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Migration
php artisan migrate --seed
```

## Environnements

### Développement
```bash
# Terminal 1 - Backend
php artisan serve

# Terminal 2 - Frontend
npm run dev
```

### Test
```bash
# Configuration
cp .env .env.testing
# Modifier DB_DATABASE=maboussole_crm_testing

# Exécution des tests
php artisan test --env=testing
```

### Production
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Vérification de l'Installation

### 1. Serveur Web
- http://localhost:8000 (Laravel)
- http://localhost:3000 (Vite dev server)

### 2. Base de Données
```bash
php artisan migrate:status
```

### 3. Tests
```bash
php artisan test
```

## Résolution des Problèmes Courants

### 1. Erreurs de Permission
```bash
# Storage et Cache
chmod -R 775 storage bootstrap/cache
```

### 2. Erreurs de Composer
```bash
composer dump-autoload
php artisan clear-compiled
```

### 3. Erreurs de Base de Données
```bash
php artisan migrate:fresh --seed
```

## Maintenance

### Mise à Jour des Dépendances
```bash
composer update
npm update
```

### Nettoyage
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

## Support

### Logs
- `storage/logs/laravel.log`
- `storage/logs/filament.log`

### Debug
```bash
php artisan about
php artisan env
```

---
*Documentation générée pour MaBoussole CRM v2*
