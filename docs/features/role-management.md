# Système de Gestion des Rôles et Permissions

## Vue d'ensemble
Le système de gestion des rôles et permissions de MaBoussole CRM permet une gestion fine des accès utilisateurs avec une hiérarchie claire des rôles. Il utilise le package Spatie Laravel-permission pour une gestion robuste et flexible des autorisations.

## Spécifications Techniques
- **Module**: Auth/Roles
- **Dépendances**: 
  - spatie/laravel-permission: ^5.10
  - filament/filament: ^3.0
- **Version**: 1.0.0

## Implémentation

### Classes principales

#### Middleware d'Initialisation
```php
// app/Http/Middleware/FilamentInitializationMiddleware.php
class FilamentInitializationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifie l'existence d'un super admin
        // Redirige vers l'initialisation si nécessaire
    }
}
```

#### Seeder des Rôles
```php
// database/seeders/RolesAndPermissionsSeeder.php
class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Création des permissions système
        $systemPermissions = [
            'system.settings.view',
            'system.settings.edit',
            // ...
        ];

        // Création des rôles avec leurs permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $manager = Role::create(['name' => 'manager']);
        $conseiller = Role::create(['name' => 'conseiller']);
        // ...
    }
}
```

### Base de données
- **Tables**: 
  - `roles`: Stockage des rôles
  - `permissions`: Stockage des permissions
  - `model_has_roles`: Association utilisateurs-rôles
  - `role_has_permissions`: Association rôles-permissions

- **Relations**:
  ```mermaid
  erDiagram
    USERS ||--o{ MODEL_HAS_ROLES : has
    ROLES ||--o{ MODEL_HAS_ROLES : belongs_to
    ROLES ||--o{ ROLE_HAS_PERMISSIONS : has
    PERMISSIONS ||--o{ ROLE_HAS_PERMISSIONS : belongs_to
  ```

### Points d'API
- **Endpoints**: 
  - `GET /admin/users`: Liste des utilisateurs avec leurs rôles
  - `POST /admin/users/{user}/roles`: Attribution de rôles
  - `GET /admin/roles`: Gestion des rôles
  - `POST /system/initialization`: Création du super admin initial

### Hiérarchie des Rôles

1. **Super Admin**
   - Accès complet au système
   - Peut gérer tous les aspects de l'application
   - Seul à pouvoir gérer les rôles et permissions

2. **Manager**
   - Gestion des utilisateurs
   - Attribution des tâches
   - Accès aux rapports et statistiques

3. **Conseiller**
   - Gestion des prospects et clients
   - Création d'activités
   - Accès limité aux rapports

4. **Partenaire**
   - Création de prospects
   - Upload de documents
   - Accès aux rapports personnels

5. **Commercial**
   - Création de prospects
   - Suivi des activités
   - Vue des bonus personnels

## Tests
- [x] Tests unitaires des permissions
  ```php
  public function test_super_admin_has_all_permissions()
  public function test_manager_cannot_modify_roles()
  public function test_conseiller_can_manage_prospects()
  ```
- [x] Tests d'intégration du middleware
- [x] Tests de bout en bout de l'initialisation

## Maintenance

### Points d'attention
- Vérifier régulièrement les permissions en cascade
- Maintenir la cohérence lors de l'ajout de nouvelles fonctionnalités
- Auditer périodiquement les attributions de rôles

### Logs et Monitoring
- Journalisation des modifications de rôles
- Alertes sur les tentatives d'accès non autorisées
- Rapport hebdomadaire des changements de permissions

## Évolutions Futures
- [ ] Ajout de rôles personnalisés
- [ ] Interface de gestion des permissions plus granulaire
- [ ] Système d'audit avancé des modifications de rôles
