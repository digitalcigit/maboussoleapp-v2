# Gestion des champs de mot de passe dans Filament

## Problème
Une erreur "Undefined array key 'current_password'" se produisait car nous essayions d'accéder aux champs de mot de passe déshydratés via le tableau de données du formulaire.

## Cause
Les champs de mot de passe étaient marqués comme `dehydrated(false)` mais nous tentions d'y accéder via `$data = $this->form->getState()`.

## Solution
1. Utilisation des propriétés publiques du composant pour les mots de passe :
```php
public $current_password;
public $new_password;
public $new_password_confirmation;
```

2. Configuration des champs pour être "live" :
```php
TextInput::make('current_password')
    ->password()
    ->label('Mot de passe actuel')
    ->dehydrated(false)
    ->live()
```

3. Accès direct aux propriétés dans la méthode submit :
```php
if (
    filled($this->current_password) &&
    filled($this->new_password) &&
    Hash::check($this->current_password, $user->password)
) {
    $user->password = Hash::make($this->new_password);
}
```

4. Réinitialisation des champs après la mise à jour :
```php
if (filled($this->new_password)) {
    $this->current_password = null;
    $this->new_password = null;
    $this->new_password_confirmation = null;
}
```

## Points clés à retenir
- Les champs déshydratés ne sont pas inclus dans `$form->getState()`
- Utiliser les propriétés publiques pour les champs sensibles comme les mots de passe
- Ajouter `->live()` pour une mise à jour en temps réel des propriétés
- Penser à réinitialiser les champs sensibles après utilisation
