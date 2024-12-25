# Documentation des Migrations - Module Workflow Admission

## Vue d'Ensemble des Migrations

### Structure Proposée
```yaml
Migrations:
  - create_institutions_table.php
  - create_programs_table.php
  - create_admission_applications_table.php
  - create_admission_requirements_table.php
  - create_admission_documents_table.php
  - create_admission_timelines_table.php
```

## Migrations Détaillées

### 1. Table des Établissements
```php
Schema::create('institutions', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->string('country');
    $table->string('city');
    $table->text('description')->nullable();
    $table->string('website')->nullable();
    $table->string('contact_email');
    $table->string('contact_phone')->nullable();
    $table->json('admission_requirements')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('code');
    $table->index(['country', 'city']);
});
```

### 2. Table des Programmes
```php
Schema::create('programs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('institution_id')->constrained();
    $table->string('name');
    $table->string('code')->unique();
    $table->enum('level', ['bachelor', 'master', 'phd', 'certificate']);
    $table->string('duration');
    $table->decimal('tuition_fee', 10, 2);
    $table->string('currency', 3);
    $table->text('description')->nullable();
    $table->json('prerequisites')->nullable();
    $table->json('admission_requirements')->nullable();
    $table->json('key_dates')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('code');
    $table->index('level');
});
```

### 3. Table des Demandes d'Admission
```php
Schema::create('admission_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained();
    $table->foreignId('institution_id')->constrained();
    $table->foreignId('program_id')->constrained();
    $table->string('reference_number')->unique();
    $table->enum('status', [
        'initiated',
        'in_progress',
        'documents_required',
        'documents_validated',
        'submitted_institution',
        'pending_decision',
        'accepted',
        'conditional_accepted',
        'additional_documents',
        'rejected',
        'final_registration'
    ]);
    $table->date('intake_date');
    $table->date('submission_deadline');
    $table->date('submitted_date')->nullable();
    $table->date('decision_date')->nullable();
    $table->text('rejection_reason')->nullable();
    $table->text('conditional_requirements')->nullable();
    $table->json('academic_history')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('status');
    $table->index('intake_date');
    $table->index('submission_deadline');
});
```

### 4. Table des Exigences d'Admission
```php
Schema::create('admission_requirements', function (Blueprint $table) {
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
Schema::create('admission_application_requirements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('admission_application_id')->constrained();
    $table->foreignId('admission_requirement_id')->constrained();
    $table->enum('status', ['pending', 'submitted', 'validated', 'rejected']);
    $table->text('notes')->nullable();
    $table->timestamp('validated_at')->nullable();
    $table->foreignId('validated_by')->nullable()->constrained('users');
    $table->timestamps();

    // Index
    $table->unique(['admission_application_id', 'admission_requirement_id']);
    $table->index('status');
});
```

### 5. Table des Documents d'Admission
```php
Schema::create('admission_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('admission_application_id')->constrained();
    $table->foreignId('admission_requirement_id')->constrained();
    $table->foreignId('document_id')->constrained();
    $table->enum('status', ['pending', 'validated', 'rejected']);
    $table->text('rejection_reason')->nullable();
    $table->timestamp('validated_at')->nullable();
    $table->foreignId('validated_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();

    // Index
    $table->index('status');
    $table->index(['admission_application_id', 'admission_requirement_id']);
});
```

### 6. Table de la Timeline
```php
Schema::create('admission_timelines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('admission_application_id')->constrained();
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
  - institutions: Établissements
  - programs: Programmes
  - admission_applications: Demandes
  - admission_requirements: Exigences
  - admission_documents: Documents
  - admission_timelines: Historique

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
    DB::statement("ALTER TABLE admission_applications MODIFY COLUMN status ENUM(
        'initiated',
        'in_progress',
        'documents_required',
        'documents_validated',
        'submitted_institution',
        'pending_decision',
        'accepted',
        'conditional_accepted',
        'additional_documents',
        'rejected',
        'final_registration',
        'nouveau_statut'
    )");
}
```

### 2. Modification des Exigences
```php
// Pour ajouter une nouvelle colonne d'exigence
public function up()
{
    Schema::table('admission_requirements', function (Blueprint $table) {
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
    DB::statement("ALTER TABLE admission_documents MODIFY COLUMN status ENUM(
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
public function test_admission_application_creation()
{
    $client = Client::factory()->create();
    $institution = Institution::factory()->create();
    $program = Program::factory()->create([
        'institution_id' => $institution->id
    ]);
    
    $admissionApplication = AdmissionApplication::create([
        'client_id' => $client->id,
        'institution_id' => $institution->id,
        'program_id' => $program->id,
        'reference_number' => 'ADM-2024-001',
        'status' => 'initiated',
        'intake_date' => now()->addMonths(6),
        'submission_deadline' => now()->addMonths(3)
    ]);

    $this->assertDatabaseHas('admission_applications', [
        'reference_number' => 'ADM-2024-001',
        'status' => 'initiated'
    ]);
}
```
