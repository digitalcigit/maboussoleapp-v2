<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Création des permissions système
        $systemPermissions = [
            'system.settings.view',
            'system.settings.edit',
            'system.logs.view',
            'access_filament',
            'access_admin_panel',
            'view_admin_panel',
        ];

        // Permissions utilisateurs
        $userPermissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.view_any',
            'users.update',
        ];

        // Permissions rôles
        $rolePermissions = [
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'roles.view_any',
            'roles.update',
        ];

        // Permissions prospects
        $prospectPermissions = [
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'prospects.delete',
            'prospects.convert',
            'prospects.assign',
            'prospects.view_any',
            'prospects.update',
        ];

        // Permissions clients
        $clientPermissions = [
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.view_any',
            'clients.update',
        ];

        // Permissions activités
        $activityPermissions = [
            'activities.view',
            'activities.create',
            'activities.edit',
            'activities.delete',
            'activities.view_any',
            'activities.update',
        ];

        // Permissions documents
        $documentPermissions = [
            'documents.view',
            'documents.upload',
            'documents.validate',
            'documents.view.own',
        ];

        // Permissions communications
        $communicationPermissions = [
            'communications.email',
            'communications.sms',
        ];

        // Permissions rapports
        $reportPermissions = [
            'reports.view',
            'reports.export',
            'reports.view.own',
            'reports.view.own.basic',
        ];

        // Permissions paramètres
        $settingsPermissions = [
            'settings.view',
            'settings.edit.department',
        ];

        // Permissions validation
        $validationPermissions = [
            'steps.validate',
        ];

        // Permissions bonus
        $bonusPermissions = [
            'bonus.view.own',
        ];

        // Création des permissions dans la base de données
        $allPermissions = array_unique(array_merge(
            $systemPermissions,
            $userPermissions,
            $rolePermissions,
            $prospectPermissions,
            $clientPermissions,
            $activityPermissions,
            $documentPermissions,
            $communicationPermissions,
            $reportPermissions,
            $settingsPermissions,
            $validationPermissions,
            $bonusPermissions
        ));

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create the Super Admin role first
        $superAdmin = Role::create(['name' => 'super-admin']);

        // Give all permissions to super-admin
        $superAdmin->givePermissionTo(Permission::all());

        // Manager
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            // Permissions système
            'access_filament',
            'access_admin_panel',
            'view_admin_panel',

            // Permissions utilisateurs
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Permissions prospects
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'prospects.delete',
            'prospects.assign',
            'prospects.convert',
            'prospects.view_any',
            'prospects.update',

            // Permissions clients
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.view_any',
            'clients.update',

            // Permissions activités
            'activities.view',
            'activities.create',
            'activities.edit',
            'activities.delete',
            'activities.view_any',
            'activities.update',

            // Autres permissions
            'reports.view',
            'reports.export',
            'documents.validate',
            'steps.validate',
            'settings.view',
            'settings.edit.department',
        ]);

        // Conseiller
        $conseiller = Role::create(['name' => 'conseiller']);
        $conseiller->givePermissionTo([
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'clients.view',
            'clients.create',
            'clients.edit',
            'activities.view',
            'activities.create',
            'activities.edit',
            'documents.view',
            'documents.upload',
            'documents.validate',
            'communications.email',
            'communications.sms',
            'reports.view.own',
        ]);

        // Partenaire
        $partenaire = Role::create(['name' => 'partenaire']);
        $partenaire->givePermissionTo([
            'prospects.create',
            'prospects.view',
            'prospects.edit',
            'documents.upload',
            'documents.view',
            'reports.view.own',
        ]);

        // Commercial
        $commercial = Role::create(['name' => 'commercial']);
        $commercial->givePermissionTo([
            'prospects.create',
            'prospects.view',
            'activities.view',
            'activities.create',
            'bonus.view.own',
            'reports.view.own.basic',
        ]);

        // Création d'un super admin par défaut
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@maboussole.ci',
            'password' => Hash::make('password'),
        ]);

        // Assign the super-admin role
        $superAdminUser->assignRole('super-admin');
    }
}
