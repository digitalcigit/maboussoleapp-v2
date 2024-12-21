<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Permissions for prospects
        $prospectPermissions = [
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'prospects.delete',
            'prospects.view_any',
            'prospects.update',
            'prospects.convert',
            'prospects.assign',
            'manage prospects',
            'manage activities'
        ];

        foreach ($prospectPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Assign all permissions to admin role
        $adminRole->givePermissionTo($prospectPermissions);
    }
}
