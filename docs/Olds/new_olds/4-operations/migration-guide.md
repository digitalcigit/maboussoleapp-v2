# Guide de Migration - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

Ce guide détaille les étapes nécessaires pour migrer le projet MaBoussole CRM v2 vers un nouvel environnement de développement.

## Prérequis Système

### Versions Requises
- PHP 8.2.26 ou supérieur
- Laravel 10.48.25
- MySQL 8.0 ou MariaDB 10.5+
- Node.js 18+ et npm
- Git

### Extensions PHP Requises
```bash
- php8.2-cli
- php8.2-common
- php8.2-curl
- php8.2-mbstring
- php8.2-mysql
- php8.2-xml
- php8.2-zip
- php8.2-bcmath
- php8.2-gd
```

## Étapes de Migration

### 1. Préparation de l'Environnement Source

1. **Sauvegarde de la Base de Données**
```bash
# Export de la structure
php artisan schema:dump

# Export des données (si nécessaire)
php artisan db:seed --class=ProductionDataSeeder
```

2. **Vérification des Dépendances**
```bash
# Liste des packages composer
composer show

# Liste des packages npm
npm list
```

3. **Sauvegarde des Fichiers Sensibles**
- `.env` (sans les secrets)
- Clés d'API
- Certificats SSL
- Configurations personnalisées

### 2. Installation sur le Nouvel Environnement

1. **Clonage du Projet**
```bash
git clone https://github.com/digitalcigit/maboussoleapp-v2.git
cd maboussoleapp-v2
```

2. **Configuration de l'Environnement**
```bash
# Copie du fichier d'environnement
cp .env.example .env

# Installation des dépendances
composer install
npm install

# Génération de la clé d'application
php artisan key:generate
```

3. **Configuration de la Base de Données**
```bash
# Dans .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maboussole_v2
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Migration et seeding
php artisan migrate
php artisan db:seed
```

4. **Permissions des Fichiers**
```bash
# Définition des permissions
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

5. **Compilation des Assets**
```bash
npm run build
```

### 3. Configuration de Windsurf/Cascade

1. **Prérequis Windsurf**
- Installation de Windsurf IDE
- Configuration de l'environnement Cascade

2. **Configuration du Projet**
```bash
# Structure des dossiers Cascade
docs/
├── .cascade/
│   └── templates/
├── 1-project/
├── 2-development/
├── 3-features/
├── 4-operations/
└── 5-contributing/
```

3. **Vérification de l'Intégration**
- Test de l'autocomplétion
- Accès à la documentation
- Fonctionnement des outils Cascade

### 4. Vérification Post-Migration

1. **Tests Automatisés**
```bash
php artisan test
```

2. **Vérifications Manuelles**
- [ ] Accès à l'interface d'administration Filament
- [ ] Fonctionnement des authentifications
- [ ] Gestion des prospects et clients
- [ ] Système de notifications
- [ ] Génération des rapports

3. **Performance et Sécurité**
```bash
# Optimisation
php artisan optimize
php artisan view:cache
php artisan route:cache

# Nettoyage
php artisan cache:clear
php artisan config:clear
```

## Résolution des Problèmes Courants

### Erreurs de Permissions
```bash
# Réinitialisation des permissions
chmod -R 775 storage bootstrap/cache
chmod -R 775 public/uploads  # si utilisé
```

### Erreurs de Cache
```bash
# Nettoyage complet
php artisan optimize:clear
composer dump-autoload
```

### Erreurs de Base de Données
```bash
# Réinitialisation de la base de données
php artisan migrate:fresh
php artisan db:seed
```

## Maintenance Post-Migration

### Tâches Quotidiennes
- Vérification des logs (`storage/logs/laravel.log`)
- Surveillance des performances
- Sauvegarde des données

### Tâches Hebdomadaires
- Mise à jour des dépendances
- Vérification des sauvegardes
- Tests de régression

### Tâches Mensuelles
- Audit de sécurité
- Nettoyage des données temporaires
- Mise à jour de la documentation

## Support et Ressources

### Documentation Officielle
- [Laravel 10.x](https://laravel.com/docs/10.x)
- [Filament](https://filamentphp.com/docs)
- [Windsurf/Cascade](https://windsurf.io)

### Contact Support
- Support technique : support@digitalcigit.com
- GitHub : https://github.com/digitalcigit/maboussoleapp-v2

---
*Documentation générée pour MaBoussole CRM v2*
