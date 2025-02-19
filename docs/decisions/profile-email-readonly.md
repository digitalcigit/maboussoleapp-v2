# Décision : Email en lecture seule dans le profil utilisateur

## Contexte
L'email étant utilisé comme identifiant de connexion, il ne doit pas être modifiable via le formulaire de profil pour maintenir la cohérence et la sécurité du système.

## Décision
- L'email est affiché en lecture seule dans le formulaire de profil
- Le champ est désactivé (`disabled`) et non-hydraté (`dehydrated(false)`)
- La logique de modification d'email a été retirée de la méthode `save()`

## Implémentation
```php
TextInput::make('email')
    ->email()
    ->disabled()
    ->dehydrated(false)
```

## Conséquences
### Positives
- Meilleure sécurité du système
- Prévention des problèmes potentiels liés au changement d'identifiant de connexion
- Interface utilisateur plus claire sur ce qui peut être modifié

### Négatives
- Les utilisateurs devront passer par un processus différent s'ils ont besoin de changer leur email
- Nécessité potentielle de créer un processus dédié pour le changement d'email si nécessaire

## Notes
Si un changement d'email devient nécessaire à l'avenir, il faudra implémenter un processus dédié avec :
- Vérification de l'ancien email
- Confirmation du nouveau email
- Mise à jour sécurisée des informations de connexion
