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
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'assigned_to',
        'last_action_at',
        'notes'
    ];

    protected $casts = [
        'last_action_at' => 'datetime',
    ];

    const STATUS_NEW = 'new';
    const STATUS_ANALYZING = 'analyzing';
    const STATUS_QUALIFIED = 'qualified';
    const STATUS_CONVERTED = 'converted';
    const STATUS_REJECTED = 'rejected';

    const SOURCE_WEBSITE = 'website';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_SOCIAL = 'social';
    const SOURCE_OTHER = 'other';

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Convertit le prospect en client
     *
     * @param User $convertedBy L'utilisateur qui effectue la conversion
     * @throws \Exception Si le prospect n'est pas qualifié
     * @return Client Le client créé
     */
    public function convertToClient(User $convertedBy): Client
    {
        if ($this->status !== self::STATUS_QUALIFIED) {
            throw new \Exception('Seuls les prospects qualifiés peuvent être convertis en clients.');
        }

        if (!$convertedBy->can('prospects.convert')) {
            throw new \Exception('Vous n\'avez pas la permission de convertir des prospects en clients.');
        }

        // Générer le numéro de client
        $lastClient = Client::orderBy('id', 'desc')->first();
        $nextId = $lastClient ? $lastClient->id + 1 : 1;
        $clientNumber = 'CLI' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        // Créer le client
        $client = Client::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => 'active',
            'assigned_to' => $this->assigned_to,
            'prospect_id' => $this->id,
            'client_number' => $clientNumber
        ]);

        // Créer une activité de conversion
        Activity::create([
            'title' => 'Conversion en client',
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'type' => 'note',
            'description' => 'Conversion du prospect en client',
            'user_id' => $convertedBy->id,
            'status' => 'terminé'
        ]);

        // Mettre à jour le statut du prospect
        $this->update(['status' => self::STATUS_CONVERTED]);

        return $client;
    }
}
