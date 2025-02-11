# Tests du Module Documents

## Scénarios de Test

### 1. Upload de Document PDF
**Objectif** : Vérifier qu'un document PDF peut être uploadé et stocké correctement
**Scénario** :
- Upload d'un fichier PDF de 1MB
- Vérification de l'existence du fichier
- Vérification du type MIME
- Vérification du stockage physique

### 2. Gestion des Gros Fichiers
**Objectif** : Tester la gestion des fichiers volumineux
**Scénario** :
- Upload d'un fichier de 10MB
- Vérification de la taille stockée
- Vérification du formatage de la taille

### 3. Gestion des Fichiers Manquants
**Objectif** : Vérifier la gestion gracieuse des fichiers manquants
**Scénario** :
- Création d'une entrée pour un fichier inexistant
- Vérification des comportements de fallback
- Test des URLs de téléchargement

### 4. Validation des Types de Documents
**Objectif** : Vérifier que seuls les types de documents autorisés sont acceptés
**Scénario** :
- Vérification des types de documents valides
- Test des constantes définies
- Validation des types autorisés

### 5. Gestion des Images
**Objectif** : Tester le support des fichiers images
**Scénario** :
- Upload d'une image JPEG
- Vérification du type MIME
- Vérification du stockage

## Résultats des Tests

```bash
php artisan test --filter=DossierDocumentTest
```

### Points Vérifiés
✅ Upload de fichiers PDF
✅ Gestion des gros fichiers
✅ Gestion des fichiers manquants
✅ Validation des types de documents
✅ Support des images

## Failles Potentielles Identifiées

1. **Sécurité**
   - Vérification des types MIME à renforcer
   - Validation de la taille maximale à implémenter
   - Scan antivirus à considérer

2. **Performance**
   - Optimisation nécessaire pour les gros fichiers
   - Mise en cache des métadonnées à considérer
   - Compression des images à implémenter

3. **Accessibilité**
   - Prévisualisation des documents à ajouter
   - Versionning des documents à considérer
   - Historique des modifications à implémenter

## Recommandations

1. **Sécurité**
```php
// Ajouter dans DossierDocument
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
```

2. **Performance**
```php
// Ajouter dans DossierDocument
public function getCachedMimeType(): string
{
    return cache()->remember(
        "document_mime_{$this->id}",
        now()->addDay(),
        fn() => $this->getMimeType()
    );
}
```

3. **Accessibilité**
```php
// Ajouter dans DossierDocument
public function getPreviewUrl(): string
{
    if ($this->isPreviewable()) {
        return route('documents.preview', $this);
    }
    return '#';
}

public function isPreviewable(): bool
{
    return in_array($this->mime_type, [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ]);
}
```
