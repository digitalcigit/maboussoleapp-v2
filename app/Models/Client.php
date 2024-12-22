<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class Client extends Model
{
    use HasFactory;

    // Constantes de statut
    public const STATUS_ACTIVE = 'actif';
    public const STATUS_INACTIVE = 'inactif';
    public const STATUS_PENDING = 'en_attente';
    public const STATUS_ARCHIVED = 'archive';

    // Constantes de statut de paiement
    public const PAYMENT_STATUS_PENDING = 'en_attente';
    public const PAYMENT_STATUS_PARTIAL = 'partiel';
    public const PAYMENT_STATUS_COMPLETE = 'complete';
    public const PAYMENT_STATUS_REFUNDED = 'rembourse';
    public const PAYMENT_STATUS_CANCELLED = 'annule';

    // Liste des statuts valides
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_PENDING,
            self::STATUS_ARCHIVED,
        ];
    }

    // Liste des statuts de paiement valides
    public static function getValidPaymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING,
            self::PAYMENT_STATUS_PARTIAL,
            self::PAYMENT_STATUS_COMPLETE,
            self::PAYMENT_STATUS_REFUNDED,
            self::PAYMENT_STATUS_CANCELLED,
        ];
    }

    protected $fillable = [
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
        'payment_status',
        'client_number',
        'assigned_to',
        'user_id',
        'prospect_id',
        'commercial_code',
        'partner_id',
        'last_action_at',
        'contract_start_date',
        'contract_end_date',
        'passport_number',
        'passport_expiry',
        'visa_status',
        'travel_preferences',
        'total_amount',
        'paid_amount',
    ];

    protected $casts = [
        'last_action_at' => 'datetime',
        'birth_date' => 'date',
        'contract_start_date' => 'datetime',
        'contract_end_date' => 'datetime',
        'passport_expiry' => 'date',
        'emergency_contact' => 'array',
        'travel_preferences' => 'array',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('checkPermission', function (Builder $builder) {
            if (!auth()->user()?->can('manage clients')) {
                $builder->whereRaw('1 = 0');
            }
        });
    }
}
