<?php

namespace App\Policies;

use App\Models\Dossier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DossierPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('dossiers.view');
    }

    public function view(User $user, Dossier $dossier): bool
    {
        return $user->hasPermissionTo('dossiers.view') &&
            ($user->hasRole('super-admin') ||
             $user->hasRole('manager') ||
             $dossier->assigned_to === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('dossiers.create');
    }

    public function update(User $user, Dossier $dossier): bool
    {
        return $user->hasPermissionTo('dossiers.edit') &&
            ($user->hasRole('super-admin') ||
             $user->hasRole('manager') ||
             $dossier->assigned_to === $user->id);
    }

    public function delete(User $user, Dossier $dossier): bool
    {
        return $user->hasPermissionTo('dossiers.delete') &&
            ($user->hasRole('super-admin') ||
             $user->hasRole('manager'));
    }

    /**
     * Détermine si l'utilisateur peut assigner un dossier à un autre utilisateur lors de la création
     */
    public function assign(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager');
    }

    /**
     * Détermine si l'utilisateur peut réassigner un dossier existant à un autre utilisateur
     */
    public function reassign(User $user, Dossier $dossier): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('manager')) {
            // Le manager ne peut réassigner que les dossiers de son équipe
            return $dossier->assigned_to === $user->id || 
                   $dossier->assignedTo->hasRole('conseiller');
        }

        return false;
    }
}
