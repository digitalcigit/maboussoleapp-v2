# Guide de Débogage - Gestion des Documents

## Suppression de Documents

### Symptôme
La boîte de dialogue de confirmation n'apparaît pas lors de la suppression d'un document.

### Causes Possibles
1. JavaScript désactivé dans le navigateur
2. Conflit avec d'autres composants Filament
3. Cache du navigateur

### Solutions
1. Vérifier que JavaScript est activé
2. Vider le cache du navigateur
3. Recharger la page

### Vérifications
```php
// Dans DossierResource.php
dd($component->getDeleteAction()); // Vérifier la configuration de l'action
```

### Logs à Consulter
- Console du navigateur pour les erreurs JavaScript
- `/storage/logs/laravel.log` pour les erreurs PHP

## Bonnes Pratiques
1. Toujours utiliser la confirmation pour les actions destructives
2. Vérifier les messages de confirmation
3. Tester les annulations de suppression
4. Sauvegarder les modifications importantes
