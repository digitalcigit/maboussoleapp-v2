# Guide d'Implémentation du Débogage Systématique

## Étapes d'Implémentation

### 1. Phase d'Observation
```php
// Exemple de logging structuré
Log::debug('Vérification des permissions', [
    'user' => auth()->user()->id,
    'action' => 'view',
    'resource' => 'dossier',
    'context' => [
        'roles' => auth()->user()->roles->pluck('name'),
        'permissions' => auth()->user()->permissions->pluck('name')
    ]
]);
```

### 2. Phase d'Hypothèse
```php
// Documentation des hypothèses dans le code
/**
 * @debug Hypothèse : L'erreur pourrait venir de la mauvaise attribution des rôles
 * Tests à effectuer :
 * 1. Vérifier les rôles de l'utilisateur
 * 2. Vérifier les permissions associées
 * 3. Tracer le chemin d'autorisation
 */
```

### 3. Phase d'Expérimentation
```php
// Exemple de point de test
public function view(User $user, Dossier $dossier): bool
{
    $hasRole = $user->hasRole('candidat');
    $hasDossier = $user->prospect && $user->prospect->dossier_id === $dossier->id;
    
    Log::debug('Test d'autorisation', [
        'hasRole' => $hasRole,
        'hasDossier' => $hasDossier,
        'user_id' => $user->id,
        'dossier_id' => $dossier->id
    ]);
    
    return $hasRole && $hasDossier;
}
```

### 4. Phase d'Analyse
```php
// Exemple de collecte de métriques
class PermissionAnalyzer
{
    public static function analyzeAccessAttempt($user, $action, $resource)
    {
        $data = [
            'timestamp' => now(),
            'user_id' => $user->id,
            'action' => $action,
            'resource' => $resource,
            'success' => false,
            'failure_reason' => null
        ];
        
        try {
            $success = $user->can($action, $resource);
            $data['success'] = $success;
        } catch (\Exception $e) {
            $data['failure_reason'] = $e->getMessage();
        }
        
        Log::channel('permission_analysis')->info('Access attempt', $data);
    }
}
```

## Bonnes Pratiques

### 1. Logging Structuré
- Utiliser des contextes JSON
- Inclure des identifiants uniques
- Catégoriser les messages

### 2. Points de Debug Stratégiques
```php
// Exemple de helper de debug
class DebugPoint
{
    public static function trace($message, $context = [])
    {
        if (config('app.debug')) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            Log::debug($message, array_merge($context, [
                'file' => $trace['file'],
                'line' => $trace['line'],
                'function' => $trace['function']
            ]));
        }
    }
}
```

### 3. Documentation des Investigations
```php
/**
 * @debug Investigation #123
 * Problème : Erreur 403 sur l'accès au dossier
 * 
 * Observations :
 * - L'utilisateur a le rôle correct
 * - Les permissions sont bien définies
 * - Le problème vient de la relation prospect->dossier
 * 
 * Solution :
 * - Ajout d'une vérification de l'existence de la relation
 * - Logging amélioré pour tracer les cas similaires
 */
```

## Tests et Validation

### 1. Tests Unitaires
```php
public function test_permission_debug_flow()
{
    $user = User::factory()->create();
    $dossier = Dossier::factory()->create();
    
    DebugPoint::trace('Début du test de permission');
    
    $this->assertFalse($user->can('view', $dossier), 'L\'utilisateur ne devrait pas avoir accès');
    
    $user->assignRole('candidat');
    $user->prospect->update(['dossier_id' => $dossier->id]);
    
    $this->assertTrue($user->can('view', $dossier), 'L\'utilisateur devrait avoir accès');
}
```

### 2. Monitoring
```php
// Exemple de middleware de monitoring
class PermissionMonitoring
{
    public function handle($request, $next)
    {
        $start = microtime(true);
        
        try {
            $response = $next($request);
            
            if ($response->status() === 403) {
                $this->logPermissionFailure($request);
            }
            
            return $response;
        } finally {
            $duration = microtime(true) - $start;
            $this->recordMetrics($duration);
        }
    }
}
```
