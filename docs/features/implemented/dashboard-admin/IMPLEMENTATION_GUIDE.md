# Guide d'Implémentation : Dashboard Super Admin

## Vue d'ensemble
Ce guide explique l'implémentation du tableau de bord Super Admin en utilisant Filament 3.x.

## Structure du Code

### 1. Configuration du Panel Admin
```php
// app/Providers/Filament/AdminPanelProvider.php

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->pages([
                SuperAdminDashboard::class, // Remplace le dashboard par défaut
            ]);
    }
}
```

**Points clés :**
- Filament utilise un système de panels pour organiser l'interface
- Le dashboard est enregistré comme page principale
- La configuration se fait via une API fluide

### 2. Création du Dashboard
```php
// app/Filament/Pages/Dashboards/SuperAdminDashboard.php

class SuperAdminDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected function getColumns(): int | array
    {
        return 4; // Layout responsive à 4 colonnes
    }
}
```

**Points clés :**
- Hérite de `Filament\Pages\Dashboard`
- Utilise les Heroicons pour l'interface
- Configuration du layout en grille

### 3. Widgets de Statistiques
```php
// app/Filament/Widgets/StatsOverviewWidget.php

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    
    protected function getStats(): array
    {
        return [
            Stat::make('Titre', 'Valeur')
                ->description('Description')
                ->chart([1, 2, 3]) // Données du graphique
                ->color('success'), // Couleur conditionnelle
        ];
    }
}
```

**Points clés :**
- Les widgets sont des composants réutilisables
- Rafraîchissement automatique via `pollingInterval`
- API fluide pour la configuration

## Bonnes Pratiques

### 1. Organisation des Widgets
```yaml
Structure Recommandée:
  - Un widget = Une responsabilité
  - Widgets courts et focalisés
  - Nommage explicite
  - Commentaires pour les calculs complexes
```

### 2. Performance
```yaml
Optimisations:
  - Cache des requêtes lourdes
  - Polling interval adapté
  - Lazy loading des données
  - Index sur les colonnes filtrées
```

### 3. Maintenance
```yaml
Conseils:
  - Documenter les formules de calcul
  - Centraliser les requêtes communes
  - Tests pour les calculs critiques
  - Logging des performances
```

## Exemples Concrets

### 1. Widget KPI Simple
```php
Stat::make('Utilisateurs Actifs', $count)
    ->description("$trend% vs hier")
    ->descriptionIcon('heroicon-m-arrow-trending-up')
    ->chart($weeklyData)
    ->color($trend >= 0 ? 'success' : 'danger')
```

### 2. Widget avec Calculs
```php
// Calcul du taux de conversion
$conversionRate = $totalProspects > 0 
    ? ($totalConversions / $totalProspects) * 100 
    : 0;

Stat::make('Taux Conversion', number_format($conversionRate, 1) . '%')
```

## Debugging

### 1. Problèmes Courants
```yaml
Widget Non Visible:
  - Vérifier l'enregistrement dans getHeaderWidgets()
  - Contrôler les permissions
  - Inspecter les logs Laravel

Données Incorrectes:
  - Valider les calculs avec dd()
  - Vérifier le timezone
  - Contrôler les formats de date
```

### 2. Outils de Debug
```php
// Dans un widget
public function mount(): void
{
    ray($this->getStats()); // Avec Ray
    logger()->debug($this->getStats()); // Logs Laravel
}
```

## Tests

### 1. Test Unitaire
```php
public function test_conversion_rate_calculation(): void
{
    // Arrange
    $prospects = Prospect::factory()->count(100)->create();
    $clients = Client::factory()->count(20)->create();
    
    // Act
    $widget = new StatsOverviewWidget();
    $stats = $widget->getStats();
    
    // Assert
    $this->assertEquals(20, $stats[2]->getValue());
}
```

### 2. Test de Vue
```php
public function test_widget_visibility(): void
{
    $this->actingAs($superAdmin)
        ->get('/admin')
        ->assertSeeLivewire(StatsOverviewWidget::class);
}
```

## Ressources

