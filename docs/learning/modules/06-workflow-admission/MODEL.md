# Documentation des Modèles - Module Workflow Admission

## Vue d'Ensemble

Le module Workflow Admission utilise plusieurs modèles interconnectés pour gérer le processus complet de demande et suivi des admissions.

## Modèles Principaux

### 1. Institution

```php
class Institution extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'country',
        'city',
        'description',
        'website',
        'contact_email',
        'contact_phone',
        'admission_requirements',
        'metadata'
    ];

    protected $casts = [
        'admission_requirements' => 'array',
        'metadata' => 'array'
    ];

    // Relations
    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function admissionApplications()
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    // Scopes
    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    // Méthodes utilitaires
    public function getFullAddressAttribute(): string
    {
        return "{$this->city}, {$this->country}";
    }

    public function hasProgram(string $programCode): bool
    {
        return $this->programs()
            ->where('code', $programCode)
            ->exists();
    }
}
```

### 2. Program

```php
class Program extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'name',
        'code',
        'level',
        'duration',
        'tuition_fee',
        'currency',
        'description',
        'prerequisites',
        'admission_requirements',
        'key_dates',
        'metadata'
    ];

    protected $casts = [
        'prerequisites' => 'array',
        'admission_requirements' => 'array',
        'key_dates' => 'array',
        'metadata' => 'array',
        'tuition_fee' => 'decimal:2'
    ];

    // Relations
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function admissionApplications()
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    // Scopes
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    // Méthodes utilitaires
    public function getFormattedTuitionAttribute(): string
    {
        return number_format($this->tuition_fee, 2) . ' ' . $this->currency;
    }

    public function isEligible(Client $client): bool
    {
        // Logique pour vérifier l'éligibilité basée sur les prérequis
        return true;
    }
}
```

### 3. AdmissionApplication

```php
class AdmissionApplication extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTimeline;

    // États de l'admission
    public const STATUS_INITIATED = 'initiated';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DOCUMENTS_REQUIRED = 'documents_required';
    public const STATUS_DOCUMENTS_VALIDATED = 'documents_validated';
    public const STATUS_SUBMITTED_INSTITUTION = 'submitted_institution';
    public const STATUS_PENDING_DECISION = 'pending_decision';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_CONDITIONAL_ACCEPTED = 'conditional_accepted';
    public const STATUS_ADDITIONAL_DOCUMENTS = 'additional_documents';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FINAL_REGISTRATION = 'final_registration';

    protected $fillable = [
        'client_id',
        'institution_id',
        'program_id',
        'reference_number',
        'status',
        'intake_date',
        'submission_deadline',
        'submitted_date',
        'decision_date',
        'rejection_reason',
        'conditional_requirements',
        'academic_history',
        'metadata'
    ];

    protected $casts = [
        'intake_date' => 'date',
        'submission_deadline' => 'date',
        'submitted_date' => 'date',
        'decision_date' => 'date',
        'academic_history' => 'array',
        'metadata' => 'array'
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function requirements()
    {
        return $this->belongsToMany(AdmissionRequirement::class, 'admission_application_requirements')
            ->withPivot(['status', 'notes', 'validated_at', 'validated_by'])
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(AdmissionDocument::class);
    }

    public function timeline()
    {
        return $this->hasMany(AdmissionTimeline::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_FINAL_REGISTRATION
        ]);
    }

    public function scopeRequiringAction($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DOCUMENTS_REQUIRED,
            self::STATUS_ADDITIONAL_DOCUMENTS
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

    public function isDeadlineApproaching(): bool
    {
        return $this->submission_deadline->diffInDays(now()) <= 14;
    }
}
```

### 4. AdmissionRequirement

```php
class AdmissionRequirement extends Model
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
    public function admissionApplications()
    {
        return $this->belongsToMany(AdmissionApplication::class, 'admission_application_requirements')
            ->withPivot(['status', 'notes', 'validated_at', 'validated_by'])
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(AdmissionDocument::class);
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

### 5. AdmissionDocument

```php
class AdmissionDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'admission_application_id',
        'admission_requirement_id',
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
    public function admissionApplication()
    {
        return $this->belongsTo(AdmissionApplication::class);
    }

    public function requirement()
    {
        return $this->belongsTo(AdmissionRequirement::class, 'admission_requirement_id');
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

        $this->admissionApplication->recordTimelineEvent(
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

        $this->admissionApplication->recordTimelineEvent(
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

### 6. AdmissionTimeline

```php
class AdmissionTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_application_id',
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
    public function admissionApplication()
    {
        return $this->belongsTo(AdmissionApplication::class);
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
    ): AdmissionTimeline {
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
$admissionApplication = AdmissionApplication::create([
    'client_id' => $client->id,
    'institution_id' => $institution->id,
    'program_id' => $program->id,
    'reference_number' => 'ADM-2024-001',
    'status' => AdmissionApplication::STATUS_INITIATED,
    'intake_date' => now()->addMonths(6),
    'submission_deadline' => now()->addMonths(3)
]);

// Attacher les exigences
$requirements = AdmissionRequirement::where('program_id', $program->id)->get();
$admissionApplication->requirements()->attach($requirements->pluck('id')->toArray(), [
    'status' => 'pending'
]);
```

### 2. Validation Document
```php
$admissionDocument = AdmissionDocument::create([
    'admission_application_id' => $admissionApplication->id,
    'admission_requirement_id' => $requirement->id,
    'document_id' => $document->id,
    'status' => 'pending'
]);

if ($requirement->validateDocument($document)['isValid']) {
    $admissionDocument->validate(auth()->user());
} else {
    $admissionDocument->reject(auth()->user(), 'Document non conforme');
}
```

### 3. Timeline
```php
$admissionApplication->recordTimelineEvent(
    'document_uploaded',
    'Nouveau document ajouté',
    [
        'document_id' => $document->id,
        'document_name' => $document->name
    ]
);
```
