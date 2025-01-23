<?php

namespace App\Policies;

use App\Models\Prospect;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProspectPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Les prospects peuvent voir leur propre profil
        if ($user->hasRole('prospect')) {
            return true;
        }
        return $user->hasPermissionTo('admin.panel.access');
    }

    public function view(User $user, Prospect $prospect): bool
    {
        // Les prospects ne peuvent voir que leur propre profil
        if ($user->hasRole('prospect')) {
            return $user->id === $prospect->assigned_to;
        }
        
        return $user->hasPermissionTo('admin.panel.access') &&
            ($user->hasRole('super-admin') ||
             $user->hasRole('manager') ||
             $prospect->assigned_to === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('prospects.create');
    }

    public function update(User $user, Prospect $prospect): bool
    {
        return $user->hasPermissionTo('prospects.edit') &&
            ($user->hasRole('super-admin') ||
             $user->hasRole('manager') ||
             $prospect->assigned_to === $user->id);
    }

    public function delete(User $user, Prospect $prospect): bool
    {
        return $user->hasPermissionTo('prospects.delete') &&
            ($user->hasRole('super-admin') ||
             $user->hasRole('manager'));
    }

    public function assign(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('manager');
    }

    public function reassign(User $user, Prospect $prospect): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('manager')) {
            // Le manager ne peut réassigner que les prospects de son équipe
            return $prospect->assigned_to === $user->id || 
                   $prospect->assignedTo->hasRole('conseiller');
        }

        return false;
    }
}
