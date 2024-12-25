# Documentation des Tests - Module Notifications

## Structure des Tests

```yaml
tests/
  ├── Unit/
  │   ├── Models/
  │   │   └── NotificationTest.php
  │   └── Notifications/
  │       ├── ProspectNotificationTest.php
  │       ├── DocumentNotificationTest.php
  │       └── ClientNotificationTest.php
  └── Feature/
      ├── Filament/
      │   └── Resources/
      │       └── NotificationResourceTest.php
      └── Notifications/
          ├── NotificationCreationTest.php
          ├── NotificationDeliveryTest.php
          └── NotificationTriggerTest.php
```

## Tests Unitaires

### 1. Test du Modèle
```php
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_uuid_on_creation()
    {
        $notification = Notification::factory()->create();

        $this->assertIsString($notification->id);
        $this->assertEquals(36, strlen($notification->id));
    }

    /** @test */
    public function it_can_be_marked_as_read()
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create();

        $notification->markAsRead($user);

        $this->assertNotNull($notification->read_at);
        $this->assertEquals($user->id, $notification->read_by);
    }

    /** @test */
    public function it_can_access_json_data()
    {
        $notification = Notification::factory()->create([
            'data' => ['key' => 'value']
        ]);

        $this->assertEquals('value', $notification->getData('key'));
    }
}
```

### 2. Test des Notifications Spécifiques
```php
class ProspectNotificationTest extends TestCase
{
    /** @test */
    public function it_formats_prospect_notification_correctly()
    {
        $prospect = Prospect::factory()->create();
        $notification = new ProspectNotification($prospect, 'review_required');

        $array = $notification->toArray(new User());

        $this->assertEquals($prospect->id, $array['prospect_id']);
        $this->assertEquals('review_required', $array['action']);
    }

    /** @test */
    public function it_broadcasts_correctly()
    {
        $prospect = Prospect::factory()->create();
        $notification = new ProspectNotification($prospect, 'review_required');

        $broadcastMessage = $notification->toBroadcast(new User());

        $this->assertInstanceOf(BroadcastMessage::class, $broadcastMessage);
        $this->assertArrayHasKey('type', $broadcastMessage->data);
    }
}
```

## Tests Fonctionnels

### 1. Test de Création
```php
class NotificationCreationTest extends TestCase
{
    /** @test */
    public function it_creates_notification_for_prospect_deadline()
    {
        $prospect = Prospect::factory()->create([
            'analysis_deadline' => now()->addHours(23)
        ]);

        Event::fake([NotificationCreated::class]);

        $prospect->update(['status' => 'in_progress']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => get_class($prospect),
            'notifiable_id' => $prospect->id,
            'type' => 'App\\Notifications\\ProspectDeadlineApproaching'
        ]);

        Event::assertDispatched(NotificationCreated::class);
    }

    /** @test */
    public function it_creates_notification_for_document_validation()
    {
        $document = Document::factory()->create();
        $user = User::factory()->create();

        $document->validate($user);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => get_class($document),
            'notifiable_id' => $document->id,
            'type' => 'App\\Notifications\\DocumentValidated'
        ]);
    }
}
```

### 2. Test de Livraison
```php
class NotificationDeliveryTest extends TestCase
{
    /** @test */
    public function it_delivers_notification_via_database()
    {
        $user = User::factory()->create();
        $notification = new ProspectNotification(
            Prospect::factory()->create(),
            'review_required'
        );

        $user->notify($notification);

        $this->assertDatabaseHas('notifications', [
            'type' => get_class($notification),
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id
        ]);
    }

    /** @test */
    public function it_delivers_notification_via_broadcast()
    {
        Event::fake([BroadcastNotificationCreated::class]);

        $user = User::factory()->create();
        $notification = new ProspectNotification(
            Prospect::factory()->create(),
            'review_required'
        );

        $user->notify($notification);

        Event::assertDispatched(BroadcastNotificationCreated::class);
    }
}
```

### 3. Test des Triggers
```php
class NotificationTriggerTest extends TestCase
{
    /** @test */
    public function it_triggers_notification_on_prospect_status_change()
    {
        $prospect = Prospect::factory()->create([
            'status' => 'new'
        ]);

        $prospect->update(['status' => 'in_progress']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => get_class($prospect),
            'notifiable_id' => $prospect->id,
            'type' => 'App\\Notifications\\ProspectStatusChanged'
        ]);
    }

    /** @test */
    public function it_triggers_notification_on_client_payment()
    {
        $client = Client::factory()->create();
        
        Event::fake([NotificationCreated::class]);

        $payment = Payment::factory()->create([
            'client_id' => $client->id,
            'status' => 'completed'
        ]);

        Event::assertDispatched(NotificationCreated::class);
    }
}
```

## Tests de l'Interface Filament

### 1. Test de la Resource
```php
class NotificationResourceTest extends TestCase
{
    /** @test */
    public function it_can_list_notifications()
    {
        $this->actingAs(User::factory()->create());

        Notification::factory()->count(5)->create();

        $this->get(NotificationResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('Notifications');
    }

    /** @test */
    public function it_can_mark_notification_as_read()
    {
        $this->actingAs(User::factory()->create());
        
        $notification = Notification::factory()->create();

        $this->post(NotificationResource::getUrl('markAsRead', [
            'record' => $notification->id
        ]));

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
```

## Points d'Apprentissage

### 1. Types de Tests
```yaml
Couverture:
  - Tests unitaires
  - Tests fonctionnels
  - Tests d'intégration
  - Tests d'interface
```

### 2. Bonnes Pratiques
```yaml
Organisation:
  - Un test par fonctionnalité
  - Nommage explicite
  - Isolation des tests
  - Documentation claire
```

### 3. Performance
```yaml
Optimisations:
  - Database transactions
  - Event faking
  - Factory states
  - Mocking services
```

## Factory

```php
class NotificationFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'type' => $this->faker->randomElement([
                Notification::TYPE_PROSPECT,
                Notification::TYPE_DOCUMENT,
                Notification::TYPE_CLIENT,
                Notification::TYPE_SYSTEM
            ]),
            'data' => [
                'title' => $this->faker->sentence(),
                'message' => $this->faker->paragraph(),
                'priority' => $this->faker->randomElement(['low', 'medium', 'high'])
            ],
            'read_at' => null
        ];
    }

    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => now(),
                'read_by' => User::factory()
            ];
        });
    }

    public function highPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'data' => array_merge($attributes['data'] ?? [], [
                    'priority' => 'high'
                ])
            ];
        });
    }
}
```
