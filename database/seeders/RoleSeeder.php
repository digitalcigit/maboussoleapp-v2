<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Création des rôles
        $superAdmin = Role::create(['name' => 'super-admin']);
        $manager = Role::create(['name' => 'manager']);
        $conseiller = Role::create(['name' => 'conseiller']);
        $commercial = Role::create(['name' => 'commercial']);
        $partenaire = Role::create(['name' => 'partenaire']);

        // Création des permissions
        $permissions = [
            // Permissions Clients
            'clients.view' => 'Voir les clients',
            'clients.create' => 'Créer des clients',
            'clients.edit' => 'Modifier les clients',
            'clients.delete' => 'Supprimer les clients',
            'clients.activities.view' => 'Voir les activités des clients',
            'clients.activities.create' => 'Créer des activités pour les clients',
            
            // Permissions Prospects
            'prospects.view' => 'Voir les prospects',
            'prospects.create' => 'Créer des prospects',
            'prospects.edit' => 'Modifier les prospects',
            'prospects.delete' => 'Supprimer les prospects',
            'prospects.convert' => 'Convertir les prospects en clients',
            'prospects.activities.view' => 'Voir les activités des prospects',
            'prospects.activities.create' => 'Créer des activités pour les prospects',
            
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
            'reports.export' => 'Exporter les rapports'
        ];

        foreach ($permissions as $name => $description) {
            Permission::create([
                'name' => $name,
                'description' => $description
            ]);
        }

        // Attribution des permissions aux rôles
        $manager->givePermissionTo([
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'clients.activities.view', 'clients.activities.create',
            'prospects.view', 'prospects.create', 'prospects.edit', 'prospects.delete',
            'prospects.convert', 'prospects.activities.view', 'prospects.activities.create',
            'activities.view', 'activities.create', 'activities.edit', 'activities.delete',
            'users.view', 'reports.view', 'reports.export'
        ]);

        $conseiller->givePermissionTo([
            'clients.view', 'clients.activities.view', 'clients.activities.create',
            'prospects.view', 'prospects.activities.view', 'prospects.activities.create',
            'activities.view', 'activities.create', 'activities.edit'
        ]);

        $commercial->givePermissionTo([
            'prospects.view', 'prospects.create', 'prospects.edit',
            'prospects.activities.view', 'prospects.activities.create',
            'activities.view', 'activities.create'
        ]);

        $partenaire->givePermissionTo([
            'prospects.view', 'prospects.create',
            'prospects.activities.view', 'prospects.activities.create',
            'activities.view', 'activities.create'
        ]);
    }
}
