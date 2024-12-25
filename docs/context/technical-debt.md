# Dette Technique - MaBoussole CRM v2

## Dette Active (24 décembre 2024)

### 1. Tests 🔴 (Priorité Haute)
#### Problème
- Tests Filament échouent avec erreurs 403/405
- Couverture de test incomplète pour les nouvelles fonctionnalités

#### Impact
- Risque de régression
- Ralentissement du développement

#### Solution Proposée
- Corriger les permissions dans les tests
- Ajouter les tests manquants
- Mettre à jour la documentation de test

### 2. Migrations 🟡 (Priorité Moyenne)
#### Problème
- Anciennes migrations non supprimées
- Quelques incohérences dans les noms de colonnes

#### Impact
- Confusion possible pour les nouveaux développeurs
- Risque d'erreurs lors des rollbacks

#### Solution Proposée
- Nettoyer les anciennes migrations
- Standardiser les noms de colonnes
- Mettre à jour la documentation

### 3. Documentation 🟡 (Priorité Moyenne)
#### Problème
- Documentation technique partiellement obsolète
- Manque de documentation sur les nouveaux statuts

#### Impact
- Onboarding plus difficile
- Risque d'erreurs d'implémentation

#### Solution Proposée
- Mettre à jour la documentation technique
- Ajouter des exemples d'utilisation
- Créer un guide de contribution

### 4. Permissions 🟢 (Priorité Basse)
#### Problème
- Quelques permissions en double
- Nommage pas toujours cohérent

#### Impact
- Légère confusion dans la gestion des droits
- Maintenance un peu plus complexe

#### Solution Proposée
- Audit complet des permissions
- Standardisation des noms
- Documentation des cas d'usage

## Dette Résolue

### 1. Structure de la Base de Données ✅
#### Problème Initial
- Incohérence dans les statuts
- Problèmes de dépendances

#### Solution Appliquée
- Restructuration complète des modèles
- Standardisation des statuts en français
- Correction de l'ordre des migrations

### 2. Seeders ✅
#### Problème Initial
- Duplication dans les seeders de rôles
- Données de test peu réalistes

#### Solution Appliquée
- Consolidation des seeders
- Amélioration des données de test
- Documentation mise à jour
