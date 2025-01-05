<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions système
        Permission::create(['name' => 'system.settings.view']);
        Permission::create(['name' => 'system.settings.edit']);
        Permission::create(['name' => 'system.logs.view']);

        // Permissions utilisateurs
        Permission::create(['name' => 'users.view']);
        Permission::create(['name' => 'users.create']);
        Permission::create(['name' => 'users.edit']);
        Permission::create(['name' => 'users.delete']);
        Permission::create(['name' => 'roles.view']);
        Permission::create(['name' => 'roles.manage']);

        // Permissions prospects
        Permission::create(['name' => 'prospects.view']);
        Permission::create(['name' => 'prospects.view.own']);
        Permission::create(['name' => 'prospects.create']);
        Permission::create(['name' => 'prospects.edit']);
        Permission::create(['name' => 'prospects.edit.own']);
        Permission::create(['name' => 'prospects.delete.own']);

        // Permissions clients
        Permission::create(['name' => 'clients.view']);
        Permission::create(['name' => 'clients.create']);
        Permission::create(['name' => 'clients.edit']);
        Permission::create(['name' => 'clients.edit.own']);

        // Permissions documents
        Permission::create(['name' => 'documents.view']);
        Permission::create(['name' => 'documents.view.own']);
        Permission::create(['name' => 'documents.upload']);
        Permission::create(['name' => 'documents.validate']);

        // Permissions rapports
        Permission::create(['name' => 'reports.view']);
        Permission::create(['name' => 'reports.view.own']);
        Permission::create(['name' => 'reports.view.own.basic']);
        Permission::create(['name' => 'reports.export']);

        // Permissions communications
        Permission::create(['name' => 'communications.email']);
        Permission::create(['name' => 'communications.sms']);

        // Permissions validation
        Permission::create(['name' => 'steps.validate']);
        Permission::create(['name' => 'settings.view']);
        Permission::create(['name' => 'settings.edit.department']);
        Permission::create(['name' => 'bonus.view.own']);

        // Création des rôles
        $superAdmin = Role::create(['name' => 'super-admin']);
        $manager = Role::create(['name' => 'manager']);
        $conseiller = Role::create(['name' => 'conseiller']);
        $partenaire = Role::create(['name' => 'partenaire']);
        $commercial = Role::create(['name' => 'commercial']);

        // Super Admin - tous les droits
        $superAdmin->givePermissionTo(Permission::all());

        // Manager
        $manager->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'clients.view',
            'clients.create',
            'clients.edit',
            'reports.view',
            'reports.export',
            'documents.validate',
            'steps.validate',
            'settings.view',
            'settings.edit.department'
        ]);

        // Conseiller
        $conseiller->givePermissionTo([
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'prospects.delete.own',
            'clients.view',
            'clients.create',
            'clients.edit.own',
            'documents.view',
            'documents.upload',
            'documents.validate',
            'communications.email',
            'communications.sms',
            'reports.view.own'
        ]);

        // Partenaire
        $partenaire->givePermissionTo([
            'prospects.create',
            'prospects.view.own',
            'prospects.edit.own',
            'documents.upload',
            'documents.view.own',
            'reports.view.own'
        ]);

        // Commercial
        $commercial->givePermissionTo([
            'prospects.create',
            'prospects.view.own',
            'bonus.view.own',
            'reports.view.own.basic'
        ]);
    }
}
