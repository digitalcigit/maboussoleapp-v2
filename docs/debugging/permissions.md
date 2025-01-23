# Guide de débogage des permissions

## Erreur : Permission inexistante

### Problème
Erreur : `There is no permission named 'xxx' for guard 'web'`

Cette erreur survient lorsqu'il y a une incohérence entre les noms des permissions utilisées dans le code et celles définies dans les seeders.

### Cause
Les permissions peuvent être définies avec différents formats de nommage :
- Format avec point : `resource.action` (ex: `dossiers.view`)
- Format avec underscore : `action_resource` (ex: `view_dossiers`)

### Solution
1. Vérifier la cohérence des noms de permissions dans :
   - Les seeders (`database/seeders/*.php`)
   - Les policies (`app/Policies/*.php`)
   - Les contrôleurs (`app/Http/Controllers/*.php`)
   - Les middleware de permissions

2. Standardiser le format de nommage :
   - Utiliser le format `resource.action` (ex: `dossiers.view`, `dossiers.create`)
   - Éviter le format avec underscore pour les nouvelles permissions

3. Après modification des noms de permissions :
   ```bash
   php artisan migrate:fresh --seed
   ```

### Prévention
- Toujours utiliser le format `resource.action` pour les nouvelles permissions
- Documenter le format choisi dans le guide technique
- Vérifier la cohérence des noms avant d'exécuter les migrations
