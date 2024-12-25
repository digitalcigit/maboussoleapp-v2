# Documentation des Tests - Module Prospects

## Structure des Tests

```yaml
tests/
  └── Feature/
      ├── Filament/
      │   └── Resources/
      │       └── ProspectResourceTest.php    # Tests de l'interface Filament
      └── Business/
          ├── ProspectConversionTest.php      # Tests de conversion
          └── ProspectToClientConversionTest.php # Tests spécifiques conversion client
```

## Types de Tests

### 1. Tests de Resource Filament
Fichier : `ProspectResourceTest.php`

#### Objectifs
- Vérifier l'interface administrateur
- Tester les formulaires
- Valider les actions
- Contrôler les permissions

#### Exemples de Tests
```php
class ProspectResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_prospects()
    {
        // Test d'affichage de la liste
    }

    /** @test */
    public function it_can_create_prospect()
    {
        // Test de création
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Test de validation
    }
}
```

### 2. Tests de Conversion
Fichier : `ProspectConversionTest.php`

#### Objectifs
- Tester le processus de conversion
- Valider les règles métier
- Vérifier les transitions d'état
- Contrôler les données

#### Exemples de Tests
```php
class ProspectConversionTest extends TestCase
{
    /** @test */
    public function it_can_convert_valid_prospect()
    {
        // Test de conversion valide
    }

    /** @test */
    public function it_prevents_invalid_conversion()
    {
        // Test de validation conversion
    }
}
```

## Bonnes Pratiques

### 1. Organisation des Tests
```yaml
Par Fonctionnalité:
  - Tests unitaires
  - Tests d'intégration
  - Tests de bout en bout

Par Contexte:
  - Interface utilisateur
  - Logique métier
  - Validation données
```

### 2. Nommage des Tests
```php
// Format recommandé
public function test_action_condition_resultat_attendu()
{
    // Contenu du test
}

// Exemples
test_can_create_prospect_with_valid_data()
test_cannot_convert_prospect_without_required_documents()
```

### 3. Structure AAA
```php
public function test_example()
{
    // Arrange
    $prospect = Prospect::factory()->create();

    // Act
    $result = $prospect->someAction();

    // Assert
    $this->assertTrue($result);
}
```

## Points d'Apprentissage

### 1. Tests Filament
- Navigation dans l'interface
- Soumission de formulaires
- Validation des actions
- Gestion des permissions

### 2. Tests Métier
- Règles de conversion
- Validation des données
- Transitions d'état
- Intégrité des données

### 3. Meilleures Pratiques
- Tests isolés
- Données de test claires
- Assertions précises
- Documentation des tests

## Exemples Pratiques

### Test de Création
```php
/** @test */
public function test_can_create_prospect()
{
    $this->actingAs($user = User::factory()->create());

    $response = $this->post(route('filament.resources.prospects.create'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        // autres champs requis
    ]);

    $this->assertDatabaseHas('prospects', [
        'email' => 'john@example.com',
    ]);
}
```

### Test de Conversion
```php
/** @test */
public function test_prospect_conversion()
{
    $prospect = Prospect::factory()->create([
        'status' => Prospect::STATUS_APPROVED
    ]);

    $result = $prospect->convertToClient();

    $this->assertInstanceOf(Client::class, $result);
    $this->assertEquals(Prospect::STATUS_CONVERTED, $prospect->fresh()->status);
}
```

## Maintenance des Tests

### Points de Surveillance
1. **Couverture de Code**
   - Tests critiques
   - Scénarios edge-case
   - Validation données

2. **Performance**
   - Temps d'exécution
   - Utilisation mémoire
   - Isolation tests

3. **Maintenance**
   - Documentation à jour
   - Nettoyage données
   - Revue régulière
