<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\FilamentPermissionsTrait;

class ActivityResourceTest extends TestCase
{
    use FilamentPermissionsTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un manager avec toutes les permissions nécessaires
        $this->user = $this->createManager();

        // Créer un client pour les tests
        $this->client = Client::factory()->create([
            'status' => Client::STATUS_ACTIVE,
        ]);

        // Créer une activité pour les tests
        $this->activity = Activity::factory()->create([
            'subject_type' => Client::class,
            'subject_id' => $this->client->id,
            'type' => Activity::TYPE_NOTE,
            'status' => Activity::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function it_can_list_activities()
    {
        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('index'));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertCanSeeTableRecords([$this->activity]);
    }

    /** @test */
    public function it_can_create_activity()
    {
        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('create'));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm([
                'subject_type' => Client::class,
                'subject_id' => $this->client->id,
                'type' => Activity::TYPE_CALL,
                'title' => 'Test Call',
                'description' => 'Test Description',
                'status' => Activity::STATUS_PENDING,
                'scheduled_at' => now(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', [
            'subject_type' => Client::class,
            'subject_id' => $this->client->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
        ]);
    }

    /** @test */
    public function it_can_edit_activity()
    {
        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('edit', [
                'record' => $this->activity,
            ]));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\EditActivity::class, [
            'record' => $this->activity->id,
        ])
            ->fillForm([
                'status' => Activity::STATUS_COMPLETED,
                'description' => 'Updated Description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', [
            'id' => $this->activity->id,
            'status' => Activity::STATUS_COMPLETED,
            'description' => 'Updated Description',
        ]);
    }

