<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\ClientResource\Pages\ListClients;
use App\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Filament\Resources\ClientResource\Pages\EditClient;
use App\Filament\Resources\ClientResource\Pages\ViewClient;
use App\Filament\Resources\ClientResource\RelationManagers\ActivitiesRelationManager;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use App\Models\Activity;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Traits\HasTestPermissions;
use Tests\TestCase;
use Filament\Pages\Page;
use Filament\Actions\CreateAction;
use Filament\Tables\Actions\CreateAction as TableCreateAction;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class ClientResourceTest extends TestCase
{
    use RefreshDatabase, HasTestPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate user with permissions
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->setupPermissions();
        
        // Give all necessary permissions
        $this->user->givePermissionTo([
            'access_filament',
            'access_admin_panel',
            'view_admin_panel',
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',
            'clients.view_any',
            'clients.edit',
            'manage clients',
        ]);

        // Assign super_admin role
        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $this->user->assignRole($role);
    }

    /** @test */
    public function it_can_list_clients()
    {
        $clients = Client::factory()->count(5)->create();

        Livewire::test(ListClients::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($clients);
    }

    /** @test */
    public function it_can_create_client()
    {
        $prospect = Prospect::factory()->create(['status' => 'qualified']);
        $newClientData = [
            'prospect_id' => $prospect->id,
            'client_number' => 'CLI-' . rand(10000, 99999),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'status' => 'active',
            'total_amount' => 1000,
            'paid_amount' => 500,
        ];

        Livewire::test(CreateClient::class)
            ->fillForm($newClientData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('clients', [
            'client_number' => $newClientData['client_number'],
            'email' => $newClientData['email'],
        ]);
    }

    /** @test */
    public function it_can_edit_client()
    {
        $client = Client::factory()->create(['status' => 'active']);
        $newData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
            'status' => 'active',
            'total_amount' => 2000,
            'paid_amount' => 1000,
        ];

        Livewire::test(EditClient::class, [
            'record' => $client->id,
        ])
            ->assertSuccessful()
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'email' => $newData['email'],
        ]);
    }

    /** @test */
    public function it_can_delete_client()
    {
        $client = Client::factory()->create(['status' => 'active']);

        Livewire::test(ListClients::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$client])
            ->callTableAction('delete', $client);

        $this->assertDatabaseMissing('clients', [
            'id' => $client->id,
        ]);
    }

    /** @test */
    public function it_can_view_client_details()
    {
        $client = Client::factory()->create([
            'status' => 'active',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Livewire::test(ViewClient::class, [
            'record' => $client->id,
        ])
            ->assertSuccessful()
            ->assertSee($client->client_number)
            ->assertSee($client->first_name)
            ->assertSee($client->last_name);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        Livewire::test(CreateClient::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
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

        Livewire::test(CreateClient::class)
            ->fillForm([
                'client_number' => $existingClient->client_number,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
            ])
            ->call('create')
            ->assertHasFormErrors(['client_number']);
    }

    /** @test */
    public function it_can_filter_clients_by_status()
    {
        $activeClient = Client::factory()->create(['status' => 'active']);
        $inactiveClient = Client::factory()->create(['status' => 'inactive']);

        Livewire::test(ListClients::class)
            ->assertCanSeeTableRecords([$activeClient, $inactiveClient])
            ->filterTable('status', 'active')
            ->assertCanSeeTableRecords([$activeClient])
            ->assertCanNotSeeTableRecords([$inactiveClient]);
    }

    /** @test */
    public function it_can_search_clients()
    {
        $client1 = Client::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $client2 = Client::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        Livewire::test(ListClients::class)
            ->assertSuccessful()
            ->searchTable('John Doe')
            ->assertCanSeeTableRecords([$client1])
            ->assertCanNotSeeTableRecords([$client2]);
    }

    /** @test */
    public function it_can_sort_clients()
    {
        $client1 = Client::factory()->create(['created_at' => now()->subDays(2)]);
        $client2 = Client::factory()->create(['created_at' => now()]);

        Livewire::test(ListClients::class)
            ->assertSuccessful()
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$client2, $client1], inOrder: true);
    }

    /** @test */
    public function it_can_bulk_assign_clients()
    {
        // Créer l'utilisateur assigné
        $assignedTo = User::factory()->create();
        Log::info('Created assigned user', [
            'user_id' => $assignedTo->id,
            'user_email' => $assignedTo->email,
        ]);

        // Créer les clients sans assignation
        $clients = Client::factory()->count(3)->create(['assigned_to' => null]);
        $clientIds = $clients->pluck('id')->toArray();
        $assignedToId = $assignedTo->id;
        
        Log::info('Created test data', [
            'client_ids' => $clientIds,
            'assigned_to_id' => $assignedToId,
        ]);

        // Exécuter l'action bulk assign
        Livewire::test(ListClients::class)
            ->assertSuccessful()
            ->mountTableBulkAction('assign', $clientIds)
            ->setTableBulkActionData([
                'assigned_to' => $assignedToId,
            ])
            ->callMountedTableBulkAction()
            ->assertHasNoTableBulkActionErrors();

        // Vérifier que les clients ont été mis à jour
        foreach ($clientIds as $clientId) {
            $client = Client::find($clientId);
            $this->assertEquals($assignedToId, $client->assigned_to,
                "Client {$clientId} should be assigned to user {$assignedToId} but is assigned to {$client->assigned_to}");
        }
    }

    /** @test */
    public function it_can_manage_client_activities()
    {
        $client = Client::factory()->create();
        
        $formData = [
            'title' => 'Test Activity',
            'type' => 'appel',
            'status' => 'planifié',
            'description' => 'Test description',
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ];

        // Créer l'activité directement dans la base de données
        $activity = $client->activities()->create($formData);

        // Vérifier que l'activité a été créée
        $this->assertDatabaseHas('activities', [
            'subject_id' => $client->id,
            'subject_type' => Client::class,
            'title' => 'Test Activity',
            'type' => 'appel',
            'status' => 'planifié',
            'description' => 'Test description',
        ]);

        // Vérifier que l'activité apparaît dans la liste
        $component = Livewire::test(ActivitiesRelationManager::class, [
            'ownerRecord' => $client,
            'pageClass' => ViewClient::class,
        ]);

        $component
            ->assertSuccessful()
            ->assertSeeText('Test Activity')
            ->assertSeeText('appel');
    }

    /** @test */
    public function it_validates_payment_amounts()
    {
        $client = Client::factory()->create();
        
        Livewire::test(EditClient::class, [
            'record' => $client->id,
        ])
            ->fillForm([
                'total_amount' => 1000,
                'paid_amount' => 2000, // Paid amount greater than total
            ])
            ->call('save')
            ->assertHasFormErrors(['paid_amount']);
    }

    /** @test */
    public function it_tracks_client_conversion_from_prospect()
    {
        $prospect = Prospect::factory()->create(['status' => 'qualified']);
        $clientData = [
            'prospect_id' => $prospect->id,
            'client_number' => 'CLI-' . rand(10000, 99999),
            'first_name' => $prospect->first_name,
            'last_name' => $prospect->last_name,
            'email' => $prospect->email,
            'phone' => $prospect->phone,
            'status' => 'active',
            'total_amount' => 1000,
            'paid_amount' => 500,
        ];

        Livewire::test(CreateClient::class)
            ->fillForm($clientData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prospects', [
            'id' => $prospect->id,
            'status' => 'converted',
        ]);
    }

    /** @test */
    public function it_requires_permission_to_manage_clients()
    {
        // Créer un nouvel utilisateur sans aucune permission
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        Livewire::test(ListClients::class)
            ->assertForbidden();

        $client = Client::factory()->create();
        Livewire::test(EditClient::class, [
            'record' => $client->id,
        ])
            ->assertForbidden();
    }

    /** @test */
    public function it_can_filter_clients_by_payment_status()
    {
        $paidClient = Client::factory()->create([
            'total_amount' => 1000,
            'paid_amount' => 1000,
        ]);
        
        $partiallyPaidClient = Client::factory()->create([
            'total_amount' => 1000,
            'paid_amount' => 500,
        ]);

        Livewire::test(ListClients::class)
            ->assertSuccessful()
            ->filterTable('payment_status', 'paid')
            ->assertCanSeeTableRecords([$paidClient])
            ->assertCanNotSeeTableRecords([$partiallyPaidClient]);
    }
}
