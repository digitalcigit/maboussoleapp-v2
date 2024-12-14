<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        Log::info('Tentative d\'accès au panel', [
            'user_id' => $this->id,
            'user_email' => $this->email,
            'panel_id' => $panel->getId(),
            'roles' => $this->roles->pluck('name'),
            'timestamp' => now()->toDateTimeString(),
            'session_id' => session()->getId(),
        ]);

        // Pour déboguer, retournons true et voyons si la méthode est appelée
        $canAccess = true;

        Log::info('Résultat de canAccessPanel', [
            'can_access' => $canAccess,
            'user_email' => $this->email,
        ]);

        return $canAccess;
    }
}