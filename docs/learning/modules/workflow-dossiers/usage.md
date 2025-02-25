# Guide d'utilisation du Workflow des Dossiers

## 1. Création d'un nouveau dossier

### Via l'interface d'administration

1. Accédez à la section "Dossiers" dans le panel d'administration
2. Cliquez sur "Nouveau dossier"
3. Remplissez les informations requises :
   - Référence (générée automatiquement)
   - Informations personnelles
   - Documents initiaux si disponibles
4. Validez la création

### Via l'API

```php
use App\Models\Dossier;

$dossier = Dossier::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+33612345678',
    'current_step' => Dossier::STEP_INITIAL,
    'current_status' => Dossier::STATUS_WAITING_DOCS,
]);
```

## 2. Gestion des étapes

### Progression du workflow

Pour faire progresser un dossier :

1. Vérifiez que toutes les conditions sont remplies :
   - Documents requis présents
   - Validations effectuées
   - Paiements réalisés (si applicable)

2. Utilisez la méthode `moveToNextStep()` :

```php
if ($dossier->canMoveToNextStep()) {
    $dossier->moveToNextStep();
}
```

### Retour en arrière

Si nécessaire, un dossier peut revenir à une étape précédente :

```php
$dossier->moveToPreviousStep();
```

## 3. Gestion des documents

### Ajout de documents

```php
$dossier->addDocument([
    'type' => 'passport',
    'file' => $uploadedFile,
    'metadata' => [
        'expiration_date' => '2025-12-31',
    ],
]);
```

### Vérification des documents requis

```php
if ($dossier->hasRequiredDocuments()) {
    // Tous les documents sont présents
} else {
    $missingDocs = $dossier->getMissingDocuments();
}
```

## 4. Validation et contrôles

### Validation d'une étape

```php
if ($dossier->validateCurrentStep()) {
    // L'étape est valide
} else {
    $errors = $dossier->getValidationErrors();
}
```

### Blocage d'un dossier

```php
$dossier->block('Pièce d'identité invalide');
```

### Déblocage

```php
$dossier->unblock();
```

## 5. Recherche et filtrage

### Recherche simple

```php
$dossiers = Dossier::where('current_step', Dossier::STEP_VALIDATION)
    ->where('current_status', Dossier::STATUS_WAITING_DOCS)
    ->get();
```

### Recherche avancée

```php
$dossiers = Dossier::query()
    ->whereHasMissingDocuments()
    ->whereIsProspect()
    ->whereHasValidPayment()
    ->get();
```

## 6. Reporting

### Statistiques globales

```php
$stats = [
    'total' => Dossier::count(),
    'prospects' => Dossier::whereIsProspect()->count(),
    'clients' => Dossier::whereIsClient()->count(),
    'blocked' => Dossier::whereBlocked()->count(),
];
```

### Rapport par étape

```php
$stepStats = Dossier::groupBy('current_step')
    ->selectRaw('current_step, count(*) as total')
    ->get();
```

## 7. Bonnes pratiques

### Vérifications recommandées

Avant chaque action importante :

1. Vérifier les permissions de l'utilisateur
2. Valider l'état actuel du dossier
3. Confirmer la disponibilité des ressources nécessaires
4. Vérifier la cohérence des données

### Gestion des erreurs

```php
try {
    $dossier->moveToNextStep();
} catch (InvalidStepTransitionException $e) {
    // Gérer l'erreur
} catch (MissingDocumentsException $e) {
    // Gérer les documents manquants
}
```

### Audit et traçabilité

```php
// Consulter l'historique des modifications
$history = $dossier->activities()
    ->orderBy('created_at', 'desc')
    ->get();

// Enregistrer une action importante
activity()
    ->performedOn($dossier)
    ->log('Validation manuelle effectuée');
```

## 8. Intégration avec d'autres modules

### Notifications

```php
$dossier->notify(new DossierStepCompleted($dossier));
```

### Paiements

```php
if ($dossier->needsPayment()) {
    $payment = $dossier->createPayment([
        'amount' => 1000,
        'currency' => 'EUR',
    ]);
}
```

### Export de données

```php
$export = new DossierExport($dossier);
return Excel::download($export, 'dossier.xlsx');
```
