<?php

namespace Tests\Feature\Filament\Resources;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use App\Filament\Resources\ActivityResource;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\HasTestPermissions;
use Spatie\Permission\Models\Role;

class ActivityResourceTest extends TestCase
{
    use RefreshDatabase, HasTestPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Create permissions
        $permissions = [
            'activities.view_any',
            'activities.view',
            'activities.create',
            'activities.update',
            'activities.delete',
            'access_filament',
            'access_admin_panel',
            'view_admin_panel'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->user->givePermissionTo($permissions);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_list_activities()
    {
        Activity::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(Activity::limit(3)->get());
    }

    /** @test */
    public function test_it_can_create_activity()
    {
        $this->user->givePermissionTo('activities.create');
        
        $prospect = Prospect::factory()->create();

        $data = [
            'title' => 'Test Activity',
            'type' => Activity::TYPE_MEETING,
            'status' => Activity::STATUS_PENDING,
            'prospect_id' => $prospect->id,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'created_by' => $this->user->id,
            'description' => 'Test description',
            'user_id' => $this->user->id,
        ];

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', array_merge($data, [
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
        ]));
    }

    /** @test */
    public function test_it_can_create_activity_for_prospect()
    {
        $this->user->givePermissionTo('activities.create');
        
        $prospect = Prospect::factory()->create();

        $data = [
            'title' => 'Test Activity',
            'type' => Activity::TYPE_MEETING,
            'status' => Activity::STATUS_PENDING,
            'prospect_id' => $prospect->id,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'created_by' => $this->user->id,
            'description' => 'Test description',
            'user_id' => $this->user->id,
        ];

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', array_merge($data, [
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
        ]));
    }

    /** @test */
    public function test_it_can_create_activity_for_client()
    {
        $this->user->givePermissionTo('activities.create');
        
        $client = Client::factory()->create();

        $data = [
            'title' => 'Test Activity',
            'type' => Activity::TYPE_MEETING,
            'status' => Activity::STATUS_PENDING,
            'client_id' => $client->id,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'created_by' => $this->user->id,
            'description' => 'Test description',
            'user_id' => $this->user->id,
        ];

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', array_merge($data, [
            'subject_type' => Client::class,
            'subject_id' => $client->id,
        ]));
    }

    /** @test */
    public function it_can_edit_activity(): void
    {
        $this->actingAs($this->user);
        $this->user->assignRole('manager');

        $client = Client::factory()->create();
        $activity = Activity::factory()->create([
            'title' => 'Original Activity',
            'type' => Activity::TYPE_CALL,
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
            'description' => 'Original description',
            'client_id' => $client->id,
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'subject_type' => Client::class,
            'subject_id' => $client->id,
        ]);

        $response = Livewire::test(ActivityResource\Pages\EditActivity::class, [
            'record' => $activity->id,
        ]);

        $response->assertSuccessful();

        $newData = [
            'title' => 'Updated Activity',
            'type' => Activity::TYPE_MEETING,
            'status' => Activity::STATUS_COMPLETED,
            'scheduled_at' => now()->addDay(),
            'description' => 'Updated description',
            'client_id' => $client->id,
            'user_id' => $this->user->id,
        ];

        $response
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', array_merge($newData, [
            'id' => $activity->id,
            'subject_type' => Client::class,
            'subject_id' => $client->id,
        ]));
    }

    /** @test */
    public function test_it_validates_required_fields()
    {
        $this->actingAs($this->user);

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm([
                'title' => '',
                'type' => '',
                'status' => '',
                'scheduled_at' => '',
                'user_id' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'title' => 'required',
                'type' => 'required',
                'status' => 'required',
                'scheduled_at' => 'required',
                'user_id' => 'required',
            ]);
    }

    /** @test */
    public function test_it_validates_type_enum()
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        $data = [
            'title' => 'Test Activity',
            'type' => 'invalid_type',
            'status' => 'pending',
            'scheduled_at' => now()->addDay(),
            'description' => 'Test description',
            'client_id' => $client->id,
            'user_id' => $this->user->id,
            'subject_type' => Client::class,
            'subject_id' => $client->id,
        ];

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasFormErrors(['type']);
    }

    /** @test */
    public function test_it_validates_status_enum()
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        $data = [
            'title' => 'Test Activity',
            'type' => 'meeting',
            'status' => 'invalid_status',
            'scheduled_at' => now()->addDay(),
            'description' => 'Test description',
            'client_id' => $client->id,
            'user_id' => $this->user->id,
            'subject_type' => Client::class,
            'subject_id' => $client->id,
        ];

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasFormErrors(['status']);
    }

    /** @test */
    public function it_can_delete_activity()
    {
        $activity = Activity::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->get(ActivityResource::getUrl('index'));
        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$activity])
            ->callTableAction('delete', $activity)
            ->assertCanNotSeeTableRecords([$activity]);

        $this->assertDatabaseMissing('activities', [
            'id' => $activity->id,
        ]);
    }

    /** @test */
    public function it_can_filter_activities_by_type()
    {
        $client = Client::factory()->create();

        $callActivity = Activity::factory()->create([
            'title' => 'Call Activity',
            'type' => Activity::TYPE_CALL,
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        $emailActivity = Activity::factory()->create([
            'title' => 'Email Activity',
            'type' => Activity::TYPE_EMAIL,
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        $response = $this->get(ActivityResource::getUrl('index'));
        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->filterTable('type', Activity::TYPE_CALL)
            ->assertCanSeeTableRecords([$callActivity])
            ->assertCanNotSeeTableRecords([$emailActivity]);
    }

    /** @test */
    public function it_can_filter_activities_by_completion_status()
    {
        $client = Client::factory()->create();

        $completedActivity = Activity::factory()->create([
            'title' => 'Completed Activity',
            'type' => Activity::TYPE_CALL,
            'status' => Activity::STATUS_COMPLETED,
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        $pendingActivity = Activity::factory()->create([
            'title' => 'Pending Activity',
            'type' => Activity::TYPE_CALL,
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        $response = $this->get(ActivityResource::getUrl('index'));
        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->filterTable('status', Activity::STATUS_COMPLETED)
            ->assertCanSeeTableRecords([$completedActivity])
            ->assertCanNotSeeTableRecords([$pendingActivity]);
    }

    /** @test */
    public function it_can_filter_activities_by_date_range()
    {
        $client = Client::factory()->create();

        $recentActivity = Activity::factory()->create([
            'title' => 'Recent Activity',
            'type' => Activity::TYPE_CALL,
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        $oldActivity = Activity::factory()->create([
            'title' => 'Old Activity',
            'type' => Activity::TYPE_CALL,
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now()->subMonths(2),
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        $response = $this->get(ActivityResource::getUrl('index'));
        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->filterTable('scheduled_at', [
                'since' => now()->subMonth()->format('Y-m-d'),
                'until' => now()->addDay()->format('Y-m-d'),
            ])
            ->assertCanSeeTableRecords([$recentActivity])
            ->assertCanNotSeeTableRecords([$oldActivity]);
    }

    /** @test */
    public function test_it_can_filter_activities_by_subject_type()
    {
        $this->user->givePermissionTo('activities.view_any');
        
        $prospect = Prospect::factory()->create();
        $client = Client::factory()->create();

        $prospectActivity = Activity::factory()->create([
            'title' => 'Prospect Activity',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
            'prospect_id' => $prospect->id,
        ]);

        $clientActivity = Activity::factory()->create([
            'title' => 'Client Activity',
            'status' => Activity::STATUS_PENDING,
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        $response = $this->get(ActivityResource::getUrl('index'));
        $response->assertSuccessful();

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->filterTable('subject_type', Prospect::class)
            ->assertCanSeeTableRecords([$prospectActivity])
            ->assertCanNotSeeTableRecords([$clientActivity]);
    }

    /** @test */
    public function test_it_can_paginate_activities()
    {
        $this->user->givePermissionTo('activities.view_any');

        // Create 15 activities
        $activities = Activity::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'client_id' => Client::factory()->create()->id,
            'created_by' => $this->user->id,
        ]);

        $firstPageActivities = $activities->take(10);
        $secondPageActivities = $activities->skip(10);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertCanSeeTableRecords($firstPageActivities)
            ->assertCanNotSeeTableRecords($secondPageActivities)
            ->set('tableRecordsPerPage', 10)
            ->assertCanSeeTableRecords($firstPageActivities);
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
            'user_id' => $this->user->id
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
}
