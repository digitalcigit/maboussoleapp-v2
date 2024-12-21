# Guide de Monitoring - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Documentation complète du système de monitoring de MaBoussole CRM v2.

## Métriques Clés

### Performance
```yaml
Response Time:
  Warning: > 300ms
  Critical: > 500ms
  Source: CloudWatch

Error Rate:
  Warning: > 1%
  Critical: > 5%
  Source: CloudWatch

CPU Usage:
  Warning: > 70%
  Critical: > 85%
  Source: CloudWatch

Memory Usage:
  Warning: > 75%
  Critical: > 90%
  Source: CloudWatch
```

### Base de Données
```yaml
Query Response Time:
  Warning: > 100ms
  Critical: > 500ms
  Source: RDS Enhanced Monitoring

Connection Count:
  Warning: > 80%
  Critical: > 90%
  Source: RDS CloudWatch

Disk Usage:
  Warning: > 75%
  Critical: > 90%
  Source: RDS CloudWatch
```

## Outils de Monitoring

### 1. New Relic
```php
// config/newrelic.php
return [
    'license_key' => env('NEW_RELIC_LICENSE_KEY'),
    'app_name' => env('NEW_RELIC_APP_NAME', 'MaBoussole CRM'),
    'distributed_tracing' => [
        'enabled' => true,
        'exclude_newrelic_header' => false,
    ],
];
```

### 2. CloudWatch
```yaml
Dashboards:
  - Application Performance
  - Database Metrics
  - Infrastructure Health
  - Error Tracking

Alarms:
  High CPU:
    Threshold: 85%
    Period: 5 minutes
    Actions: SNS notification

  Error Rate:
    Threshold: 5%
    Period: 5 minutes
    Actions: SNS notification
```

### 3. Logs
```yaml
Application Logs:
  Source: Laravel Log
  Path: storage/logs/laravel.log
  Rotation: Daily
  Retention: 14 days

Error Logs:
  Source: Laravel Log
  Path: storage/logs/laravel-error.log
  Rotation: Daily
  Retention: 30 days

Access Logs:
  Source: Nginx
  Path: /var/log/nginx/access.log
  Format: Combined
  Rotation: Daily
```

## Alerting

### Configuration SNS
```yaml
Topics:
  - name: maboussole-alerts-critical
    subscribers:
      - type: email
        endpoint: ops@maboussole.com
      - type: sms
        endpoint: +33612345678

  - name: maboussole-alerts-warning
    subscribers:
      - type: email
        endpoint: dev@maboussole.com
```

### Règles d'Alerte
```yaml
Critical:
  - condition: Error Rate > 5%
    duration: 5 minutes
    channels: [email, sms]

  - condition: Response Time > 500ms
    duration: 10 minutes
    channels: [email]

Warning:
  - condition: CPU Usage > 70%
    duration: 15 minutes
    channels: [email]

  - condition: Memory Usage > 75%
    duration: 15 minutes
    channels: [email]
```

## Tableau de Bord

### Metrics Dashboard
```php
class MetricsDashboard extends Component
{
    public function render()
    {
        return view('dashboard.metrics', [
            'responseTime' => $this->getAverageResponseTime(),
            'errorRate' => $this->getErrorRate(),
            'cpuUsage' => $this->getCPUUsage(),
            'memoryUsage' => $this->getMemoryUsage(),
            'databaseMetrics' => $this->getDatabaseMetrics(),
        ]);
    }

    private function getAverageResponseTime()
    {
        return Cache::remember('avg_response_time', 300, function () {
            // Logic to fetch from CloudWatch
        });
    }
}
```

## Logging

### Configuration
```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack', 'newrelic'],
    ],
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 14,
    ],
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'MaBoussole Bot',
        'emoji' => ':boom:',
        'level' => 'critical',
    ],
],
```

### Log Levels
```php
Log::emergency($message); // Système inutilisable
Log::alert($message);     // Action immédiate requise
Log::critical($message);  // Conditions critiques
Log::error($message);     // Erreurs d'exécution
Log::warning($message);   // Conditions exceptionnelles
Log::notice($message);    // Événements normaux significatifs
Log::info($message);      // Événements intéressants
Log::debug($message);     // Information détaillée de debug
```

## Performance Monitoring

### Middleware
```php
class PerformanceMonitoringMiddleware
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        
        if ($duration > 0.3) { // 300ms
            Log::warning("Slow request: {$request->path()} - {$duration}s");
        }
        
        return $response;
    }
}
```

### Query Monitoring
```php
DB::listen(function ($query) {
    if ($query->time > 100) { // 100ms
        Log::warning("Slow query: {$query->sql} - {$query->time}ms");
    }
});
```

## Maintenance

### Commandes Artisan
```bash
# Nettoyage logs
php artisan logs:clean

# Vérification santé
php artisan health:check

# Rapport métriques
php artisan metrics:report
```

### Cron Jobs
```bash
# /etc/cron.d/maboussole-monitoring
0 * * * * www-data /usr/bin/php /var/www/maboussole/artisan monitoring:hourly-check
0 0 * * * www-data /usr/bin/php /var/www/maboussole/artisan monitoring:daily-report
```

---
*Documentation générée pour MaBoussole CRM v2*
