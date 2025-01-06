<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ClientPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Création des permissions pour les clients
        $permissions = [
            'clients.view' => 'Voir les clients',
            'clients.create' => 'Créer des clients',
            'clients.edit' => 'Modifier les clients',
            'clients.delete' => 'Supprimer les clients',
            'manage clients' => 'Gérer les clients (tous les droits)',
        ];

        $createdPermissions = [];
        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['description' => $description]
            );
            $createdPermissions[] = $permission;
        }

        // Attribution des permissions au rôle super-admin
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            foreach ($createdPermissions as $permission) {
                if (!$superAdminRole->hasPermissionTo($permission)) {
                    $superAdminRole->givePermissionTo($permission);
                }
            }
        }
    }
}
