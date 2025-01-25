# Priorité aux Fonctionnalités Natives des Frameworks

Date: 2025-01-23

## Contexte

Lors de l'implémentation de la vérification d'email pour le portail candidat, nous avons initialement commencé à créer une implémentation personnalisée avec :
- Routes personnalisées
- Vues personnalisées
- Logique de vérification personnalisée

Cependant, Filament, notre framework d'administration, offrait déjà cette fonctionnalité nativement via `->emailVerification()`.

## Décision

Nous adoptons la règle suivante : "Toujours vérifier l'existence et la pertinence des fonctionnalités natives du framework avant de créer une implémentation personnalisée."

### Processus de Vérification
1. Consulter la documentation officielle du framework
2. Vérifier les configurations existantes dans le code
3. Tester la fonctionnalité native avant de décider de la personnaliser

### Avantages
- Réduction de la dette technique
- Meilleure maintenabilité
- Mise à jour plus facile
- Sécurité renforcée (les fonctionnalités natives sont généralement bien testées)
- Gain de temps de développement

### Cas d'Exception
La création d'une implémentation personnalisée est justifiée uniquement si :
1. La fonctionnalité native ne répond pas aux exigences spécifiques du projet
2. La personnalisation est nécessaire pour l'intégration avec d'autres systèmes
3. Les performances de la solution native ne sont pas satisfaisantes

## Conséquences

### Positives
- Code plus propre et standardisé
- Moins de code à maintenir
- Meilleure intégration avec l'écosystème du framework

### Négatives
- Possible limitation dans la personnalisation
- Dépendance accrue envers le framework

## Exemple Concret

Dans le cas de la vérification d'email :
```php
// Solution native Filament (préférée)
public function panel(Panel $panel): Panel
{
    return $panel
        ->emailVerification();
}

// Solution personnalisée (à éviter si la native suffit)
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/portail');
})->middleware(['auth', 'signed'])->name('verification.verify');
```

## Checklist d'Application

Avant d'implémenter une nouvelle fonctionnalité :

1. [ ] Vérifier la documentation du framework
2. [ ] Rechercher les configurations existantes
3. [ ] Évaluer si la solution native répond aux besoins
4. [ ] Documenter pourquoi une implémentation personnalisée est nécessaire (si c'est le cas)

## Notes d'Implémentation

Pour les développeurs :
1. Commencer par explorer les options de configuration du framework
2. Tester la solution native dans un environnement de développement
3. Ne personnaliser que les aspects nécessaires
4. Documenter les raisons des personnalisations
