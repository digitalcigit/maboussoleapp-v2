<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const TYPE_CALL = 'call';
    const TYPE_EMAIL = 'email';
    const TYPE_MEETING = 'meeting';
    const TYPE_NOTE = 'note';
    const TYPE_TASK = 'task';

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
        'completed_at'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
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
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            self::TYPE_TASK => __('Tâche'),
        ];
    }
}
