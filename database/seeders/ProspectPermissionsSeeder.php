<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProspectPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions pour les prospects
        $prospectPermissions = [
            // Permissions de base
            'prospect.access',
            'prospect.view_dashboard',
            
            // Permissions profil
            'prospect.profile.view.own',
            'prospect.profile.update.own',
            
            // Permissions documents
            'prospect.documents.view.own',
            'prospect.documents.upload.own',
            
            // Permissions demandes
            'prospect.requests.create',
            'prospect.requests.view.own',
            'prospect.requests.update.own',
            
            // Permissions messages
            'prospect.messages.view.own',
            'prospect.messages.send',
            
            // Permissions notifications
            'prospect.notifications.view.own',
            'prospect.notifications.mark_read.own',
        ];

        // Création des permissions
        foreach ($prospectPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Création du rôle prospect
        $role = Role::findOrCreate('prospect');

        // Attribution des permissions au rôle
        $role->givePermissionTo($prospectPermissions);
    }
}
