<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

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
            
            if (!$activity->user_id) {
                $activity->user_id = $currentUserId;
            }
            
            if (!$activity->created_by) {
                $activity->created_by = $currentUserId;
            }

            if ($activity->prospect_id && !$activity->subject_id) {
                $activity->subject_type = Prospect::class;
                $activity->subject_id = $activity->prospect_id;
            } elseif ($activity->client_id && !$activity->subject_id) {
                $activity->subject_type = Client::class;
                $activity->subject_id = $activity->client_id;
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
}
