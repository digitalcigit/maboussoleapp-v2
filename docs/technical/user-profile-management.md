# Gestion du profil utilisateur

## Vue d'ensemble
Cette documentation détaille l'implémentation de la page de profil utilisateur dans l'application Ma Boussole. Cette fonctionnalité permet aux utilisateurs de visualiser et modifier certaines informations de leur profil, notamment leur avatar et leur mot de passe.

## Implémentation technique

### Classe de la page de profil

La page de profil est implémentée via la classe `Profile` dans `app/Filament/Pages/Profile.php`. Elle étend la classe de base `Page` de Filament et utilise plusieurs traits pour gérer les interactions utilisateur.

```php
namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.profile';
    protected static ?string $title = 'Mon profil';
    
    // ...
}
```

### Formulaire de profil

Le formulaire de profil permet de modifier plusieurs éléments :

1. **Email (lecture seule)** - Affiche l'email de l'utilisateur sans permettre sa modification
2. **Avatar** - Upload et édition d'image avec recadrage circulaire
3. **Changement de mot de passe** - Avec validation du mot de passe actuel

```php
public function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('email')->disabled(),
            FileUpload::make('avatar')
                ->image()
                ->disk('avatars')
                ->directory('profiles')
                ->visibility('public')
                ->imageEditor()
                ->circleCropper()
                ->maxSize(5120),
            TextInput::make('current_password')
                ->password()
                ->label('Mot de passe actuel'),
            TextInput::make('new_password')
                ->password()
                ->label('Nouveau mot de passe')
                ->rule(Password::default()),
            TextInput::make('new_password_confirmation')
                ->password()
                ->label('Confirmation du mot de passe')
                ->same('new_password'),
        ]);
}
```

### Validation et sécurité

La validation du mot de passe est effectuée à plusieurs niveaux :
- Vérification que le mot de passe actuel est correct
- Application des règles de mot de passe fort via `Password::default()`
- Confirmation du nouveau mot de passe

### Traitement du formulaire

Lors de la soumission, plusieurs actions sont effectuées :
1. Vérification du mot de passe actuel
2. Validation du nouveau mot de passe
3. Mise à jour de l'avatar si nécessaire
4. Enregistrement des modifications

```php
public function save()
{
    $data = $this->form->getState();
    
    $user = auth()->user();
    
    // Si l'utilisateur tente de changer son mot de passe
    if ($data['current_password'] && $data['new_password']) {
        // Vérifier le mot de passe actuel
        if (!Hash::check($data['current_password'], $user->password)) {
            Notification::make()
                ->title('Mot de passe actuel incorrect')
                ->danger()
                ->send();
            return;
        }
        
        // Mettre à jour le mot de passe
        $user->password = Hash::make($data['new_password']);
    }
    
    // Mettre à jour l'avatar
    if (isset($data['avatar']) && $data['avatar']) {
        $user->avatar = $data['avatar'];
    }
    
    $user->save();
    
    Notification::make()
        ->title('Profil mis à jour avec succès')
        ->success()
        ->send();
}
```

## Gestion des avatars

La gestion des avatars est intégrée à cette page et utilise les fonctionnalités suivantes :

1. **Stockage** : Les avatars sont stockés dans le dossier `storage/app/public/avatars/profiles`
2. **Manipulation d'image** : L'utilisateur peut recadrer et éditer son image avant l'upload
3. **Format circulaire** : Un recadrage circulaire est appliqué pour avoir un format standard

## Intégration avec le système d'authentification

Cette fonctionnalité s'intègre avec le système d'authentification existant :
- Utilisation de `auth()->user()` pour récupérer l'utilisateur connecté
- Vérification du mot de passe via `Hash::check()`
- Génération de notifications contextuelles pour informer l'utilisateur

## Considérations de sécurité

1. L'email est affiché en lecture seule pour éviter les modifications non autorisées
2. Les mots de passe sont soumis aux règles standards de Laravel pour garantir leur robustesse
3. La vérification du mot de passe actuel est requise avant toute modification

## Personnalisation et extension

Cette implémentation peut être étendue pour inclure d'autres informations utilisateur comme :
- Informations de contact supplémentaires
- Préférences de notification
- Configuration de l'interface

## Problèmes connus et résolutions

| Problème | Solution |
|----------|----------|
| Échec du chargement d'avatar | Vérifier les permissions sur le dossier de stockage |
| Erreurs lors du recadrage | S'assurer que les bibliothèques d'image sont correctement installées |
| Mot de passe non mis à jour | Vérifier la correspondance entre le mot de passe actuel et celui en base de données |
