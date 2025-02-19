# Gestion des avatars utilisateurs

## Configuration

### Stockage
- Les avatars sont stockés dans le dossier `storage/app/public/avatars/profiles`
- Utilisation du disque `avatars` configuré dans `config/filesystems.php`
- Les fichiers sont accessibles publiquement via le lien symbolique dans `public/storage`

### Champ de formulaire
```php
FileUpload::make('avatar')
    ->image()
    ->disk('avatars')
    ->directory('profiles')
    ->visibility('public')
    ->imageEditor()
    ->circleCropper()
    ->maxSize(5120)
    ->avatar()
    ->imagePreviewHeight('250')
    ->panelAspectRatio('1:1')
    ->downloadable()
```

### Modèle User
Le modèle User implémente l'interface `HasAvatar` et gère deux types d'avatars :
- URLs externes (commençant par 'http')
- Fichiers locaux stockés dans le disque `avatars`

```php
public function getFilamentAvatarUrl(): ?string
{
    if (!$this->avatar) {
        return null;
    }

    if (str_starts_with($this->avatar, 'http')) {
        return $this->avatar;
    }

    return Storage::disk('avatars')->url($this->avatar);
}
```

## Fonctionnalités
- Upload d'images avec prévisualisation
- Édition d'image intégrée
- Recadrage circulaire
- Taille maximale de 5 Mo
- Support des URLs externes
- Téléchargement de l'avatar

## Bonnes pratiques
1. Toujours vérifier le type d'avatar (URL externe vs fichier local)
2. Supprimer l'ancien avatar lors de la mise à jour
3. Utiliser le disque dédié `avatars` pour le stockage
4. Maintenir une cohérence dans les proportions (1:1)
5. Permettre l'édition et le recadrage pour une meilleure expérience utilisateur
