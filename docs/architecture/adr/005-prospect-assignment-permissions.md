# ADR 005: Restriction des permissions d'assignation des prospects

## Date
2025-01-11

## Statut
Accepté

## Contexte
Le système permettait à tous les utilisateurs ayant accès aux prospects de les assigner à n'importe quel autre utilisateur. Cette liberté posait des problèmes de sécurité et de hiérarchie organisationnelle.

## Décision
Nous avons décidé de :
1. Restreindre les permissions d'assignation aux managers et super-admins uniquement
2. Implémenter un système d'auto-assignation pour les conseillers lors de la création de prospects
3. Mettre en place un système de traçabilité des changements d'assignation
4. Implémenter des notifications en base de données pour informer les utilisateurs des nouvelles assignations

### Modifications techniques
- Mise à jour de `ProspectPolicy` avec des règles d'assignation strictes
- Création du trait `TracksAssignmentChanges` pour la traçabilité
- Implémentation de `ProspectAssigned` notification
- Adaptation du formulaire Filament pour respecter ces restrictions

## Conséquences
### Positives
- Meilleure sécurité et contrôle des assignations
- Respect de la hiérarchie organisationnelle
- Traçabilité complète des changements d'assignation
- Notifications automatiques aux utilisateurs concernés

### Négatives
- Processus d'assignation plus rigide
- Nécessité de passer par un manager pour les réassignations

## Notes d'implémentation
- Les conseillers sont automatiquement assignés aux prospects qu'ils créent
- Les managers peuvent réassigner uniquement au sein de leur équipe
- Les super-admins ont un contrôle total sur les assignations
- Toutes les modifications d'assignation sont enregistrées dans la table `activities`
- Les notifications sont stockées en base de données (table `notifications`)
