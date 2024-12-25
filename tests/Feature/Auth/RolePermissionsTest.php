<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /**
     * Test des permissions du Super Admin
     */
    public function test_super_admin_has_all_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $allPermissions = Permission::all();
        
        foreach ($allPermissions as $permission) {
            $this->assertTrue(
                $user->can($permission->name),
                "Super Admin devrait avoir la permission: {$permission->name}"
            );
        }
    }

    /**
     * Test des permissions du Manager
     */
    public function test_manager_has_correct_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        // Permissions que le manager devrait avoir
        $managerPermissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'prospects.view', 'prospects.create', 'prospects.edit', 'prospects.delete', 'prospects.assign', 'prospects.convert',
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'activities.view', 'activities.create', 'activities.edit', 'activities.delete',
            'reports.view', 'reports.export',
            'documents.validate', 'steps.validate',
            'settings.view', 'settings.edit.department',
        ];

        // Vérifie que le manager a toutes les permissions attendues
        foreach ($managerPermissions as $permission) {
            $this->assertTrue(
                $user->can($permission),
                "Manager devrait avoir la permission: {$permission}"
            );
        }

        // Vérifie que le manager n'a pas certaines permissions réservées
        $restrictedPermissions = [
            'system.settings.view',
            'system.logs.view',
            'bonus.view.own',
        ];

        foreach ($restrictedPermissions as $permission) {
            $this->assertFalse(
                $user->can($permission),
                "Manager ne devrait pas avoir la permission: {$permission}"
            );
        }
    }

    /**
     * Test des permissions du Conseiller
     */
    public function test_conseiller_has_correct_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('conseiller');

        // Permissions que le conseiller devrait avoir
        $conseillerPermissions = [
            'prospects.view', 'prospects.create', 'prospects.edit',
            'clients.view', 'clients.create', 'clients.edit',
            'activities.view', 'activities.create', 'activities.edit',
            'documents.view', 'documents.upload', 'documents.validate',
            'communications.email', 'communications.sms',
            'reports.view.own',
        ];

        foreach ($conseillerPermissions as $permission) {
            $this->assertTrue(
                $user->can($permission),
                "Conseiller devrait avoir la permission: {$permission}"
            );
        }

        // Permissions que le conseiller ne devrait pas avoir
        $restrictedPermissions = [
            'prospects.delete',
            'clients.delete',
            'activities.delete',
            'users.view',
            'settings.view',
        ];

        foreach ($restrictedPermissions as $permission) {
            $this->assertFalse(
                $user->can($permission),
                "Conseiller ne devrait pas avoir la permission: {$permission}"
            );
        }
    }

    /**
     * Test des permissions du Partenaire
     */
    public function test_partenaire_has_correct_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('partenaire');

        // Permissions que le partenaire devrait avoir
        $partenairePermissions = [
            'prospects.create', 'prospects.view', 'prospects.edit',
            'documents.upload', 'documents.view',
            'reports.view.own',
        ];

        foreach ($partenairePermissions as $permission) {
            $this->assertTrue(
                $user->can($permission),
                "Partenaire devrait avoir la permission: {$permission}"
            );
        }

        // Permissions que le partenaire ne devrait pas avoir
        $restrictedPermissions = [
            'prospects.delete',
            'clients.view',
            'activities.view',
            'communications.email',
        ];

        foreach ($restrictedPermissions as $permission) {
            $this->assertFalse(
                $user->can($permission),
                "Partenaire ne devrait pas avoir la permission: {$permission}"
            );
        }
    }

    /**
     * Test des permissions du Commercial
     */
    public function test_commercial_has_correct_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('commercial');

        // Permissions que le commercial devrait avoir
        $commercialPermissions = [
            'prospects.create', 'prospects.view',
            'activities.view', 'activities.create',
            'bonus.view.own',
            'reports.view.own.basic',
        ];

        foreach ($commercialPermissions as $permission) {
            $this->assertTrue(
                $user->can($permission),
                "Commercial devrait avoir la permission: {$permission}"
            );
        }

        // Permissions que le commercial ne devrait pas avoir
        $restrictedPermissions = [
            'prospects.edit',
            'prospects.delete',
            'clients.view',
            'documents.upload',
            'communications.email',
        ];

        foreach ($restrictedPermissions as $permission) {
            $this->assertFalse(
                $user->can($permission),
                "Commercial ne devrait pas avoir la permission: {$permission}"
            );
        }
    }
}
