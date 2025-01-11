# Dashboard Conseiller

## Vue d'ensemble
Le tableau de bord conseiller offre une interface dédiée permettant aux conseillers de suivre leurs prospects et tâches quotidiennes.

## Fonctionnalités

### 1. KPIs Principaux
- Nombre de prospects actifs
- Taux de conversion personnel
- Nombre de prospects à contacter

### 2. Visualisations
- Graphique des conversions (6 derniers mois)
- État des prospects (Doughnut chart)
- Liste des prospects prioritaires
- Liste des tâches du jour

## Implémentation Technique

### Structure
```
app/Filament/Pages/
└── ConseillerDashboard.php
    ├── ConseillerStatsWidget
    ├── ConseillerConversionChart
    ├── ConseillerProspectStatusChart
    ├── ConseillerPriorityProspectsWidget
    └── ConseillerTasksWidget
```

### Widgets
1. **ConseillerStatsWidget**
   - KPIs en temps réel
   - Tendances et comparaisons

2. **ConseillerConversionChart**
   - Graphique linéaire
   - Données sur 6 mois

3. **ConseillerProspectStatusChart**
   - Graphique circulaire
   - Distribution des statuts

4. **ConseillerPriorityProspectsWidget**
   - Table des prospects prioritaires
   - Tri et filtrage

5. **ConseillerTasksWidget**
   - Tâches du jour
   - Tri par échéance

## Sécurité
- Accès limité au rôle 'conseiller'
- Filtrage automatique par conseiller_id
- Validation des permissions

## Performance
- Requêtes optimisées
- Cache des widgets
- Lazy loading des graphiques

## Tests
- Tests unitaires pour chaque widget
- Tests d'intégration pour le dashboard
- Tests de performance

## Documentation Associée
- ADR-009 : Décision d'implémentation
- DASHBOARD_SPECS.md : Spécifications initiales
- PROCESS_QUALITE.md : Cas d'étude
