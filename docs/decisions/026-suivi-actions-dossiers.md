# ADR 20 : Implémentation du Suivi des Actions sur les Dossiers

Date : 20/01/2025
Statut : Accepté
Décideurs : Équipe de développement

## Contexte
Le besoin de suivre l'activité sur les dossiers est devenu important pour permettre aux utilisateurs de :
- Identifier rapidement les dossiers récemment modifiés
- Prioriser les dossiers nécessitant une attention
- Maintenir une trace chronologique des modifications

## Décision
Nous avons décidé d'implémenter un système de suivi automatique des actions via un champ `last_action_at` qui est mis à jour automatiquement lors de toute modification significative d'un dossier.

### Points clés de l'implémentation
1. Ajout du champ `last_action_at` dans la table `dossiers`
2. Mise à jour automatique lors des modifications individuelles
3. Mise à jour lors des actions en masse
4. Mise à jour lors de la progression d'étape
5. Affichage dans une colonne dédiée avec tri par défaut

## Conséquences

### Positives
- Meilleure visibilité de l'activité sur les dossiers
- Facilité de suivi des modifications récentes
- Possibilité de trier par dernière action
- Automatisation complète du suivi

### Négatives
- Légère augmentation de la complexité du code
- Impact mineur sur les performances (index supplémentaire)

### Neutres
- Nécessité de maintenir la cohérence des mises à jour automatiques

## Notes d'implémentation
- Le champ est nullable pour permettre une migration progressive
- L'index sur `last_action_at` optimise les performances de tri
- Le format d'affichage est standardisé (dd/mm/yyyy HH:mm)
