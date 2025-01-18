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
        return $user->hasPermissionTo('view_admin_panel');
    }

    public function view(User $user, Prospect $prospect): bool
    {
        return $user->hasPermissionTo('view_admin_panel') &&
            ($user->hasRole('super-admin') ||
             $user->hasRole('manager') ||
             $prospect->assigned_to === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_prospects');
    }

    public function update(User $user, Prospect $prospect): bool
    {
        return $user->hasPermissionTo('edit_prospects') &&
            ($user->hasRole('super-admin') ||
             $user->hasRole('manager') ||
             $prospect->assigned_to === $user->id);
    }

    public function delete(User $user, Prospect $prospect): bool
    {
        return $user->hasPermissionTo('delete_prospects') &&
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
