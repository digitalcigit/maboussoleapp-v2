<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions système
        Permission::create(['name' => 'view_admin_panel', 'description' => 'Accéder au panneau d\'administration']);
        Permission::create(['name' => 'manage_settings', 'description' => 'Gérer les paramètres du système']);
        Permission::create(['name' => 'view_audit_logs', 'description' => 'Voir les logs d\'audit']);

        // Créer les permissions utilisateurs
        Permission::create(['name' => 'create_users', 'description' => 'Créer des utilisateurs']);
        Permission::create(['name' => 'edit_users', 'description' => 'Modifier des utilisateurs']);
        Permission::create(['name' => 'delete_users', 'description' => 'Supprimer des utilisateurs']);
        Permission::create(['name' => 'assign_roles', 'description' => 'Assigner des rôles']);

        // Créer les permissions prospects
        Permission::create(['name' => 'create_prospects', 'description' => 'Créer des prospects']);
        Permission::create(['name' => 'edit_prospects', 'description' => 'Modifier des prospects']);
        Permission::create(['name' => 'delete_prospects', 'description' => 'Supprimer des prospects']);
        Permission::create(['name' => 'assign_prospects', 'description' => 'Assigner des prospects']);

        // Créer les permissions clients
        Permission::create(['name' => 'create_clients', 'description' => 'Créer des clients']);
        Permission::create(['name' => 'edit_clients', 'description' => 'Modifier des clients']);
        Permission::create(['name' => 'delete_clients', 'description' => 'Supprimer des clients']);
        Permission::create(['name' => 'manage_documents', 'description' => 'Gérer les documents']);

        // Créer les rôles
        $superAdmin = Role::create(['name' => 'super-admin']);
        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $conseiller = Role::create(['name' => 'conseiller']);
        $assistant = Role::create(['name' => 'assistant']);

        // Super Admin a toutes les permissions
        $superAdmin->givePermissionTo(Permission::all());

        // Admin a toutes les permissions sauf la gestion des paramètres système
        $admin->givePermissionTo(Permission::whereNotIn('name', ['manage_settings'])->get());

        // Manager peut gérer les prospects, clients et voir les logs
        $manager->givePermissionTo([
            'view_admin_panel',
            'view_audit_logs',
            'create_prospects',
            'edit_prospects',
            'assign_prospects',
            'create_clients',
            'edit_clients',
            'manage_documents',
        ]);

        // Conseiller peut gérer ses prospects et clients assignés
        $conseiller->givePermissionTo([
            'view_admin_panel',
            'create_prospects',
            'edit_prospects',
            'create_clients',
            'edit_clients',
            'manage_documents',
        ]);

        // Assistant peut voir le panel et gérer les documents
        $assistant->givePermissionTo([
            'view_admin_panel',
            'manage_documents',
        ]);
    }
}
