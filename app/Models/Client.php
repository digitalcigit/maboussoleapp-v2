<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Statuts de base du client
    public const STATUS_ACTIVE = 'actif';

    public const STATUS_INACTIVE = 'inactif';

    public const STATUS_PENDING = 'en_attente';

    public const STATUS_ARCHIVED = 'archive';

    // Statuts de paiement
    public const PAYMENT_STATUS_PENDING = 'en_attente';

    public const PAYMENT_STATUS_PARTIAL = 'partiel';

    public const PAYMENT_STATUS_COMPLETED = 'complete';

    // Statuts de visa
    public const VISA_STATUS_NOT_STARTED = 'non_demarre';

    public const VISA_STATUS_IN_PROGRESS = 'en_cours';

    public const VISA_STATUS_OBTAINED = 'obtenu';

    public const VISA_STATUS_REJECTED = 'refuse';

    protected $fillable = [
        'prospect_id',
        'client_number',
        'passport_number',
        'passport_expiry',
        'visa_status',
        'travel_preferences',
        'payment_status',
        'total_amount',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'passport_expiry' => 'date',
        'travel_preferences' => 'json',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    /**
     * Liste des statuts de base valides
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_PENDING,
            self::STATUS_ARCHIVED,
        ];
    }

    /**
     * Liste des statuts de paiement valides
     */
    public static function getValidPaymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING,
            self::PAYMENT_STATUS_PARTIAL,
            self::PAYMENT_STATUS_COMPLETED,
        ];
    }

    /**
     * Liste des statuts de visa valides
     */
    public static function getValidVisaStatuses(): array
    {
        return [
            self::VISA_STATUS_NOT_STARTED,
            self::VISA_STATUS_IN_PROGRESS,
            self::VISA_STATUS_OBTAINED,
            self::VISA_STATUS_REJECTED,
        ];
    }

    /**
     * Obtenir le libellé traduit du statut de base
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_INACTIVE => 'Inactif',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_ARCHIVED => 'Archivé',
            default => $this->status,
        };
    }

    /**
     * Obtenir le libellé traduit du statut de paiement
     */
    public function getPaymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'En attente',
            self::PAYMENT_STATUS_PARTIAL => 'Partiel',
            self::PAYMENT_STATUS_COMPLETED => 'Complété',
            default => $this->payment_status,
        };
    }

    /**
     * Obtenir le libellé traduit du statut de visa
     */
    public function getVisaStatusLabel(): string
    {
        return match ($this->visa_status) {
            self::VISA_STATUS_NOT_STARTED => 'Non démarré',
            self::VISA_STATUS_IN_PROGRESS => 'En cours',
            self::VISA_STATUS_OBTAINED => 'Obtenu',
            self::VISA_STATUS_REJECTED => 'Refusé',
            default => $this->visa_status,
        };
    }

    /**
     * Relation avec le prospect d'origine
     */
    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
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
     * Accesseur pour obtenir le nom complet du client via le prospect
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->prospect->first_name} {$this->prospect->last_name}";
    }
}
