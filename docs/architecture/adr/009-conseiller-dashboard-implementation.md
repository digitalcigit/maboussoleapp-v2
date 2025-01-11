# ADR 009 : Implémentation du Dashboard Conseiller

## Contexte
- Date : 2025-01-08
- Statut : Accepté
- Décideurs : Équipe de développement

## Problème
Nécessité d'implémenter un tableau de bord spécifique pour le rôle conseiller, offrant une vue d'ensemble de leurs prospects et tâches.

## Options Considérées

### 1. Dashboard Générique avec Conditions
- Utiliser le dashboard par défaut avec conditions
- Avantages : Simple à implémenter
- Inconvénients : Moins performant, moins spécialisé

### 2. ✅ Dashboard Dédié (Choisi)
- Créer une classe dédiée `ConseillerDashboard`
- Widgets spécifiques au rôle
- Meilleure organisation et performance

### 3. Widgets Conditionnels
- Ajouter des conditions dans chaque widget
- Avantages : Réutilisation possible
- Inconvénients : Complexité accrue, moins maintenable

## Décision
Nous avons choisi de créer un dashboard dédié avec des widgets spécifiques :

1. **Structure**
   - `ConseillerDashboard` : Page principale
   - Widgets spécialisés pour chaque fonctionnalité

2. **Widgets Implémentés**
   - `ConseillerStatsWidget` : KPIs principaux
   - `ConseillerConversionChart` : Graphique des conversions
   - `ConseillerProspectStatusChart` : État des prospects
   - `ConseillerPriorityProspectsWidget` : Liste des priorités
   - `ConseillerTasksWidget` : Tâches du jour

## Conséquences

### Positives
- Interface utilisateur optimisée pour les conseillers
- Meilleure performance (requêtes optimisées)
- Maintenance facilitée
- Code plus lisible et organisé

### Négatives
- Plus de fichiers à maintenir
- Duplication potentielle de certaines logiques

## Implémentation
```php
class ConseillerDashboard extends Dashboard
{
    public function getWidgets(): array
    {
        return [
            ConseillerStatsWidget::class,
            ConseillerConversionChart::class,
            ConseillerProspectStatusChart::class,
            ConseillerPriorityProspectsWidget::class,
            ConseillerTasksWidget::class,
        ];
    }
}
```

## Notes
- Utilisation des composants Filament 3.x
- Intégration avec le système de rôles existant
- Respect des standards de l'application
