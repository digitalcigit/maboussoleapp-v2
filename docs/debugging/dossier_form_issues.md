# Guide de Débogage - Formulaire de Dossier

## Synchronisation Étape-Statut

### Symptôme
Les statuts affichés ne correspondent pas à l'étape sélectionnée ou ne se mettent pas à jour.

### Causes Possibles
1. JavaScript désactivé dans le navigateur
2. Problème de réactivité Livewire
3. Erreur dans les méthodes du modèle Dossier

### Solutions
1. Vérifier que JavaScript est activé
2. Vider le cache du navigateur
3. Vérifier les logs Livewire

### Vérifications
```php
// Dans DossierResource.php
dd($get('current_step')); // Vérifier l'étape sélectionnée
dd(Dossier::getValidStatusesForStep($step)); // Vérifier les statuts retournés
```

### Logs à Consulter
- Console du navigateur pour les erreurs JavaScript
- `/storage/logs/laravel.log` pour les erreurs PHP
- Logs Livewire pour les erreurs de composant

## Problèmes de Validation

### Symptôme
Le formulaire accepte des combinaisons étape-statut invalides.

### Causes Possibles
1. Problème dans getValidStatusesForStep()
2. Cache de formulaire
3. Manipulation directe des valeurs

### Solutions
1. Vérifier l'implémentation de getValidStatusesForStep()
2. Forcer le rechargement du formulaire
3. Ajouter des règles de validation supplémentaires

## Bonnes Pratiques
1. Toujours utiliser les constantes du modèle Dossier
2. Vérifier la cohérence étape-statut
3. Utiliser les méthodes de validation Filament
4. Logger les modifications importantes
