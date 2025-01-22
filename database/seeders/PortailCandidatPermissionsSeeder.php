<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PortailCandidatPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions pour le portail candidat
        $portailPermissions = [
            // Permissions de base
            'portail.access',
            'portail.view_dashboard',
            
            // Permissions dossier
            'dossier.view.own',
            'dossier.update.own',
            
            // Permissions documents
            'documents.view.own',
            'documents.upload.own',
            'documents.update.own',
            
            // Permissions profil
            'profile.view.own',
            'profile.update.own',
            
            // Permissions notifications
            'notifications.view.own',
            'notifications.mark_read.own',
        ];

        // Création des permissions
        foreach ($portailPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Création du rôle portail_candidat
        $role = Role::findOrCreate('portail_candidat');

        // Attribution des permissions au rôle
        $role->givePermissionTo($portailPermissions);

        // Log pour confirmer la création
        \Illuminate\Support\Facades\Log::info('Rôle et permissions du portail candidat créés avec succès');
    }
}
