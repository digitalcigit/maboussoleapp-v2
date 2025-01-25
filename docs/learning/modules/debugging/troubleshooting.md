# Guide de Résolution des Problèmes

## Problèmes Courants

### 1. Erreurs d'Autorisation (403)
#### Symptômes
- Page d'erreur 403
- Message "Forbidden"
- Accès refusé aux ressources

#### Diagnostic
1. Vérifier les logs d'erreur
2. Examiner les permissions utilisateur
3. Tracer le chemin d'autorisation

#### Solutions
```php
// 1. Vérifier l'authentification
if (!auth()->check()) {
    Log::warning('Utilisateur non authentifié');
    return false;
}

// 2. Vérifier les rôles
if (!auth()->user()->hasRole('candidat')) {
    Log::warning('Utilisateur sans rôle candidat');
    return false;
}

// 3. Vérifier les relations
if (!auth()->user()->prospect?->dossier_id) {
    Log::warning('Relation prospect-dossier manquante');
    return false;
}
```

### 2. Erreurs de Relations
#### Symptômes
- Erreur "Undefined relationship"
- Accès null à des propriétés
- Erreurs de type

#### Diagnostic
1. Vérifier les définitions de modèle
2. Examiner les migrations
3. Tester les relations en tinker

#### Solutions
```php
// 1. Définir correctement les relations
public function prospect()
{
    return $this->hasOne(Prospect::class);
}

// 2. Vérifier les relations de manière sécurisée
$dossier_id = optional(auth()->user()->prospect)->dossier_id;
```

### 3. Erreurs de Configuration
#### Symptômes
- Comportement inattendu
- Erreurs de chargement
- Problèmes de cache

#### Diagnostic
1. Vérifier les fichiers de configuration
2. Examiner les variables d'environnement
3. Tester différentes configurations

#### Solutions
```bash
# 1. Nettoyer le cache
php artisan config:clear
php artisan cache:clear

# 2. Recharger les configurations
php artisan config:cache
```

## Outils de Diagnostic

### 1. Logging Avancé
```php
class DebugLogger
{
    public static function logPermissionCheck($user, $action, $resource)
    {
        Log::channel('debug')->info('Permission check', [
            'user' => [
                'id' => $user->id,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->permissions->pluck('name')
            ],
            'action' => $action,
            'resource' => [
                'type' => get_class($resource),
                'id' => $resource->id
            ]
        ]);
    }
}
```

### 2. Tests de Diagnostic
```php
class DiagnosticTest extends TestCase
{
    /** @test */
    public function it_can_diagnose_permission_issues()
    {
        $user = User::factory()->create();
        $dossier = Dossier::factory()->create();
        
        $this->diagnosePermissions($user, $dossier);
    }
    
    private function diagnosePermissions($user, $dossier)
    {
        $checks = [
            'authentication' => auth()->check(),
            'roles' => $user->roles->pluck('name'),
            'dossier_exists' => $dossier->exists,
            'prospect_relation' => $user->prospect()->exists(),
            'dossier_relation' => optional($user->prospect)->dossier_id === $dossier->id
        ];
        
        foreach ($checks as $check => $result) {
            Log::info("Diagnostic: {$check}", ['result' => $result]);
        }
    }
}
```

## Prévention des Problèmes

### 1. Monitoring Proactif
```php
// Middleware de monitoring
class PermissionMonitoring
{
    public function handle($request, $next)
    {
        $start = microtime(true);
        
        try {
            $response = $next($request);
            
            if ($response->status() === 403) {
                $this->alertPermissionFailure($request);
            }
            
            return $response;
        } finally {
            $this->recordMetrics([
                'duration' => microtime(true) - $start,
                'status' => $response->status(),
                'user' => auth()->id()
            ]);
        }
    }
}
```

### 2. Tests Automatisés
```php
class PermissionTest extends TestCase
{
    /** @test */
    public function candidat_can_access_own_dossier()
    {
        $user = User::factory()->create();
        $user->assignRole('candidat');
        
        $dossier = Dossier::factory()->create();
        $user->prospect->update(['dossier_id' => $dossier->id]);
        
        $this->assertTrue($user->can('view', $dossier));
    }
}
```

## Checklist de Résolution

### Avant de Commencer
- [ ] Collecter les logs d'erreur
- [ ] Identifier le contexte exact
- [ ] Documenter les étapes de reproduction

### Pendant le Diagnostic
- [ ] Ajouter des logs de debug stratégiques
- [ ] Tester chaque hypothèse
- [ ] Documenter les résultats

### Après la Résolution
- [ ] Implémenter des tests de régression
- [ ] Mettre à jour la documentation
- [ ] Partager les leçons apprises
