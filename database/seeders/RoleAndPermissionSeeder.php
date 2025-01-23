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
        Permission::findOrCreate('system.settings.view');
        Permission::findOrCreate('system.settings.edit');
        Permission::findOrCreate('system.logs.view');
        Permission::findOrCreate('admin.panel.access');

        // Permissions utilisateurs
        Permission::findOrCreate('users.view');
        Permission::findOrCreate('users.create');
        Permission::findOrCreate('users.edit');
        Permission::findOrCreate('users.delete');
        Permission::findOrCreate('roles.view');
        Permission::findOrCreate('roles.manage');

        // Permissions prospects
        Permission::findOrCreate('prospects.view');
        Permission::findOrCreate('prospects.view.own');
        Permission::findOrCreate('prospects.create');
        Permission::findOrCreate('prospects.edit');
        Permission::findOrCreate('prospects.edit.own');
        Permission::findOrCreate('prospects.delete.own');

        // Permissions clients
        Permission::findOrCreate('clients.view');
        Permission::findOrCreate('clients.create');
        Permission::findOrCreate('clients.edit');
        Permission::findOrCreate('clients.edit.own');

        // Permissions documents
        Permission::findOrCreate('documents.view');
        Permission::findOrCreate('documents.view.own');
        Permission::findOrCreate('documents.upload');
        Permission::findOrCreate('documents.validate');

        // Permissions rapports
        Permission::findOrCreate('reports.view');
        Permission::findOrCreate('reports.view.own');
        Permission::findOrCreate('reports.view.own.basic');
        Permission::findOrCreate('reports.export');

        // Permissions communications
        Permission::findOrCreate('communications.email');
        Permission::findOrCreate('communications.sms');

        // Permissions activités
        Permission::findOrCreate('activities.view');
        Permission::findOrCreate('activities.create');
        Permission::findOrCreate('activities.edit');
        Permission::findOrCreate('activities.delete');

        // Permissions validation
        Permission::findOrCreate('steps.validate');
        Permission::findOrCreate('settings.view');
        Permission::findOrCreate('settings.edit.department');
        Permission::findOrCreate('bonus.view.own');

        // Création des rôles si non existants
        $superAdmin = Role::findOrCreate('super-admin');
        $manager = Role::findOrCreate('manager');
        $conseiller = Role::findOrCreate('conseiller');
        $partenaire = Role::findOrCreate('partenaire');
        $commercial = Role::findOrCreate('commercial');

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
            'activities.view',
            'activities.create',
            'activities.edit',
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
            'activities.view',
            'activities.create',
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
