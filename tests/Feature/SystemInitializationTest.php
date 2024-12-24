<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemInitializationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the roles and permissions before each test
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function system_redirects_to_initialization_when_no_users_exist()
    {
        $response = $this->get('/admin');
        
        $response->assertRedirect('/system/initialization');
    }

    /** @test */
    public function super_admin_can_be_created_during_initialization()
    {
        $userData = [
            'name' => 'Super Admin',
            'email' => 'admin@maboussole.fr',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/system/initialization', $userData);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@maboussole.fr',
            'name' => 'Super Admin'
        ]);

        $user = User::where('email', 'admin@maboussole.fr')->first();
        $this->assertTrue($user->hasRole('super-admin'));
        
        $response->assertRedirect('/admin');
    }

    /** @test */
    public function initialization_is_not_accessible_when_system_is_already_initialized()
    {
        // Créer un super admin
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        // Essayer d'accéder à la page d'initialisation
        $response = $this->get('/system/initialization');
        
        $response->assertRedirect('/admin');
    }

    /** @test */
    public function initialization_requires_valid_data()
    {
        $invalidData = [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different'
        ];

        $response = $this->post('/system/initialization', $invalidData);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function initialization_creates_necessary_roles_and_permissions()
    {
        $userData = [
            'name' => 'Super Admin',
            'email' => 'admin@maboussole.fr',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $this->post('/system/initialization', $userData);

        // Vérifier que les rôles existent
        $this->assertDatabaseHas('roles', ['name' => 'super-admin']);
        $this->assertDatabaseHas('roles', ['name' => 'manager']);
        $this->assertDatabaseHas('roles', ['name' => 'conseiller']);
    }
}
