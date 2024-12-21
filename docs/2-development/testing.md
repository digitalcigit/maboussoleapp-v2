# Guide des Tests - MaBoussole CRM v2

> Dernière mise à jour : 2024-12-21

## Vue d'ensemble
Documentation complète sur la stratégie de test et les bonnes pratiques pour MaBoussole CRM v2.

## Configuration

### Environnement de Test
```php
// phpunit.xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="testing"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="QUEUE_DRIVER" value="sync"/>
</php>
```

### Base de Données de Test
```php
// config/database.php
'testing' => [
    'driver' => 'mysql',
    'host' => env('DB_TEST_HOST', '127.0.0.1'),
    'database' => 'maboussole_crm_testing',
    'username' => env('DB_TEST_USERNAME', 'root'),
    'password' => env('DB_TEST_PASSWORD', ''),
],
```

## Types de Tests

### 1. Tests Unitaires
```php
class ClientTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_client()
    {
        $client = Client::factory()->create();
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }
}
```

### 2. Tests de Feature
```php
class ClientResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_clients()
    {
        $this->actingAs(User::factory()->create());
        $response = $this->get('/admin/clients');
        $response->assertSuccessful();
    }
}
```

### 3. Tests d'Intégration
```php
class ProspectToClientTest extends TestCase
{
    /** @test */
    public function it_can_convert_prospect_to_client()
    {
        $prospect = Prospect::factory()->create();
        $service = new ConversionService();
        
        $client = $service->convert($prospect);
        
        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals($prospect->id, $client->prospect_id);
    }
}
```

## Bonnes Pratiques

### 1. Organisation des Tests
```
tests/
├── Unit/
│   ├── Models/
│   └── Services/
├── Feature/
│   ├── Resources/
│   └── Controllers/
└── Integration/
    └── Workflows/
```

### 2. Conventions de Nommage
```php
/** @test */
public function it_does_something_when_something() // Descriptif et clair
```

### 3. Assertions Communes
```php
$this->assertDatabaseHas('table', []); // Vérification BDD
$this->assertInstanceOf(Class::class);  // Type checking
$response->assertStatus(200);           // HTTP status
$this->assertEquals(expected, actual);  // Valeurs
```

## Tests Spécifiques

### 1. Resources Filament
```php
class ResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_can_list_resources()
    {
        $response = $this->get('/admin/resources');
        $response->assertSuccessful();
    }
}
```

### 2. Livewire Components
```php
class ComponentTest extends TestCase
{
    /** @test */
    public function it_can_mount_component()
    {
        Livewire::test(Component::class)
            ->assertSee('Expected Content')
            ->call('action')
            ->assertEmitted('event');
    }
}
```

## Exécution des Tests

### Commandes Principales
```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=TestName

# Tests avec couverture
php artisan test --coverage
```

### Tests Parallèles
```bash
php artisan test --parallel
```

## Couverture de Code

### Configuration
```xml
<coverage>
    <include>
        <directory suffix=".php">./app</directory>
    </include>
</coverage>
```

### Rapports
```bash
php artisan test --coverage-html reports/
```

## CI/CD

### GitHub Actions
```yaml
name: Tests
on: [push, pull_request]
jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Run Tests
        run: php artisan test
```

## Maintenance

### 1. Nettoyage
```bash
php artisan test:clear
```

### 2. Mise à Jour
```bash
composer update --dev
```

---
*Documentation générée pour MaBoussole CRM v2*
