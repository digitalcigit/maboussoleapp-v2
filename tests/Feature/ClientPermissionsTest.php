<?php

namespace Tests\Feature;

use App\Filament\Resources\ClientResource;
use App\Models\Activity;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur admin
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');
        
        // Créer un utilisateur normal
        $this->user = User::factory()->create();
        $this->user->assignRole('conseiller');
        
        // Créer un client pour les tests
        $this->client = Client::factory()->create([
            'created_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function admin_can_view_client_activities()
    {
        $activity = Activity::factory()->create([
            'client_id' => $this->client->id,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(ClientResource\RelationManagers\ActivitiesRelationManager::class, [
            'ownerRecord' => $this->client,
        ])
            ->assertSuccessful();

        $this->assertTrue($this->admin->can('clients.activities.view'));
    }

    /** @test */
    public function user_can_view_client_activities_with_permission()
    {
        $activity = Activity::factory()->create([
            'client_id' => $this->client->id,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(ClientResource\RelationManagers\ActivitiesRelationManager::class, [
            'ownerRecord' => $this->client,
        ])
            ->assertSuccessful();

        $this->assertTrue($this->user->can('clients.activities.view'));
    }
}
