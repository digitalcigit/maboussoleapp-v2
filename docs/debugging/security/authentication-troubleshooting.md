# Guide de Débogage - Authentification Portail Candidat

## Problème
Échec de connexion au portail candidat malgré des identifiants valides.

## Points de Vérification

### 1. Configuration du Panel
```php
// Dans PortailCandidatPanelProvider
->id('portail-candidat')
->path('portail')
->login()
->emailVerification()  // Important : Vérifie que l'email est vérifié
```

### 2. Vérification des Rôles
- Vérifier que l'utilisateur a le rôle 'portail_candidat'
- Commande Tinker :
```php
$user = \App\Models\User::where('email', 'email@example.com')->first();
$user->roles->pluck('name');  // Doit contenir 'portail_candidat'
```

### 3. État de l'Email
- Vérifier si l'email est vérifié (email_verified_at)
- Commande Tinker :
```php
$user->email_verified_at;  // Ne doit pas être null
```

### 4. Mot de Passe
- Vérifier que le mot de passe est défini
```php
!empty($user->password);  // Doit retourner true
```

## Solutions Courantes

### 1. Email Non Vérifié
Si email_verified_at est null :
```php
$user->email_verified_at = now();
$user->save();
```

### 2. Rôle Manquant
```php
// Ajouter le rôle portail_candidat
$user->assignRole('portail_candidat');
```

### 3. Réinitialisation du Mot de Passe
```php
// Via Tinker
$user->password = Hash::make('nouveau_mot_de_passe');
$user->save();
```

## Points Importants
1. L'URL de connexion est : `/portail/login`
2. Après connexion, redirection vers : `/portail`
3. La vérification d'email est activée par défaut
4. Le guard utilisé est 'web' (configuration standard Laravel)

## Logs à Vérifier
- `/storage/logs/laravel.log` pour les erreurs d'authentification
- Vérifier les notifications Filament pour les messages d'erreur spécifiques

## Cas Particuliers
1. Si l'utilisateur a plusieurs rôles, vérifier les conflits potentiels
2. En cas de problème de session, vider le cache :
```bash
php artisan optimize:clear
```

## Leçons Apprises
1. Toujours vérifier l'état de vérification de l'email quand emailVerification() est activé
2. La configuration correcte des guards et des rôles est essentielle
3. Le système utilise le guard 'web' standard de Laravel avec les rôles Spatie
