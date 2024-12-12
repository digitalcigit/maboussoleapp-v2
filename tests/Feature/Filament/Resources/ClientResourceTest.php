<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\User;
use App\Models\Prospect;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTestPermissions;

class ClientResourceTest extends TestCase
{
    use RefreshDatabase, HasTestPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->setupPermissions();
    }

    /** @test */
    public function it_can_list_clients()
    {
        $clients = Client::factory()->count(5)->create();

        $response = $this->get(ClientResource::getUrl('index'));

        $response->assertSuccessful();
        $response->assertSee($clients[0]->client_number);
    }

    /** @test */
    public function it_can_create_client()
    {
        $prospect = Prospect::factory()->create();
        $newClient = Client::factory()->make([
            'prospect_id' => $prospect->id,
        ]);

        $response = $this->post(ClientResource::getUrl('create'), [
            'prospect_id' => $newClient->prospect_id,
            'client_number' => $newClient->client_number,
            'first_name' => $newClient->first_name,
            'last_name' => $newClient->last_name,
            'email' => $newClient->email,
            'phone' => $newClient->phone,
            'address' => $newClient->address,
            'passport_number' => $newClient->passport_number,
            'passport_expiry' => $newClient->passport_expiry->format('Y-m-d'),
            'notes' => $newClient->notes,
            'status' => $newClient->status,
            'total_amount' => $newClient->total_amount,
            'paid_amount' => $newClient->paid_amount,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'client_number' => $newClient->client_number,
            'prospect_id' => $prospect->id,
        ]);
    }

    /** @test */
    public function it_can_edit_client()
    {
        $client = Client::factory()->create();
        $updatedData = Client::factory()->make();

        $response = $this->put(ClientResource::getUrl('edit', ['record' => $client]), [
            'first_name' => $updatedData->first_name,
            'last_name' => $updatedData->last_name,
            'email' => $updatedData->email,
            'phone' => $updatedData->phone,
            'address' => $updatedData->address,
            'passport_number' => $updatedData->passport_number,
            'passport_expiry' => $updatedData->passport_expiry->format('Y-m-d'),
            'notes' => $updatedData->notes,
            'status' => $updatedData->status,
            'total_amount' => $updatedData->total_amount,
            'paid_amount' => $updatedData->paid_amount,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'passport_number' => $updatedData->passport_number,
        ]);
    }

    /** @test */
    public function it_can_delete_client()
    {
        $client = Client::factory()->create();

        $response = $this->delete(ClientResource::getUrl('edit', ['record' => $client]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('clients', [
            'id' => $client->id,
        ]);
    }

    /** @test */
    public function it_can_view_client_details()
    {
        $client = Client::factory()->create();
        Activity::factory()->count(3)->create([
            'subject_type' => Client::class,
            'subject_id' => $client->id,
        ]);

        $response = $this->get(ClientResource::getUrl('edit', ['record' => $client]));

        $response->assertSuccessful();
        $response->assertSee($client->client_number);
        $response->assertSee($client->first_name);
        $response->assertSee($client->last_name);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post(ClientResource::getUrl('create'), []);

        $response->assertSessionHasErrors([
            'client_number',
            'first_name',
            'last_name',
            'email',
            'phone',
        ]);
    }

    /** @test */
    public function it_validates_unique_client_number()
    {
        $existingClient = Client::factory()->create();

        $response = $this->post(ClientResource::getUrl('create'), [
            'client_number' => $existingClient->client_number,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ]);

        $response->assertSessionHasErrors(['client_number']);
    }

    /** @test */
    public function it_can_filter_clients_by_status()
    {
        $activeClient = Client::factory()->create(['status' => 'active']);
        $inactiveClient = Client::factory()->create(['status' => 'inactive']);

        $response = $this->get(ClientResource::getUrl('index', [
            'filter' => [
                'status' => 'active',
            ],
        ]));

        $response->assertSuccessful();
        $response->assertSee($activeClient->client_number);
        $response->assertDontSee($inactiveClient->client_number);
    }

    /** @test */
    public function it_can_search_clients()
    {
        $client1 = Client::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $client2 = Client::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        $response = $this->get(ClientResource::getUrl('index', [
            'search' => 'John Doe',
        ]));

        $response->assertSuccessful();
        $response->assertSee($client1->client_number);
        $response->assertDontSee($client2->client_number);
    }

    /** @test */
    public function it_can_sort_clients()
    {
        $client1 = Client::factory()->create(['created_at' => now()->subDays(2)]);
        $client2 = Client::factory()->create(['created_at' => now()]);

        $response = $this->get(ClientResource::getUrl('index', [
            'sort' => 'created_at',
            'direction' => 'desc',
        ]));

        $response->assertSuccessful();
        $response->assertSeeInOrder([
            $client2->client_number,
            $client1->client_number,
        ]);
    }
}
