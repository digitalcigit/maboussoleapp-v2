# Guide d'Utilisation - Gestion des Documents

## Table des Matières
1. [Validation des Fichiers](#validation-des-fichiers)
2. [Prévisualisation des Documents](#prévisualisation-des-documents)
3. [Système de Cache](#système-de-cache)
4. [Gestion des Permissions](#gestion-des-permissions)
5. [Exemples d'Utilisation](#exemples-dutilisation)

## Validation des Fichiers

### Types de Fichiers Autorisés
```php
DossierDocument::ALLOWED_MIME_TYPES = [
    'application/pdf' => ['pdf'],
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png' => ['png'],
    'application/msword' => ['doc'],
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
];
```

### Limites de Taille
- Taille maximale : 20MB
```php
DossierDocument::MAX_FILE_SIZE = 20 * 1024 * 1024;
```

### Validation d'un Fichier
```php
// Dans un contrôleur
public function store(Request $request)
{
    if (!DossierDocument::validateFile($request->file('document'))) {
        return back()->withErrors(['document' => 'Type de fichier ou taille non autorisé']);
    }

    // Procéder à l'upload...
}
```

## Prévisualisation des Documents

### Types de Documents Prévisualisables
```php
DossierDocument::PREVIEWABLE_TYPES = [
    'application/pdf',
    'image/jpeg',
    'image/png',
];
```

### Vérifier si un Document est Prévisualisable
```php
if ($document->isPreviewable()) {
    $previewUrl = $document->getPreviewUrl();
}
```

### Obtenir l'URL de Prévisualisation
```php
// Dans une vue Blade
<a href="{{ $document->getPreviewUrl() }}" target="_blank">
    Prévisualiser le document
</a>
```

### Générer une Vignette
```php
// Dans une vue Blade
@if($thumbnailUrl = $document->generateThumbnail())
    <img src="{{ $thumbnailUrl }}" alt="Aperçu du document">
@endif
```

## Système de Cache

### Cache des Types MIME
```php
// Utilisation automatique du cache pour le type MIME
$mimeType = $document->getCachedMimeType();
```

### Cache du Contenu
```php
// Pour les fichiers < 1MB
$contents = $document->getCachedContents();
```

### Cache des Vignettes
```php
// Les vignettes sont automatiquement mises en cache pour une semaine
$thumbnailUrl = $document->generateThumbnail();
```

## Gestion des Permissions

### Rôles et Accès
- **Super Admin** : Accès total à tous les documents
- **Manager** : Accès aux documents des dossiers qu'il gère
- **Client** : Accès à ses propres documents

### Vérification des Permissions
```php
// Dans un contrôleur
public function show(DossierDocument $document)
{
    $this->authorize('view', $document);
    // ...
}

// Dans une vue Blade
@can('view', $document)
    <a href="{{ $document->getPreviewUrl() }}">Voir le document</a>
@endcan
```

## Exemples d'Utilisation

### Upload de Document
```php
public function store(Request $request)
{
    $request->validate([
        'document' => 'required|file',
        'dossier_id' => 'required|exists:dossiers,id',
        'document_type' => 'required|string',
    ]);

    $file = $request->file('document');

    if (!DossierDocument::validateFile($file)) {
        return back()->withErrors(['document' => 'Document non valide']);
    }

    $document = DossierDocument::create([
        'dossier_id' => $request->dossier_id,
        'document_type' => $request->document_type,
        'file_path' => $file->store('documents', 'public'),
        'original_name' => $file->getClientOriginalName(),
        'mime_type' => $file->getMimeType(),
        'size' => $file->getSize(),
        'uploaded_at' => now(),
    ]);

    return back()->with('success', 'Document uploadé avec succès');
}
```

### Affichage dans une Vue
```blade
<div class="document-preview">
    @if($document->isPreviewable())
        @if($thumbnailUrl = $document->generateThumbnail())
            <img src="{{ $thumbnailUrl }}" alt="Aperçu" class="document-thumbnail">
        @endif
        <a href="{{ $document->getPreviewUrl() }}" class="preview-link" target="_blank">
            Prévisualiser
        </a>
    @else
        <a href="{{ $document->getDownloadUrl() }}" class="download-link">
            Télécharger
        </a>
    @endif
</div>
```

### Gestion des Erreurs
```php
public function download(DossierDocument $document)
{
    $this->authorize('download', $document);

    if (!$document->fileExists()) {
        return back()->withErrors(['document' => 'Le fichier est introuvable']);
    }

    return response()->download(
        $document->getFullPath(),
        $document->original_name,
        ['Content-Type' => $document->getCachedMimeType()]
    );
}
```
