# Guide de Dépannage des Permissions

## Problèmes Courants

### 1. Permission Manquante
```
There is no permission named 'xxx' for guard 'web'
```
**Solution :**
- Vérifier que la permission existe dans le RoleSeeder
- Vérifier que le nom correspond exactement à celui utilisé dans la Policy
- Réexécuter le seeder : `php artisan db:seed --class=RoleSeeder`

### 2. Accès Refusé au Panel Admin
```
User does not have permission to 'view_admin_panel'
```
**Solution :**
- Vérifier que le rôle de l'utilisateur inclut 'view_admin_panel'
- Vérifier que la permission est correctement synchronisée

### 3. Perte des Permissions Super-admin
**Solution :**
- Vérifier que le super-admin reçoit toutes les permissions via :
```php
$superAdmin->syncPermissions(Permission::all());
```
- Ne pas supprimer manuellement les permissions sans les réattribuer

## Vérification des Permissions

### Via Tinker
```bash
php artisan tinker
```
```php
// Vérifier les permissions d'un utilisateur
$user = User::find(1);
$user->getAllPermissions();

// Vérifier un rôle spécifique
$role = Role::findByName('manager');
$role->permissions;
```

### Dans les Logs
- Vérifier `/storage/logs/debug.log` pour les erreurs de permission
- Rechercher les messages "Permission does not exist" ou "Access denied"

## Bonnes Pratiques
1. Toujours utiliser les mêmes noms de permissions dans :
   - Les Policies
   - Le RoleSeeder
   - Les vérifications hasPermissionTo()
2. Éviter de supprimer directement les permissions de la base de données
3. Utiliser le seeder pour les modifications de permissions
4. Documenter les changements de permissions dans les ADRs
