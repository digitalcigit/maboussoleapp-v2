# Guide d'Utilisation des Tests Unitaires

## Utilisation Quotidienne

### Exécution des Tests

1. **Tous les tests**
```bash
php artisan test
```

2. **Tests spécifiques**
```bash
# Un fichier
php artisan test tests/Unit/DossierAmountTest.php

# Une méthode spécifique
php artisan test --filter test_amount_formatting
```

3. **Tests avec couverture**
```bash
php artisan test --coverage
```

## Création de Nouveaux Tests

### 1. Générer un Test
```bash
# Créer un test unitaire
php artisan make:test DossierAmountTest --unit

# Créer un test de fonctionnalité
php artisan make:test DossierAmountTest
```

### 2. Structure de Base
```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Dossier;

class DossierAmountTest extends TestCase
{
    /** @test */
    public function test_something()
    {
        // 1. Arrangement (Given)
        $dossier = new Dossier();
        
        // 2. Action (When)
        $result = $dossier->someMethod();
        
        // 3. Assertion (Then)
        $this->assertEquals(expected, $result);
    }
}
```

## Workflow TDD

### 1. Écrire le Test
```php
public function test_amount_formatting()
{
    $dossier = new Dossier();
    $dossier->tuition_total_amount = "1000000.00";
    $this->assertEquals(1000000, $dossier->tuition_total_amount);
}
```

### 2. Exécuter et Voir Échouer
```bash
php artisan test --filter test_amount_formatting
```

### 3. Implémenter le Code
```php
public function setTuitionTotalAmountAttribute($value)
{
    if (!$value) return null;
    $float = (float) str_replace([',', ' '], '', $value);
    $this->attributes['tuition_total_amount'] = (int) $float;
}
```

### 4. Vérifier le Succès
```bash
php artisan test --filter test_amount_formatting
```

## Bonnes Pratiques Quotidiennes

### 1. Avant de Commiter
```bash
# Exécuter tous les tests
php artisan test

# Vérifier la couverture
php artisan test --coverage
```

### 2. Pendant le Développement
- Écrire les tests avant le code (TDD)
- Exécuter fréquemment les tests
- Corriger immédiatement les tests qui échouent

### 3. Revue de Code
- Vérifier la couverture des tests
- Examiner la qualité des tests
- S'assurer que les tests sont maintenables

## Débogage des Tests

### 1. Afficher les Détails
```bash
php artisan test -v
```

### 2. Utiliser dd() dans les Tests
```php
public function test_something()
{
    $result = someMethod();
    dd($result); // Arrête l'exécution et affiche la valeur
}
```

### 3. Tests Spécifiques
```bash
# Exécuter un seul test
php artisan test --filter=test_name

# Exécuter avec debug
php artisan test --debug
```

## Maintenance des Tests

### 1. Nettoyage Régulier
- Supprimer les tests obsolètes
- Mettre à jour les tests cassés
- Améliorer la lisibilité

### 2. Documentation
- Maintenir les commentaires à jour
- Expliquer les cas complexes
- Documenter les décisions de test

### 3. Organisation
- Garder les tests organisés
- Suivre les conventions de nommage
- Maintenir la cohérence

## Conclusion
Les tests unitaires doivent faire partie de votre workflow quotidien :
- Écrire les tests en premier (TDD)
- Exécuter les tests fréquemment
- Maintenir une bonne couverture
- Garder les tests propres et maintenables
