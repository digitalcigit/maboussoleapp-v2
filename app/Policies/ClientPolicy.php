<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('clients.view');
    }

    public function view(User $user, Client $client): bool
    {
        return $user->hasPermissionTo('clients.view') &&
            ($user->hasRole('super_admin') ||
             $user->hasRole('manager') ||
             $client->assigned_to === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('clients.create');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->hasPermissionTo('clients.edit') &&
            ($user->hasRole('super_admin') ||
             $user->hasRole('manager') ||
             $client->assigned_to === $user->id);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->hasPermissionTo('clients.delete') &&
            ($user->hasRole('super_admin') ||
             $user->hasRole('manager'));
    }

    public function convert(User $user): bool
    {
        return $user->hasPermissionTo('prospects.convert');
    }
}
