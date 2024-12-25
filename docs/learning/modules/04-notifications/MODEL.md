# Documentation du Modèle Notification

## Vue d'Ensemble

Le modèle `Notification` gère les notifications du système CRM MaBoussole, permettant un suivi efficace des événements importants et de la communication avec les utilisateurs.

## Structure du Modèle

### Traits Utilisés
```php
use HasFactory;
use Notifiable;
```

### Constantes
```php
class Notification extends Model
{
    // Types de notifications
    public const TYPE_PROSPECT = 'App\\Notifications\\ProspectNotification';
    public const TYPE_DOCUMENT = 'App\\Notifications\\DocumentNotification';
    public const TYPE_CLIENT = 'App\\Notifications\\ClientNotification';
    public const TYPE_SYSTEM = 'App\\Notifications\\SystemNotification';

    // Niveaux d'urgence
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
}
```

### Attributs
```php
protected $fillable = [
    'type',           // Type de notification
    'data',           // Données JSON
    'read_at',        // Date de lecture
];

protected $casts = [
    'id' => 'string', // UUID
    'data' => 'array',
    'read_at' => 'datetime',
];

// Clé primaire UUID
public $incrementing = false;
protected $keyType = 'string';
```

## Relations

### 1. Relation Polymorphique
```php
public function notifiable()
{
    return $this->morphTo();
}
```

### 2. Lecteur (Optionnel)
```php
public function reader()
{
    return $this->belongsTo(User::class, 'read_by');
}
```

## Méthodes Utilitaires

### 1. Gestion de l'État
```php
public function markAsRead(?User $user = null): void
{
    $this->update([
        'read_at' => now(),
        'read_by' => $user?->id
    ]);
}

public function markAsUnread(): void
{
    $this->update([
        'read_at' => null,
        'read_by' => null
    ]);
}

public function isRead(): bool
{
    return $this->read_at !== null;
}
```

### 2. Données
```php
public function getData(string $key, $default = null)
{
    return data_get($this->data, $key, $default);
}

public function setData(string $key, $value): void
{
    $data = $this->data;
    data_set($data, $key, $value);
    $this->data = $data;
}
```

### 3. Formatage
```php
public function getFormattedDateAttribute(): string
{
    return $this->created_at->diffForHumans();
}

public function getIconAttribute(): string
{
    return match($this->type) {
        self::TYPE_PROSPECT => 'heroicon-o-user',
        self::TYPE_DOCUMENT => 'heroicon-o-document',
        self::TYPE_CLIENT => 'heroicon-o-briefcase',
        self::TYPE_SYSTEM => 'heroicon-o-cog',
        default => 'heroicon-o-bell'
    };
}
```

## Scopes

### 1. Filtres d'État
```php
public function scopeUnread($query)
{
    return $query->whereNull('read_at');
}

public function scopeRead($query)
{
    return $query->whereNotNull('read_at');
}

public function scopeOfType($query, string $type)
{
    return $query->where('type', $type);
}
```

### 2. Filtres Temporels
```php
public function scopeRecent($query, int $days = 7)
{
    return $query->where('created_at', '>=', now()->subDays($days));
}

public function scopeOld($query, int $days = 30)
{
    return $query->where('created_at', '<', now()->subDays($days));
}
```

## Observateur

```php
class NotificationObserver
{
    public function creating(Notification $notification)
    {
        $notification->id = (string) Str::uuid();
    }

    public function created(Notification $notification)
    {
        // Broadcast en temps réel si nécessaire
        broadcast(new NotificationCreated($notification))->toOthers();
    }
}
```

## Classes de Notifications

### 1. ProspectNotification
```php
class ProspectNotification extends Notification
{
    public function toArray($notifiable): array
    {
        return [
            'prospect_id' => $this->prospect->id,
            'reference' => $this->prospect->reference_number,
            'action' => $this->action,
            'priority' => $this->priority
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => static::class,
            'data' => $this->toArray($notifiable)
        ]);
    }
}
```

### 2. DocumentNotification
```php
class DocumentNotification extends Notification
{
    public function toArray($notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'name' => $this->document->name,
            'status' => $this->document->status,
            'action_required' => $this->actionRequired
        ];
    }
}
```

## Points d'Apprentissage

### 1. Gestion des Notifications
```yaml
Aspects:
  - Création UUID
  - Données JSON
  - États lecture
  - Relations polymorphiques
```

### 2. Real-time
```yaml
Fonctionnalités:
  - Broadcasting
  - Queues
  - Events
  - Listeners
```

### 3. Performance
```yaml
Optimisations:
  - Eager loading
  - Index utilisation
  - Cache stratégies
```

## Exemples d'Utilisation

### 1. Création
```php
$notification = Notification::create([
    'type' => Notification::TYPE_PROSPECT,
    'data' => [
        'prospect_id' => $prospect->id,
        'action' => 'review_required',
        'priority' => 'high'
    ]
]);

$prospect->notifications()->save($notification);
```

### 2. Requêtes
```php
// Notifications non lues récentes
$notifications = Notification::unread()
    ->recent()
    ->with('notifiable')
    ->get();

// Notifications par type
$prospectNotifications = Notification::ofType(Notification::TYPE_PROSPECT)
    ->orderByDesc('created_at')
    ->paginate(20);
```

### 3. Broadcast
```php
broadcast(new NotificationCreated($notification))
    ->toPrivateChannel('user.' . $user->id);
```
