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
     * @return Client Le client créé
     */
    public function convertToClient(): Client
    {
        return Client::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'client_number' => 'CLI-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'status' => 'active',
            'prospect_id' => $this->id,
        ]);
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
