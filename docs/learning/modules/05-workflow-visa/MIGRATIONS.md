# Documentation des Migrations - Module Workflow Visa

## Vue d'Ensemble des Migrations

### Structure Proposée
```yaml
Migrations:
  - create_visa_applications_table.php
  - create_visa_requirements_table.php
  - create_visa_documents_table.php
  - create_visa_timelines_table.php
```

## Migrations Détaillées

### 1. Table des Demandes de Visa
```php
Schema::create('visa_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained();
    $table->string('reference_number')->unique();
    $table->enum('status', [
        'initiated',
        'in_progress',
        'documents_required',
        'documents_validated',
        'submitted_embassy',
        'pending_decision',
        'approved',
        'rejected',
        'revision'
    ]);
    $table->string('visa_type');
    $table->string('embassy');
    $table->date('planned_travel_date');
    $table->date('submission_date')->nullable();
    $table->date('decision_date')->nullable();
    $table->text('rejection_reason')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('status');
    $table->index('visa_type');
    $table->index('submission_date');
});
```

### 2. Table des Exigences Visa
```php
Schema::create('visa_requirements', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->text('description');
    $table->string('document_type');
    $table->boolean('is_mandatory');
    $table->json('validation_rules')->nullable();
    $table->timestamps();

    // Index
    $table->index('code');
    $table->index('document_type');
});

// Table pivot pour les exigences spécifiques
Schema::create('visa_application_requirements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('visa_application_id')->constrained();
    $table->foreignId('visa_requirement_id')->constrained();
    $table->enum('status', ['pending', 'submitted', 'validated', 'rejected']);
    $table->text('notes')->nullable();
    $table->timestamp('validated_at')->nullable();
    $table->foreignId('validated_by')->nullable()->constrained('users');
    $table->timestamps();

    // Index
    $table->unique(['visa_application_id', 'visa_requirement_id']);
    $table->index('status');
});
```

### 3. Table des Documents Visa
```php
Schema::create('visa_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('visa_application_id')->constrained();
    $table->foreignId('visa_requirement_id')->constrained();
    $table->foreignId('document_id')->constrained();
    $table->enum('status', ['pending', 'validated', 'rejected']);
    $table->text('rejection_reason')->nullable();
    $table->timestamp('validated_at')->nullable();
    $table->foreignId('validated_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('status');
    $table->index(['visa_application_id', 'visa_requirement_id']);
});
```

### 4. Table de la Timeline
```php
Schema::create('visa_timelines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('visa_application_id')->constrained();
    $table->string('event_type');
    $table->string('title');
    $table->text('description')->nullable();
    $table->json('metadata')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();

    // Index
    $table->index('event_type');
    $table->index('created_at');
});
```

## Points d'Apprentissage

### 1. Structure des Tables
```yaml
Tables Principales:
  - visa_applications: Demandes
  - visa_requirements: Exigences
  - visa_documents: Documents
  - visa_timelines: Historique

Relations:
  - One-to-Many
  - Many-to-Many
  - Polymorphique
```

### 2. Types de Données
```yaml
Utilisés:
  - Enum pour statuts
  - JSON pour metadata
  - Date pour échéances
  - Text pour descriptions
  - UUID pour références
```

### 3. Indexation
```yaml
Stratégie:
  - Index simples
  - Index composites
  - Index uniques
  - Clés étrangères
```

## Bonnes Pratiques

### 1. Intégrité des Données
```yaml
Contraintes:
  - Clés étrangères
  - Valeurs uniques
  - Valeurs par défaut
  - Nullable/Required
```

### 2. Performance
```yaml
Optimisations:
  - Index appropriés
  - Types optimaux
  - Relations efficaces
```

### 3. Maintenance
```yaml
Stratégies:
  - Soft deletes
  - Timestamps
  - Audit trail
  - Backup data
```

## Guide de Maintenance

### 1. Ajout de Statuts
```php
// Pour ajouter un nouveau statut
public function up()
{
    DB::statement("ALTER TABLE visa_applications MODIFY COLUMN status ENUM(
        'initiated',
        'in_progress',
        'documents_required',
        'documents_validated',
        'submitted_embassy',
        'pending_decision',
        'approved',
        'rejected',
        'revision',
        'nouveau_statut'
    )");
}
```

### 2. Modification des Exigences
```php
// Pour ajouter une nouvelle colonne d'exigence
public function up()
{
    Schema::table('visa_requirements', function (Blueprint $table) {
        $table->boolean('requires_translation')
            ->after('is_mandatory')
            ->default(false);
    });
}
```

### 3. Gestion des Documents
```php
// Pour ajouter un nouveau type de document
public function up()
{
    DB::statement("ALTER TABLE visa_documents MODIFY COLUMN status ENUM(
        'pending',
        'validated',
        'rejected',
        'needs_update'
    )");
}
```

## Tests et Validation

### Points à Vérifier
1. **Intégrité des Données**
   - Relations valides
   - Contraintes respectées
   - Types corrects

2. **Performance**
   - Index efficaces
   - Requêtes optimisées
   - Charge données

3. **Maintenance**
   - Migrations propres
   - Rollback possible
   - Données cohérentes

### Exemple de Test
```php
/** @test */
public function test_visa_application_creation()
{
    $client = Client::factory()->create();
    
    $visaApplication = VisaApplication::create([
        'client_id' => $client->id,
        'reference_number' => 'VISA-2024-001',
        'status' => 'initiated',
        'visa_type' => 'tourist',
        'embassy' => 'France',
        'planned_travel_date' => now()->addMonths(3)
    ]);

    $this->assertDatabaseHas('visa_applications', [
        'reference_number' => 'VISA-2024-001',
        'status' => 'initiated'
    ]);
}
```
