<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientPermissionsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
            ->assertSuccessful();

        $this->assertTrue($this->admin->can('clients.activities.view'));
    }

    /** @test */
    public function user_can_view_client_activities_with_permission()
    {
        $activity = Activity::factory()->forClient($this->client)->create([
            'user_id' => $this->user->id,
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
