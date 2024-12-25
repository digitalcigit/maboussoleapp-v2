# Documentation des Modèles - Module Workflow Visa

## Vue d'Ensemble

Le module Workflow Visa utilise plusieurs modèles interconnectés pour gérer le processus complet de demande et suivi des visas.

## Modèles Principaux

### 1. VisaApplication

```php
class VisaApplication extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTimeline;

    // États du visa
    public const STATUS_INITIATED = 'initiated';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DOCUMENTS_REQUIRED = 'documents_required';
    public const STATUS_DOCUMENTS_VALIDATED = 'documents_validated';
    public const STATUS_SUBMITTED_EMBASSY = 'submitted_embassy';
    public const STATUS_PENDING_DECISION = 'pending_decision';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVISION = 'revision';

    protected $fillable = [
        'client_id',
        'reference_number',
        'status',
        'visa_type',
        'embassy',
        'planned_travel_date',
        'submission_date',
        'decision_date',
        'rejection_reason',
        'metadata'
    ];

    protected $casts = [
        'planned_travel_date' => 'date',
        'submission_date' => 'date',
        'decision_date' => 'date',
        'metadata' => 'array'
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function requirements()
    {
        return $this->belongsToMany(VisaRequirement::class, 'visa_application_requirements')
            ->withPivot(['status', 'notes', 'validated_at', 'validated_by'])
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(VisaDocument::class);
    }

    public function timeline()
    {
        return $this->hasMany(VisaTimeline::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_REJECTED
        ]);
    }

    public function scopeRequiringAction($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DOCUMENTS_REQUIRED,
            self::STATUS_REVISION
        ]);
    }

    // Méthodes d'état
    public function updateStatus(string $status, ?string $reason = null): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => $status,
            'rejection_reason' => $status === self::STATUS_REJECTED ? $reason : null
        ]);

        $this->recordTimelineEvent(
            'status_changed',
            "Statut changé de $oldStatus à $status",
            compact('oldStatus', 'status', 'reason')
        );
    }

    public function isComplete(): bool
    {
        return $this->requirements()
            ->wherePivot('status', '!=', 'validated')
            ->doesntExist();
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DOCUMENTS_VALIDATED
            && $this->isComplete();
    }
}
```

### 2. VisaRequirement

```php
class VisaRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'document_type',
        'is_mandatory',
        'validation_rules'
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'validation_rules' => 'array'
    ];

    // Relations
    public function visaApplications()
    {
        return $this->belongsToMany(VisaApplication::class, 'visa_application_requirements')
            ->withPivot(['status', 'notes', 'validated_at', 'validated_by'])
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(VisaDocument::class);
    }

    // Méthodes utilitaires
    public function validateDocument(Document $document): array
    {
        $rules = $this->validation_rules ?? [];
        $validator = Validator::make($document->toArray(), $rules);

        return [
            'isValid' => $validator->passes(),
            'errors' => $validator->errors()->toArray()
        ];
    }
}
```

### 3. VisaDocument

```php
class VisaDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'visa_application_id',
        'visa_requirement_id',
        'document_id',
        'status',
        'rejection_reason',
        'validated_at',
        'validated_by'
    ];

    protected $casts = [
        'validated_at' => 'datetime'
    ];

    // Relations
    public function visaApplication()
    {
        return $this->belongsTo(VisaApplication::class);
    }

    public function requirement()
    {
        return $this->belongsTo(VisaRequirement::class, 'visa_requirement_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Méthodes de validation
    public function validate(User $user): void
    {
        $this->update([
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => $user->id,
            'rejection_reason' => null
        ]);

        $this->visaApplication->recordTimelineEvent(
            'document_validated',
            "Document validé: {$this->document->name}",
            ['document_id' => $this->document_id]
        );
    }

    public function reject(User $user, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'validated_at' => now(),
            'validated_by' => $user->id
        ]);

        $this->visaApplication->recordTimelineEvent(
            'document_rejected',
            "Document rejeté: {$this->document->name}",
            [
                'document_id' => $this->document_id,
                'reason' => $reason
            ]
        );
    }
}
```

### 4. VisaTimeline

```php
class VisaTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id',
        'event_type',
        'title',
        'description',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    // Relations
    public function visaApplication()
    {
        return $this->belongsTo(VisaApplication::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Méthodes utilitaires
    public function getIconAttribute(): string
    {
        return match($this->event_type) {
            'status_changed' => 'heroicon-o-arrow-path',
            'document_uploaded' => 'heroicon-o-document-plus',
            'document_validated' => 'heroicon-o-check-circle',
            'document_rejected' => 'heroicon-o-x-circle',
            default => 'heroicon-o-information-circle'
        };
    }

    public function getColorAttribute(): string
    {
        return match($this->event_type) {
            'status_changed' => 'primary',
            'document_validated' => 'success',
            'document_rejected' => 'danger',
            default => 'secondary'
        };
    }
}
```

## Trait HasTimeline

```php
trait HasTimeline
{
    public function recordTimelineEvent(
        string $eventType,
        string $title,
        array $metadata = []
    ): VisaTimeline {
        return $this->timeline()->create([
            'event_type' => $eventType,
            'title' => $title,
            'metadata' => $metadata,
            'created_by' => auth()->id()
        ]);
    }

    public function getTimelineEvents(): Collection
    {
        return $this->timeline()
            ->with('creator')
            ->latest()
            ->get();
    }
}
```

## Points d'Apprentissage

### 1. Gestion des États
```yaml
Workflow:
  - États définis par constantes
  - Transitions contrôlées
  - Validation conditions
  - Historique changements
```

### 2. Relations
```yaml
Types:
  - One-to-Many
  - Many-to-Many
  - Polymorphique
  - Avec métadonnées
```

### 3. Validation
```yaml
Process:
  - Règles dynamiques
  - Validation documents
  - État requirements
  - Historique validations
```

## Exemples d'Utilisation

### 1. Création Demande
```php
$visaApplication = VisaApplication::create([
    'client_id' => $client->id,
    'reference_number' => 'VISA-2024-001',
    'status' => VisaApplication::STATUS_INITIATED,
    'visa_type' => 'tourist',
    'embassy' => 'France',
    'planned_travel_date' => now()->addMonths(3)
]);

// Attacher les exigences
$requirements = VisaRequirement::where('visa_type', 'tourist')->get();
$visaApplication->requirements()->attach($requirements->pluck('id')->toArray(), [
    'status' => 'pending'
]);
```

### 2. Validation Document
```php
$visaDocument = VisaDocument::create([
    'visa_application_id' => $visaApplication->id,
    'visa_requirement_id' => $requirement->id,
    'document_id' => $document->id,
    'status' => 'pending'
]);

if ($requirement->validateDocument($document)['isValid']) {
    $visaDocument->validate(auth()->user());
} else {
    $visaDocument->reject(auth()->user(), 'Document non conforme');
}
```

### 3. Timeline
```php
$visaApplication->recordTimelineEvent(
    'document_uploaded',
    'Nouveau document ajouté',
    [
        'document_id' => $document->id,
        'document_name' => $document->name
    ]
);
```
