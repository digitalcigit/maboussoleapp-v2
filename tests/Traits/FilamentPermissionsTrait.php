<?php

namespace Tests\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait FilamentPermissionsTrait
{
    /**
     * Permissions de base requises pour Filament
     */
    protected function getBaseFilamentPermissions(): array
    {
        return [
            'access_filament',
            'access_admin_panel',
            'view_admin_panel',
        ];
    }

    /**
     * Permissions pour les clients
     */
    protected function getClientPermissions(): array
    {
        return [
            'clients.view_any',
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',
            'clients.manage',
        ];
    }

    /**
     * Permissions pour les prospects
     */
    protected function getProspectPermissions(): array
    {
        return [
            'prospects.view_any',
            'prospects.view',
            'prospects.create',
            'prospects.update',
            'prospects.delete',
            'prospects.manage',
        ];
    }

    /**
     * Permissions pour les activités
     */
    protected function getActivityPermissions(): array
    {
        return [
            'activities.view_any',
            'activities.view',
            'activities.create',
            'activities.update',
            'activities.delete',
            'activities.manage',
        ];
    }

    /**
     * Crée toutes les permissions nécessaires
     */
    protected function createAllPermissions(): void
    {
        $allPermissions = array_merge(
            $this->getBaseFilamentPermissions(),
            $this->getClientPermissions(),
            $this->getProspectPermissions(),
            $this->getActivityPermissions()
        );

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'description' => "Permission : $permission",
            ]);
        }
    }

    /**
     * Crée un utilisateur de test avec des permissions spécifiques
     */
    protected function createUserWithPermissions(array $permissions, string $role = null): User
    {
        $this->createAllPermissions();

        $user = User::factory()->create();

        if ($role) {
            $roleModel = Role::firstOrCreate(['name' => $role]);
            $roleModel->syncPermissions($permissions);
            $user->assignRole($roleModel);
        } else {
            $user->syncPermissions($permissions);
        }

        return $user;
    }

    /**
     * Crée un super admin pour les tests
     */
    protected function createSuperAdmin(): User
    {
        $this->createAllPermissions();
        
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'super_admin@maboussole.ci',
            'password' => Hash::make('password'),
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $role->syncPermissions(Permission::all());
        $superAdmin->assignRole($role);

        return $superAdmin;
    }

    /**
     * Crée un manager pour les tests
     */
    protected function createManager(): User
    {
        $permissions = array_merge(
            $this->getBaseFilamentPermissions(),
            $this->getClientPermissions(),
            $this->getProspectPermissions(),
            $this->getActivityPermissions()
        );

        return $this->createUserWithPermissions($permissions, 'manager');
    }

    /**
     * Crée un conseiller pour les tests
     */
    protected function createConseiller(): User
    {
        $permissions = array_merge(
            $this->getBaseFilamentPermissions(),
            [
                'clients.view_any',
                'clients.view',
                'prospects.view_any',
                'prospects.view',
                'activities.view_any',
                'activities.create',
                'activities.view',
            ]
        );

        return $this->createUserWithPermissions($permissions, 'conseiller');
    }

    /**
     * Vérifie si l'utilisateur a les permissions Filament de base
     */
    protected function assertHasBaseFilamentAccess(User $user): void
    {
        foreach ($this->getBaseFilamentPermissions() as $permission) {
            $this->assertTrue(
                $user->hasPermissionTo($permission),
                "L'utilisateur devrait avoir la permission : $permission"
            );
        }
    }
}
