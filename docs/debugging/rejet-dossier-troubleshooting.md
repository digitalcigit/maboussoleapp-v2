# Guide de débogage - Rejet de dossier

## Problèmes courants et solutions

### 1. La modal ne s'affiche pas

**Symptômes** :
- Le bouton "Rejeter le dossier" ne répond pas
- La modal ne s'ouvre pas

**Solutions** :
1. Vérifier que le statut du dossier permet le rejet
2. Vérifier les permissions de l'utilisateur
3. Consulter la console du navigateur pour les erreurs JavaScript

### 2. Problèmes avec l'éditeur Markdown

**Symptômes** :
- Les boutons de mise en forme ne fonctionnent pas
- La prévisualisation ne s'actualise pas

**Solutions** :
1. Vérifier que le state de l'éditeur est correctement mis à jour
2. Vérifier que la fonction afterStateUpdated est appelée
3. Inspecter la réponse du serveur pour la prévisualisation

### 3. Erreurs lors de la sauvegarde

**Symptômes** :
- Message d'erreur à la validation
- Le statut du dossier n'est pas mis à jour

**Solutions** :
1. Vérifier que tous les champs requis sont remplis
2. Vérifier les logs Laravel pour les erreurs SQL
3. S'assurer que l'utilisateur a les permissions nécessaires

## Logs à consulter

1. Logs Laravel : `/storage/logs/laravel.log`
2. Logs de débogage : `/storage/logs/debug.log`

## Requêtes SQL utiles

```sql
-- Vérifier les rapports d'un dossier
SELECT * FROM dossier_rejection_reports WHERE dossier_id = X;

-- Vérifier les permissions d'un utilisateur
SELECT * FROM model_has_permissions WHERE model_id = X;
```

## Points de vérification

1. Table `dossier_rejection_reports` :
   - Structure correcte
   - Contraintes de clé étrangère
   - Indexes

2. Composants Filament :
   - Action correctement enregistrée
   - Formulaire bien configuré
   - Notifications fonctionnelles

3. Statuts du dossier :
   - Transition correcte vers STATUS_SUBMISSION_REJECTED
   - Mise à jour de last_action_at
