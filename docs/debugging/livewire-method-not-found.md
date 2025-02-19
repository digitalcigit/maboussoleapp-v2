# Résolution du problème MethodNotFoundException dans Livewire

## Problème
Une erreur `MethodNotFoundException` était levée car la méthode `submit` n'existait pas dans le composant alors qu'elle était appelée via `wire:submit="submit"` dans la vue.

## Cause
La méthode de soumission du formulaire s'appelait `save()` dans le composant PHP, mais la vue Blade faisait référence à une méthode `submit()`.

## Solution
Renommage de la méthode `save()` en `submit()` dans le composant pour correspondre à l'attribut `wire:submit` de la vue.

```php
// Avant
public function save()
{
    // ...
}

// Après
public function submit()
{
    // ...
}
```

## Points clés à retenir
- Les méthodes appelées via `wire:submit` dans les vues Blade doivent exister dans le composant Livewire
- Le nom de la méthode dans le composant doit correspondre exactement à celui spécifié dans l'attribut `wire:submit`
- Vérifier la cohérence entre les noms de méthodes dans les composants et les vues en cas d'erreur `MethodNotFoundException`
