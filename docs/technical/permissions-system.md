# Système de Permissions

## Vue d'ensemble
Le système de permissions de l'application utilise le package Spatie Laravel-Permission pour gérer les accès aux différentes fonctionnalités.

## Structure des Permissions

### Permissions Administratives
- `view_admin_panel` : Permission de base pour accéder au panel d'administration

### Permissions des Prospects
- `create_prospects` : Création de nouveaux prospects
- `edit_prospects` : Modification des prospects existants
- `delete_prospects` : Suppression des prospects
- `prospects.activities.view` : Visualisation des activités des prospects
- `prospects.activities.create` : Création d'activités pour les prospects
- `prospects.convert` : Conversion des prospects en clients

### Permissions des Dossiers
- `dossiers.view` : Visualisation des dossiers
- `dossiers.create` : Création de nouveaux dossiers
- `dossiers.edit` : Modification des dossiers existants
- `dossiers.delete` : Suppression des dossiers

## Rôles et Permissions

### Super-admin
- Accès à toutes les permissions du système
- Attribué via `syncPermissions(Permission::all())`

### Manager
- Accès complet aux fonctionnalités opérationnelles
- Gestion complète des dossiers, prospects, clients et activités
- Peut réassigner les dossiers et prospects

### Conseiller
- Accès aux fonctionnalités de base
- Peut créer et gérer ses propres dossiers
- Accès limité aux prospects et clients assignés

### Commercial
- Focus sur la gestion des prospects
- Création et modification des prospects
- Gestion des activités liées aux prospects

### Partenaire
- Permissions minimales
- Création de prospects uniquement
- Accès aux activités de base

## Implémentation

### Dans les Policies
```php
public function create(User $user): bool
{
    return $user->hasPermissionTo('create_prospects');
}
```

### Dans le Seeder
```php
$superAdmin->syncPermissions(Permission::all());
$manager->syncPermissions([
    'view_admin_panel',
    'create_prospects',
    // ...
]);
```

## Maintenance
- Utiliser le RoleSeeder pour mettre à jour les permissions
- Exécuter `php artisan db:seed --class=RoleSeeder`
- Vérifier la cohérence des noms de permissions entre les Policies et le Seeder
