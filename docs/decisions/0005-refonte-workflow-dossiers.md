# 5. Refonte du workflow des dossiers

Date: 2025-02-25

## État

Accepté

## Contexte

Le système de gestion des dossiers nécessitait une refonte pour mieux refléter la réalité du processus métier. Les prospects et clients ne sont plus des entités séparées mais des états d'un même dossier, déterminés par l'étape actuelle du workflow.

## Décision

Nous avons décidé de :

1. Unifier la gestion des dossiers avec un workflow en 7 étapes :
   - STEP_INITIAL (1) : Création initiale du dossier
   - STEP_DOCUMENTS (2) : Collecte des documents
   - STEP_VALIDATION (3) : Validation du dossier
   - STEP_PAYMENT (4) : Paiement des droits
   - STEP_PROCESSING (5) : Traitement du dossier
   - STEP_COMPLETED (6) : Dossier terminé
   - STEP_REJECTED (7) : Dossier rejeté

2. Définir le statut prospect/client selon l'étape :
   - Prospect : étapes 1 à 3 (INITIAL, DOCUMENTS, VALIDATION)
   - Client : étapes 4 à 6 (PAYMENT, PROCESSING, COMPLETED)

3. Simplifier les statuts à 4 états possibles :
   - STATUS_WAITING_DOCS : En attente de documents
   - STATUS_IN_PROGRESS : En cours
   - STATUS_BLOCKED : Bloqué
   - STATUS_COMPLETED : Terminé

4. Stocker toutes les informations personnelles directement dans la table dossiers

## Conséquences

### Positives

1. Workflow plus clair et linéaire
2. Meilleure traçabilité du statut prospect/client
3. Simplification de la base de données
4. Réduction de la duplication des données
5. Facilité de reporting et de statistiques

### Négatives

1. Nécessité de migrer les données existantes
2. Adaptation requise des interfaces utilisateur
3. Mise à jour nécessaire de la documentation

### Mitigations

1. Création de migrations avec vérification des colonnes existantes
2. Tests approfondis du nouveau workflow
3. Documentation détaillée des changements

## Notes

Cette refonte s'inscrit dans une démarche d'amélioration continue du système, en le rendant plus proche des processus métier réels de l'entreprise.
