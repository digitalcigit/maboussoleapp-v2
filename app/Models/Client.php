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
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ARCHIVED = 'archived';

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

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'country',
        'status',
        'client_number',
        'assigned_to',
        'prospect_id',
        'last_action_at',
        'total_amount',
        'paid_amount',
    ];

    protected $casts = [
        'last_action_at' => 'datetime',
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
