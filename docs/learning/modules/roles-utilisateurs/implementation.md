# Implémentation des Rôles Utilisateurs

## Structure technique

### Configuration des rôles dans Filament
```php
// UserResource.php
Select::make('roles')
    ->multiple()
    ->relationship('roles', 'name')
    ->preload()
    ->options(function () {
        if (auth()->user()->hasRole('manager')) {
            return Role::where('name', '!=', 'super-admin')
                ->where('name', '!=', 'prospect')
                ->where('name', '!=', 'portail_candidat')
                ->pluck('name', 'id');
        }
        return Role::where('name', '!=', 'prospect')
            ->where('name', '!=', 'portail_candidat')
            ->pluck('name', 'id');
    })
```

### Rôles masqués dans le formulaire utilisateur
- prospect
- portail_candidat

### Permissions de l'Apporteur d'Affaire
- view_own_prospects
- create_prospects
- edit_own_prospects
- delete_own_prospects

## Tests associés
```php
// Tests à implémenter
public function test_apporteur_affaire_can_create_prospect()
public function test_apporteur_affaire_can_view_own_prospects()
public function test_apporteur_affaire_cannot_view_other_prospects()
public function test_prospect_role_not_available_in_user_form()
public function test_portail_candidat_role_not_available_in_user_form()
```

## Points d'attention
1. Toujours utiliser les Gates et Policies pour la vérification des permissions
2. Vérifier les permissions à chaque niveau (Route, Controller, View)
3. Maintenir la documentation à jour lors de l'ajout de nouveaux rôles
