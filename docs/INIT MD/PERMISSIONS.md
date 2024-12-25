# Structure des Permissions - MaBoussole CRM v2

## 1. Rôles et Permissions

### Super Administrateur
```php
[
    // Gestion Système
    'system.settings.view',
    'system.settings.edit',
    'system.logs.view',
    
    // Gestion Utilisateurs
    'users.*',  // Tous les droits sur les utilisateurs
    'roles.*',  // Gestion complète des rôles
    
    // Rapports
    'reports.*',
    
    // Accès complet
    '*'  // Super admin a accès à tout
]
```

### Manager
```php
[
    // Gestion Utilisateurs
    'users.view',
    'users.create',
    'users.edit',
    'users.delete',
    
    // Gestion Prospects
    'prospects.*',
    
    // Gestion Clients
    'clients.*',
    
    // Rapports
    'reports.view',
    'reports.export',
    
    // Validation
    'documents.validate',
    'steps.validate',
    
    // Configuration
    'settings.view',
    'settings.edit.department'
]
```

### Conseiller
```php
[
    // Prospects
    'prospects.view',
    'prospects.create',
    'prospects.edit',
    'prospects.delete.own',
    
    // Clients
    'clients.view',
    'clients.create',
    'clients.edit.own',
    
    // Documents
    'documents.view',
    'documents.upload',
    'documents.validate',
    
    // Communications
    'communications.email',
    'communications.sms',
    
    // Rapports
    'reports.view.own'
]
```

### Partenaire
```php
[
    // Prospects
    'prospects.create',
    'prospects.view.own',
    'prospects.edit.own',
    
    // Documents
    'documents.upload',
    'documents.view.own',
    
    // Rapports
    'reports.view.own'
]
```

### Commercial
```php
[
    // Prospects
    'prospects.create',
    'prospects.view.own',
    
    // Bonus
    'bonus.view.own',
    
    // Rapports
    'reports.view.own.basic'
]
```

## 2. Implémentation Technique

### Structure de Base
```php
// app/Models/Permission.php
class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'module'
    ];
}

// app/Models/Role.php
class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'level'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
```

### Middleware de Vérification
```php
// app/Http/Middleware/CheckPermission.php
class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        if (!$request->user()->hasPermission($permission)) {
            abort(403);
        }
        return $next($request);
    }
}
```

### Traits Utilisateur
```php
// app/Traits/HasPermissions.php
trait HasPermissions
{
    public function hasPermission($permission)
    {
        return $this->permissions->contains('slug', $permission) ||
               $this->roles->flatMap->permissions->contains('slug', $permission);
    }

    public function hasRole($role)
    {
        return $this->roles->contains('slug', $role);
    }
}
```

## 3. Vérifications de Permissions

### Dans les Controllers
```php
class ProspectController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Prospect::class);
        // ...
    }

    public function update(Request $request, Prospect $prospect)
    {
        $this->authorize('update', $prospect);
        // ...
    }
}
```

### Dans les Policies
```php
class ProspectPolicy
{
    public function view(User $user, Prospect $prospect)
    {
        return $user->hasPermission('prospects.view') ||
               ($user->hasPermission('prospects.view.own') && 
                $prospect->assigned_to === $user->id);
    }

    public function create(User $user)
    {
        return $user->hasPermission('prospects.create');
    }

    public function update(User $user, Prospect $prospect)
    {
        return $user->hasPermission('prospects.edit') ||
               ($user->hasPermission('prospects.edit.own') && 
                $prospect->assigned_to === $user->id);
    }
}
```

### Dans les Vues
```php
@can('prospects.create')
    <button>Nouveau Prospect</button>
@endcan

@can('prospects.edit', $prospect)
    <button>Modifier</button>
@endcan
```

## 4. Gestion des Permissions dans Filament

### Resources
```php
class UserResource extends Resource
{
    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('users.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('users.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('users.edit');
    }
}
```

### Pages
```php
class ListProspects extends ListRecords
{
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        
        if (!auth()->user()->hasPermission('prospects.view.all')) {
            $query->where('assigned_to', auth()->id());
        }
        
        return $query;
    }
}
```

## 5. Audit et Logging

### Actions à Logger
```php
[
    'permission.granted',
    'permission.revoked',
    'role.assigned',
    'role.removed',
    'access.denied'
]
```

### Structure Log
```php
{
    "event": "permission.granted",
    "user_id": 1,
    "target_id": 2,
    "permission": "prospects.create",
    "timestamp": "2024-12-12 10:00:00",
    "ip": "192.168.1.1",
    "user_agent": "Mozilla/5.0..."
}
```
