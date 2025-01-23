<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminPanelPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les permissions nécessaires
        $permissions = [
            'view_admin_panel' => 'Permet d\'accéder au panneau d\'administration',
            'create_prospects' => 'Permet de créer des prospects',
            'view_prospects' => 'Permet de voir les prospects',
            'edit_prospects' => 'Permet de modifier les prospects',
            'delete_prospects' => 'Permet de supprimer les prospects',
            'list_prospects' => 'Permet de lister les prospects'
        ];

        foreach ($permissions as $permission => $description) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
                'description' => $description
            ]);
        }

        // Attribuer les permissions aux rôles existants
        $roles = [
            'super-admin' => $permissions,
            'admin' => $permissions,
            'conseiller' => [
                'view_admin_panel',
                'create_prospects',
                'view_prospects',
                'edit_prospects',
                'list_prospects'
            ]
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo(array_keys($rolePermissions));
            }
        }
    }
}
