# Guide de dépannage - Gestion des Rôles

## Problèmes courants

### 1. Rôles masqués apparaissant dans le formulaire
**Solution :** 
- Vérifier que la méthode options() dans UserResource.php exclut correctement les rôles :
  - 'prospect'
  - 'portail_candidat'
- Vider le cache des permissions : `php artisan cache:clear`

### 2. Permissions manquantes pour l'apporteur d'affaire
**Solution :**
- Vérifier que la migration a été exécutée
- Exécuter : `php artisan permission:cache-reset`
- Vérifier dans la base de données que les permissions sont correctement assignées

### 3. Conflits de permissions
**Solution :**
- Vérifier les rôles multiples d'un utilisateur
- S'assurer que les Gates et Policies sont correctement définies
- Utiliser la commande `php artisan permission:show` pour diagnostiquer

## Vérification rapide
1. `php artisan tinker`
2. `\Spatie\Permission\Models\Role::pluck('name')`
3. `\Spatie\Permission\Models\Permission::pluck('name')`

## Commandes utiles
```bash
# Réinitialiser le cache des permissions
php artisan permission:cache-reset

# Afficher toutes les permissions
php artisan permission:show

# Vérifier l'intégrité des rôles
php artisan permission:check-hierarchy
```
