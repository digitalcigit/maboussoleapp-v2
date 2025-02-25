# Guide de résolution des problèmes du Workflow des Dossiers

## 1. Problèmes courants

### Transition d'étape impossible

#### Symptômes
- Message d'erreur lors de la tentative de passage à l'étape suivante
- Le bouton de progression est grisé
- Exception `InvalidStepTransitionException`

#### Solutions
1. Vérifier les documents requis :
```php
$missingDocs = $dossier->getMissingDocuments();
if (!empty($missingDocs)) {
    foreach ($missingDocs as $doc) {
        Log::info("Document manquant : {$doc->type}");
    }
}
```

2. Vérifier le statut actuel :
```php
if ($dossier->current_status === Dossier::STATUS_BLOCKED) {
    $blockReason = $dossier->block_reason;
    // Résoudre la raison du blocage
}
```

3. Vérifier les validations :
```php
$validationErrors = $dossier->getValidationErrors();
foreach ($validationErrors as $error) {
    Log::error("Erreur de validation : {$error}");
}
```

### Documents non validés

#### Symptômes
- Documents uploadés mais non visibles
- Erreur lors de la validation des documents
- Status bloqué sur "En attente de documents"

#### Solutions
1. Vérifier le stockage :
```php
$storage = Storage::disk('documents');
if (!$storage->exists($document->path)) {
    Log::error("Document non trouvé : {$document->path}");
}
```

2. Vérifier les métadonnées :
```php
if (!$document->metadata || empty($document->metadata)) {
    Log::warning("Métadonnées manquantes pour le document {$document->id}");
}
```

3. Forcer la revalidation :
```php
$document->refresh();
$document->validate();
```

### Problèmes de paiement

#### Symptômes
- Blocage à l'étape de paiement
- Transaction non finalisée
- Statut de paiement incohérent

#### Solutions
1. Vérifier l'état du paiement :
```php
$payment = $dossier->lastPayment;
if ($payment && $payment->status === 'pending') {
    $payment->checkStatus(); // Synchronise avec le prestataire
}
```

2. Vérifier les montants :
```php
if ($dossier->totalPaid < $dossier->requiredAmount) {
    $difference = $dossier->requiredAmount - $dossier->totalPaid;
    Log::info("Paiement incomplet : {$difference} restants");
}
```

## 2. Problèmes de performance

### Lenteur de chargement

#### Symptômes
- Temps de réponse élevé
- Timeout lors du chargement des dossiers
- Utilisation excessive de la mémoire

#### Solutions
1. Optimiser les requêtes :
```php
// Avant
$dossiers = Dossier::all();

// Après
$dossiers = Dossier::with(['documents', 'payments'])
    ->whereActive()
    ->get();
```

2. Utiliser le cache :
```php
$dossiers = Cache::remember('dossiers.active', now()->addHours(1), function () {
    return Dossier::whereActive()->get();
});
```

3. Pagination :
```php
$dossiers = Dossier::paginate(25);
```

### Problèmes de cache

#### Symptômes
- Données obsolètes
- Incohérences entre utilisateurs
- Cache non invalidé

#### Solutions
1. Forcer le rafraîchissement :
```php
Cache::tags(['dossiers'])->flush();
```

2. Mettre à jour spécifiquement :
```php
Cache::forget("dossier.{$dossier->id}");
```

## 3. Problèmes de sécurité

### Accès non autorisé

#### Symptômes
- Erreurs 403
- Actions non disponibles
- Permissions manquantes

#### Solutions
1. Vérifier les permissions :
```php
if (auth()->user()->cannot('view', $dossier)) {
    Log::warning("Tentative d'accès non autorisé au dossier {$dossier->id}");
}
```

2. Ajouter les rôles manquants :
```php
$user->assignRole('agent');
```

### Fuite de données

#### Symptômes
- Données sensibles visibles
- Logs contenant des informations personnelles
- Accès aux documents non sécurisé

#### Solutions
1. Masquer les données sensibles :
```php
protected $hidden = [
    'email',
    'phone',
    'documents.content',
];
```

2. Sécuriser les URLs de documents :
```php
public function getDocumentUrl($document)
{
    return URL::signedRoute('documents.download', [
        'document' => $document->id
    ]);
}
```

## 4. Outils de diagnostic

### Logs et monitoring

1. Activer les logs détaillés :
```php
Log::channel('dossier')->debug('Détails du dossier', [
    'id' => $dossier->id,
    'step' => $dossier->current_step,
    'status' => $dossier->current_status,
]);
```

2. Surveiller les performances :
```php
DB::listen(function ($query) {
    if ($query->time > 100) {
        Log::warning("Requête lente : {$query->sql}");
    }
});
```

### Tests de validation

1. Tester la cohérence :
```php
public function validateDossierState()
{
    if ($this->isClient() && $this->current_step < self::STEP_PAYMENT) {
        throw new InconsistentStateException();
    }
}
```

2. Vérifier les documents :
```php
public function validateDocuments()
{
    foreach ($this->documents as $document) {
        if (!$document->isValid()) {
            throw new InvalidDocumentException($document);
        }
    }
}
```

## 5. Procédures de récupération

### Restauration de données

1. Créer un point de restauration :
```php
$backup = $dossier->toArray();
Cache::put("dossier.{$dossier->id}.backup", $backup, now()->addDay());
```

2. Restaurer depuis la sauvegarde :
```php
if ($backup = Cache::get("dossier.{$dossier->id}.backup")) {
    $dossier->fill($backup)->save();
}
```

### Nettoyage du système

1. Supprimer les fichiers temporaires :
```php
Storage::disk('temp')->delete($oldFiles);
```

2. Nettoyer le cache :
```php
Cache::tags(['dossiers', 'documents'])->flush();
```
