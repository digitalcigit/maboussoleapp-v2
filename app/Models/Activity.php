<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

    // Constantes de statut
    const STATUS_PENDING = 'en_attente';
    const STATUS_IN_PROGRESS = 'en_cours';
    const STATUS_COMPLETED = 'termine';
    const STATUS_CANCELLED = 'annule';

    // Constantes de type
    const TYPE_CALL = 'appel';
    const TYPE_EMAIL = 'email';
    const TYPE_MEETING = 'reunion';
    const TYPE_NOTE = 'note';
    const TYPE_DOCUMENT = 'document';
    const TYPE_PAYMENT = 'paiement';
    const TYPE_CONVERSION = 'conversion';
    const TYPE_OTHER = 'autre';

    // Liste des statuts valides
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    // Liste des types valides
    public static function getValidTypes(): array
    {
        return [
            self::TYPE_CALL,
            self::TYPE_EMAIL,
            self::TYPE_MEETING,
            self::TYPE_NOTE,
            self::TYPE_DOCUMENT,
            self::TYPE_PAYMENT,
            self::TYPE_CONVERSION,
            self::TYPE_OTHER,
        ];
    }

    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'user_id',
        'created_by',
        'subject_type',
        'subject_id',
        'prospect_id',
        'client_id',
        'scheduled_at',
        'completed_at',
        'notes',
        'result',
        'attachments'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'attachments' => 'array',
        'result' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activity) {
            $currentUserId = auth()->id();
            
            if (empty($activity->created_by)) {
                $activity->created_by = $currentUserId;
            }

            if (empty($activity->user_id)) {
                $activity->user_id = $currentUserId;
            }

            if (!empty($activity->prospect_id)) {
                $activity->subject_type = Prospect::class;
                $activity->subject_id = $activity->prospect_id;
            } elseif (!empty($activity->client_id)) {
                $activity->subject_type = Client::class;
                $activity->subject_id = $activity->client_id;
            }

            if (empty($activity->status)) {
                $activity->status = self::STATUS_PENDING;
            }

            // Valider le type et le statut
            if (!in_array($activity->type, self::getValidTypes())) {
                throw new \InvalidArgumentException('Type d\'activité invalide');
            }

            if (!in_array($activity->status, self::getValidStatuses())) {
                throw new \InvalidArgumentException('Statut d\'activité invalide');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => __('En attente'),
            self::STATUS_IN_PROGRESS => __('En cours'),
            self::STATUS_COMPLETED => __('Terminé'),
            self::STATUS_CANCELLED => __('Annulé'),
        ];
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_CALL => __('Appel'),
            self::TYPE_EMAIL => __('Email'),
            self::TYPE_MEETING => __('Réunion'),
            self::TYPE_NOTE => __('Note'),
            self::TYPE_DOCUMENT => __('Document'),
            self::TYPE_PAYMENT => __('Paiement'),
            self::TYPE_CONVERSION => __('Conversion'),
            self::TYPE_OTHER => __('Autre'),
        ];
    }
}
