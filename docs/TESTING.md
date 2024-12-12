# Guide des Tests - MaBoussole CRM v2

## Vue d'ensemble

Ce document décrit l'approche de test adoptée pour MaBoussole CRM v2, incluant la configuration, les conventions et les bonnes pratiques.

## Configuration

### Base de données de test

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

### PHPUnit

```xml
<!-- phpunit.xml -->
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="testing"/>
</php>
```

## Structure des Tests

### Tests de Ressources Filament

Chaque ressource Filament dispose de tests couvrant les opérations CRUD :

```php
class ResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /** @test */
    public function it_can_list_resources() { ... }

    /** @test */
    public function it_can_create_resource() { ... }

    /** @test */
    public function it_can_edit_resource() { ... }

    /** @test */
    public function it_can_delete_resource() { ... }
}
```

## Factories

### Conventions pour les Factories

1. **Dates et Timestamps**
   ```php
   'created_at' => $this->faker->dateTimeBetween('-1 year'),
   'updated_at' => $this->faker->dateTimeBetween('-1 month'),
   ```

2. **Relations**
   ```php
   'user_id' => User::factory(),
   'subject_type' => $subject::class,
   'subject_id' => $subject->id,
   ```

3. **États Spécifiques**
   ```php
   public function completed()
   {
       return $this->state([
           'completed_at' => now(),
       ]);
   }
   ```

## Bonnes Pratiques

### 1. Préparation des Tests

- Utiliser `RefreshDatabase` pour un état propre
- Créer un utilisateur authentifié dans `setUp()`
- Préparer les données nécessaires avant chaque test

### 2. Assertions

- Vérifier les réponses HTTP
- Valider les données en base
- Tester les redirections
- Vérifier les autorisations

### 3. Dates et Formats

- Formater les dates en `Y-m-d` pour les requêtes
- Utiliser Carbon pour les comparaisons
- Gérer les fuseaux horaires

## Exemples de Tests

### Test de Liste

```php
/** @test */
public function it_can_list_resources()
{
    $resources = Resource::factory()->count(5)->create();
    
    $response = $this->get(ResourceClass::getUrl('index'));
    
    $response->assertSuccessful();
    $response->assertSee($resources[0]->name);
}
```

### Test de Création

```php
/** @test */
public function it_can_create_resource()
{
    $newResource = Resource::factory()->make();
    
    $response = $this->post(ResourceClass::getUrl('create'), [
        'name' => $newResource->name,
        'email' => $newResource->email,
        // autres champs...
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('resources', [
        'email' => $newResource->email,
    ]);
}
```

## Couverture des Tests

### Éléments à Tester

1. **CRUD de Base**
   - Liste
   - Création
   - Édition
   - Suppression

2. **Validations**
   - Champs requis
   - Formats
   - Règles métier

3. **Autorisations**
   - Accès aux ressources
   - Permissions spécifiques
   - Restrictions par rôle

4. **Relations**
   - Associations
   - Contraintes
   - Cascades

## Maintenance

### Bonnes Pratiques

1. **Organisation**
   - Un fichier par ressource
   - Tests groupés par fonctionnalité
   - Noms descriptifs

2. **Documentation**
   - Commentaires explicatifs
   - Annotations PHPDoc
   - Cas de test documentés

3. **Performance**
   - Tests isolés
   - Utilisation des transactions
   - Nettoyage des données
