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

    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'type',
        'description',
        'scheduled_at',
        'completed_at',
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
     * Obtenir le libellé traduit du type
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
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
     * Relation avec l'utilisateur assigné
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le créateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation polymorphe avec le sujet de l'activité
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
