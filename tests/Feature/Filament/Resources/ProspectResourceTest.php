<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProspectResource;
use App\Filament\Resources\ProspectResource\Pages\CreateProspect;
use App\Filament\Resources\ProspectResource\Pages\EditProspect;
use App\Filament\Resources\ProspectResource\Pages\ListProspects;
use App\Filament\Resources\ProspectResource\Pages\ViewProspect;
use App\Filament\Resources\ProspectResource\RelationManagers\ActivitiesRelationManager;
use App\Models\Activity;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\FilamentPermissionsTrait;

class ProspectResourceTest extends TestCase
{
    use FilamentPermissionsTrait;
    use RefreshDatabase;

    protected User $user;

    protected Prospect $prospect;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un manager avec toutes les permissions nécessaires
        $this->user = $this->createManager();

        // Créer un prospect pour les tests
        $this->prospect = Prospect::factory()->create([
            'status' => Prospect::STATUS_NEW,
        ]);
    }

    /** @test */
    public function it_can_list_prospects()
    {
        $response = $this->actingAs($this->user)
            ->get(ProspectResource::getUrl('index'));

        $response->assertSuccessful();

        Livewire::test(ListProspects::class)
            ->assertCanSeeTableRecords([$this->prospect]);
    }

    /** @test */
    public function it_can_create_prospect()
    {
        $response = $this->actingAs($this->user)
            ->get(ProspectResource::getUrl('create'));

        $response->assertSuccessful();

        Livewire::test(CreateProspect::class)
            ->fillForm([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+22501020304',
                'status' => Prospect::STATUS_NEW,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prospects', [
            'email' => 'john.doe@example.com',
            'status' => Prospect::STATUS_NEW,
        ]);
    }

    /** @test */
    public function it_can_edit_prospect()
    {
        $response = $this->actingAs($this->user)
            ->get(ProspectResource::getUrl('edit', [
                'record' => $this->prospect,
            ]));

        $response->assertSuccessful();

        Livewire::test(EditProspect::class, [
            'record' => $this->prospect->id,
        ])
            ->fillForm([
                'status' => Prospect::STATUS_ANALYZING,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prospects', [
            'id' => $this->prospect->id,
            'status' => Prospect::STATUS_ANALYZING,
        ]);
    }

    /** @test */
    public function it_can_view_prospect()
    {
        $response = $this->actingAs($this->user)
            ->get(ProspectResource::getUrl('view', [
                'record' => $this->prospect,
            ]));

        $response->assertSuccessful();

        Livewire::test(ViewProspect::class, [
            'record' => $this->prospect->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function it_can_delete_prospect()
    {
        Livewire::test(ListProspects::class)
            ->callTableAction(DeleteAction::class, $this->prospect)
            ->assertSuccessful();

        $this->assertSoftDeleted($this->prospect);
    }

    /** @test */
    public function it_can_assign_prospect()
    {
        $assignee = User::factory()->create([
            'email' => 'assignee_'.uniqid().'@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->get(ProspectResource::getUrl('edit', [
                'record' => $this->prospect,
            ]));

        $response->assertSuccessful();

        Livewire::test(EditProspect::class, [
            'record' => $this->prospect->id,
        ])
            ->fillForm([
                'assigned_to' => $assignee->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prospects', [
            'id' => $this->prospect->id,
            'assigned_to' => $assignee->id,
        ]);
    }

    /** @test */
    public function it_can_filter_prospects_by_status()
    {
        $activeProspect = Prospect::factory()->create(['status' => Prospect::STATUS_ANALYZING]);
        $inactiveProspect = Prospect::factory()->create(['status' => Prospect::STATUS_REJECTED]);

        Livewire::test(ListProspects::class)
            ->filterTable('status', Prospect::STATUS_ANALYZING)
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
            'status' => Prospect::STATUS_ANALYZING,
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
            'status' => Prospect::STATUS_CONVERTED,
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
            'status' => Prospect::STATUS_NEW,
        ]);

        $this->get(ProspectResource::getUrl('index'))
            ->assertSuccessful();

        Livewire::test(ListProspects::class)
            ->assertCanSeeTableRecords($prospects)
            ->assertTableBulkActionExists('bulk-update')
            ->callTableBulkAction('bulk-update', $prospects->pluck('id')->toArray(), [
                'status' => Prospect::STATUS_ANALYZING,
            ])
            ->assertHasNoActionErrors();

        foreach ($prospects as $prospect) {
            $this->assertDatabaseHas('prospects', [
                'id' => $prospect->id,
                'status' => Prospect::STATUS_ANALYZING,
            ]);
        }
    }

    /** @test */
    public function it_can_manage_prospect_activities()
    {
        $activity = Activity::factory()->make([
            'subject_type' => Prospect::class,
            'subject_id' => $this->prospect->id,
            'title' => 'Test Activity',
            'scheduled_at' => now(),
        ]);

        Livewire::test(ActivitiesRelationManager::class, [
            'ownerRecord' => $this->prospect,
            'pageClass' => EditProspect::class,
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
            'subject_id' => $this->prospect->id,
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
