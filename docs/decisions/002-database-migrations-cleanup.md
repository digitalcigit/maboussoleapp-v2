# ADR 002 : Nettoyage des Migrations et Seeders

## Contexte
- Date : 23 décembre 2023
- État : En cours

Le projet présente actuellement plusieurs problèmes liés aux migrations et aux seeders :
1. Migrations en double pour les tables `activities`, `prospects` et `clients`
2. Problèmes avec le `TestDataSeeder` qui n'est pas reconnu
3. Complexité croissante dans la gestion des états et transitions

## Décision

### 1. Consolidation des Migrations
Nous allons :
- Identifier et supprimer les migrations redondantes
- Consolider les modifications dans des migrations uniques par table
- Maintenir un ordre logique des dépendances

### 2. Restructuration des Seeders
Nous allons :
- Vérifier et corriger les namespaces
- Assurer la cohérence avec les modèles
- Améliorer la gestion des données de test

## Plan d'Action

### Phase 1 : Audit
1. Lister toutes les migrations par table
2. Identifier les dépendances entre les tables
3. Documenter les colonnes et leurs modifications

### Phase 2 : Consolidation
1. Créer de nouvelles migrations consolidées
2. Tester les migrations sur une base propre
3. Vérifier la compatibilité avec les seeders

### Phase 3 : Nettoyage
1. Supprimer les migrations obsolètes
2. Mettre à jour les seeders
3. Documenter les changements

## Conséquences

### Positives
- Structure de base de données plus claire
- Réduction des conflits potentiels
- Meilleure maintenabilité

### Négatives
- Nécessite une réinitialisation complète de la base de données
- Risque de perte de données en production si mal géré

### Atténuation des Risques
1. Sauvegarder toutes les données avant modifications
2. Tester exhaustivement sur un environnement de développement
3. Préparer un plan de rollback

## Notes de Suivi
- [ ] Audit des migrations existantes
- [ ] Création des nouvelles migrations consolidées
- [ ] Tests de migration complète
- [ ] Mise à jour des seeders
- [ ] Documentation des changements
- [ ] Validation finale
