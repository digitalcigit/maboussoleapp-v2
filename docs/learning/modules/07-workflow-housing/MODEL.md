# Documentation des Modèles - Module Workflow Logement

## Vue d'Ensemble

Le module Workflow Logement utilise plusieurs modèles interconnectés pour gérer le processus complet de demande et suivi des logements.

## Modèles Principaux

### 1. Residence

```php
class Residence extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'country',
        'city',
        'address',
        'description',
        'website',
        'contact_email',
        'contact_phone',
        'amenities',
        'rules',
        'metadata'
    ];

    protected $casts = [
        'amenities' => 'array',
        'rules' => 'array',
        'metadata' => 'array'
    ];

    // Relations
    public function housings()
    {
        return $this->hasMany(Housing::class);
    }

    // Scopes
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    // Méthodes utilitaires
    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->city}, {$this->country}";
    }

    public function hasAvailableHousings(): bool
    {
        return $this->housings()
            ->where('is_available', true)
            ->exists();
    }
}
```

### 2. Landlord

```php
class Landlord extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'preferred_contact_method',
        'notes',
        'preferences',
        'metadata'
    ];

    protected $casts = [
        'preferences' => 'array',
        'metadata' => 'array'
    ];

    // Relations
    public function housings()
    {
        return $this->hasMany(Housing::class);
    }

    // Scopes
    public function scopeWithAvailableHousings($query)
    {
        return $query->whereHas('housings', function ($query) {
            $query->where('is_available', true);
        });
    }

    // Méthodes utilitaires
    public function getPreferredContactInfoAttribute(): string
    {
        return $this->preferred_contact_method === 'email' 
            ? $this->email 
            : $this->phone;
    }

    public function notifyAboutApplication(HousingApplication $application): void
    {
        // Logique de notification
    }
}
```

### 3. Housing

```php
class Housing extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'residence_id',
        'landlord_id',
        'reference',
        'type',
        'address',
        'city',
        'postal_code',
        'surface',
        'rooms',
        'rent',
        'deposit',
        'agency_fees',
        'description',
        'is_furnished',
        'available_from',
        'available_until',
        'is_available',
        'amenities',
        'requirements',
        'metadata'
    ];

    protected $casts = [
        'rent' => 'decimal:2',
        'deposit' => 'decimal:2',
        'agency_fees' => 'decimal:2',
        'is_furnished' => 'boolean',
        'is_available' => 'boolean',
        'available_from' => 'date',
        'available_until' => 'date',
        'amenities' => 'array',
        'requirements' => 'array',
        'metadata' => 'array'
    ];

    // Relations
    public function residence()
    {
        return $this->belongsTo(Residence::class);
    }

    public function landlord()
    {
        return $this->belongsTo(Landlord::class);
    }

    public function applications()
    {
        return $this->hasMany(HousingApplication::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where('available_from', '<=', now());
    }

    public function scopeInBudget($query, float $budget)
    {
        return $query->where('rent', '<=', $budget);
    }

    // Méthodes utilitaires
    public function getTotalInitialCostAttribute(): float
    {
        return $this->rent + $this->deposit + ($this->agency_fees ?? 0);
    }

    public function isEligible(Client $client): bool
    {
        // Logique pour vérifier l'éligibilité
        return true;
    }

    public function markAsRented(): void
    {
        $this->update([
            'is_available' => false
        ]);
    }
}
```

### 4. HousingApplication

