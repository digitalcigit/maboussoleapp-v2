# Implémentation du Module Documents

## Structure du Stockage
Les documents sont stockés dans le dossier `storage/app/public` et sont accessibles via un lien symbolique dans `public/storage`.

## Configuration
```php
// config/filesystems.php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

## Modèle DossierDocument
Le modèle gère les métadonnées et l'accès aux fichiers :

```php
class DossierDocument extends Model
{
    // Méthodes principales
    public function getDownloadUrl(): string
    {
        if (!Storage::disk('public')->exists($this->file_path)) {
            return '#';
        }
        return Storage::disk('public')->url($this->file_path);
    }

    public function fileExists(): bool
    {
        return Storage::disk('public')->exists($this->file_path);
    }

    public function getFileContents(): ?string
    {
        if (!$this->fileExists()) {
            return null;
        }
        return Storage::disk('public')->get($this->file_path);
    }

    public function getMimeType(): string
    {
        return $this->mime_type ?? Storage::disk('public')->mimeType($this->file_path);
    }
}
```

## Permissions
Les dossiers de stockage nécessitent les permissions suivantes :
```bash
chmod -R 775 storage/app/public
chmod -R 775 storage/framework
chmod -R 775 storage/logs
```

## Lien Symbolique
Le lien symbolique est créé avec :
```bash
php artisan storage:link
```
