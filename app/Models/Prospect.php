<?php

namespace App\Models;

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
        'current_location',
        'current_field',
        'desired_field',
        'desired_destination',
        'emergency_contact',
        'status',
        'assigned_to',
        'commercial_code',
        'partner_id',
        'last_action_at',
        'analysis_deadline',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'last_action_at' => 'datetime',
        'analysis_deadline' => 'datetime',
        'emergency_contact' => 'json',
    ];

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
}
