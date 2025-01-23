<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DossierPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dossiers.view',
            'dossiers.create',
            'dossiers.edit',
            'dossiers.delete',
            'dossiers.assign',
            'dossiers.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Assigner les permissions aux rôles
        $superAdmin = Role::findOrCreate('super-admin');
        $manager = Role::findOrCreate('manager');
        $conseiller = Role::findOrCreate('conseiller');

        // Super Admin obtient toutes les permissions
        $superAdmin->givePermissionTo($permissions);

        // Manager obtient toutes les permissions sauf la suppression
        $manager->givePermissionTo([
            'dossiers.view',
            'dossiers.create',
            'dossiers.edit',
            'dossiers.assign',
            'dossiers.manage',
        ]);

        // Conseiller obtient les permissions de base
        $conseiller->givePermissionTo([
            'dossiers.view',
            'dossiers.create',
            'dossiers.edit',
        ]);
    }
}