```php
class HousingApplication extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTimeline;

    // États du logement
    public const STATUS_INITIATED = 'initiated';
    public const STATUS_SEARCHING = 'searching';
    public const STATUS_HOUSING_IDENTIFIED = 'housing_identified';
    public const STATUS_DOCUMENTS_REQUIRED = 'documents_required';
    public const STATUS_DOCUMENTS_VALIDATED = 'documents_validated';
    public const STATUS_APPLICATION_SUBMITTED = 'application_submitted';
    public const STATUS_PENDING_DECISION = 'pending_decision';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CONTRACT_SIGNED = 'contract_signed';
    public const STATUS_DEPOSIT_PAID = 'deposit_paid';
    public const STATUS_MOVE_IN_PLANNED = 'move_in_planned';
    public const STATUS_MOVED_IN = 'moved_in';

    protected $fillable = [
        'client_id',
        'housing_id',
        'reference_number',
        'status',
        'desired_move_in_date',
        'submission_date',
        'decision_date',
        'contract_date',
        'move_in_date',
        'rejection_reason',
        'monthly_budget',
        'guarantor_info',
        'metadata'
    ];

    protected $casts = [
        'desired_move_in_date' => 'date',
        'submission_date' => 'date',
        'decision_date' => 'date',
        'contract_date' => 'date',
        'move_in_date' => 'date',
        'monthly_budget' => 'decimal:2',
        'guarantor_info' => 'array',
        'metadata' => 'array'
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function housing()
    {
        return $this->belongsTo(Housing::class);
    }

    public function requirements()
    {
        return $this->belongsToMany(HousingRequirement::class, 'housing_application_requirements')
            ->withPivot(['status', 'notes', 'validated_at', 'validated_by'])
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(HousingDocument::class);
    }

    public function timeline()
    {
        return $this->hasMany(HousingTimeline::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_MOVED_IN,
            self::STATUS_REJECTED
        ]);
    }

    public function scopeRequiringAction($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DOCUMENTS_REQUIRED,
            self::STATUS_CONTRACT_SIGNED
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

        if ($status === self::STATUS_MOVED_IN) {
            $this->housing->markAsRented();
        }
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

    public function isMoveInDateValid(): bool
    {
        return $this->desired_move_in_date >= $this->housing->available_from
            && (!$this->housing->available_until 
                || $this->desired_move_in_date <= $this->housing->available_until);
    }
}
```

### 5. HousingRequirement

```php
class HousingRequirement extends Model
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
    public function housingApplications()
    {
        return $this->belongsToMany(HousingApplication::class, 'housing_application_requirements')
            ->withPivot(['status', 'notes', 'validated_at', 'validated_by'])
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(HousingDocument::class);
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

### 6. HousingDocument

```php
class HousingDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'housing_application_id',
        'housing_requirement_id',
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
    public function housingApplication()
    {
        return $this->belongsTo(HousingApplication::class);
    }

    public function requirement()
    {
        return $this->belongsTo(HousingRequirement::class, 'housing_requirement_id');
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

        $this->housingApplication->recordTimelineEvent(
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

        $this->housingApplication->recordTimelineEvent(
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

### 7. HousingTimeline

```php
class HousingTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_application_id',
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
    public function housingApplication()
    {
        return $this->belongsTo(HousingApplication::class);
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
    ): HousingTimeline {
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
$housingApplication = HousingApplication::create([
    'client_id' => $client->id,
    'housing_id' => $housing->id,
    'reference_number' => 'HSG-2024-001',
    'status' => HousingApplication::STATUS_INITIATED,
    'desired_move_in_date' => now()->addMonths(2),
    'monthly_budget' => 800.00
]);

// Attacher les exigences
$requirements = HousingRequirement::where('housing_id', $housing->id)->get();
$housingApplication->requirements()->attach($requirements->pluck('id')->toArray(), [
    'status' => 'pending'
]);
```

### 2. Validation Document
```php
$housingDocument = HousingDocument::create([
    'housing_application_id' => $housingApplication->id,
    'housing_requirement_id' => $requirement->id,
    'document_id' => $document->id,
    'status' => 'pending'
]);

if ($requirement->validateDocument($document)['isValid']) {
    $housingDocument->validate(auth()->user());
} else {
    $housingDocument->reject(auth()->user(), 'Document non conforme');
}
```

### 3. Timeline
```php
$housingApplication->recordTimelineEvent(
    'document_uploaded',
    'Nouveau document ajouté',
    [
        'document_id' => $document->id,
        'document_name' => $document->name
    ]
);
```
