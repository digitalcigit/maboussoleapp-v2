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

## Gestion des Bases de Données de Test

### Configuration

L'application utilise deux bases de données distinctes :

1. **Base de Production/Développement**
   - Nom : `maboussole_crm`
   - Configuration : fichier `.env`
   - Usage : développement et production
   - Protection : ne jamais modifier directement pendant les tests

2. **Base de Test**
   - Nom : `maboussole_crm_testing`
   - Configuration : fichier `phpunit.xml`
   - Usage : tests automatisés uniquement
   - Comportement : réinitialisée à chaque suite de tests

### ⚠️ Important : Commandes de Test

Pour éviter toute modification accidentelle de la base de production, TOUJOURS utiliser le flag `--env=testing` pour les commandes liées aux tests :

```bash
# Exécuter les tests
php artisan test --env=testing

# Rafraîchir la base de test
php artisan migrate:fresh --seed --env=testing

# Autres commandes artisan en contexte de test
php artisan [commande] --env=testing
```

### Sécurité et Isolation

1. **Trait RefreshDatabase**
   - Utilisé dans les classes de test
   - Réinitialise la base UNIQUEMENT pendant l'exécution des tests
   - Ne protège PAS contre les commandes artisan manuelles

2. **Vérifications de Sécurité**
   - Toujours vérifier la base ciblée avant les migrations
   - Ne jamais exécuter `migrate:fresh` sans `--env=testing`
   - Maintenir les configurations séparées et à jour

3. **Bonnes Pratiques**
   - Documenter toute modification du schéma de base de données
   - Tester les migrations dans les deux sens (up/down)
   - Vérifier régulièrement l'isolation des environnements

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
