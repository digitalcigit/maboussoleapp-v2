<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ProspectRoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Création des permissions pour les prospects
        $prospectPermissions = [
            'view_prospect_panel',
            'view_own_prospect_data',
            'edit_own_prospect_data',
            'upload_prospect_documents'
        ];

        foreach ($prospectPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Création ou récupération du rôle prospect
        $prospectRole = Role::findOrCreate('prospect');

        // Attribution des permissions au rôle prospect
        $prospectRole->givePermissionTo($prospectPermissions);
    }
}
