<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RBACTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un utilisateur peut être assigné à un rôle
     */
    public function test_user_can_be_assigned_role(): void
    {
        // Arrange
        $user = User::factory()->create();
        $role = Role::create(['name' => 'manager']);

        // Act
        $user->assignRole($role);

        // Assert
        $this->assertTrue($user->hasRole('manager'));
    }

    /**
     * Test qu'un rôle peut recevoir une permission
     */
    public function test_role_can_be_assigned_permission(): void
    {
        // Arrange
        $role = Role::create(['name' => 'manager']);
        $permission = Permission::create(['name' => 'prospects.view']);

        // Act
        $role->givePermissionTo($permission);

        // Assert
        $this->assertTrue($role->hasPermissionTo('prospects.view'));
    }

    /**
     * Test qu'un utilisateur avec un rôle a les bonnes permissions
     */
    public function test_user_with_role_has_correct_permissions(): void
    {
        // Arrange
        $user = User::factory()->create();
        $role = Role::create(['name' => 'manager']);
        $permission = Permission::create(['name' => 'prospects.view']);
        
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        // Assert
        $this->assertTrue($user->can('prospects.view'));
    }
}
