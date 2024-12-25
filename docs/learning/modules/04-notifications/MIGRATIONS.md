# Documentation des Migrations - Module Notifications

## Vue d'Ensemble des Migrations

### Structure Actuelle
```yaml
Migrations:
  - 2024_12_12_145602_create_notifications_table.php
```

## Migration Principale

### Table des Notifications
```php
Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary();          // ID unique UUID
    $table->string('type');                 // Type de notification
    $table->morphs('notifiable');           // Relation polymorphique
    $table->json('data');                   // Données de notification
    $table->timestamp('read_at')->nullable(); // Date de lecture
    $table->timestamps();                   // created_at et updated_at
});
```

## Points d'Apprentissage

### 1. Structure de la Table
```yaml
Champs Clés:
  - ID:
    - Type: UUID
    - Primary Key
    - Format standardisé
  
  - Relations:
    - notifiable_type
    - notifiable_id
    - Polymorphique
  
  - Données:
    - Type notification
    - Données JSON
    - État lecture
  
  - Timestamps:
    - Création
    - Mise à jour
    - Lecture
```

### 2. Triggers Database

#### Trigger Deadline Prospect
```sql
CREATE TRIGGER check_prospect_deadline
AFTER UPDATE ON prospects
FOR EACH ROW
BEGIN
    IF NEW.analysis_deadline < NOW() + INTERVAL 24 HOUR THEN
        INSERT INTO notifications (
            id,
            type,
            notifiable_type,
            notifiable_id,
            data,
            created_at,
            updated_at
        )
        VALUES (
            UUID(),
            "App\\Notifications\\ProspectDeadlineApproaching",
            "prospect",
            NEW.id,
            JSON_OBJECT(
                "prospect_id", NEW.id,
                "reference_number", NEW.reference_number,
                "deadline", NEW.analysis_deadline
            ),
            NOW(),
            NOW()
        );
    END IF;
END;
```

## Guide d'Utilisation

### 1. Types de Notifications
```php
// Exemples de types de notifications
const NOTIFICATION_TYPES = [
    'App\\Notifications\\ProspectDeadlineApproaching',
    'App\\Notifications\\DocumentValidated',
    'App\\Notifications\\ClientPaymentReceived',
    'App\\Notifications\\SystemMaintenance'
];
```

### 2. Structure JSON
```json
{
    "prospect_id": 123,
    "reference_number": "PROS-2024-001",
    "deadline": "2024-12-31 23:59:59",
    "additional_data": {
        "priority": "high",
        "category": "visa"
    }
}
```

### 3. Relations Polymorphiques
```php
// Exemple de requête
$notifications = Notification::where('notifiable_type', 'prospect')
    ->where('notifiable_id', $prospectId)
    ->whereNull('read_at')
    ->get();
```

## Bonnes Pratiques

### 1. Performance
```yaml
Indexes:
  - notifiable_type et notifiable_id
  - read_at pour filtrage
  - type pour recherche

Optimisations:
  - JSON indexé
  - Partitionnement possible
  - Nettoyage régulier
```

### 2. Maintenance
```yaml
Tâches:
  - Purge anciennes notifications
  - Vérification triggers
  - Optimisation indexes
```

### 3. Sécurité
```yaml
Protection:
  - Validation input
  - Échappement SQL
  - Contrôle accès
```

## Guide de Maintenance

### 1. Ajout de Triggers
```sql
-- Template pour nouveau trigger
DELIMITER //

CREATE TRIGGER trigger_name
AFTER [INSERT|UPDATE|DELETE] ON table_name
FOR EACH ROW
BEGIN
    -- Logique du trigger
    INSERT INTO notifications (...) VALUES (...);
END//

DELIMITER ;
```

### 2. Nettoyage
```sql
-- Suppression notifications anciennes
DELETE FROM notifications
WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
AND read_at IS NOT NULL;
```

### 3. Maintenance Indexes
```sql
-- Ajout index pour performance
ALTER TABLE notifications
ADD INDEX idx_notification_type (type),
ADD INDEX idx_notification_read (read_at);
```

## Tests et Validation

### Points à Vérifier
1. **Intégrité des Données**
   - Format UUID valide
   - JSON bien formé
   - Relations valides

2. **Performance**
   - Exécution triggers
   - Temps réponse
   - Utilisation indexes

3. **Maintenance**
   - Purge données
   - État triggers
   - Santé indexes

### Exemple de Test
```php
/** @test */
public function test_notification_trigger_creation()
{
    $prospect = Prospect::factory()->create([
        'analysis_deadline' => now()->addHours(23)
    ]);

    $prospect->update([
        'status' => 'in_progress'
    ]);

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => 'prospect',
        'notifiable_id' => $prospect->id,
        'type' => 'App\\Notifications\\ProspectDeadlineApproaching'
    ]);
}
```
