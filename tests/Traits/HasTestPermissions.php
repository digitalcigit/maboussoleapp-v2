<?php

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

trait HasTestPermissions
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crée un utilisateur de test
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Authentifie l'utilisateur
        $this->actingAs($this->user);

        // Configure les permissions
        $this->setupPermissions();
    }

    protected function setupPermissions(): void
    {
        // Supprime toutes les permissions existantes
        Role::query()->delete();
        Permission::query()->delete();

        // Crée les permissions
        $permissions = [
            // Permissions générales Filament
            'access_filament',
            'access_admin_panel',
            
            // Permissions pour les activités
            'activities.view',
            'activities.create',
            'activities.update',
            'activities.delete',
            'activities.view_any',
            'activities.edit',
            
            // Permissions pour les prospects
            'prospects.view',
            'prospects.create',
            'prospects.update',
            'prospects.delete',
            'prospects.view_any',
            'prospects.edit',
            
            // Permissions pour les clients
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',
            'clients.view_any',
            'clients.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crée le rôle admin et assigne toutes les permissions
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo($permissions);

        // Crée un rôle avec toutes les permissions
        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo($permissions);

        // Assigne le rôle à l'utilisateur de test
        $this->user->assignRole('super-admin');
    }
}
