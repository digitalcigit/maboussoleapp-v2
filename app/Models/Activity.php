<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Types d'activités
    public const TYPE_NOTE = 'note';
    public const TYPE_CALL = 'appel';
    public const TYPE_EMAIL = 'email';
    public const TYPE_MEETING = 'reunion';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_CONVERSION = 'conversion';

    // Statuts d'activités
    public const STATUS_PENDING = 'en_attente';
    public const STATUS_IN_PROGRESS = 'en_cours';
    public const STATUS_COMPLETED = 'termine';
    public const STATUS_CANCELLED = 'annule';

    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'type',
        'description',
        'scheduled_at',
        'completed_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Liste des types d'activités valides
     */
    public static function getValidTypes(): array
    {
        return [
            self::TYPE_NOTE,
            self::TYPE_CALL,
            self::TYPE_EMAIL,
            self::TYPE_MEETING,
            self::TYPE_DOCUMENT,
            self::TYPE_CONVERSION,
        ];
    }

    /**
     * Liste des statuts d'activités valides
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Obtenir le libellé traduit du type
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_NOTE => 'Note',
            self::TYPE_CALL => 'Appel',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_MEETING => 'Réunion',
            self::TYPE_DOCUMENT => 'Document',
            self::TYPE_CONVERSION => 'Conversion',
            default => $this->type,
        };
    }

    /**
     * Obtenir le libellé traduit du statut
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_COMPLETED => 'Terminé',
            self::STATUS_CANCELLED => 'Annulé',
            default => $this->status,
        };
    }

    /**
     * Relation polymorphique avec le sujet de l'activité
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation avec l'utilisateur qui a créé l'activité
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur assigné à l'activité
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
