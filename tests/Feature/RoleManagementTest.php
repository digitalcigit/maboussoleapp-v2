<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Exécuter le seeder des rôles et permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    /** @test */
    public function super_admin_can_be_created()
    {
        $user = User::factory()->create();
        $superAdminRole = Role::findByName('super-admin');

        $user->assignRole($superAdminRole);

        $this->assertTrue($user->hasRole('super-admin'));
    }

    /** @test */
    public function super_admin_has_all_permissions()
    {
        $user = User::factory()->create();
        $superAdminRole = Role::findByName('super-admin');
        $user->assignRole($superAdminRole);

        $allPermissions = Permission::all();

        foreach ($allPermissions as $permission) {
            $this->assertTrue($user->hasPermissionTo($permission->name));
        }
    }

    /** @test */
    public function manager_has_correct_permissions()
    {
        $user = User::factory()->create();
        $managerRole = Role::findByName('manager');
        $user->assignRole($managerRole);

        // Vérifier les permissions spécifiques du manager
        $this->assertTrue($user->hasPermissionTo('users.view'));
        $this->assertTrue($user->hasPermissionTo('users.create'));
        $this->assertFalse($user->hasPermissionTo('system.settings.edit'));
    }

    /** @test */
    public function conseiller_has_limited_permissions()
    {
        $user = User::factory()->create();
        $conseillerRole = Role::findByName('conseiller');
        $user->assignRole($conseillerRole);

        // Vérifier les permissions du conseiller
        $this->assertTrue($user->hasPermissionTo('prospects.view'));
        $this->assertTrue($user->hasPermissionTo('prospects.create'));
        $this->assertFalse($user->hasPermissionTo('users.create'));
    }

    /** @test */
    public function user_can_have_multiple_roles()
    {
        $user = User::factory()->create();

        $user->assignRole('manager', 'conseiller');

        $this->assertTrue($user->hasRole('manager'));
        $this->assertTrue($user->hasRole('conseiller'));
    }

    /** @test */
    public function unauthorized_user_cannot_access_protected_route()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/admin/settings');

        $response->assertStatus(403);
    }

    /** @test */
    public function role_can_be_removed_from_user()
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $this->assertTrue($user->hasRole('manager'));

        $user->removeRole('manager');

        $this->assertFalse($user->hasRole('manager'));
    }
}
