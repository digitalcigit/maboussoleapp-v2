# Implémentation des Tests Unitaires

## Configuration de l'Environnement

### 1. Configuration de PHPUnit
```php
// phpunit.xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### 2. Structure des Dossiers
```
tests/
├── Unit/
│   ├── DossierAmountTest.php
│   └── ...
└── Feature/
    └── ...
```

## Exemples Pratiques

### 1. Test de Formatage des Montants

#### Le Code à Tester
```php
// app/Models/Dossier.php
class Dossier extends Model
{
    public function setTuitionTotalAmountAttribute($value)
    {
        if (!$value) return null;
        $float = (float) str_replace([',', ' '], '', $value);
        $this->attributes['tuition_total_amount'] = (int) $float;
    }
}
```

#### Le Test Unitaire
```php
// tests/Unit/DossierAmountTest.php
class DossierAmountTest extends TestCase
{
    /** @test */
    public function test_amount_formatting()
    {
        $dossier = new Dossier();
        
        // Test avec différents formats
        $dossier->tuition_total_amount = "1000000.00";
        $this->assertEquals(1000000, $dossier->tuition_total_amount);
        
        $dossier->tuition_total_amount = "1,000,000";
        $this->assertEquals(1000000, $dossier->tuition_total_amount);
    }
}
```

### 2. Test avec Mock d'Objets

```php
class PaymentServiceTest extends TestCase
{
    public function test_payment_processing()
    {
        // Créer un mock du service de paiement
        $paymentGateway = $this->createMock(PaymentGateway::class);
        
        // Configurer le comportement attendu
        $paymentGateway->expects($this->once())
            ->method('process')
            ->with(1000000)
            ->willReturn(true);
            
        // Injecter le mock
        $service = new PaymentService($paymentGateway);
        
        // Tester le service
        $result = $service->processPayment(1000000);
        $this->assertTrue($result);
    }
}
```

## Techniques Avancées

### 1. Data Providers
```php
class DossierAmountTest extends TestCase
{
    /**
     * @dataProvider amountFormats
     */
    public function test_amount_formatting($input, $expected)
    {
        $dossier = new Dossier();
        $dossier->tuition_total_amount = $input;
        $this->assertEquals($expected, $dossier->tuition_total_amount);
    }
    
    public function amountFormats()
    {
        return [
            ["1000000.00", 1000000],
            ["1,000,000", 1000000],
            ["1 000 000", 1000000],
        ];
    }
}
```

### 2. Tests d'Exceptions
```php
public function test_invalid_amount_throws_exception()
{
    $this->expectException(InvalidArgumentException::class);
    
    $dossier = new Dossier();
    $dossier->tuition_total_amount = "invalid";
}
```

## Intégration Continue

### 1. Configuration GitHub Actions
```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        
    - name: Run Tests
      run: vendor/bin/phpunit
```

### 2. Rapports de Couverture
```xml
<phpunit>
    <coverage>
        <include>
            <directory>app</directory>
        </include>
        <report>
            <html outputDirectory="coverage"/>
        </report>
    </coverage>
</phpunit>
```

## Bonnes Pratiques d'Implémentation

### 1. Organisation des Tests
- Un fichier de test par classe
- Noms de méthodes descriptifs
- Regroupement logique des tests

### 2. Gestion des Données de Test
- Utilisation de factories
- Données de test isolées
- Nettoyage après les tests

### 3. Performance
- Tests rapides
- Isolation des tests lents
- Utilisation de mocks quand approprié

## Conclusion
Une bonne implémentation des tests unitaires nécessite :
- Une structure claire
- Des outils appropriés
- Des pratiques cohérentes
- Une intégration continue
