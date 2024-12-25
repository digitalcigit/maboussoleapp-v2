<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login()
    {
        // Créer un utilisateur admin
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@maboussole.ci',
            'password' => Hash::make('password'),
        ]);

        // Tenter de se connecter
        $response = $this->post('/admin/login', [
            'email' => 'admin@maboussole.ci',
            'password' => 'password',
        ]);

        // Vérifier que l'utilisateur est authentifié
        $this->assertAuthenticated();
    }
}
