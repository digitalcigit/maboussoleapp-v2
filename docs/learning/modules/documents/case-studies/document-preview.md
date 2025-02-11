# Étude de Cas : Prévisualisation des Documents

## Contexte
Les utilisateurs ont besoin de prévisualiser les documents avant de les télécharger pour s'assurer qu'ils consultent le bon fichier.

## Solution

### 1. Prévisualisation des Images
```php
class DossierDocumentPreviewController extends Controller
{
    public function __invoke(DossierDocument $document)
    {
        if (str_starts_with($document->mime_type, 'image/')) {
            return redirect($document->getDownloadUrl());
        }
        // ...
    }
}
```

### 2. Prévisualisation des PDFs
```php
// Dans le contrôleur
if ($document->mime_type === 'application/pdf') {
    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
    ]);
}
```

### 3. Génération de Vignettes
```php
public function generateThumbnail(): ?string
{
    if (!$this->isPreviewable()) {
        return null;
    }

    return cache()->remember("document_thumbnail_{$this->id}", now()->addWeek(), function () {
        if (str_starts_with($this->mime_type, 'image/')) {
            $image = \Intervention\Image\Facades\Image::make($this->getFullPath());
            $image->fit(200, 200);
            
            $thumbnailPath = 'thumbnails/' . basename($this->file_path);
            Storage::disk('public')->put($thumbnailPath, $image->encode());
            
            return Storage::disk('public')->url($thumbnailPath);
        }
        
        return null;
    });
}
```

## Implémentation dans l'Interface

### 1. Vue Liste des Documents
```blade
<div class="document-grid">
    @foreach($documents as $document)
        <div class="document-card">
            @if($thumbnailUrl = $document->generateThumbnail())
                <img src="{{ $thumbnailUrl }}" alt="Aperçu" class="document-thumbnail">
            @else
                <div class="document-icon">
                    <i class="fas fa-file"></i>
                </div>
            @endif
            
            <div class="document-info">
                <h3>{{ $document->original_name }}</h3>
                <p>{{ $document->getHumanFileSize() }}</p>
            </div>
            
            <div class="document-actions">
                @if($document->isPreviewable())
                    <a href="{{ $document->getPreviewUrl() }}" class="btn btn-primary">
                        Prévisualiser
                    </a>
                @endif
                <a href="{{ $document->getDownloadUrl() }}" class="btn btn-secondary">
                    Télécharger
                </a>
            </div>
        </div>
    @endforeach
</div>
```

### 2. CSS pour l'Interface
```css
.document-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
    padding: 1rem;
}

.document-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    overflow: hidden;
    transition: all 0.2s;
}

.document-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.document-thumbnail {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.document-icon {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f7fafc;
    font-size: 3rem;
    color: #718096;
}

.document-info {
    padding: 1rem;
}

.document-actions {
    padding: 1rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 0.5rem;
}
```

## Leçons Apprises

1. **Performance**
   - Mise en cache des vignettes pour éviter la régénération
   - Limitation de la taille des vignettes pour optimiser le stockage
   - Chargement différé des images pour améliorer les performances

2. **Sécurité**
   - Vérification des permissions avant la prévisualisation
   - Validation des types de fichiers
   - Protection contre les injections de fichiers malveillants

3. **Expérience Utilisateur**
   - Interface intuitive avec aperçus visuels
   - Options de prévisualisation adaptées au type de fichier
   - Retour visuel immédiat sur les documents uploadés

## Applications Futures

1. **Améliorations Possibles**
   - Support de plus de types de documents
   - Prévisualisation des documents Office
   - Extraction de texte pour la recherche

2. **Fonctionnalités Additionnelles**
   - Annotation de documents
   - Versioning des fichiers
   - Collaboration en temps réel

3. **Optimisations**
   - Compression des images à l'upload
   - Conversion automatique des formats
   - Stockage distribué pour les gros fichiers
