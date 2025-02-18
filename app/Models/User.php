<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

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
        // Les utilisateurs du portail candidat ne peuvent accéder qu'au panel portail-candidat
        if ($this->hasRole('portail_candidat')) {
            return $panel->getId() === 'portail-candidat';
        }

        // Les administrateurs, managers et conseillers peuvent accéder au panel admin
        if ($this->hasAnyRole(['super-admin', 'manager', 'conseiller'])) {
            return $panel->getId() === 'admin';
        }

        return false;
    }

    /**
     * Relation avec le prospect pour les utilisateurs du portail candidat
     */
    public function prospect(): HasOne
    {
        return $this->hasOne(Prospect::class, 'id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar) {
            return $this->avatar;
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=FFFFFF&background=FB923C';
    }
}
