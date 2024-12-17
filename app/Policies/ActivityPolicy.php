<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('activities.view');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->hasPermissionTo('activities.view') &&
            ($user->hasRole('super_admin') || 
             $user->hasRole('manager') || 
             $activity->user_id === $user->id ||
             ($activity->prospect && $activity->prospect->assigned_to === $user->id) ||
             ($activity->client && $activity->client->assigned_to === $user->id));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('activities.create');
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->hasPermissionTo('activities.edit') &&
            ($user->hasRole('super_admin') || 
             $user->hasRole('manager') || 
             $activity->user_id === $user->id);
    }

    public function delete(User $user, ?Activity $activity = null): bool
    {
        if (!$activity) {
            return $user->hasPermissionTo('activities.delete') &&
                ($user->hasRole('super_admin') || 
                 $user->hasRole('manager'));
        }

        return $user->hasPermissionTo('activities.delete') &&
            ($user->hasRole('super_admin') || 
             $user->hasRole('manager'));
    }
}