    /** @test */
    public function it_can_view_activity()
    {
        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('view', [
                'record' => $this->activity,
            ]));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\ViewActivity::class, [
            'record' => $this->activity->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function it_can_delete_activity()
    {
        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->callTableAction(DeleteAction::class, $this->activity)
            ->assertSuccessful();

        $this->assertSoftDeleted($this->activity);
    }

    /** @test */
    public function it_can_filter_activities_by_type()
    {
        $callActivity = Activity::factory()->create([
            'type' => Activity::TYPE_CALL,
            'subject_type' => Client::class,
            'subject_id' => $this->client->id,
        ]);

        $noteActivity = Activity::factory()->create([
            'type' => Activity::TYPE_NOTE,
            'subject_type' => Client::class,
            'subject_id' => $this->client->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->filterTable('type', Activity::TYPE_CALL)
            ->assertCanSeeTableRecords([$callActivity])
            ->assertCanNotSeeTableRecords([$noteActivity]);
    }

    /** @test */
    public function it_can_filter_activities_by_status()
    {
        $pendingActivity = Activity::factory()->create([
            'status' => Activity::STATUS_PENDING,
            'subject_type' => Client::class,
            'subject_id' => $this->client->id,
        ]);

        $completedActivity = Activity::factory()->create([
            'status' => Activity::STATUS_COMPLETED,
            'subject_type' => Client::class,
            'subject_id' => $this->client->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->filterTable('status', Activity::STATUS_PENDING)
            ->assertCanSeeTableRecords([$pendingActivity])
            ->assertCanNotSeeTableRecords([$completedActivity]);
    }

    /** @test */
    public function it_can_create_activity_for_prospect()
    {
        $prospect = Prospect::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('create'));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm([
                'subject_type' => Prospect::class,
                'subject_id' => $prospect->id,
                'type' => Activity::TYPE_CALL,
                'title' => 'Test Call',
                'description' => 'Test Description',
                'status' => Activity::STATUS_PENDING,
                'scheduled_at' => now(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', [
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
        ]);
    }

    /** @test */
    public function it_can_create_activity_for_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('create'));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm([
                'subject_type' => Client::class,
                'subject_id' => $client->id,
                'type' => Activity::TYPE_CALL,
                'title' => 'Test Call',
                'description' => 'Test Description',
                'status' => Activity::STATUS_PENDING,
                'scheduled_at' => now(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', [
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
        ]);
    }

    /** @test */
    public function it_can_edit_activity_for_prospect()
    {
        $prospect = Prospect::factory()->create();

        $activity = Activity::factory()->create([
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
            'description' => 'Test Description',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('edit', [
                'record' => $activity,
            ]));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\EditActivity::class, [
            'record' => $activity->id,
        ])
            ->fillForm([
                'status' => Activity::STATUS_COMPLETED,
                'description' => 'Updated Description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'status' => Activity::STATUS_COMPLETED,
            'description' => 'Updated Description',
        ]);
    }

    /** @test */
    public function it_can_edit_activity_for_client()
    {
        $client = Client::factory()->create();

        $activity = Activity::factory()->create([
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
            'description' => 'Test Description',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('edit', [
                'record' => $activity,
            ]));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\EditActivity::class, [
            'record' => $activity->id,
        ])
            ->fillForm([
                'status' => Activity::STATUS_COMPLETED,
                'description' => 'Updated Description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'status' => Activity::STATUS_COMPLETED,
            'description' => 'Updated Description',
        ]);
    }

    /** @test */
    public function it_can_view_activity_for_prospect()
    {
        $prospect = Prospect::factory()->create();

        $activity = Activity::factory()->create([
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
            'description' => 'Test Description',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('view', [
                'record' => $activity,
            ]));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\ViewActivity::class, [
            'record' => $activity->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function it_can_view_activity_for_client()
    {
        $client = Client::factory()->create();

        $activity = Activity::factory()->create([
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
            'description' => 'Test Description',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(ActivityResource::getUrl('view', [
                'record' => $activity,
            ]));

        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\ViewActivity::class, [
            'record' => $activity->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function it_can_delete_activity_for_prospect()
    {
        $prospect = Prospect::factory()->create();

        $activity = Activity::factory()->create([
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
            'description' => 'Test Description',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->callTableAction(DeleteAction::class, $activity)
            ->assertSuccessful();

        $this->assertSoftDeleted($activity);
    }

    /** @test */
    public function it_can_delete_activity_for_client()
    {
        $client = Client::factory()->create();

        $activity = Activity::factory()->create([
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
            'description' => 'Test Description',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->callTableAction(DeleteAction::class, $activity)
            ->assertSuccessful();

        $this->assertSoftDeleted($activity);
    }

    /** @test */
    public function it_can_filter_activities_by_subject_type()
    {
        $prospect = Prospect::factory()->create();

        $prospectActivity = Activity::factory()->create([
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
            'description' => 'Test Description',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
        ]);

        $client = Client::factory()->create();

        $clientActivity = Activity::factory()->create([
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'type' => Activity::TYPE_CALL,
            'title' => 'Test Call',
            'description' => 'Test Description',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->filterTable('subject_type', Prospect::class)
            ->assertCanSeeTableRecords([$prospectActivity])
            ->assertCanNotSeeTableRecords([$clientActivity]);
    }

    /** @test */
    public function it_can_sort_activities()
    {
        $firstActivity = Activity::factory()->create([
            'title' => 'A Test Activity',
            'created_at' => now()->subDay(),
        ]);

        $secondActivity = Activity::factory()->create([
            'title' => 'B Test Activity',
            'created_at' => now(),
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->sortTable('title')
            ->assertCanSeeTableRecords([$firstActivity, $secondActivity], inOrder: true)
            ->sortTable('title', 'desc')
            ->assertCanSeeTableRecords([$secondActivity, $firstActivity], inOrder: true);
    }

    /** @test */
    public function it_requires_permission_to_view_activities()
    {
        // Simuler un utilisateur sans permission
        $this->actingAs(User::factory()->create(['role' => 'guest']));

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertForbidden();
    }

    /** @test */
    public function it_requires_permission_to_create_activity()
    {
        // Simuler un utilisateur sans permission
        $this->actingAs(User::factory()->create(['role' => 'guest']));

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->assertForbidden();
    }

    /** @test */
    public function it_requires_permission_to_edit_activity()
    {
        $user = User::factory()->create(['role' => 'guest']);
        $this->actingAs($user);

        $activity = Activity::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ActivityResource\Pages\EditActivity::class, [
            'record' => $activity->id,
        ])
            ->assertForbidden();
    }

    /** @test */
    public function test_it_requires_permission_to_delete_activity()
    {
        $this->user->revokePermissionTo(['activities.delete']);

        $activity = Activity::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$activity])
            ->assertTableActionHidden('delete', $activity);
    }

    /** @test */
    public function it_loads_relationships_correctly()
    {
        $user = User::factory()->create();
        $prospect = Prospect::factory()->create();

        $activity = Activity::factory()->create([
            'user_id' => $user->id,
            'prospect_id' => $prospect->id,
        ]);

        $this->assertEquals($user->id, $activity->user->id);
        $this->assertEquals($prospect->id, $activity->prospect->id);
        $this->assertInstanceOf(Prospect::class, $activity->prospect);
    }
}
