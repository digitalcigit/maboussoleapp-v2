# Documentation MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
MaBoussole CRM v2 est une application de gestion de la relation client spécialisée dans l'accompagnement des étudiants pour leurs études à l'étranger. Cette documentation couvre tous les aspects du projet, de l'architecture technique aux guides d'utilisation.

## Structure de la Documentation

### 📋 1. Project
- [Vision et Objectifs](./vision.md)
- [Roadmap](./roadmap.md)
- [Architecture](./architecture.md)

### 🛠 2. Development
- [Guide d'Installation](../2-development/setup.md)
- [Base de Données](../2-development/database.md)
- [API](../2-development/api.md)
- [Tests](../2-development/testing.md)

### 🔧 3. Features
- [Authentification](../3-features/authentication.md)
- [Gestion des Prospects](../3-features/prospects.md)
- [Gestion des Clients](../3-features/clients.md)
- [Notifications](../3-features/notifications.md)

### 🚀 4. Operations
- [Déploiement](../4-operations/deployment.md)
- [Monitoring](../4-operations/monitoring.md)
- [Maintenance](../4-operations/maintenance.md)

### 👥 5. Contributing
- [Guide de Contribution](../5-contributing/guidelines.md)
- [Standards de Code](../5-contributing/code-style.md)
- [Bonnes Pratiques](../5-contributing/best-practices.md)

## Quick Start

### Installation
```bash
git clone [repository]
cd maboussoleapp-v2
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Configuration Base de Données
```bash
php artisan migrate --seed
```

### Lancement Local
```bash
php artisan serve
npm run dev
```

## Stack Technique
- Laravel 10.x
- Filament 3.x
- Livewire 3.x
- MySQL 8.0+
- PHP 8.1+

## Liens Utiles
- [Documentation Laravel](https://laravel.com/docs)
- [Documentation Filament](https://filamentphp.com/docs)
- [Documentation Livewire](https://livewire.laravel.com/docs)

## Support
Pour toute question ou problème :
1. Consulter la [FAQ](./faq.md)
2. Ouvrir une issue sur le repository
3. Contacter l'équipe technique

---
*Documentation générée pour MaBoussole CRM v2*
