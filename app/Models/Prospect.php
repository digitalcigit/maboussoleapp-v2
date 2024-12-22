<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prospect extends Model
{
    use HasFactory;

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
        'notes',
        'user_id',
    ];

    protected $casts = [
        'last_action_at' => 'datetime',
        'birth_date' => 'date',
        'analysis_deadline' => 'datetime',
        'emergency_contact' => 'array',
    ];

    // Constantes de statut
    const STATUS_NEW = 'nouveau';
    const STATUS_IN_PROGRESS = 'en_cours';
    const STATUS_QUALIFIED = 'qualifie';
    const STATUS_CONVERTED = 'converti';
    const STATUS_CANCELLED = 'annule';

    // Liste des statuts valides
    public static $validStatuses = [
        self::STATUS_NEW,
        self::STATUS_IN_PROGRESS,
        self::STATUS_QUALIFIED,
        self::STATUS_CONVERTED,
        self::STATUS_CANCELLED,
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convertit le prospect en client
     * @return Client Le client créé
     */
    public function convertToClient(): Client
    {
        // Vérifier si le prospect peut être converti
        if ($this->status !== self::STATUS_QUALIFIED) {
            throw new \Exception('Seuls les prospects qualifiés peuvent être convertis en clients.');
        }

        $client = Client::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date,
            'profession' => $this->profession,
            'education_level' => $this->education_level,
            'current_location' => $this->current_location,
            'current_field' => $this->current_field,
            'desired_field' => $this->desired_field,
            'desired_destination' => $this->desired_destination,
            'emergency_contact' => $this->emergency_contact,
            'client_number' => 'CLI-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'status' => 'actif',
            'prospect_id' => $this->id,
            'assigned_to' => $this->assigned_to,
            'user_id' => $this->user_id,
            'commercial_code' => $this->commercial_code,
            'partner_id' => $this->partner_id,
        ]);

        // Mettre à jour le statut du prospect
        $this->update(['status' => self::STATUS_CONVERTED]);

        // Créer une activité de conversion
        Activity::create([
            'title' => 'Conversion en client',
            'description' => 'Prospect converti en client',
            'type' => 'conversion',
            'status' => 'termine',
            'user_id' => auth()->id(),
            'prospect_id' => $this->id,
            'client_id' => $client->id,
            'created_by' => auth()->id(),
        ]);

        return $client;
    }

    public function convertToClientSimple()
    {
        return Client::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'reference_number' => 'CLI-' . random_int(10000, 99999),
            'status' => 'actif',
            'prospect_id' => $this->id,
        ]);
    }
}
