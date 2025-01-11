<?php

namespace App\Models;

use App\Traits\CalculatesWorkingDays;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
    use HasFactory;
    use SoftDeletes;
    use CalculatesWorkingDays;

    // Nombre de jours ouvrés pour l'analyse
    public const ANALYSIS_WORKING_DAYS = 5;

    // Statuts des prospects
    public const STATUS_NEW = 'nouveau';

    public const STATUS_ANALYZING = 'en_analyse';

    public const STATUS_APPROVED = 'approuve';

    public const STATUS_REJECTED = 'refuse';

    public const STATUS_CONVERTED = 'converti';

    protected $fillable = [
        'reference_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'profession',
        'education_level',
        'desired_field',
        'desired_destination',
        'emergency_contact',
        'status',
        'assigned_to',
        'commercial_code',
        'partner_id',
        'last_action_at',
        'analysis_deadline',
        'documents',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'last_action_at' => 'datetime',
        'analysis_deadline' => 'datetime',
        'emergency_contact' => 'json',
        'documents' => 'array',
        'old_documents' => 'array',
    ];

    /**
     * Get the documents
     *
     * @return array
     */
    public function getDocumentsAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        
        $documents = is_string($value) ? json_decode($value, true) : $value;
        
        // Normaliser les chemins de fichiers
        if (is_array($documents)) {
            foreach ($documents as $key => $document) {
                if (isset($document['file']) && is_string($document['file'])) {
                    if (!str_starts_with($document['file'], 'prospects/documents/')) {
                        $documents[$key]['file'] = 'prospects/documents/' . basename($document['file']);
                    }
                }
            }
        }
        
        return $documents;
    }

    /**
     * Set the documents
     *
     * @param array|string $value
     * @return void
     */
    public function setDocumentsAttribute($value)
    {
        if (is_array($value)) {
            $documents = [];
            foreach ($value as $document) {
                if (isset($document['file'])) {
                    if ($document['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $path = $document['file']->store('prospects/documents', 'public');
                        $document['file'] = $path;
                    } elseif (is_string($document['file']) && !str_starts_with($document['file'], 'prospects/documents/')) {
                        $document['file'] = 'prospects/documents/' . basename($document['file']);
                    }
                }
                $documents[] = $document;
            }
            $value = $documents;
        }
        
        $this->attributes['documents'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Liste des statuts valides pour la base de données
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_ANALYZING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CONVERTED,
        ];
    }

    /**
     * Obtenir le libellé traduit du statut
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'Nouveau',
            self::STATUS_ANALYZING => 'En analyse',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_REJECTED => 'Refusé',
            self::STATUS_CONVERTED => 'Converti',
            default => $this->status,
        };
    }

    /**
     * Relation avec l'utilisateur assigné
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relation avec le partenaire
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    /**
     * Relation avec les activités
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Relation avec les documents
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Relation avec le client
     */
    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Obtenir les documents par type
     */
    public function getDocumentsByType(string $type): array
    {
        $documents = $this->documents;
        return collect($documents)
            ->where('type', $type)
            ->all();
    }

    /**
     * Vérifier si un type de document existe
     */
    public function hasDocumentType(string $type): bool
    {
        $documents = $this->documents;
        return collect($documents)
            ->where('type', $type)
            ->isNotEmpty();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prospect) {
            // Définir la date limite d'analyse à 5 jours ouvrés
            $prospect->analysis_deadline = $prospect->addWorkingDays(
                Carbon::now(),
                self::ANALYSIS_WORKING_DAYS
            );
        });

        static::retrieved(function ($model) {
            // Supprimer cette méthode car elle est maintenant gérée dans l'accesseur getDocumentsAttribute
        });
    }
}
