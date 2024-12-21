# Système de Notifications - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Documentation du système de notifications, incluant les notifications en temps réel, emails et SMS.

## Types de Notifications

### 1. Notifications Système
```php
const SYSTEM_NOTIFICATIONS = [
    'new_prospect'     => 'Nouveau Prospect',
    'document_upload'  => 'Document Uploadé',
    'visa_update'      => 'Mise à Jour Visa',
    'payment_received' => 'Paiement Reçu',
    'task_assigned'    => 'Tâche Assignée'
];
```

### 2. Notifications Email
```php
const EMAIL_TEMPLATES = [
    'prospect_welcome'    => 'Bienvenue Prospect',
    'client_welcome'      => 'Bienvenue Client',
    'document_reminder'   => 'Rappel Documents',
    'visa_status_update' => 'Statut Visa',
    'payment_confirmation' => 'Confirmation Paiement'
];
```

### 3. Notifications SMS
```php
const SMS_TEMPLATES = [
    'appointment_reminder' => 'Rappel RDV',
    'document_urgent'     => 'Document Urgent',
    'visa_approved'       => 'Visa Approuvé'
];
```

## Configuration

### Canaux de Notification
```php
// config/notifications.php
return [
    'channels' => [
        'mail' => [
            'driver' => 'smtp',
            'template_path' => resource_path('views/emails'),
        ],
        'sms' => [
            'driver' => 'twilio',
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM_NUMBER'),
        ],
        'database' => [
            'table' => 'notifications',
            'cleanup_days' => 30,
        ],
    ],
];
```

## Implémentation

### 1. Notification de Base
```php
abstract class BaseNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function shouldSend($notifiable)
    {
        return $notifiable->notifications_enabled;
    }
}
```

### 2. Notification Prospect
```php
class NewProspectNotification extends BaseNotification
{
    private $prospect;

    public function __construct(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouveau Prospect Assigné')
            ->markdown('emails.prospects.new', [
                'prospect' => $this->prospect,
                'advisor' => $notifiable
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_prospect',
            'prospect_id' => $this->prospect->id,
            'message' => "Nouveau prospect {$this->prospect->full_name}"
        ];
    }
}
```

### 3. Notification Document
```php
class DocumentUploadNotification extends BaseNotification
{
    private $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'document_id' => $this->document->id,
            'status' => 'uploaded'
        ]);
    }
}
```

## Broadcast en Temps Réel

### Configuration Echo
```javascript
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true
});
```

### Écoute des Notifications
```javascript
// resources/js/notifications.js
Echo.private(`App.Models.User.${userId}`)
    .notification((notification) => {
        this.notifications.unshift(notification);
        this.showNotification(notification);
    });
```

## File d'Attente

### Configuration
```php
// config/queue.php
'notifications' => [
    'driver' => 'redis',
    'connection' => 'notifications',
    'queue' => 'notifications',
    'retry_after' => 90,
],
```

### Traitement
```php
class ProcessNotificationJob implements ShouldQueue
{
    public function handle()
    {
        // Logique de traitement
    }

    public function failed(Exception $exception)
    {
        Log::error('Notification failed', [
            'exception' => $exception->getMessage()
        ]);
    }
}
```

## Interface Filament

### Widget Notifications
```php
class NotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.notifications';

    protected function getViewData(): array
    {
        return [
            'notifications' => auth()->user()
                ->unreadNotifications()
                ->take(5)
                ->get()
        ];
    }
}
```

## Tests

### Test des Notifications
```php
class NotificationTest extends TestCase
{
    /** @test */
    public function it_sends_new_prospect_notification()
    {
        Notification::fake();

        $advisor = User::factory()->create();
        $prospect = Prospect::factory()->create();

        $advisor->notify(new NewProspectNotification($prospect));

        Notification::assertSentTo(
            $advisor,
            NewProspectNotification::class,
            function ($notification) use ($prospect) {
                return $notification->prospect->id === $prospect->id;
            }
        );
    }
}
```

## Maintenance

### Commandes Artisan
```bash
# Nettoyage notifications
php artisan notifications:clean

# Envoi notifications en attente
php artisan notifications:send-pending

# Stats notifications
php artisan notifications:stats
```

---
*Documentation générée pour MaBoussole CRM v2*
