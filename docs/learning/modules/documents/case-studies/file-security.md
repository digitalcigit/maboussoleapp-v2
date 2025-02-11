# Étude de Cas : Sécurité des Fichiers

## Contexte du Problème
Les documents uploadés nécessitent une sécurité renforcée pour protéger les informations sensibles des clients.

## Solution Implémentée

### 1. Validation des Fichiers
```php
class DossierDocument extends Model
{
    public const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB
    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    public static function validateFile($file): bool
    {
        return in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES) &&
               $file->getSize() <= self::MAX_FILE_SIZE;
    }
}
```

### 2. Stockage Sécurisé
```php
// Dans le contrôleur
public function store(Request $request)
{
    $file = $request->file('document');
    
    if (!DossierDocument::validateFile($file)) {
        return back()->withErrors(['document' => 'Type de fichier ou taille non autorisé']);
    }

    $path = $file->store('documents/' . date('Y/m'), 'public');
    
    return DossierDocument::create([
        'file_path' => $path,
        'mime_type' => $file->getMimeType(),
        'size' => $file->getSize(),
        // ...
    ]);
}
```

### 3. Accès Sécurisé
```php
class DossierDocumentPolicy
{
    public function view(User $user, DossierDocument $document)
    {
        return $user->can('view', $document->dossier);
    }

    public function download(User $user, DossierDocument $document)
    {
        return $user->can('view', $document->dossier);
    }
}
```

## Tests de Sécurité

```php
class DossierDocumentSecurityTest extends TestCase
{
    /** @test */
    public function it_rejects_unauthorized_mime_types()
    {
        $file = UploadedFile::fake()->create('malicious.exe', 100);
        
        $this->assertFalse(DossierDocument::validateFile($file));
    }

    /** @test */
    public function it_rejects_oversized_files()
    {
        $file = UploadedFile::fake()->create('large.pdf', 25 * 1024); // 25MB
        
        $this->assertFalse(DossierDocument::validateFile($file));
    }
}
```

## Leçons Apprises

1. **Validation Préventive**
   - Vérifier les fichiers avant l'upload
   - Valider le type MIME réel, pas juste l'extension
   - Limiter la taille des fichiers

2. **Stockage Organisé**
   - Utiliser une structure de dossiers par date
   - Garder les noms de fichiers originaux
   - Sauvegarder les métadonnées importantes

3. **Contrôle d'Accès**
   - Implémenter des politiques d'accès
   - Vérifier les permissions à chaque accès
   - Logger les accès aux documents

## Applications Possibles

1. **Autres Types de Documents**
   - Appliquer le même système aux pièces jointes des emails
   - Gérer les documents temporaires
   - Supporter les archives ZIP

2. **Améliorations Futures**
   - Ajout de chiffrement des fichiers sensibles
   - Mise en place d'un système de versioning
   - Implémentation de signatures numériques
