# Résolution du problème de validation des propriétés Livewire

## Problème
Une erreur de validation indiquant "No property found for validation: [email]" se produisait lors de la soumission du formulaire.

## Cause
Bien que le champ email soit désactivé et non-hydraté (`dehydrated(false)`), Livewire nécessite toujours que les propriétés utilisées dans le formulaire soient définies dans le composant.

## Solution
1. Ajout des propriétés publiques dans le composant :
```php
public $email;
public $current_password;
public $new_password;
public $new_password_confirmation;
```

2. Initialisation de la propriété email dans mount() :
```php
public function mount(): void
{
    $user = auth()->user();
    $this->email = $user->email;
    $this->form->fill([
        'email' => $user->email,
        'avatar' => $user->avatar,
    ]);
}
```

3. Configuration du champ avec une valeur par défaut :
```php
TextInput::make('email')
    ->email()
    ->disabled()
    ->dehydrated(false)
    ->default(fn () => $this->email)
```

## Points clés à retenir
- Même si un champ est désactivé (`disabled`) et non-hydraté (`dehydrated(false)`), sa propriété doit être définie dans le composant Livewire
- Les propriétés doivent être initialisées dans la méthode `mount()`
- Utiliser `default()` pour définir la valeur par défaut d'un champ peut aider à maintenir la cohérence des données
