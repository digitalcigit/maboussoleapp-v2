# Structure des Permissions

## Architecture

Le système utilise Filament pour la gestion des panels d'administration. La vérification des permissions se fait à plusieurs niveaux :

1. **Niveau Panel (Filament)**
   - Classe : `App\Models\User`
   - Méthode : `canAccessPanel(Panel $panel)`
   - Vérifie si l'utilisateur a un rôle autorisé pour accéder au panel
   - Rôles autorisés : `super-admin`, `manager`, `conseiller`, `prospect`

2. **Niveau Ressource (Policies)**
   - Dossier : `app/Policies/`
   - Vérifie les permissions spécifiques pour chaque action (view, create, edit, delete)
   - Utilise la méthode `hasPermissionTo()` de Spatie Permissions

## Format des Permissions

Le système utilise deux formats de permissions :

1. **Format avec points** (nouveau format)
   ```php
   'admin.panel.access'
   'system.settings.view'
   'users.create'
   'prospects.view'
   ```

2. **Format avec underscore** (ancien format)
   ```php
   'view_admin_panel'
   'create_prospects'
   'edit_prospects'
   ```

## Problèmes Identifiés

1. **Incohérence des formats** : Certaines policies utilisent encore l'ancien format avec underscore
2. **Duplication des permissions** : Certaines permissions existent dans les deux formats
3. **Seeders multiples** : Plusieurs seeders définissent les mêmes permissions avec des formats différents

## Solution Proposée

1. **Standardisation du format**
   - Utiliser uniquement le format avec points : `resource.action`
   - Exemple : `prospects.create` au lieu de `create_prospects`

2. **Migration des permissions**
   - Mettre à jour toutes les policies pour utiliser le nouveau format
   - Mettre à jour les seeders pour n'utiliser qu'un seul format
   - Créer une migration pour renommer les anciennes permissions

3. **Documentation des permissions**
   - Maintenir une liste centralisée des permissions dans la documentation
   - Documenter le format standard pour les nouvelles permissions

## Tests

Les tests existants (`tests/Feature/RoleManagementTest.php`) vérifient déjà la bonne attribution des permissions. Il faudra :

1. Mettre à jour les tests pour utiliser le nouveau format
2. Ajouter des tests pour vérifier la cohérence des formats de permission
3. Vérifier que les anciennes permissions sont correctement migrées

## Prochaines Étapes

1. Créer une migration pour standardiser les noms de permissions
2. Mettre à jour les policies une par une
3. Mettre à jour les seeders pour n'utiliser que le nouveau format
4. Mettre à jour les tests
