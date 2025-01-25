# Étude de Cas : Résolution d'une Erreur 403 dans le Portail Candidat

## Contexte
Un candidat reçoit une erreur 403 lors de l'accès à son dossier dans le portail, malgré avoir le rôle approprié.

## Problème Initial
```
Erreur : 403 Forbidden
URL : /portail/mon-dossier/7/edit
Utilisateur : ID 14 (rôle: candidat)
```

## Application de la Méthode Scientifique

### 1. Observation
#### Données Collectées
```php
Log::debug('État initial', [
    'user' => [
        'id' => auth()->id(),
        'roles' => auth()->user()->roles->pluck('name'),
        'permissions' => auth()->user()->permissions->pluck('name')
    ],
    'request' => [
        'path' => request()->path(),
        'method' => request()->method()
    ]
]);
```

#### Résultats
- Utilisateur authentifié
- Rôle 'candidat' présent
- Permissions définies dans AuthServiceProvider

### 2. Hypothèses
1. Problème de configuration des Gates
2. Relation prospect-dossier incorrecte
3. Conflit entre politiques d'autorisation

### 3. Expérimentation
#### Test 1 : Vérification des Gates
```php
// Dans AuthServiceProvider
Gate::define('portail-candidat.dossier.view', function (User $user, Dossier $dossier) {
    DebugPoint::trace('Gate check', [
        'user_id' => $user->id,
        'dossier_id' => $dossier->id,
        'has_prospect' => $user->prospect ? 'yes' : 'no',
        'prospect_dossier' => $user->prospect?->dossier_id
    ]);
    
    return $user->prospect && $user->prospect->dossier_id === $dossier->id;
});
```

#### Test 2 : Vérification des Relations
```php
// Test unitaire
public function test_prospect_dossier_relation()
{
    $user = User::factory()->create();
    $user->assignRole('candidat');
    
    $dossier = Dossier::factory()->create();
    
    // Test sans relation
    $this->assertFalse($user->can('view', $dossier));
    
    // Test avec relation
    $user->prospect->update(['dossier_id' => $dossier->id]);
    $this->assertTrue($user->can('view', $dossier));
}
```

### 4. Analyse
Les logs ont révélé que la relation prospect->dossier n'était pas correctement établie :
```
[2025-01-25 11:31:15] DEBUG: Gate check
{
    "user_id": 14,
    "dossier_id": 7,
    "has_prospect": "yes",
    "prospect_dossier": null
}
```

### 5. Solution
```php
// 1. Correction de la relation dans le modèle User
public function prospect()
{
    return $this->hasOne(Prospect::class)->withDefault();
}

// 2. Amélioration de la vérification dans DossierPolicy
public function view(User $user, Dossier $dossier): bool
{
    return $user->prospect && 
           $user->prospect->dossier_id === $dossier->id;
}

// 3. Ajout de logging pour prévention
public function boot()
{
    Gate::before(function ($user, $ability) {
        Log::debug("Permission check: {$ability}", [
            'user' => $user->id,
            'roles' => $user->roles->pluck('name')
        ]);
    });
}
```

## Leçons Apprises
1. Importance de la vérification des relations
2. Nécessité d'un logging détaillé
3. Utilité des tests unitaires
4. Importance de la documentation des erreurs

## Applications Possibles
1. Implémentation d'un système de monitoring des erreurs 403
2. Création d'un middleware de diagnostic
3. Amélioration de la documentation des permissions
4. Mise en place de tests automatisés pour les relations

## Documentation Mise à Jour
- Ajout de guides de débogage
- Mise à jour des diagrammes de relations
- Documentation des cas d'erreur courants
- Guide de résolution des problèmes d'autorisation
