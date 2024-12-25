# Documentation des Tests - Module Clients

## Structure des Tests

```yaml
tests/Feature/
  ├── Filament/Resources/
  │   └── ClientResourceTest.php         # Tests de l'interface Filament
  ├── Business/
  │   └── ProspectToClientConversionTest.php  # Tests de conversion
  └── ClientPermissionsTest.php          # Tests des permissions
```

## Types de Tests

### 1. Tests de Resource Filament
Fichier : `ClientResourceTest.php`

#### Objectifs
- Vérifier l'interface d'administration
- Tester les formulaires clients
- Valider les actions spécifiques
- Contrôler les permissions

#### Exemples de Tests
```php
class ClientResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_clients()
    {
        // Test d'affichage de la liste
    }

    /** @test */
    public function it_can_create_client()
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
Fichier : `ProspectToClientConversionTest.php`

#### Objectifs
- Tester la conversion prospect → client
- Valider les données transférées
- Vérifier les statuts
- Contrôler les documents

#### Exemples de Tests
```php
class ProspectToClientConversionTest extends TestCase
{
    /** @test */
    public function it_converts_prospect_to_client()
    {
        // Test de conversion
    }

    /** @test */
    public function it_transfers_prospect_data()
    {
        // Test de transfert de données
    }
}
```

### 3. Tests de Permissions
Fichier : `ClientPermissionsTest.php`

#### Objectifs
- Vérifier les droits d'accès
- Tester les restrictions
- Valider les rôles

#### Exemples de Tests
```php
class ClientPermissionsTest extends TestCase
{
    /** @test */
    public function user_can_view_assigned_clients()
    {
        // Test d'accès aux clients assignés
    }

    /** @test */
    public function user_cannot_view_unassigned_clients()
    {
        // Test de restriction d'accès
    }
}
```

## Points d'Apprentissage

### 1. Tests Filament
- Navigation dans l'interface
- Soumission de formulaires
- Validation des actions
- Gestion des permissions

### 2. Tests Métier
- Processus de conversion
- Validation des données
- Transitions d'état
- Intégrité des données

### 3. Tests de Sécurité
- Contrôle d'accès
- Validation des rôles
- Protection des données

## Bonnes Pratiques

### 1. Organisation des Tests
```yaml
Par Fonctionnalité:
  - Interface utilisateur
  - Logique métier
  - Sécurité

Par Type:
  - Tests unitaires
  - Tests d'intégration
  - Tests de bout en bout
```

### 2. Nommage des Tests
```php
// Format recommandé
public function test_action_condition_resultat_attendu()
{
    // Contenu du test
}

// Exemples
test_can_create_client_with_valid_data()
test_cannot_access_unassigned_client_details()
```

### 3. Structure AAA
```php
public function test_example()
{
    // Arrange
    $client = Client::factory()->create();

    // Act
    $result = $client->someAction();

    // Assert
    $this->assertTrue($result);
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

### Exemple de Test Complet
```php
/** @test */
public function test_client_creation_process()
{
    // Arrange
    $this->actingAs($user = User::factory()->create());
    $prospect = Prospect::factory()->create();

    // Act
    $response = $this->post(route('filament.resources.clients.create'), [
        'prospect_id' => $prospect->id,
        'client_number' => 'CL-2024-001',
        'status' => Client::STATUS_ACTIVE
    ]);

    // Assert
    $this->assertDatabaseHas('clients', [
        'prospect_id' => $prospect->id,
        'client_number' => 'CL-2024-001'
    ]);
}
```