### Documentation
- [Filament Docs](https://filamentphp.com/docs/3.x/panels/dashboard)
- [Laravel Livewire](https://laravel-livewire.com/docs/2.x/quickstart)
- [Heroicons](https://heroicons.com/)

### Outils Utiles
- Laravel Telescope pour le debugging
- Ray pour l'inspection des données
- Laravel IDE Helper pour l'autocomplétion

## Widgets Avancés

### 1. Graphique de Performance Financière
```php
class FinancialPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Performance Financière';
    
    protected function getData(): array
    {
        // Récupération des données sur 6 mois
        $months = collect(range(5, 0))->map(fn ($month) => 
            now()->subMonths($month)->format('M')
        );

        // Structure des données
        return [
            'datasets' => [
                [
                    'label' => 'Chiffre d\'Affaires',
                    'data' => $revenue,
                    'borderColor' => '#10B981',
                    'fill' => true,
                ],
                // ... autres datasets
            ],
            'labels' => $months,
        ];
    }
}
```

**Points clés :**
- Utilise `ChartWidget` de Filament
- Données sur 6 mois glissants
- Graphique interactif avec légende
- Personnalisation des couleurs et styles

### 2. Liste des Transactions
```php
class LatestTransactionsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Client::query()
                    ->whereMonth('created_at', now()->month)
                    ->orderByDesc('contract_value')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contract_value')
                    ->money('eur'),
                // ... autres colonnes
            ]);
    }
}
```

**Points clés :**
- Utilise `TableWidget` de Filament
- Occupe toute la largeur (`columnSpan = 'full'`)
- Tri et filtres intégrés
- Formatage monétaire automatique

### 3. Configuration du Layout
```php
protected function getColumns(): int | array
{
    return [
        'default' => 4,
        'sm' => 3,
        'lg' => 4,
        'xl' => 4,
    ];
}
```

**Points clés :**
- Layout responsive
- S'adapte à différentes tailles d'écran
- Optimisé pour desktop et tablette

## Personnalisation des Widgets

### 1. Styles et Thèmes
```php
// Configuration des couleurs
protected function getOptions(): array
{
    return [
        'plugins' => [
            'legend' => [
                'position' => 'bottom',
            ],
        ],
        'scales' => [
            'y' => [
                'ticks' => [
                    'callback' => '(value) => value + " €"',
                ],
            ],
        ],
    ];
}
```

### 2. Interactions
```php
// Actions sur les lignes du tableau
Tables\Actions\ViewAction::make()
    ->modalContent(fn (Client $record) => view(
        'clients.modal.view',
        ['client' => $record]
    ));
```

### 3. Filtres Avancés
```php
Tables\Filters\Filter::make('high_value')
    ->query(fn (Builder $query): Builder => 
        $query->where('contract_value', '>', 10000)
    );
```

## Optimisation des Performances

### 1. Requêtes Efficaces
```php
// Optimisation des requêtes
protected function getData(): array
{
    return Cache::remember('financial_chart', now()->addMinutes(15), function () {
        // Requêtes avec eager loading si nécessaire
        return Client::with('service')
            ->select(DB::raw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                SUM(contract_value) as revenue
            '))
            ->groupBy('month')
            ->get();
    });
}
```

### 2. Mise en Cache
```php
// Configuration du polling
protected static ?string $pollingInterval = '30s';

// Cache des données lourdes
public function table(Table $table): Table
{
    return $table
        ->query(function () {
            return Cache::remember(
                'latest_transactions',
                now()->addMinutes(5),
                fn () => Client::query()
                    ->whereMonth('created_at', now()->month)
                    ->orderByDesc('contract_value')
            );
        });
}
```

## Tests des Widgets

### 1. Test du Graphique
```php
public function test_chart_data_structure(): void
{
    $chart = new FinancialPerformanceChart();
    $data = $chart->getData();

    $this->assertArrayHasKey('datasets', $data);
    $this->assertArrayHasKey('labels', $data);
    $this->assertCount(6, $data['labels']); // 6 mois
}
```

### 2. Test de la Liste
```php
public function test_transaction_filters(): void
{
    $this->actingAs($superAdmin);

    Livewire::test(LatestTransactionsWidget::class)
        ->filterTable('high_value')
        ->assertCanSeeTableRecords(
            Client::where('contract_value', '>', 10000)
        )
        ->assertCanNotSeeTableRecords(
            Client::where('contract_value', '<=', 10000)
        );
}
```

## Dépannage Courant

### 1. Problèmes de Performance
```yaml
Symptôme: Chargement lent du graphique
Solutions:
  - Ajouter des index sur created_at et contract_value
  - Mettre en cache les calculs lourds
  - Réduire la période de données
  - Optimiser les requêtes avec EXPLAIN
```

### 2. Problèmes d'Affichage
```yaml
Symptôme: Graphique tronqué
Solutions:
  - Vérifier les breakpoints CSS
  - Ajuster getColumns()
  - Utiliser les outils de dev Chrome
  - Inspecter les classes Tailwind
```

## Ressources Additionnelles

### Documentation
- [Filament Charts](https://filamentphp.com/docs/3.x/widgets/charts)
- [Filament Tables](https://filamentphp.com/docs/3.x/tables/installation)
- [Chart.js](https://www.chartjs.org/docs/latest/)

### Outils de Développement
- Chrome DevTools pour le debugging visuel
- Laravel Telescope pour les requêtes
- Laravel Debug Bar pour les performances
