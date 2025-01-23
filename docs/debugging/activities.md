# Guide de Débogage - Table Activities

## Structure des Champs Utilisateur

La table `activities` utilise plusieurs champs liés aux utilisateurs, chacun ayant un rôle spécifique :

1. **user_id**
   - Utilisateur concerné par l'activité
   - Obligatoire
   - Généralement l'utilisateur connecté

2. **created_by**
   - Utilisateur ayant créé l'enregistrement
   - Obligatoire
   - Toujours l'utilisateur connecté au moment de la création

3. **causer_id**
   - Utilisateur ayant déclenché l'action
   - Utilisé avec causer_type pour le polymorphisme
   - Dans notre cas, identique à created_by

4. **changed_by** (dans properties)
   - Stocké dans le JSON des propriétés
   - Utilisé pour tracer les modifications
   - Historique des changements

## Problèmes Courants

1. **Erreur : Field 'xxx' doesn't have a default value**
   - Vérifier que tous les champs obligatoires sont remplis
   - S'assurer que auth()->id() est disponible
   - Vérifier les migrations pour les contraintes

2. **Solution Temporaire**
   ```php
   Activity::create([
       'user_id' => auth()->id(),
       'created_by' => auth()->id(),
       'causer_id' => auth()->id(),
       'properties' => ['changed_by' => auth()->id()],
   ]);
   ```

## À Noter pour la v2

Dans la refonte prévue, nous simplifierons cette structure pour :
- Réduire la redondance des informations
- Clarifier le rôle de chaque champ
- Améliorer la traçabilité des actions
