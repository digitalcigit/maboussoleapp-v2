# Documentation des Migrations - Module Workflow Logement

## Vue d'Ensemble des Migrations

### Structure Proposée
```yaml
Migrations:
  - create_residences_table.php
  - create_landlords_table.php
  - create_housings_table.php
  - create_housing_applications_table.php
  - create_housing_requirements_table.php
  - create_housing_documents_table.php
  - create_housing_timelines_table.php
```

## Migrations Détaillées

### 1. Table des Résidences
```php
Schema::create('residences', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->string('country');
    $table->string('city');
    $table->string('address');
    $table->text('description')->nullable();
    $table->string('website')->nullable();
    $table->string('contact_email');
    $table->string('contact_phone')->nullable();
    $table->json('amenities')->nullable();
    $table->json('rules')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('code');
    $table->index(['country', 'city']);
});
```

### 2. Table des Propriétaires
```php
Schema::create('landlords', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('phone')->nullable();
    $table->string('preferred_contact_method');
    $table->text('notes')->nullable();
    $table->json('preferences')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('email');
    $table->index('phone');
});
```

### 3. Table des Logements
```php
Schema::create('housings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('residence_id')->nullable()->constrained();
    $table->foreignId('landlord_id')->nullable()->constrained();
    $table->string('reference')->unique();
    $table->enum('type', ['studio', 'room', 'apartment', 'shared', 'residence']);
    $table->string('address');
    $table->string('city');
    $table->string('postal_code');
    $table->integer('surface');
    $table->integer('rooms');
    $table->decimal('rent', 10, 2);
    $table->decimal('deposit', 10, 2);
    $table->decimal('agency_fees', 10, 2)->nullable();
    $table->text('description')->nullable();
    $table->boolean('is_furnished');
    $table->date('available_from');
    $table->date('available_until')->nullable();
    $table->boolean('is_available')->default(true);
    $table->json('amenities')->nullable();
    $table->json('requirements')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('type');
    $table->index('city');
    $table->index('is_available');
    $table->index('available_from');
});
```

### 4. Table des Demandes de Logement
```php
Schema::create('housing_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained();
    $table->foreignId('housing_id')->constrained();
    $table->string('reference_number')->unique();
    $table->enum('status', [
        'initiated',
        'searching',
        'housing_identified',
        'documents_required',
        'documents_validated',
        'application_submitted',
        'pending_decision',
        'accepted',
        'rejected',
        'contract_signed',
        'deposit_paid',
        'move_in_planned',
        'moved_in'
    ]);
    $table->date('desired_move_in_date');
    $table->date('submission_date')->nullable();
    $table->date('decision_date')->nullable();
    $table->date('contract_date')->nullable();
    $table->date('move_in_date')->nullable();
    $table->text('rejection_reason')->nullable();
    $table->decimal('monthly_budget', 10, 2);
    $table->json('guarantor_info')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('status');
    $table->index('desired_move_in_date');
    $table->index('submission_date');
});
```

### 5. Table des Exigences de Logement
```php
Schema::create('housing_requirements', function (Blueprint $table) {
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
Schema::create('housing_application_requirements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('housing_application_id')->constrained();
    $table->foreignId('housing_requirement_id')->constrained();
    $table->enum('status', ['pending', 'submitted', 'validated', 'rejected']);
    $table->text('notes')->nullable();
    $table->timestamp('validated_at')->nullable();
    $table->foreignId('validated_by')->nullable()->constrained('users');
    $table->timestamps();

    // Index
    $table->unique(['housing_application_id', 'housing_requirement_id']);
    $table->index('status');
});
```

### 6. Table des Documents de Logement
```php
Schema::create('housing_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('housing_application_id')->constrained();
    $table->foreignId('housing_requirement_id')->constrained();
    $table->foreignId('document_id')->constrained();
    $table->enum('status', ['pending', 'validated', 'rejected']);
    $table->text('rejection_reason')->nullable();
    $table->timestamp('validated_at')->nullable();
    $table->foreignId('validated_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('status');
    $table->index(['housing_application_id', 'housing_requirement_id']);
});
```

### 7. Table de la Timeline
```php
Schema::create('housing_timelines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('housing_application_id')->constrained();
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
  - residences: Résidences
  - landlords: Propriétaires
  - housings: Logements
  - housing_applications: Demandes
  - housing_requirements: Exigences
  - housing_documents: Documents
  - housing_timelines: Historique

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
  - Decimal pour montants
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
    DB::statement("ALTER TABLE housing_applications MODIFY COLUMN status ENUM(
        'initiated',
        'searching',
        'housing_identified',
        'documents_required',
        'documents_validated',
        'application_submitted',
        'pending_decision',
        'accepted',
        'rejected',
        'contract_signed',
        'deposit_paid',
        'move_in_planned',
        'moved_in',
        'nouveau_statut'
    )");
}
```

### 2. Modification des Exigences
```php
// Pour ajouter une nouvelle colonne d'exigence
public function up()
{
    Schema::table('housing_requirements', function (Blueprint $table) {
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
    DB::statement("ALTER TABLE housing_documents MODIFY COLUMN status ENUM(
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
public function test_housing_application_creation()
{
    $client = Client::factory()->create();
    $housing = Housing::factory()->create();
    
    $housingApplication = HousingApplication::create([
        'client_id' => $client->id,
        'housing_id' => $housing->id,
        'reference_number' => 'HSG-2024-001',
        'status' => 'initiated',
        'desired_move_in_date' => now()->addMonths(2),
        'monthly_budget' => 800.00
    ]);

    $this->assertDatabaseHas('housing_applications', [
        'reference_number' => 'HSG-2024-001',
        'status' => 'initiated'
    ]);
}
```
