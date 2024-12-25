# Documentation des Migrations - Module Documents

## Vue d'Ensemble des Migrations

### Structure Actuelle
```yaml
Migrations:
  - 2024_12_12_145426_create_documents_table.php
```

## Migration Principale

### Table des Documents
```php
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->morphs('documentable');     // Relation polymorphique (prospects/clients)
    $table->string('name');             // Nom du document
    $table->enum('type', [             // Type de document
        'passport',
        'cv',
        'diploma',
        'other'
    ]);
    $table->string('path');            // Chemin de stockage
    $table->bigInteger('size');        // Taille en octets
    $table->enum('status', [           // Statut de validation
        'pending',
        'validated',
        'rejected'
    ]);
    $table->foreignId('validated_by')  // Validateur
        ->nullable()
        ->constrained('users');
    $table->timestamp('validation_date')->nullable(); // Date de validation
    $table->text('comments')->nullable(); // Commentaires
    $table->timestamps();              // created_at et updated_at

    // Index pour les performances
    $table->index('status');
    $table->index('type');
});
```

## Points d'Apprentissage

### 1. Structure de la Table
```yaml
Champs Clés:
  - Relations:
    - documentable_type (polymorphique)
    - documentable_id (polymorphique)
    - validated_by (foreign key)
  
  - Informations Document:
    - name
    - type (enum)
    - path
    - size
  
  - Validation:
    - status (enum)
    - validation_date
    - comments
  
  - Métadonnées:
    - created_at
    - updated_at
```

### 2. Relations Polymorphiques
```php
// La relation documentable permet de lier un document à :
- Prospects
- Clients
- Autres entités futures

// Exemple d'utilisation :
$document->documentable; // Retourne le prospect ou client associé
```

### 3. Énumérations
```php
// Types de documents
enum DocumentType: string {
    case PASSPORT = 'passport';
    case CV = 'cv';
    case DIPLOMA = 'diploma';
    case OTHER = 'other';
}

// Statuts de validation
enum DocumentStatus: string {
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case REJECTED = 'rejected';
}
```

## Bonnes Pratiques

### 1. Indexation
```yaml
Index Créés:
  - status: Pour filtrage rapide par statut
  - type: Pour recherche par type de document
  - documentable: Automatique pour relation polymorphique
```

### 2. Validation
```php
// Règles de validation suggérées
$rules = [
    'name' => ['required', 'string', 'max:255'],
    'type' => ['required', 'in:passport,cv,diploma,other'],
    'path' => ['required', 'string'],
    'size' => ['required', 'integer', 'min:0'],
    'status' => ['required', 'in:pending,validated,rejected'],
];
```

### 3. Stockage
```php
// Structure de stockage suggérée
storage/
  └── documents/
      ├── prospects/
      │   └── {prospect_id}/
      └── clients/
          └── {client_id}/
```

## Guide de Maintenance

### 1. Ajout de Types
```php
// Pour ajouter un nouveau type de document
public function up()
{
    DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('passport', 'cv', 'diploma', 'other', 'nouveau_type')");
}
```

### 2. Modification de Statuts
```php
// Pour modifier les statuts disponibles
public function up()
{
    DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending', 'validated', 'rejected', 'nouveau_statut')");
}
```

### 3. Gestion des Fichiers
```php
// Lors de la suppression d'un document
public function down()
{
    // Nettoyer les fichiers physiques
    Storage::delete($document->path);
    
    // Supprimer l'enregistrement
    $document->delete();
}
```

## Tests et Validation

### Points à Vérifier
1. **Intégrité des Données**
   - Types de fichiers valides
   - Tailles correctes
   - Relations intègres

2. **Performance**
   - Utilisation des index
   - Taille des fichiers
   - Temps d'accès

3. **Sécurité**
   - Accès contrôlé
   - Validation fichiers
   - Protection données

### Exemple de Test
```php
/** @test */
public function test_document_creation_with_valid_data()
{
    $document = Document::create([
        'name' => 'test.pdf',
        'type' => 'passport',
        'path' => 'documents/test.pdf',
        'size' => 1024,
        'status' => 'pending'
    ]);

    $this->assertDatabaseHas('documents', [
        'name' => 'test.pdf',
        'type' => 'passport'
    ]);
}
```
