<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Filament\Events\Auth\Login;
use Filament\Events\Auth\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\FilamentTestHelpers;

class AuthenticationWorkflowTest extends TestCase
{
    use RefreshDatabase;
    use FilamentTestHelpers;

    private User $superAdmin;
    private User $manager;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Création des utilisateurs avec leurs rôles respectifs
        $this->superAdmin = $this->createSuperAdmin();
        $this->manager = $this->createManager();
        $this->user = $this->createStandardUser();
    }

    /** @test */
    public function test_complete_login_workflow()
    {
        Event::fake([Login::class]);

        // 1. Tentative de connexion avec des informations invalides
        $this->post('/admin/login', [
            'email' => 'super_admin@maboussole.ci',
            'password' => 'wrong_password',
            'remember' => false,
        ]);
        
        $this->assertGuest();

        // 2. Connexion réussie
        $response = $this->attemptFilamentLogin([
            'email' => 'super_admin@maboussole.ci',
            'password' => 'password',
        ]);

        Event::assertDispatched(Login::class);
        $this->assertAuthenticated();
    }

    /** @test */
    public function test_role_based_access_control()
    {
        // Test avec super admin (devrait avoir accès à tout)
        $this->assertHasAccessToFilamentPanel($this->superAdmin);
        $this->assertHasAccessToFilamentResource($this->superAdmin, 'users');
        $this->assertHasAccessToFilamentResource($this->superAdmin, 'prospects');
        $this->assertHasAccessToFilamentResource($this->superAdmin, 'clients');
        $this->assertHasAccessToFilamentResource($this->superAdmin, 'activities');

        // Test avec manager (accès limité)
        $this->assertHasAccessToFilamentPanel($this->manager);
        $this->assertHasAccessToFilamentResource($this->manager, 'prospects');
        $this->assertHasAccessToFilamentResource($this->manager, 'clients');
        $this->assertNoAccessToFilamentResource($this->manager, 'users');

        // Test avec utilisateur standard (accès très limité)
        $this->assertHasAccessToFilamentPanel($this->user);
        $this->assertHasAccessToFilamentResource($this->user, 'prospects');
        $this->assertNoAccessToFilamentResource($this->user, 'users');
    }

    /** @test */
    public function test_logout_workflow()
    {
        Event::fake([Logout::class]);

        $this->actingAs($this->superAdmin);
        $this->assertAuthenticated();

        $this->post('/admin/logout');
        
        Event::assertDispatched(Logout::class);
        $this->assertGuest();
    }

    /** @test */
    public function test_remember_me_functionality()
    {
        $response = $this->attemptFilamentLogin([
            'email' => 'super_admin@maboussole.ci',
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
    }

    /** @test */
    public function test_permission_inheritance()
    {
        // Test des permissions du manager
        $this->assertTrue($this->manager->can('prospects.view'));
        $this->assertTrue($this->manager->can('prospects.create'));
        $this->assertFalse($this->manager->can('users.view'));
        
        // Test des permissions de l'utilisateur standard
        $this->assertTrue($this->user->can('prospects.view'));
        $this->assertTrue($this->user->can('activities.view'));
        $this->assertFalse($this->user->can('prospects.edit'));
    }
}
