# Documentation du Modèle Document

## Vue d'Ensemble

Le modèle `Document` gère les fichiers uploadés dans le système, permettant une association flexible avec différentes entités via des relations polymorphiques.

## Structure du Modèle

### Traits Utilisés
```php
use HasFactory;
use SoftDeletes;
```

### Énumérations
```php
enum DocumentType: string
{
    case PASSPORT = 'passport';
    case CV = 'cv';
    case DIPLOMA = 'diploma';
    case OTHER = 'other';
}

enum DocumentStatus: string
{
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case REJECTED = 'rejected';
}
```

### Attributs
```php
protected $fillable = [
    'name',           // Nom du fichier
    'type',          // Type de document (enum)
    'path',          // Chemin de stockage
    'size',          // Taille en octets
    'status',        // Statut de validation (enum)
    'validated_by',  // ID du validateur
    'validation_date', // Date de validation
    'comments'       // Commentaires
];

protected $casts = [
    'type' => DocumentType::class,
    'status' => DocumentStatus::class,
    'validation_date' => 'datetime',
    'size' => 'integer'
];
```

## Relations

### 1. Relation Polymorphique
```php
public function documentable()
{
    return $this->morphTo();
}
```

### 2. Validateur
```php
public function validator()
{
    return $this->belongsTo(User::class, 'validated_by');
}
```

## Méthodes Utilitaires

### 1. Gestion des Fichiers
```php
public function getStoragePath(): string
{
    return Storage::disk('documents')->path($this->path);
}

public function getUrl(): string
{
    return Storage::disk('documents')->url($this->path);
}

public function delete(): bool
{
    Storage::disk('documents')->delete($this->path);
    return parent::delete();
}
```

### 2. Validation
```php
public function validate(User $user, ?string $comments = null): void
{
    $this->update([
        'status' => DocumentStatus::VALIDATED,
        'validated_by' => $user->id,
        'validation_date' => now(),
        'comments' => $comments
    ]);
}

public function reject(User $user, string $comments): void
{
    $this->update([
        'status' => DocumentStatus::REJECTED,
        'validated_by' => $user->id,
        'validation_date' => now(),
        'comments' => $comments
    ]);
}
```

### 3. Accesseurs
```php
public function getFormattedSizeAttribute(): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $size = $this->size;
    $unit = 0;

    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }

    return round($size, 2) . ' ' . $units[$unit];
}

public function getStatusLabelAttribute(): string
{
    return match($this->status) {
        DocumentStatus::PENDING => 'En attente',
        DocumentStatus::VALIDATED => 'Validé',
        DocumentStatus::REJECTED => 'Rejeté',
        default => $this->status->value
    };
}
```

## Scopes

### 1. Filtres de Status
```php
public function scopePending($query)
{
    return $query->where('status', DocumentStatus::PENDING);
}

public function scopeValidated($query)
{
    return $query->where('status', DocumentStatus::VALIDATED);
}

public function scopeRejected($query)
{
    return $query->where('status', DocumentStatus::REJECTED);
}
```

### 2. Filtres de Type
```php
public function scopeOfType($query, DocumentType $type)
{
    return $query->where('type', $type);
}

public function scopePassports($query)
{
    return $query->where('type', DocumentType::PASSPORT);
}
```

## Observateur

```php
class DocumentObserver
{
    public function creating(Document $document)
    {
        if (!$document->status) {
            $document->status = DocumentStatus::PENDING;
        }
    }

    public function deleting(Document $document)
    {
        Storage::disk('documents')->delete($document->path);
    }
}
```

## Points d'Apprentissage

### 1. Gestion des Fichiers
```yaml
Storage:
  - Utilisation de disques dédiés
  - Chemins sécurisés
  - Nettoyage automatique
```

### 2. Validation
```yaml
Process:
  - État initial: PENDING
  - Validation: VALIDATED + metadata
  - Rejet: REJECTED + commentaires
```

### 3. Relations Polymorphiques
```yaml
Associations:
  - Prospects
  - Clients
  - Flexibilité future
```

## Exemples d'Utilisation

### 1. Création
```php
$document = Document::create([
    'name' => 'passport.pdf',
    'type' => DocumentType::PASSPORT,
    'path' => 'clients/1/passport.pdf',
    'size' => $file->getSize()
]);

$client->documents()->save($document);
```

### 2. Validation
```php
$document->validate(
    user: Auth::user(),
    comments: 'Document conforme'
);
```

### 3. Requêtes
```php
// Documents en attente
$pending = Document::pending()->get();

// Passeports validés
$validPassports = Document::passports()
    ->validated()
    ->with('documentable')
    ->get();
```

## Bonnes Pratiques

### 1. Sécurité
```yaml
Upload:
  - Validation MIME types
  - Limite taille
  - Noms sécurisés

Stockage:
  - Permissions strictes
  - Backup régulier
  - Isolation fichiers
```

### 2. Performance
```yaml
Optimisations:
  - Eager loading relations
  - Index utilisés
  - Cache si nécessaire
```

### 3. Maintenance
```yaml
Tâches:
  - Nettoyage fichiers orphelins
  - Vérification intégrité
  - Rotation logs
```
