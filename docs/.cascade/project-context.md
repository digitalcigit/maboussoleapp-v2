# MaBoussole CRM v2 - Contexte du Projet pour Cascade AI

## Vue d'Ensemble
MaBoussole CRM v2 est un système de gestion de la relation client (CRM) développé avec Laravel et Filament. Le projet est conçu pour gérer les prospects, les clients et leurs activités associées.

## Structure Technique
- **Framework** : Laravel 10.x
- **Admin Panel** : Filament
- **Gestion des Rôles** : Spatie Laravel-Permission
- **Base de Données** : MySQL

## Points d'Attention pour Cascade
1. **Gestion des Migrations**
   - Présence de migrations en double pour les tables : activities, prospects, clients
   - Nécessité de nettoyer et consolider ces migrations

2. **Seeders Critiques**
   - RolesAndPermissionsSeeder : Configuration initiale des rôles
   - TestDataSeeder : Données de test pour le développement
   - AdminUserSeeder : Création des utilisateurs administrateurs

3. **Modèles Principaux**
   - Client
   - Prospect
   - Activity
   - User

4. **États et Transitions**
   - Les prospects ont des statuts spécifiques (nouveau, qualifié, converti)
   - Les activités ont des types et statuts définis

## Historique des Problèmes Connus
1. Problèmes avec le TestDataSeeder et la reconnaissance des classes
2. Questions sur la gestion des rôles et permissions
3. Conflits dans les migrations de la table activities

## Conventions de Code
1. Utilisation des constantes pour les statuts et types
2. Validation des données via les Form Requests
3. Utilisation des Resources Filament pour l'interface admin

## Points d'Amélioration
1. Nettoyage des migrations en double
2. Consolidation des seeders
3. Documentation des API
4. Tests automatisés

## Commandes Utiles
```bash
# Réinitialisation de la base de données
php artisan migrate:fresh --seed

# Mise à jour des autoloaders
composer dump-autoload

# Nettoyage du cache
php artisan config:clear
php artisan cache:clear
```

## Structure des Dossiers Clés
```
app/
├── Filament/         # Resources et Pages Filament
├── Models/           # Modèles Eloquent
├── Services/         # Logique métier
└── Http/
    ├── Controllers/  # Controllers
    └── Requests/     # Form Requests

database/
├── migrations/       # Migrations
└── seeders/         # Seeders

docs/
├── .cascade/        # Documentation spécifique pour Cascade
├── technical/       # Documentation technique
├── features/       # Documentation des fonctionnalités
└── decisions/      # Journal des décisions (ADR)
```

## Notes pour les Futures Interactions
1. Toujours vérifier l'état des migrations avant toute modification
2. Porter une attention particulière aux seeders lors des modifications de schéma
3. Suivre les conventions de nommage établies
4. Documenter les décisions importantes dans le dossier decisions/
