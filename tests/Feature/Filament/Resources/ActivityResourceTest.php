<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\User;
use App\Models\Client;
use App\Models\Prospect;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Traits\HasTestPermissions;

class ActivityResourceTest extends TestCase
{
    use RefreshDatabase, HasTestPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // D'abord on crée les permissions
        $this->setupPermissions();

        // Ensuite on les attribue à l'utilisateur
        $this->user->givePermissionTo([
            'activities.view_any',
            'activities.view',
            'activities.create',
            'activities.edit',
            'activities.delete'
        ]);
        $this->user->assignRole('manager');
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
    public function it_can_create_activity_for_prospect()
    {
        $prospect = Prospect::factory()->create();
        $newActivity = Activity::factory()->make([
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
        ]);

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm([
                'title' => 'Test Activity',
                'type' => $newActivity->type,
                'status' => 'planifié',
                'description' => $newActivity->description,
                'scheduled_at' => now()->format('Y-m-d H:i:s'),
                'prospect_id' => $prospect->id,
                'subject_type' => Prospect::class,
                'subject_id' => $prospect->id,
                'user_id' => $this->user->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', [
            'title' => 'Test Activity',
            'type' => $newActivity->type,
            'status' => 'planifié',
            'prospect_id' => $prospect->id,
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_create_activity_for_client()
    {
        $client = Client::factory()->create();
        $newActivity = Activity::factory()->make([
            'subject_type' => Client::class,
            'subject_id' => $client->id,
        ]);

        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm([
                'title' => 'Test Activity',
                'type' => 'email',
                'status' => 'planifié',
                'description' => $newActivity->description,
                'scheduled_at' => now()->format('Y-m-d H:i:s'),
                'client_id' => $client->id,
                'subject_type' => Client::class,
                'subject_id' => $client->id,
                'user_id' => $this->user->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', [
            'title' => 'Test Activity',
            'type' => 'email',
            'status' => 'planifié',
            'client_id' => $client->id,
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_edit_activity()
    {
        $activity = Activity::factory()->create([
            'title' => 'Original Title',
            'type' => 'call',
            'status' => 'planifié',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ActivityResource\Pages\EditActivity::class, [
            'record' => $activity->id,
        ])
            ->fillForm([
                'title' => 'Updated Title',
                'type' => 'meeting',
                'description' => 'Updated description',
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'status' => 'en_cours',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'title' => 'Updated Title',
            'type' => 'meeting',
            'status' => 'en_cours',
        ]);
    }

    /** @test */
    public function it_can_delete_activity()
    {
        $activity = Activity::factory()->create([
            'title' => 'Activity to delete',
            'type' => 'call',
            'status' => 'planifié',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertCanSeeTableRecords([$activity])
            ->assertTableActionVisible('delete', $activity)
            ->callTableAction('delete', $activity);

        $this->assertModelMissing($activity);
    }

    /** @test */
    public function it_can_filter_activities_by_type()
    {
        $callActivity = Activity::factory()->create([
            'title' => 'Call Activity',
            'type' => 'call',
            'status' => 'planifié',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
        ]);

        $emailActivity = Activity::factory()->create([
            'title' => 'Email Activity',
            'type' => 'email',
            'status' => 'planifié',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->filterTable('type', 'call')
            ->assertSee('Call Activity')
            ->assertDontSee('Email Activity')
            ->resetTableFilters()
            ->filterTable('type', 'email')
            ->assertSee('Email Activity')
            ->assertDontSee('Call Activity');
    }

    /** @test */
    public function it_can_filter_activities_by_completion_status()
    {
        $completedActivity = Activity::factory()->create([
            'title' => 'Completed Activity',
            'type' => 'call',
            'status' => 'terminé',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
        ]);

        $pendingActivity = Activity::factory()->create([
            'title' => 'Pending Activity',
            'type' => 'call',
            'status' => 'planifié',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->filterTable('status', 'terminé')
            ->assertCanSeeTableRecords([$completedActivity])
            ->assertCanNotSeeTableRecords([$pendingActivity]);
    }

    /** @test */
    public function it_can_filter_activities_by_date_range()
    {
        $recentActivity = Activity::factory()->create([
            'title' => 'Recent Activity',
            'type' => 'call',
            'status' => 'planifié',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
        ]);

        $oldActivity = Activity::factory()->create([
            'title' => 'Old Activity',
            'type' => 'call',
            'status' => 'planifié',
            'scheduled_at' => now()->subMonths(2),
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->filterTable('scheduled_at', [
                'from' => now()->subWeek()->toDateTimeString(),
                'until' => now()->addWeek()->toDateTimeString(),
            ])
            ->assertSee('Recent Activity')
            ->assertDontSee('Old Activity');
    }

    /** @test */
    public function it_can_filter_activities_by_subject_type()
    {
        $prospect = Prospect::factory()->create();
        $client = Client::factory()->create();

        $prospectActivity = Activity::factory()->create([
            'title' => 'Prospect Activity',
            'type' => 'call',
            'status' => 'planifié',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
        ]);

        $clientActivity = Activity::factory()->create([
            'title' => 'Client Activity',
            'type' => 'call',
            'status' => 'planifié',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
            'subject_type' => Client::class,
            'subject_id' => $client->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->filterTable('subject_type', Prospect::class)
            ->assertCanSeeTableRecords([$prospectActivity])
            ->assertCanNotSeeTableRecords([$clientActivity]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        Livewire::test(ActivityResource\Pages\CreateActivity::class)
            ->fillForm([
                'title' => '',
                'type' => '',
                'status' => '',
                'scheduled_at' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'title' => 'required',
                'type' => 'required',
                'status' => 'required',
                'scheduled_at' => 'required',
            ]);
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
    public function it_requires_permission_to_delete_activity()
    {
        $user = User::factory()->create(['role' => 'guest']);
        $this->actingAs($user);

        $activity = Activity::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Give view permission but not delete permission
        $user->givePermissionTo('activities.view');

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
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
        ]);

        $this->assertEquals($user->id, $activity->user->id);
        $this->assertEquals($prospect->id, $activity->subject->id);
        $this->assertInstanceOf(Prospect::class, $activity->subject);
    }

    /** @test */
    public function it_can_paginate_activities()
    {
        // Create 20 activities with different scheduled_at dates
        $activities = collect(range(1, 20))->map(function ($i) {
            return Activity::factory()->create([
                'user_id' => $this->user->id,
                'scheduled_at' => now()->addDays($i),
            ]);
        });

        $component = Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful();

        // Get the first 10 activities (sorted by scheduled_at desc)
        $firstPageActivities = $activities->sortByDesc('scheduled_at')->take(10);
        $component->assertCanSeeTableRecords($firstPageActivities);

        // Go to the second page
        $component->call('gotoPage', 2, 'page');

        // Get the next 10 activities (sorted by scheduled_at desc)
        $secondPageActivities = $activities->sortByDesc('scheduled_at')->skip(10)->take(10);
        $component->assertCanSeeTableRecords($secondPageActivities);
    }

    /** @test */
    public function it_can_sort_activities()
    {
        $oldActivity = Activity::factory()->create([
            'title' => 'Z Activity',
            'scheduled_at' => now()->subWeek(),
            'user_id' => $this->user->id,
        ]);

        $newActivity = Activity::factory()->create([
            'title' => 'A Activity',
            'scheduled_at' => now(),
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->sortTable('title')
            ->assertSee(['A Activity', 'Z Activity'], true)
            ->sortTable('title', 'desc')
            ->assertSee(['Z Activity', 'A Activity'], true);
    }
}
