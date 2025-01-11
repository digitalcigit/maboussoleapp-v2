<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    public function view(User $user, User $model): bool
    {
        // Un manager peut voir tous les utilisateurs sauf les super-admin
        if ($user->hasRole('manager')) {
            return !$model->hasRole('super-admin');
        }
        
        return $user->hasPermissionTo('users.view');
    }

    public function create(User $user): bool
    {
        // Un manager peut créer des utilisateurs mais pas des super-admin
        if ($user->hasRole('manager')) {
            // Le manager ne peut pas créer de super-admin
            return true && !request()->has('roles') || 
                   !in_array('super-admin', request()->input('roles', []));
        }

        return $user->hasPermissionTo('users.create');
    }

    public function update(User $user, User $model): bool
    {
        // Un manager ne peut pas modifier les super-admin
        if ($user->hasRole('manager')) {
            return !$model->hasRole('super-admin');
        }

        return $user->hasPermissionTo('users.edit');
    }

    public function delete(User $user, User $model): bool
    {
        // Un manager ne peut pas supprimer les super-admin
        if ($user->hasRole('manager')) {
            return !$model->hasRole('super-admin');
        }

        return $user->hasPermissionTo('users.delete');
    }

    public function assignRole(User $user, User $model): bool
    {
        // Seul le super-admin peut assigner le rôle super-admin
        if ($user->hasRole('manager')) {
            return !$model->hasRole('super-admin');
        }

        return $user->hasRole('super-admin');
    }
}
