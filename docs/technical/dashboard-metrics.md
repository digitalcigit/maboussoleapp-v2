# Métriques du Tableau de Bord

## Vue d'ensemble
Le tableau de bord de l'application Ma Boussole a été amélioré avec des métriques avancées qui fournissent une vue d'ensemble en temps réel des principales statistiques de l'application. Ces métriques incluent le suivi des utilisateurs actifs, le nombre total de prospects, et les tendances par rapport aux périodes précédentes.

## Implémentation technique

### Widget de statistiques
Les métriques sont implémentées via la classe `StatsOverviewWidget` dans `app/Filament/Widgets/StatsOverviewWidget.php`. Ce widget utilise le composant `StatsOverviewCard` de Filament pour afficher les différentes métriques dans un format visuel attrayant.

```php
namespace App\Filament\Widgets;

use App\Models\Prospect;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            $this->getUsersStats(),
            $this->getProspectsStats(),
            // Autres métriques...
        ];
    }
}
```

### Calcul des métriques

#### Utilisateurs actifs
La métrique des utilisateurs actifs compte le nombre d'utilisateurs qui se sont connectés dans les 30 derniers jours :

```php
protected function getUsersStats(): Stat
{
    $totalUsers = User::count();
    $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))->count();
    $previousPeriodUsers = User::where('created_at', '>=', now()->subDays(60))
        ->where('created_at', '<', now()->subDays(30))
        ->count();
        
    $difference = $activeUsers - $previousPeriodUsers;
    $percentChange = $previousPeriodUsers > 0
        ? round(($difference / $previousPeriodUsers) * 100)
        : 0;
        
    return Stat::make('Utilisateurs actifs', $activeUsers)
        ->description($percentChange >= 0 
            ? "+{$percentChange}% par rapport au mois précédent" 
            : "{$percentChange}% par rapport au mois précédent")
        ->descriptionIcon($percentChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
        ->color($percentChange >= 0 ? 'success' : 'danger')
        ->chart($this->generateChartData($activeUsers, $previousPeriodUsers));
}
```

#### Total des prospects
La métrique du total des prospects affiche le nombre total de prospects dans le système et sa variation par rapport à la période précédente :

```php
protected function getProspectsStats(): Stat
{
    $totalProspects = Prospect::count();
    $newProspects = Prospect::where('created_at', '>=', now()->subDays(30))->count();
    $previousPeriodProspects = Prospect::where('created_at', '>=', now()->subDays(60))
        ->where('created_at', '<', now()->subDays(30))
        ->count();
        
    $difference = $newProspects - $previousPeriodProspects;
    $percentChange = $previousPeriodProspects > 0
        ? round(($difference / $previousPeriodProspects) * 100)
        : 0;
        
    return Stat::make('Total des prospects', $totalProspects)
        ->description($percentChange >= 0 
            ? "{$newProspects} nouveaux (+{$percentChange}%)" 
            : "{$newProspects} nouveaux ({$percentChange}%)")
        ->descriptionIcon($percentChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
        ->color($percentChange >= 0 ? 'success' : 'danger')
        ->chart($this->generateChartData($newProspects, $previousPeriodProspects));
}
```

### Génération des données du graphique
Les métriques incluent également des mini-graphiques qui visualisent la tendance des données :

```php
protected function generateChartData(int $current, int $previous): array
{
    // Génère un tableau de 7 valeurs qui montre la progression de previous à current
    $step = ($current - $previous) / 6;
    $data = [];
    
    for ($i = 0; $i < 7; $i++) {
        $data[] = round($previous + ($step * $i));
    }
    
    return $data;
}
```

## Fonctionnalités des métriques

### Indicateurs visuels
Chaque métrique inclut plusieurs indicateurs visuels :
- **Valeur actuelle** : Le nombre principal affiché
- **Comparaison** : Pourcentage de variation par rapport à la période précédente
- **Icône de tendance** : Flèche montante ou descendante selon la direction de la variation
- **Couleur** : Vert pour les tendances positives, rouge pour les tendances négatives
- **Mini-graphique** : Visualisation de la tendance sur une période

### Actualisation des données
Les métriques sont actualisées selon les règles suivantes :
- Automatiquement lors du chargement initial du tableau de bord
- Périodiquement via polling (toutes les 60 secondes)
- Manuellement via le bouton d'actualisation

## Personnalisation des métriques
Le widget est conçu pour être facilement extensible :

1. **Ajout de nouvelles métriques** : Créer une nouvelle méthode qui retourne un objet `Stat`
2. **Modification des périodes de comparaison** : Ajuster les paramètres `subDays()` dans les requêtes
3. **Styles visuels** : Personnaliser les couleurs et icônes via les méthodes `color()` et `icon()`

## Optimisation des performances
Pour maintenir de bonnes performances avec un grand volume de données :

1. **Mise en cache** : Les résultats sont mis en cache pendant 5 minutes
2. **Requêtes optimisées** : Utilisation d'index sur les colonnes `created_at` et `last_login_at`
3. **Requêtes légères** : Utilisation de `count()` plutôt que de récupérer tous les enregistrements

## Considérations futures
1. **Métriques personnalisables** : Permettre aux utilisateurs de choisir les métriques à afficher
2. **Filtres temporels** : Ajouter des options pour changer la période d'analyse (semaine, mois, trimestre)
3. **Exportation** : Ajouter la possibilité d'exporter les métriques au format CSV ou PDF
4. **Alertes** : Configurer des alertes lorsque certains seuils sont atteints
