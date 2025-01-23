# ADR 017 : Harmonisation des Permissions et Correction des Accès

Date : 20/01/2025
Statut : Accepté
Décideurs : Équipe de développement

## Contexte
Suite à la mise en place des permissions spécifiques pour les dossiers, plusieurs problèmes ont été identifiés :
1. Incohérence entre les noms des permissions dans le code et dans la base de données
2. Perte des permissions du super-admin lors de la réinitialisation
3. Absence de la permission 'view_admin_panel' nécessaire pour l'accès de base

## Décision
Nous avons décidé de :
1. Harmoniser les noms des permissions entre le code et la base de données
2. Assurer que le super-admin conserve toutes les permissions
3. Standardiser la structure des permissions pour tous les modules

### Standardisation des Permissions
- Permissions d'administration : 'view_admin_panel'
- Permissions Prospects : 'create_prospects', 'edit_prospects', 'delete_prospects'
- Permissions Dossiers : 'dossiers.view', 'dossiers.create', 'dossiers.edit', 'dossiers.delete'
- Autres permissions conservées dans leur format actuel

### Attribution par Rôle
- Super-admin : Toutes les permissions (via syncPermissions(Permission::all()))
- Manager : Toutes les permissions opérationnelles
- Conseiller : Permissions limitées aux opérations de base
- Commercial : Permissions axées sur les prospects
- Partenaire : Permissions minimales pour la création de prospects

## Conséquences

### Positives
- Cohérence globale du système de permissions
- Meilleure maintenabilité du code
- Clarté dans l'attribution des permissions par rôle

### Négatives
- Nécessité de réexécuter le seeder lors des modifications
- Migration requise pour les installations existantes

### Notes Techniques
- Utilisation de firstOrCreate pour la gestion des rôles existants
- Synchronisation des permissions via syncPermissions
- Vérification systématique via les Policies
