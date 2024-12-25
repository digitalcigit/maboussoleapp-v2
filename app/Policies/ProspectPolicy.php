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
        return $user->hasPermissionTo('prospects.view');
    }

    public function view(User $user, Prospect $prospect): bool
    {
        return $user->hasPermissionTo('prospects.view') &&
            ($user->hasRole('super_admin') ||
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
            ($user->hasRole('super_admin') ||
             $user->hasRole('manager') ||
             $prospect->assigned_to === $user->id);
    }

    public function delete(User $user, Prospect $prospect): bool
    {
        return $user->hasPermissionTo('prospects.delete') &&
            ($user->hasRole('super_admin') ||
             $user->hasRole('manager'));
    }

    public function assign(User $user): bool
    {
        return $user->hasRole('manager') || $user->hasRole('super_admin');
    }
}
