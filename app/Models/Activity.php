<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'start_date',
        'end_date',
        'client_id',
        'prospect_id',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

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

    /**
     * Get the client associated with the activity.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the prospect associated with the activity.
     */
    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    /**
     * Get the user who created the activity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include activities for a specific client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope a query to only include activities for a specific prospect.
     */
    public function scopeForProspect($query, $prospectId)
    {
        return $query->where('prospect_id', $prospectId);
    }

    /**
     * Scope a query to only include activities created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include activities with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include activities with a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include upcoming activities.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())
                    ->orderBy('start_date', 'asc');
    }

    /**
     * Scope a query to only include past activities.
     */
    public function scopePast($query)
    {
        return $query->where('start_date', '<', now())
                    ->orderBy('start_date', 'desc');
    }
}
