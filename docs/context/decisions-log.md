# Journal des Décisions Techniques - MaBoussole CRM v2

## 2024-12-25 - Documentation d'Apprentissage
### Contexte
- Besoin de documentation pour les nouveaux développeurs
- Code existant déjà structuré
- Approche IA-first pour le développement

### Décisions
1. **Documentation Existant**
   - Analyser le code existant avant toute génération
   - Créer une documentation d'apprentissage basée sur l'existant
   - Respecter la structure et les patterns actuels

2. **Processus Documentation**
   - Examiner chaque composant existant
   - Documenter la structure actuelle
   - Ajouter des explications pédagogiques
   - Lier avec les concepts Laravel/Filament

### Impact
- Meilleure cohérence du projet
- Documentation alignée avec le code réel
- Formation plus efficace des nouveaux développeurs

## 2024-12-24 - Restructuration Base de Données
### Contexte
- Besoin de cohérence dans les statuts en français
- Problèmes avec l'ordre des migrations
- Duplication dans les seeders

### Décisions
1. **Modèles**
   - Constantes de statut en français
   - Méthodes de traduction intégrées
   - Relations polymorphiques pour les activités

2. **Migrations**
   - Consolidation en fichiers uniques par table
   - Réorganisation de l'ordre d'exécution
   - Ajout de la colonne description aux permissions

3. **Seeders**
   - Fusion des seeders de rôles
   - Mise à jour du TestDataSeeder
   - Données de test plus réalistes

### Impact
- Meilleure cohérence de la base de données
- Simplification des migrations
- Documentation plus claire

## 2024-12-23 - Nettoyage des Migrations
### Contexte
- Migrations en double
- Problèmes de dépendances
- Incohérences dans les seeders

### Décisions
1. **Consolidation**
   - Une migration principale par table
   - Ordre logique des dépendances
   - Nommage standardisé

2. **Structure**
   - Utilisation systématique des soft deletes
   - Standardisation des timestamps
   - Gestion cohérente des clés étrangères

### Impact
- Installation plus fiable
- Maintenance simplifiée
- Tests plus stables

## 2024-12-22 - Système de Rôles
### Contexte
- Besoin de gestion fine des permissions
- Rôles spécifiques au métier
- Documentation en français

### Décisions
1. **Architecture**
   - Utilisation de Spatie Laravel-Permission
   - Rôles métier définis
   - Permissions granulaires

2. **Implémentation**
   - Description française des permissions
   - Héritage de rôles
   - Tests automatisés

### Impact
- Sécurité renforcée
- Flexibilité accrue
- Maintenance simplifiée

## 2025-01-05 - Correction de la Gestion des Activités
### Contexte
- Problèmes avec la création d'activités pour les prospects
- Relations polymorphiques mal configurées
- Duplication dans les relations

### Décisions
1. **Relations**
   - Utilisation de la relation `assignedTo` au lieu de `user`
   - Suppression de la mutation manuelle des données
   - Utilisation des relations polymorphiques natives de Laravel

2. **Implémentation**
   - Suppression du doublon dans `getRelations()`
   - Configuration correcte du RelationManager
   - Documentation complète de la fonctionnalité

### Impact
- Création d'activités fonctionnelle
- Code plus propre et maintenable
- Documentation technique détaillée
