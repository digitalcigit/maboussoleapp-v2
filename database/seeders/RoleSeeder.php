<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Supprimer toutes les permissions existantes
        Permission::query()->delete();

        // Création des rôles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $conseiller = Role::firstOrCreate(['name' => 'conseiller']);
        $commercial = Role::firstOrCreate(['name' => 'commercial']);
        $partenaire = Role::firstOrCreate(['name' => 'partenaire']);

        // Création des permissions
        $permissions = [
            // Permission d'accès au panel admin
            'view_admin_panel' => 'Accéder au panel d\'administration',

            // Permissions Clients
            'clients.view' => 'Voir les clients',
            'clients.create' => 'Créer des clients',
            'clients.edit' => 'Modifier les clients',
            'clients.delete' => 'Supprimer les clients',
            'clients.activities.view' => 'Voir les activités des clients',
            'clients.activities.create' => 'Créer des activités pour les clients',

            // Permissions Prospects
            'create_prospects' => 'Créer des prospects',
            'edit_prospects' => 'Modifier les prospects',
            'delete_prospects' => 'Supprimer les prospects',
            'prospects.activities.view' => 'Voir les activités des prospects',
            'prospects.activities.create' => 'Créer des activités pour les prospects',
            'prospects.convert' => 'Convertir les prospects en clients',
            'prospects.view' => 'Voir les prospects',

            // Permissions Activités
            'activities.view' => 'Voir toutes les activités',
            'activities.create' => 'Créer des activités',
            'activities.edit' => 'Modifier les activités',
            'activities.delete' => 'Supprimer les activités',

            // Permissions Utilisateurs
            'users.view' => 'Voir les utilisateurs',
            'users.create' => 'Créer des utilisateurs',
            'users.edit' => 'Modifier les utilisateurs',
            'users.delete' => 'Supprimer les utilisateurs',

            // Permissions Rapports
            'reports.view' => 'Voir les rapports',
            'reports.export' => 'Exporter les rapports',

            // Permissions Dossiers
            'dossiers.view' => 'Voir les dossiers',
            'dossiers.create' => 'Créer des dossiers',
            'dossiers.edit' => 'Modifier les dossiers',
            'dossiers.delete' => 'Supprimer les dossiers',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'description' => $description,
            ]);
        }

        // Donner toutes les permissions au super-admin
        $superAdmin->syncPermissions(Permission::all());

        // Synchronisation des permissions pour chaque rôle
        $manager->syncPermissions([
            'view_admin_panel',
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'clients.activities.view', 'clients.activities.create',
            'create_prospects', 'edit_prospects', 'delete_prospects',
            'prospects.activities.view', 'prospects.activities.create',
            'activities.view', 'activities.create', 'activities.edit', 'activities.delete',
            'users.view', 'reports.view', 'reports.export',
            'dossiers.view', 'dossiers.create', 'dossiers.edit', 'dossiers.delete',
        ]);

        $conseiller->syncPermissions([
            'view_admin_panel',
            'clients.view', 'clients.activities.view', 'clients.activities.create',
            'create_prospects', 'edit_prospects',
            'prospects.activities.view', 'prospects.activities.create',
            'activities.view', 'activities.create', 'activities.edit',
            'dossiers.view', 'dossiers.create', 'dossiers.edit',
        ]);

        $commercial->syncPermissions([
            'view_admin_panel',
            'create_prospects', 'edit_prospects',
            'prospects.activities.view', 'prospects.activities.create',
            'activities.view', 'activities.create',
        ]);

        $partenaire->syncPermissions([
            'view_admin_panel',
            'create_prospects',
            'prospects.activities.view', 'prospects.activities.create',
            'activities.view', 'activities.create',
        ]);
    }
}
