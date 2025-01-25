# Guide de Débogage - Permissions du Portail Candidat

## Problème : Erreur 403 lors de l'accès au dossier

### Symptômes
- L'utilisateur reçoit une erreur 403 en essayant d'accéder à son dossier
- Les logs montrent que la vérification des permissions échoue

### Causes Possibles

1. **Mauvais nom de rôle**
   - Le système vérifie le rôle "candidat" au lieu de "portail_candidat"
   - Solution : Utiliser le bon nom de rôle dans les Gates

2. **Relation incorrecte entre Prospect et Dossier**
   - La vérification cherche un `dossier_id` dans la table `prospects` qui n'existe pas
   - La relation correcte est via le `prospect_id` dans la table `dossiers`
   - Solution : Modifier la vérification pour utiliser la relation inverse

### Solution Appliquée
```php
Gate::define('portail-candidat.dossier.update', function ($user, $dossier) {
    $hasRole = $user->hasRole('portail_candidat');
    $hasDossier = $user->prospect && $dossier->prospect_id === $user->prospect->id;
    return $hasRole && $hasDossier;
});
```

### Points à Vérifier
1. L'utilisateur a le rôle "portail_candidat"
2. L'utilisateur a un prospect associé
3. Le prospect de l'utilisateur correspond au prospect_id du dossier

### Améliorations Futures
- Ajouter une colonne `dossier_id` à la table `prospects` pour une relation bidirectionnelle
- Mettre en place des tests automatisés pour les vérifications de permissions
