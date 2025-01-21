# Guide de Débogage - Problèmes liés aux Prospects

## Synchronisation Prospect-Dossier

### Symptôme
Les modifications des informations du prospect dans le formulaire de dossier ne sont pas reflétées dans la base de données ou l'interface.

### Causes Possibles
1. Hook `afterSave` non déclenché
2. Données du prospect non incluses dans `$this->data`
3. ID du prospect incorrect ou manquant
4. Permissions insuffisantes pour la mise à jour

### Solutions
1. Vérifier la présence et le format des données dans `prospect_info`
2. Confirmer que le prospect existe dans la base de données
3. Vérifier les logs pour les erreurs de mise à jour
4. Tester les permissions de l'utilisateur

### Vérifications
```php
// Dans EditDossier.php
dd($this->data['prospect_info']); // Vérifier les données
dd($this->record->prospect_id);   // Vérifier l'ID du prospect
```

### Logs à Consulter
- `/storage/logs/debug.log` pour les erreurs PHP
- Logs de la base de données pour les erreurs SQL

## Problèmes d'Affichage

### Symptôme
Les informations mises à jour ne s'affichent pas correctement dans l'interface.

### Causes Possibles
1. Cache du navigateur
2. Cache de Filament
3. Problèmes de rechargement des composants

### Solutions
1. Vider le cache du navigateur
2. Rafraîchir la page
3. Vérifier les événements de mise à jour Livewire
