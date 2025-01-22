# Étude de Cas : Formatage des Montants

## Le Contexte
Dans notre application, nous devions gérer le formatage des montants dans les formulaires de paiement. Les exigences étaient :
- Accepter différents formats de saisie (1000000.00, 1,000,000, 1 000 000)
- Stocker uniquement des nombres entiers
- Afficher les montants de manière cohérente

## Le Problème Initial
Le code initial ne gérait pas correctement les différents formats :

```php
public function setTuitionTotalAmountAttribute($value)
{
    $this->attributes['tuition_total_amount'] = $value ? 
        (int) preg_replace('/[^0-9]/', '', $value) : null;
}
```

### Les Problèmes Rencontrés
1. "1000000.00" devenait 100000000 (gardait les décimales)
2. "1,000,000" était mal interprété
3. Les espaces causaient des erreurs

## La Solution par les Tests

### 1. Tests de Différents Formats
```php
public function test_amount_formatting()
{
    $dossier = new Dossier();
    
    // Test avec décimales
    $dossier->tuition_total_amount = "1000000.00";
    $this->assertEquals(1000000, $dossier->tuition_total_amount);
    
    // Test avec virgules
    $dossier->tuition_total_amount = "1,000,000";
    $this->assertEquals(1000000, $dossier->tuition_total_amount);
    
    // Test avec espaces
    $dossier->tuition_total_amount = "1 000 000";
    $this->assertEquals(1000000, $dossier->tuition_total_amount);
}
```

### 2. Solution Implémentée
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

## Les Leçons Apprises

### 1. Importance des Tests
- Les tests ont révélé des problèmes non évidents
- Ils ont guidé l'implémentation
- Ils servent de documentation

### 2. Approche TDD
1. Écrire les tests d'abord
2. Voir les tests échouer
3. Implémenter la solution
4. Vérifier le succès

### 3. Bonnes Pratiques
- Tester tous les formats possibles
- Documenter les cas limites
- Maintenir les tests à jour

## Application à d'Autres Cas

### 1. Validation des Données
```php
public function test_invalid_amounts()
{
    $dossier = new Dossier();
    
    $dossier->tuition_total_amount = "invalid";
    $this->assertNull($dossier->tuition_total_amount);
    
    $dossier->tuition_total_amount = "";
    $this->assertNull($dossier->tuition_total_amount);
}
```

### 2. Calculs Dérivés
```php
public function test_payment_progress()
{
    $dossier = new Dossier();
    $dossier->down_payment_amount = "500000";
    $dossier->tuition_paid_amount = "100000";
    
    $this->assertEquals(20, $dossier->tuition_progress);
}
```

## Conclusion
Cette étude de cas montre :
1. L'importance des tests unitaires
2. Comment les tests guident le développement
3. L'utilité des tests pour la documentation
4. La valeur du TDD dans la résolution de problèmes
