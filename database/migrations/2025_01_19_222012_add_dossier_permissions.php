<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'view_dossiers',
            'create_dossiers',
            'edit_dossiers',
            'delete_dossiers',
            'assign_dossiers',
            'manage_dossiers',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assigner les permissions aux rôles existants
        $superAdmin = Role::findByName('super-admin', 'web');
        $manager = Role::findByName('manager', 'web');
        $conseiller = Role::findByName('conseiller', 'web');

        // Super Admin obtient toutes les permissions
        $superAdmin->givePermissionTo($permissions);

        // Manager obtient toutes les permissions sauf la suppression
        $manager->givePermissionTo([
            'view_dossiers',
            'create_dossiers',
            'edit_dossiers',
            'assign_dossiers',
            'manage_dossiers',
        ]);

        // Conseiller obtient les permissions de base
        $conseiller->givePermissionTo([
            'view_dossiers',
            'create_dossiers',
            'edit_dossiers',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'view_dossiers',
            'create_dossiers',
            'edit_dossiers',
            'delete_dossiers',
            'assign_dossiers',
            'manage_dossiers',
        ];

        foreach ($permissions as $permission) {
            Permission::findByName($permission, 'web')->delete();
        }
    }
};
