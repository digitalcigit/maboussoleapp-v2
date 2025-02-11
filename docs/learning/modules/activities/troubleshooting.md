# Guide de Dépannage - Module Activités

## Problèmes Courants

### 1. Erreur de Relation
**Problème** : Erreur "Relation must be of type Relation, null given"
**Solution** : 
- Vérifier que la relation est correctement définie dans le modèle
- S'assurer que le nom de la relation dans Filament correspond au modèle
- Exemple : Utiliser `assignedTo` au lieu de `user`

### 2. Activités Non Visibles
**Problème** : Les activités créées n'apparaissent pas dans la liste
**Causes possibles** :
- Filtres actifs
- Problèmes de permissions
- Soft delete actif

**Solutions** :
1. Vérifier les filtres appliqués
2. Contrôler les permissions de l'utilisateur
3. Vérifier la colonne `deleted_at`

### 3. Problèmes de Dates
**Problème** : Erreurs lors de la sauvegarde des dates
**Solutions** :
- Vérifier le format des dates
- S'assurer que `scheduled_at` est antérieur à `completed_at`
- Utiliser les composants DateTimePicker de Filament

## Vérifications de Base

### Base de Données
```sql
-- Vérifier les activités récentes
SELECT * FROM activities ORDER BY created_at DESC LIMIT 5;

-- Vérifier les relations
SELECT a.*, u.name as assigned_to 
FROM activities a 
JOIN users u ON a.user_id = u.id 
LIMIT 5;
```

### Permissions
1. Vérifier les rôles dans `users`
2. Contrôler les permissions dans Filament
3. Vérifier les policies Laravel

## Maintenance

### Nettoyage des Données
```php
// Supprimer les activités obsolètes
Activity::where('completed_at', '<', now()->subYears(2))->delete();

// Marquer les activités en retard
Activity::whereNull('completed_at')
    ->where('scheduled_at', '<', now())
    ->update(['status' => 'late']);
```

### Performance
- Indexer les colonnes fréquemment utilisées
- Utiliser eager loading pour les relations
- Paginer les résultats dans les listes

## Support
Pour tout autre problème :
1. Consulter les logs Laravel
2. Vérifier la configuration Filament
3. Contacter l'équipe technique
