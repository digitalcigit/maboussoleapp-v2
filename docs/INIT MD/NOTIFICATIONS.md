# Architecture des Notifications - MaBoussole CRM v2

## 1. Types de Notifications

### Notifications Système
```php
[
    'system.maintenance',     // Maintenance planifiée
    'system.update',         // Mise à jour système
    'system.error',          // Erreurs système
]
```

### Notifications Prospects
```php
[
    'prospect.created',      // Nouveau prospect
    'prospect.assigned',     // Attribution prospect
    'prospect.analyzed',     // Analyse terminée
    'prospect.deadline',     // Deadline approche
    'prospect.converted'     // Conversion en client
]
```

### Notifications Clients
```php
[
    'client.document_needed',    // Document requis
    'client.document_validated', // Document validé
    'client.payment_due',        // Paiement attendu
    'client.payment_received',   // Paiement reçu
    'client.visa_update',        // Mise à jour visa
    'client.travel_prep'         // Préparation voyage
]
```

### Notifications Utilisateurs
```php
[
    'user.welcome',          // Bienvenue nouvel utilisateur
    'user.password_reset',   // Réinitialisation mot de passe
    'user.role_changed',     // Changement de rôle
    'user.login_alert'       // Alerte connexion
]
```

## 2. Canaux de Communication

### Email
```php
class EmailChannel implements NotificationChannel
{
    protected $config = [
        'driver' => 'smtp',
        'provider' => 'Amazon SES',
        'retry_after' => 3600,
        'queue' => 'emails'
    ];

    protected $templates = [
        'prospect.created' => 'emails.prospects.created',
        'client.document_needed' => 'emails.clients.document-request',
        // ...
    ];
}
```

### SMS
```php
class SMSChannel implements NotificationChannel
{
    protected $config = [
        'provider' => 'Twilio',
        'retry_attempts' => 3,
        'retry_delay' => 300,
        'queue' => 'sms'
    ];

    protected $templates = [
        'client.payment_due' => 'sms.payment-reminder',
        'prospect.deadline' => 'sms.deadline-alert',
        // ...
    ];
}
```

### In-App
```php
class InAppChannel implements NotificationChannel
{
    protected $config = [
        'storage' => 'database',
        'table' => 'notifications',
        'lifetime' => 30, // jours
        'queue' => 'default'
    ];
}
```

## 3. Implémentation

### Notification de Base
```php
abstract class BaseNotification extends Notification
{
    use Queueable;

    protected $channels = [];
    protected $priority;
    protected $data;

    public function via($notifiable)
    {
        return $this->channels;
    }

    public function shouldQueue()
    {
        return true;
    }

    protected function getTemplate($channel)
    {
        return $this->templates[$channel] ?? null;
    }
}
```

### Exemple de Notification
```php
class ProspectCreatedNotification extends BaseNotification
{
    protected $channels = ['mail', 'database'];
    protected $templates = [
        'mail' => 'emails.prospects.created',
        'database' => 'notifications.prospect-created'
    ];

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouveau Prospect')
            ->markdown($this->getTemplate('mail'), [
                'prospect' => $this->data['prospect']
            ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'prospect_created',
            'prospect_id' => $this->data['prospect']->id,
            'message' => 'Nouveau prospect créé'
        ];
    }
}
```

## 4. File d'Attente et Traitement

### Configuration Queue
```php
// config/queue.php
return [
    'default' => 'redis',
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'notifications',
            'retry_after' => 90,
            'block_for' => null,
        ],
    ],
];
```

### Job de Notification
```php
class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification;
    protected $notifiable;

    public function handle()
    {
        try {
            Notification::send(
                $this->notifiable,
                $this->notification
            );
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    public function failed(\Exception $e)
    {
        Log::error('Notification failed', [
            'notification' => get_class($this->notification),
            'notifiable' => get_class($this->notifiable),
            'error' => $e->getMessage()
        ]);
    }
}
```

## 5. Gestion des Préférences

### Structure
```php
notification_preferences
├── id
├── user_id
├── channel
├── notification_type
├── enabled
└── quiet_hours
```

### Implementation
```php
class NotificationPreference extends Model
{
    protected $casts = [
        'enabled' => 'boolean',
        'quiet_hours' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shouldSend()
    {
        if (!$this->enabled) return false;
        if ($this->isQuietHour()) return false;
        return true;
    }

    protected function isQuietHour()
    {
        $now = now();
        $hours = $this->quiet_hours;
        
        return $now->hour >= $hours['start'] && 
               $now->hour < $hours['end'];
    }
}
```

## 6. Monitoring et Analytics

### Métriques à Suivre
```php
[
    'notifications.sent',
    'notifications.failed',
    'notifications.delayed',
    'notifications.opened',
    'notifications.clicked'
]
```

### Structure des Logs
```php
notification_logs
├── id
├── notification_id
├── channel
├── status
├── error
├── sent_at
├── delivered_at
└── opened_at
```

### Rapports
```php
class NotificationReport
{
    public function getDeliveryStats($period)
    {
        return [
            'total_sent' => $this->getTotalSent($period),
            'success_rate' => $this->getSuccessRate($period),
            'average_delivery_time' => $this->getAverageDeliveryTime($period),
            'by_channel' => $this->getStatsByChannel($period)
        ];
    }
}
```
