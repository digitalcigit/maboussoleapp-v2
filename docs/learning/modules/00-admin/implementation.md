# Implémentation du Tableau de Bord Super Admin

## Structure des Fichiers

```bash
app/Filament/
├── Pages/
│   └── Dashboards/
│       └── SuperAdminDashboard.php
└── Widgets/
    ├── FinancialMetricsWidget.php
    ├── ProspectFunnelWidget.php
    ├── FinancialPerformanceChart.php
    ├── MonthlyGoalsWidget.php
    └── LatestTransactionsWidget.php
```

## Configuration du Dashboard

```php
// app/Filament/Pages/Dashboards/SuperAdminDashboard.php

class SuperAdminDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Tableaux de Bord';

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialMetricsWidget::class,
            ProspectFunnelWidget::class,
            FinancialPerformanceChart::class,
            MonthlyGoalsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestTransactionsWidget::class,
        ];
    }
}
```

## Widgets Clés

### Métriques Financières

```php
// app/Filament/Widgets/FinancialMetricsWidget.php

class FinancialMetricsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $currentMonthRevenue = Client::whereMonth('created_at', now()->month)
            ->sum('total_amount');
            
        $commissions = $currentMonthRevenue * 0.20;
        
        return [
            Stat::make('CA', number_format($currentMonthRevenue, 0, ',', ' ') . ' €')
                ->description('...')
                ->chart([...]),
            // Autres statistiques...
        ];
    }
}
```

### Entonnoir de Conversion

```php
// app/Filament/Widgets/ProspectFunnelWidget.php

class ProspectFunnelWidget extends ChartWidget
{
    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [
                        Prospect::nouveaux()->count(),
                        Prospect::contactes()->count(),
                        // ...
                    ],
                ],
            ],
            'labels' => [
                'Nouveaux',
                'Contactés',
                // ...
            ],
        ];
    }
}
```

## Intégration des Modèles

### Client

```php
// app/Models/Client.php

class Client extends Model
{
    public const PAYMENT_STATUS_PENDING = 'en_attente';
    public const PAYMENT_STATUS_PARTIAL = 'partiel';
    public const PAYMENT_STATUS_COMPLETED = 'complete';

    protected $fillable = [
        'total_amount',
        'paid_amount',
        'payment_status',
        // ...
    ];
}
```

### Prospect

```php
// app/Models/Prospect.php

class Prospect extends Model
{
    public function scopeNouveaux($query)
    {
        return $query->where('status', 'nouveau');
    }

    public function scopeContactes($query)
    {
        return $query->where('status', 'contacte');
    }
    // ...
}
```

## Gestion des Mises à Jour

### Rafraîchissement Automatique

```php
protected static ?string $pollingInterval = '30s';

// ou pour un widget spécifique
public function getPollingInterval(): ?string
{
    return '30s';
}
```

### Cache

```php
protected function getData(): array
{
    return cache()->remember('dashboard_metrics', 60, function () {
        // Calculs coûteux...
    });
}
```

## Sécurité

### Middleware

```php
public static function shouldRegister(): bool
{
    return auth()->user()->hasRole('super-admin');
}
```

### Validation

```php
protected function rules(): array
{
    return [
        'dateRange' => ['required', 'string'],
        'filters' => ['array'],
        // ...
    ];
}
```
