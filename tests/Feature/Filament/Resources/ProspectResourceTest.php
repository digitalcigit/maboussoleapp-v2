<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProspectResource;
use App\Filament\Resources\ProspectResource\Pages\CreateProspect;
use App\Filament\Resources\ProspectResource\Pages\EditProspect;
use App\Filament\Resources\ProspectResource\Pages\ListProspects;
use App\Filament\Resources\ProspectResource\RelationManagers\ActivitiesRelationManager;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProspectResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test_' . uniqid() . '@example.com',
        ]);

        // Création des permissions avec firstOrCreate
        $permissions = [
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'prospects.delete',
            'prospects.convert',
            'prospects.bulk_update',
            'activities.view',
            'activities.create',
            'activities.edit',
            'activities.delete',
            'access_filament',
            'access_admin_panel',
            'manage prospects',  // Permission Filament
            'manage activities', // Permission Filament
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->user->syncPermissions($permissions);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_list_prospects()
    {
        $prospects = Prospect::factory()->count(5)->create();

        Livewire::test(ListProspects::class)
            ->assertCanSeeTableRecords($prospects);
    }

    /** @test */
    public function it_can_create_prospect()
    {
        $prospect = Prospect::factory()->make([
            'phone' => '+33612345678'
        ]);

        Livewire::test(CreateProspect::class)
            ->fillForm([
                'reference_number' => $prospect->reference_number,
                'first_name' => $prospect->first_name,
                'last_name' => $prospect->last_name,
                'email' => $prospect->email,
                'phone' => $prospect->phone,
                'birth_date' => $prospect->birth_date,
                'profession' => $prospect->profession,
                'education_level' => $prospect->education_level,
                'current_location' => $prospect->current_location,
                'current_field' => $prospect->current_field,
                'desired_field' => $prospect->desired_field,
                'desired_destination' => $prospect->desired_destination,
                'emergency_contact' => $prospect->emergency_contact,
                'status' => $prospect->status,
                'commercial_code' => $prospect->commercial_code,
                'analysis_deadline' => $prospect->analysis_deadline,
                'notes' => $prospect->notes,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prospects', [
            'email' => $prospect->email,
            'first_name' => $prospect->first_name,
            'last_name' => $prospect->last_name,
        ]);
    }

    /** @test */
    public function it_can_edit_prospect()
    {
        $prospect = Prospect::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+33612345678'
        ]);

        $response = $this->get(ProspectResource::getUrl('edit', ['record' => $prospect]));
        $response->assertSuccessful();

        Livewire::test(EditProspect::class, [
            'record' => $prospect->id
        ])
        ->assertFormSet([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+33612345678'
        ])
        ->fillForm([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '+33687654321'
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prospects', [
            'id' => $prospect->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '+33687654321'
        ]);
    }

    /** @test */
    public function it_can_assign_prospect()
    {
        $prospect = Prospect::factory()->create([
            'assigned_to' => null
        ]);
        
        $assignee = User::factory()->create([
            'email' => 'assignee_' . uniqid() . '@example.com',
        ]);

        $response = $this->get(ProspectResource::getUrl('edit', ['record' => $prospect]));
        $response->assertSuccessful();

        Livewire::test(EditProspect::class, [
            'record' => $prospect->id
        ])
        ->assertSet('data.assigned_to', null)
        ->fillForm([
            'assigned_to' => $assignee->id
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prospects', [
            'id' => $prospect->id,
            'assigned_to' => $assignee->id
        ]);
    }

    /** @test */
    public function it_can_filter_prospects_by_status()
    {
        $activeProspect = Prospect::factory()->create(['status' => 'en_cours']);
        $inactiveProspect = Prospect::factory()->create(['status' => 'rejeté']);

        Livewire::test(ListProspects::class)
            ->filterTable('status', 'en_cours')
            ->assertCanSeeTableRecords([$activeProspect])
            ->assertCanNotSeeTableRecords([$inactiveProspect]);
    }

    /** @test */
    public function it_can_search_prospects()
    {
        $prospect = Prospect::factory()->create();
        $otherProspect = Prospect::factory()->create();

        Livewire::test(ListProspects::class)
            ->searchTable($prospect->first_name)
            ->assertCanSeeTableRecords([$prospect])
            ->assertCanNotSeeTableRecords([$otherProspect]);
    }

    /** @test */
    public function it_can_paginate_prospects()
    {
        $prospects = Prospect::factory()->count(25)->create();
        
        // Get prospects ordered by created_at desc since that's the default sort
        $orderedProspects = $prospects->sortByDesc('created_at')->values();
        
        $page1Prospects = $orderedProspects->take(10);
        $page2Prospects = $orderedProspects->slice(10, 10);

        Livewire::test(ListProspects::class)
            ->assertCanSeeTableRecords($page1Prospects)
            ->assertCanNotSeeTableRecords($page2Prospects);
    }

    /** @test */
    public function it_can_convert_prospect_to_client()
    {
        $prospect = Prospect::factory()->create([
            'status' => 'en_cours'
        ]);

        $this->get(ProspectResource::getUrl('index'))
            ->assertSuccessful();

        Livewire::test(ListProspects::class)
            ->assertTableActionExists('convert')
            ->assertCanSeeTableRecords([$prospect])
            ->callTableAction('convert', $prospect, [])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('prospects', [
            'id' => $prospect->id,
            'status' => 'converti'
        ]);

        $this->assertDatabaseHas('clients', [
            'email' => $prospect->email,
            'first_name' => $prospect->first_name,
            'last_name' => $prospect->last_name,
            'prospect_id' => $prospect->id,
        ]);
    }

    /** @test */
    public function it_can_bulk_update_prospects()
    {
        $prospects = Prospect::factory()->count(3)->create([
            'status' => 'nouveau'
        ]);

        $this->get(ProspectResource::getUrl('index'))
            ->assertSuccessful();

        Livewire::test(ListProspects::class)
            ->assertCanSeeTableRecords($prospects)
            ->assertTableBulkActionExists('bulk-update')
            ->callTableBulkAction('bulk-update', $prospects->pluck('id')->toArray(), [
                'status' => 'en_cours'
            ])
            ->assertHasNoActionErrors();

        foreach ($prospects as $prospect) {
            $this->assertDatabaseHas('prospects', [
                'id' => $prospect->id,
                'status' => 'en_cours'
            ]);
        }
    }

    /** @test */
    public function it_can_manage_prospect_activities()
    {
        $prospect = Prospect::factory()->create();
        $activity = Activity::factory()->make([
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
            'title' => 'Test Activity',
            'scheduled_at' => now(),
        ]);

        Livewire::test(ActivitiesRelationManager::class, [
            'ownerRecord' => $prospect,
            'pageClass' => EditProspect::class
        ])
        ->assertSuccessful()
        ->callTableAction('create', data: [
            'title' => $activity->title,
            'type' => $activity->type,
            'description' => $activity->description,
            'scheduled_at' => $activity->scheduled_at,
        ])
        ->assertHasNoActionErrors();

        $this->assertDatabaseHas('activities', [
            'subject_id' => $prospect->id,
            'subject_type' => Prospect::class,
            'title' => $activity->title,
            'type' => $activity->type,
            'description' => $activity->description,
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        Livewire::test(CreateProspect::class)
            ->fillForm([
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'reference_number' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                'reference_number' => 'required',
            ]);
    }

    /** @test */
    public function it_validates_unique_reference_number()
    {
        $existingProspect = Prospect::factory()->create();
        
        Livewire::test(CreateProspect::class)
            ->fillForm([
                'reference_number' => $existingProspect->reference_number,
            ])
            ->call('create')
            ->assertHasFormErrors(['reference_number' => 'unique']);
    }

    /** @test */
    public function it_validates_email_format()
    {
        Livewire::test(CreateProspect::class)
            ->fillForm([
                'email' => 'invalid-email',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    }

    /** @test */
    public function it_validates_phone_format()
    {
        Livewire::test(CreateProspect::class)
            ->fillForm([
                'phone' => 'invalid-phone',
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);
    }
}
