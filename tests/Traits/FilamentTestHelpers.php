<?php

namespace Tests\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait FilamentTestHelpers
{
    /**
     * Crée un utilisateur avec un rôle spécifique pour les tests
     */
    protected function createUserWithRole(string $roleName, array $permissions = []): User
    {
        // Créer ou récupérer le rôle
        $role = Role::firstOrCreate(['name' => $roleName]);

        // Attribuer les permissions au rôle si spécifiées
        if (! empty($permissions)) {
            foreach ($permissions as $permissionName) {
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                $role->givePermissionTo($permission);
            }
        }

        // Créer l'utilisateur
        $user = User::create([
            'name' => ucfirst($roleName),
            'email' => $roleName . '@maboussole.ci',
            'password' => Hash::make('password'),
        ]);

        $user->assignRole($role);

        return $user;
    }

    /**
     * Crée un super admin pour les tests
     */
    protected function createSuperAdmin(): User
    {
        return $this->createUserWithRole('super_admin', [
            'system.settings.view',
            'system.settings.edit',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.edit',
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'prospects.delete',
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'activities.view',
            'activities.create',
            'activities.edit',
            'activities.delete',
            'reports.view',
            'reports.export',
        ]);
    }

    /**
     * Crée un manager pour les tests
     */
    protected function createManager(): User
    {
        return $this->createUserWithRole('manager', [
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'clients.view',
            'clients.create',
            'clients.edit',
            'activities.view',
            'activities.create',
            'reports.view',
        ]);
    }

    /**
     * Crée un utilisateur standard pour les tests
     */
    protected function createStandardUser(): User
    {
        return $this->createUserWithRole('user', [
            'prospects.view',
            'clients.view',
            'activities.view',
            'activities.create',
        ]);
    }

    /**
     * Vérifie si l'utilisateur a accès à une ressource Filament
     */
    protected function assertHasAccessToFilamentResource(User $user, string $resource): void
    {
        $this->actingAs($user)
            ->get("/admin/{$resource}")
            ->assertSuccessful();
    }

    /**
     * Vérifie si l'utilisateur n'a pas accès à une ressource Filament
     */
    protected function assertNoAccessToFilamentResource(User $user, string $resource): void
    {
        $this->actingAs($user)
            ->get("/admin/{$resource}")
            ->assertForbidden();
    }

    /**
     * Vérifie si l'utilisateur a accès au panel Filament
     */
    protected function assertHasAccessToFilamentPanel(User $user): void
    {
        $this->actingAs($user)
            ->get('/admin')
            ->assertSuccessful();
    }

    /**
     * Simule une tentative de connexion Filament
     */
    protected function attemptFilamentLogin(array $credentials): TestResponse
    {
        $response = $this->post('/admin/login', array_merge([
            'remember' => false,
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]));

        return $this->followingRedirects()->get($response->headers->get('Location'));
    }

    /**
     * Simule une déconnexion Filament
     */
    protected function filamentLogout(): TestResponse
    {
        $response = $this->post('/admin/logout');
        $this->app['auth']->logout();

        return $response;
    }
}
