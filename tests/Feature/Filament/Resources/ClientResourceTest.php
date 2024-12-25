<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Filament\Resources\ClientResource\Pages\EditClient;
use App\Filament\Resources\ClientResource\Pages\ListClients;
use App\Filament\Resources\ClientResource\Pages\ViewClient;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\FilamentPermissionsTrait;

class ClientResourceTest extends TestCase
{
    use FilamentPermissionsTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un manager avec toutes les permissions nécessaires
        $this->user = $this->createManager();
        $this->actingAs($this->user);

        // Créer un prospect pour les tests
        $this->prospect = Prospect::factory()->create([
            'status' => Prospect::STATUS_APPROVED,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ]);

        // Créer un client lié au prospect
        $this->client = Client::factory()->create([
            'prospect_id' => $this->prospect->id,
            'client_number' => 'CLI-'.rand(10000, 99999),
            'status' => Client::STATUS_ACTIVE,
            'payment_status' => Client::PAYMENT_STATUS_PENDING,
            'visa_status' => Client::VISA_STATUS_NOT_STARTED,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
        ]);
    }

    /** @test */
    public function it_can_list_clients()
    {
        // Créer des clients supplémentaires pour le test
        $additionalProspects = Prospect::factory()
            ->count(5)
            ->create(['status' => Prospect::STATUS_APPROVED]);

        $additionalClients = [];
        foreach ($additionalProspects as $prospect) {
            $additionalClients[] = Client::factory()->create([
                'prospect_id' => $prospect->id,
                'status' => Client::STATUS_ACTIVE,
            ]);
        }

        $response = $this->get(ClientResource::getUrl('index'));

        $response->assertSuccessful();

        // Vérifier que nous pouvons voir tous les clients
        Livewire::test(ListClients::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$this->client])
            ->assertCanSeeTableRecords($additionalClients);
    }

    /** @test */
    public function it_can_create_client()
    {
        $newProspect = Prospect::factory()->create([
            'status' => Prospect::STATUS_APPROVED,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
        ]);

        $newClientData = [
            'prospect_id' => $newProspect->id,
            'client_number' => 'CLI-'.rand(10000, 99999),
            'status' => Client::STATUS_ACTIVE,
            'payment_status' => Client::PAYMENT_STATUS_PENDING,
            'visa_status' => Client::VISA_STATUS_NOT_STARTED,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
        ];

        $response = $this->get(ClientResource::getUrl('create'));

        $response->assertSuccessful();

        Livewire::test(CreateClient::class)
            ->fillForm($newClientData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('clients', [
            'prospect_id' => $newProspect->id,
            'client_number' => $newClientData['client_number'],
            'status' => Client::STATUS_ACTIVE,
        ]);
    }

    /** @test */
    public function it_can_edit_client()
    {
        $response = $this->get(ClientResource::getUrl('edit', [
            'record' => $this->client,
        ]));

        $response->assertSuccessful();

        $newData = [
            'status' => Client::STATUS_INACTIVE,
            'payment_status' => Client::PAYMENT_STATUS_PARTIAL,
            'total_amount' => 2000.00,
            'paid_amount' => 1000.00,
        ];

        Livewire::test(EditClient::class, [
            'record' => $this->client->id,
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('clients', array_merge(
            ['id' => $this->client->id],
            $newData
        ));
    }

    /** @test */
    public function it_can_view_client()
    {
        $response = $this->get(ClientResource::getUrl('view', [
            'record' => $this->client,
        ]));

        $response->assertSuccessful();

        Livewire::test(ViewClient::class, [
            'record' => $this->client->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function it_can_delete_client()
    {
        Livewire::test(ListClients::class)
            ->callTableAction(DeleteAction::class, $this->client)
            ->assertSuccessful();

        $this->assertSoftDeleted($this->client);
    }

    /** @test */
    public function it_can_bulk_delete_clients()
    {
        $additionalProspects = Prospect::factory()
            ->count(3)
            ->create(['status' => Prospect::STATUS_APPROVED]);

        $clientsToDelete = [];
        foreach ($additionalProspects as $prospect) {
            $clientsToDelete[] = Client::factory()->create([
                'prospect_id' => $prospect->id,
                'status' => Client::STATUS_ACTIVE,
            ]);
        }

        Livewire::test(ListClients::class)
            ->callTableBulkAction(DeleteBulkAction::class, $clientsToDelete)
            ->assertSuccessful();

        foreach ($clientsToDelete as $client) {
            $this->assertSoftDeleted($client);
        }
    }

    /** @test */
    public function it_requires_permissions_to_access_clients()
    {
        // Créer un utilisateur sans permissions
        $userWithoutPermissions = User::factory()->create();

        $response = $this->actingAs($userWithoutPermissions)
            ->get(ClientResource::getUrl('index'));

        $response->assertForbidden();
    }
}
