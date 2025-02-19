# Résolution du problème PropertyNotFoundException pour l'avatar dans le profil

## Problème
Une erreur `PropertyNotFoundException` était levée pour la propriété `avatar` dans le composant de profil Filament.

## Cause
La propriété `avatar` était définie dans le schéma du formulaire mais n'était pas initialisée dans la méthode `mount()`.

## Solution
Ajout de l'initialisation de l'avatar dans la méthode `mount()` :

```php
public function mount(): void
{
    $this->form->fill([
        'email' => auth()->user()->email,
        'avatar' => auth()->user()->avatar,
    ]);
}
```

## Points clés à retenir
- Toujours initialiser toutes les propriétés du formulaire dans la méthode `mount()`
- Vérifier la cohérence entre le schéma du formulaire et les données initiales
- Les propriétés non initialisées dans Livewire génèrent une `PropertyNotFoundException`
