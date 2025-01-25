<?php

namespace App\Models;

use App\Traits\CalculatesWorkingDays;
use App\Traits\TracksAssignmentChanges;
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
    use TracksAssignmentChanges;

    // Nombre de jours ouvrés pour l'analyse
    public const ANALYSIS_WORKING_DAYS = 5;

    // Statuts possibles d'un prospect
    public const STATUS_NOUVEAU = 'nouveau';
    public const STATUS_QUALIFIE = 'qualifié';
    public const STATUS_TRAITEMENT = 'traitement';
    public const STATUS_BLOQUE = 'bloqué';
    public const STATUS_CONVERTI = 'converti';

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
        'documents',
        'old_documents',
        'assigned_to',
        'partner_id',
        'commercial_code',
        'analysis_deadline',
        'notes',
        'current_status',
        'converted_to_dossier',
        'converted_at',
        'dossier_reference',
        'user_id',
        'dossier_id'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'analysis_deadline' => 'datetime',
        'emergency_contact' => 'json',
        'documents' => 'array',
        'old_documents' => 'array',
        'converted_to_dossier' => 'boolean',
        'converted_at' => 'datetime',
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
     * Relation avec le dossier
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    /**
     * Relation avec l'utilisateur du portail candidat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    /**
     * Get the full name of the prospect
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Marque le prospect comme converti en dossier
     */
    public function markAsConverted(string $dossierReference): void
    {
        $this->update([
            'converted_to_dossier' => true,
            'converted_at' => now(),
            'dossier_reference' => $dossierReference,
        ]);
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
