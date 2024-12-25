# Documentation des Tests - Module Documents

## Structure des Tests

```yaml
tests/
  ├── Unit/
  │   └── Models/
  │       └── DocumentTest.php
  └── Feature/
      ├── Filament/
      │   └── Resources/
      │       └── DocumentResourceTest.php
      └── Documents/
          ├── DocumentUploadTest.php
          ├── DocumentValidationTest.php
          └── DocumentPermissionsTest.php
```

## Tests Unitaires

### Test du Modèle Document
```php
class DocumentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_format_file_size()
    {
        $document = Document::factory()->create([
            'size' => 1024 * 1024 // 1MB
        ]);

        $this->assertEquals('1.00 MB', $document->formatted_size);
    }

    /** @test */
    public function it_can_be_validated()
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'status' => DocumentStatus::PENDING
        ]);

        $document->validate($user, 'Validation OK');

        $this->assertEquals(DocumentStatus::VALIDATED, $document->status);
        $this->assertEquals($user->id, $document->validated_by);
        $this->assertNotNull($document->validation_date);
    }

    /** @test */
    public function it_can_be_rejected()
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'status' => DocumentStatus::PENDING
        ]);

        $document->reject($user, 'Document non conforme');

        $this->assertEquals(DocumentStatus::REJECTED, $document->status);
        $this->assertEquals('Document non conforme', $document->comments);
    }
}
```

## Tests Fonctionnels

### 1. Upload de Documents
```php
class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_upload_valid_document()
    {
        Storage::fake('documents');

        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $response = $this->post(route('filament.resources.documents.create'), [
            'path' => $file,
            'type' => DocumentType::PASSPORT->value,
        ]);

        Storage::disk('documents')->assertExists($file->hashName());
        
        $this->assertDatabaseHas('documents', [
            'name' => 'document.pdf',
            'type' => DocumentType::PASSPORT->value,
            'size' => 100
        ]);
    }

    /** @test */
    public function it_validates_file_size()
    {
        Storage::fake('documents');

        $file = UploadedFile::fake()->create('large.pdf', 11000); // > 10MB
        
        $response = $this->post(route('filament.resources.documents.create'), [
            'path' => $file,
            'type' => DocumentType::PASSPORT->value,
        ]);

        $response->assertSessionHasErrors('path');
        Storage::disk('documents')->assertMissing($file->hashName());
    }
}
```

### 2. Validation de Documents
```php
class DocumentValidationTest extends TestCase
{
    /** @test */
    public function admin_can_validate_document()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $document = Document::factory()->create([
            'status' => DocumentStatus::PENDING
        ]);

        $this->actingAs($admin)
            ->post(route('filament.resources.documents.validate', $document));

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => DocumentStatus::VALIDATED,
            'validated_by' => $admin->id
        ]);
    }

    /** @test */
    public function it_tracks_validation_history()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $document = Document::factory()->create();

        $this->actingAs($admin)
            ->post(route('filament.resources.documents.validate', $document), [
                'comments' => 'Document validé'
            ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'comments' => 'Document validé',
            'validation_date' => now()
        ]);
    }
}
```

### 3. Tests de Permissions
```php
class DocumentPermissionsTest extends TestCase
{
    /** @test */
    public function regular_user_cannot_validate_documents()
    {
        $user = User::factory()->create(['role' => 'user']);
        $document = Document::factory()->create([
            'status' => DocumentStatus::PENDING
        ]);

        $this->actingAs($user)
            ->post(route('filament.resources.documents.validate', $document))
            ->assertForbidden();

        $this->assertEquals(DocumentStatus::PENDING, $document->fresh()->status);
    }

    /** @test */
    public function user_can_only_see_own_documents()
    {
        $user = User::factory()->create();
        $ownDocument = Document::factory()->create([
            'documentable_type' => Client::class,
            'documentable_id' => Client::factory()->create([
                'user_id' => $user->id
            ])->id
        ]);
        
        $otherDocument = Document::factory()->create();

        $this->actingAs($user)
            ->get(route('filament.resources.documents.index'))
            ->assertSee($ownDocument->name)
            ->assertDontSee($otherDocument->name);
    }
}
```

## Points d'Apprentissage

### 1. Tests de Fichiers
```yaml
Aspects Testés:
  - Upload réussi
  - Validation taille
  - Types acceptés
  - Stockage correct
```

### 2. Tests de Validation
```yaml
Scénarios:
  - Validation document
  - Rejet document
  - Historique
  - Permissions
```

### 3. Tests de Sécurité
```yaml
Vérifications:
  - Contrôle accès
  - Validation input
  - Protection données
```

## Bonnes Pratiques

### 1. Organisation
```yaml
Structure:
  - Tests unitaires
  - Tests fonctionnels
  - Tests d'intégration
```

### 2. Performance
```yaml
Optimisations:
  - Database transactions
  - Fake storage
  - Factory states
```

### 3. Maintenance
```yaml
Pratiques:
  - Nommage explicite
  - Documentation claire
  - Isolation tests
```

## Exemple de Factory

```php
class DocumentFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->file(),
            'type' => $this->faker->randomElement(DocumentType::cases()),
            'path' => 'documents/test/' . $this->faker->uuid() . '.pdf',
            'size' => $this->faker->numberBetween(1000, 5000000),
            'status' => DocumentStatus::PENDING,
        ];
    }

    public function validated()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => DocumentStatus::VALIDATED,
                'validated_by' => User::factory(),
                'validation_date' => now(),
            ];
        });
    }

    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => DocumentStatus::REJECTED,
                'validated_by' => User::factory(),
                'validation_date' => now(),
                'comments' => $this->faker->sentence(),
            ];
        });
    }
}
```
