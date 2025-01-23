# Guide de débogage - Gestion des références

## Vérification des références

### 1. Vérifier l'état des compteurs
```php
// Dans Tinker
DB::table('reference_counters')->get();
```

Résultats attendus :
- Chaque type doit avoir une seule entrée
- La valeur `current_value` doit être cohérente avec le nombre d'enregistrements
- Les timestamps doivent être à jour

### 2. Vérifier la séquence des références
```php
// Pour les dossiers
DB::table('dossiers')
    ->orderBy('created_at')
    ->get(['id', 'reference_number', 'created_at']);

// Pour les prospects
DB::table('prospects')
    ->orderBy('created_at')
    ->get(['id', 'reference_number', 'created_at']);
```

Points à vérifier :
- Format correct (ex: DOS-XXX pour les dossiers)
- Séquence continue sans trous
- Correspondance avec la date de création

## Problèmes courants

### 1. Références en doublon

**Symptômes** :
- Plusieurs enregistrements avec la même référence
- Erreurs d'unicité lors de la création

**Solutions** :
1. Vérifier les verrous de transaction :
```php
DB::table('reference_counters')
    ->where('type', 'dossier')
    ->lockForUpdate()
    ->first();
```

2. Réinitialiser et régénérer les références :
```php
// Dans une transaction
DB::transaction(function () {
    // Réinitialiser le compteur
    DB::table('reference_counters')
        ->where('type', 'dossier')
        ->update(['current_value' => 0]);
    
    // Régénérer les références
    $generator = app(ReferenceGeneratorService::class);
    // ... suite du code
});
```

### 2. Format incorrect

**Symptômes** :
- Références ne suivant pas le format attendu
- Padding incorrect

**Solutions** :
1. Vérifier l'utilisation du service :
```php
// Bon usage
$generator = app(ReferenceGeneratorService::class);
$reference = $generator->generateReference('dossier');

// À éviter
$reference = 'DOS-' . str_pad($number, 3, '0', STR_PAD_LEFT);
```

2. Rechercher les références non conformes :
```php
// Pour les dossiers
DB::table('dossiers')
    ->whereRaw("reference_number NOT REGEXP '^DOS-[0-9]{3}$'")
    ->get(['id', 'reference_number']);
```

### 3. Compteur désynchronisé

**Symptômes** :
- Écart entre le compteur et le nombre réel d'enregistrements
- Trous dans la séquence

**Solutions** :
1. Vérifier l'état actuel :
```php
$counter = DB::table('reference_counters')
    ->where('type', 'dossier')
    ->first();

$maxReference = DB::table('dossiers')
    ->whereRaw("reference_number REGEXP '^DOS-[0-9]{3}$'")
    ->orderByDesc('reference_number')
    ->first();
```

2. Corriger le compteur :
```php
DB::table('reference_counters')
    ->where('type', 'dossier')
    ->update([
        'current_value' => $valeurCorrecte,
        'updated_at' => now()
    ]);
```

## Bonnes pratiques

1. **Toujours utiliser le service dédié**
```php
$generator = app(ReferenceGeneratorService::class);
$reference = $generator->generateReference('dossier');
```

2. **Vérifier les migrations**
- Les migrations doivent initialiser les compteurs
- Utiliser des transactions pour la cohérence

3. **Logging**
```php
\Log::info("Référence générée", [
    'type' => 'dossier',
    'reference' => $reference,
    'counter_value' => $counterValue
]);
```

## Commandes utiles

### Vérification rapide
```bash
# Voir les dernières références générées
php artisan tinker --execute="DB::table('dossiers')->orderByDesc('created_at')->take(5)->get(['reference_number', 'created_at']);"

# Vérifier l'état des compteurs
php artisan tinker --execute="DB::table('reference_counters')->get();"
```

### Maintenance
```bash
# Réinitialiser un compteur (à utiliser avec précaution)
php artisan tinker --execute="DB::table('reference_counters')->where('type', 'dossier')->update(['current_value' => 0]);"
```

## En cas de problème majeur

1. Arrêter temporairement la création de nouveaux enregistrements
2. Sauvegarder l'état actuel des tables concernées
3. Analyser les logs pour identifier le moment de la désynchronisation
4. Appliquer les corrections nécessaires dans une transaction
5. Vérifier la cohérence après correction
6. Documenter l'incident et les actions correctives
