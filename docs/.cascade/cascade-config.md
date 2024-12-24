# Configuration Guide pour Cascade AI

## Ordre de Lecture Recommandé
1. `/docs/.cascade/project-context.md` - Vue d'ensemble du projet
2. `/docs/decisions/001-role-management-system.md` - Comprendre les choix d'architecture
3. `/docs/features/role-management.md` - Détails de l'implémentation
4. `/docs/guide-laravel-debutant.md` - Guide complet du projet

## Fichiers Critiques à Surveiller
- `database/seeders/TestDataSeeder.php`
- `database/seeders/RolesAndPermissionsSeeder.php`
- `app/Models/Activity.php`
- `app/Models/Client.php`
- `app/Models/Prospect.php`

## Points de Vigilance
1. **Migrations**
   - Vérifier les doublons
   - Respecter l'ordre des dépendances

2. **Seeders**
   - Ordre d'exécution important
   - Dépendances entre les données

3. **Modèles**
   - Relations complexes
   - Constantes de statut

## Commandes de Diagnostic
```bash
# Vérifier l'état des migrations
php artisan migrate:status

# Lister les routes
php artisan route:list

# Vérifier les permissions
php artisan permission:show
```

## Références Rapides
- Documentation Laravel : https://laravel.com/docs/10.x
- Documentation Filament : https://filamentphp.com/docs
- Documentation Spatie Permissions : https://spatie.be/docs/laravel-permission
