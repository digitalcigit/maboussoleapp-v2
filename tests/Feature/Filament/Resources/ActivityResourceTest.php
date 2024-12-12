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
        $this->setupPermissions();
    }

    /** @test */
    public function it_can_list_activities()
    {
        $activities = Activity::factory()->count(5)->create();

        Livewire::test(ActivityResource\Pages\ListActivities::class)
            ->assertSuccessful()
            ->assertTableRowExists([
                'type' => $activities[0]->type,
                'title' => $activities[0]->title,
            ]);
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

        Livewire::test(ActivityResource\Pages\EditActivity::class, [
            'record' => $activity->id,
        ])
            ->assertActionExists('delete')
            ->callAction('delete');

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
            ->assertCanSeeTableRecords([$callActivity])
            ->assertCanNotSeeTableRecords([$emailActivity]);
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
            ->filterTable('scheduled_at_from', now()->subWeek()->format('Y-m-d'))
            ->filterTable('scheduled_at_until', now()->addWeek()->format('Y-m-d'))
            ->assertCanSeeTableRecords([$recentActivity])
            ->assertCanNotSeeTableRecords([$oldActivity]);
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
}
