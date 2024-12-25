# ADR-002: Nettoyage et Consolidation des Migrations de Base de Données

## Contexte
- Date: 2024-12-23
- Statut: Accepté
- Décideurs: Équipe de développement

## État des lieux
Les migrations de base de données se sont accumulées au fil du développement, créant des dépendances complexes et rendant difficile la maintenance de la structure de la base de données.

## Décision
Nous avons décidé de consolider les migrations existantes pour :
1. Réduire le nombre total de fichiers de migration
2. Améliorer la gestion des dépendances entre tables
3. Maintenir une cohérence dans la structure de la base de données

## Changements spécifiques
1. Consolidation des migrations liées aux modèles principaux
2. Implémentation systématique des soft deletes
3. Standardisation des champs de statut en français
4. Optimisation des relations polymorphiques pour les activités

## Conséquences
### Positives
- Meilleure maintenabilité du schéma de base de données
- Réduction des problèmes de dépendances
- Structure plus cohérente

### Négatives
- Nécessité de recréer la base de données pour les environnements existants
- Période d'adaptation pour l'équipe

## Notes d'implémentation
- Tous les modèles principaux utilisent maintenant les soft deletes
- Les statuts sont définis comme constantes en français dans les modèles
- Les relations polymorphiques sont utilisées pour les activités

## Liens
- Issue liée: #N/A
- PR: #N/A
- Documentation: /docs/context/technical-debt.md
