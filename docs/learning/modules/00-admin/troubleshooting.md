# Guide de Dépannage du Tableau de Bord Super Admin

## Problèmes Courants

### 1. Widgets Non Affichés

#### Symptômes
- Widget vide ou message d'erreur
- Données manquantes
- Icônes non chargées

#### Solutions
1. Vérifier les permissions
```php
php artisan cache:clear
php artisan config:clear
```

2. Vérifier l'enregistrement du widget
```php
// app/Providers/FilamentServiceProvider.php
public function boot(): void
{
    Filament::registerWidgets([
        FinancialMetricsWidget::class,
        // ...
    ]);
}
```

3. Vérifier les dépendances JavaScript
```bash
npm install
npm run build
```

### 2. Données Incorrectes

#### Symptômes
- Totaux incohérents
- Graphiques vides
- Données non mises à jour

#### Solutions
1. Vérifier les calculs
```php
// Dans tinker
>>> Client::whereMonth('created_at', now()->month)->sum('total_amount')
>>> Prospect::groupBy('status')->selectRaw('status, count(*) as total')->get()
```

2. Nettoyer le cache
```php
php artisan cache:clear
php artisan view:clear
```

3. Vérifier les requêtes
```php
\DB::enableQueryLog();
// Exécuter le widget
dd(\DB::getQueryLog());
```

### 3. Problèmes de Performance

#### Symptômes
- Chargement lent
- Timeout des requêtes
- Utilisation CPU élevée

#### Solutions
1. Optimiser les requêtes
```php
protected function getStats(): array
{
    return cache()->remember('financial_metrics', 300, function () {
        // Requêtes optimisées...
    });
}
```

2. Indexer les colonnes critiques
```php
// database/migrations/add_indexes_to_clients.php
public function up()
{
    Schema::table('clients', function (Blueprint $table) {
        $table->index(['created_at', 'total_amount']);
    });
}
```

3. Ajuster la pagination
```php
protected int $tableRecordsPerPage = 10;
```

### 4. Erreurs d'Authentification

#### Symptômes
- Accès refusé
- Redirection en boucle
- Session expirée

#### Solutions
1. Vérifier les middleware
```php
// routes/web.php
Route::middleware([
    'auth',
    'verified',
    'role:super-admin'
])->group(function () {
    // Routes du dashboard
});
```

2. Réinitialiser les sessions
```php
php artisan session:table
php artisan migrate
```

3. Vérifier la configuration
```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],
```

### 5. Problèmes de Mise à Jour en Temps Réel

#### Symptômes
- Pas de rafraîchissement automatique
- Données obsolètes
- Erreurs WebSocket

#### Solutions
1. Vérifier la configuration Livewire
```php
// config/livewire.php
'asset_url' => null,
'app_url' => null,
```

2. Ajuster les intervalles
```php
protected static ?string $pollingInterval = '30s';
```

3. Déboguer les événements
```php
// Dans le widget
protected function getStats(): array
{
    logger()->info('Widget refreshed at ' . now());
    // ...
}
```

## Outils de Diagnostic

### Logs
```bash
tail -f storage/logs/laravel.log
```

### Requêtes SQL
```php
\DB::listen(function($query) {
    logger()->info(
        $query->sql,
        $query->bindings,
        $query->time
    );
});
```

### Performance
```php
// Dans AppServiceProvider
public function boot()
{
    DB::enableQueryLog();
    Event::listen('*', function ($event) {
        logger()->info(get_class($event));
    });
}
```

## Maintenance Préventive

### Vérifications Quotidiennes
1. Surveiller les logs d'erreurs
2. Vérifier les temps de réponse
3. Valider les totaux financiers

### Maintenance Hebdomadaire
1. Nettoyer les caches
2. Optimiser la base de données
3. Vérifier les sessions expirées

### Maintenance Mensuelle
1. Analyser les performances
2. Mettre à jour les dépendances
3. Sauvegarder les données

## Support et Ressources

### Documentation
- [Filament Widgets](https://filamentphp.com/docs/3.x/panels/widgets)
- [Laravel Performance](https://laravel.com/docs/10.x/queues)
- [Livewire Polling](https://livewire.laravel.com/docs/polling)

### Canaux de Support
- Slack : #support-dashboard
- Email : support@maboussole-crm.com
- Documentation interne : `/docs/troubleshooting`
