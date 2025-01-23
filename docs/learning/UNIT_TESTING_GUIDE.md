# Guide des Tests Unitaires : Cas Pratique avec le Formatage des Montants

## Introduction
Ce guide utilise un cas réel de notre application : le formatage des montants dans les formulaires de paiement. Il montre comment les tests unitaires nous ont aidés à détecter et corriger des problèmes de formatage.

## Le Problème Initial
Dans notre formulaire de paiement, nous avions besoin de :
- Accepter différents formats de saisie (avec décimales, séparateurs, espaces)
- Stocker uniquement des nombres entiers
- Afficher les montants de manière cohérente

## La Solution par les Tests

### 1. Création du Test
```php
// Dans tests/Unit/DossierAmountTest.php
public function test_amount_formatting()
{
    $dossier = new Dossier();
    
    // Test avec différents formats de saisie
    $dossier->tuition_total_amount = "1000000.00"; // avec décimales
    $this->assertEquals(1000000, $dossier->tuition_total_amount);
}
```

### 2. Première Version du Code (Problématique)
```php
// Dans app/Models/Dossier.php
public function setTuitionTotalAmountAttribute($value)
{
    $this->attributes['tuition_total_amount'] = $value ? 
        (int) preg_replace('/[^0-9]/', '', $value) : null;
}
```

### 3. Problèmes Détectés par les Tests
- "1000000.00" devenait 100000000 (gardait les décimales comme partie entière)
- "500000.50" devenait 50000050 (même problème)

### 4. Solution Corrigée
```php
public function setTuitionTotalAmountAttribute($value)
{
    if (!$value) return null;
    // Convertir en float d'abord pour gérer les décimales
    $float = (float) str_replace([',', ' '], '', $value);
    // Puis convertir en entier
    $this->attributes['tuition_total_amount'] = (int) $float;
}
```

## Pourquoi les Tests sont Importants ?

1. **Détection Précoce des Problèmes**
   - Les tests ont immédiatement montré que notre logique de nettoyage était incorrecte
   - Sans tests, ces problèmes auraient pu atteindre la production

2. **Documentation Vivante**
   - Les tests montrent clairement comment le code doit se comporter
   - Nouveaux développeurs peuvent comprendre les exigences en lisant les tests

3. **Régression**
   - Si quelqu'un modifie le code et casse la fonctionnalité, les tests échoueront
   - Protection contre les bugs accidentels

## Comment Exécuter les Tests ?

```bash
# Exécuter tous les tests
php artisan test

# Exécuter un fichier de test spécifique
php artisan test tests/Unit/DossierAmountTest.php
```

## Bonnes Pratiques

1. **Nommage Explicite**
   ```php
   public function test_amount_formatting() // Nom clair de ce qui est testé
   ```

2. **Un Test = Une Fonctionnalité**
   ```php
   test_amount_formatting()      // Pour le formatage
   test_payment_progress_calculation() // Pour le calcul de progression
   ```

3. **Assertions Claires**
   ```php
   $this->assertEquals(1000000, $dossier->tuition_total_amount);
   // Message clair en cas d'échec
   ```

## Exercices pour les Développeurs Juniors

1. Ajoutez un nouveau test pour vérifier que les montants négatifs sont correctement gérés
2. Créez un test pour vérifier le comportement avec des valeurs nulles
3. Testez le formatage avec différentes devises (pas seulement FCFA)

## Conclusion

Les tests unitaires ne sont pas juste une formalité - ils sont un outil puissant pour :
- Garantir la qualité du code
- Documenter le comportement attendu
- Faciliter la maintenance
- Former les nouveaux développeurs

Dans notre cas, ils nous ont permis de détecter et corriger un problème subtil de formatage qui aurait pu causer des erreurs en production.
