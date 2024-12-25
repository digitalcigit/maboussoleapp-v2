<?php

namespace Tests\Feature\Integration;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $conseiller;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer les utilisateurs nécessaires
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->conseiller = User::factory()->create(['role' => 'conseiller']);
        
        // Activer le fake des notifications
        Notification::fake();
        Event::fake();
    }

    /** @test */
    public function complete_prospect_to_client_workflow()
    {
        // 1. Création d'un prospect par un manager
        $prospectData = Prospect::factory()->raw();
        $response = $this->actingAs($this->manager)
            ->post('/admin/prospects', $prospectData);
        
        $response->assertStatus(200);
        $prospect = Prospect::latest()->first();
        
        // 2. Attribution à un conseiller
        $response = $this->actingAs($this->manager)
            ->patch("/admin/prospects/{$prospect->id}", [
                'user_id' => $this->conseiller->id,
                'status' => 'assigned',
            ]);
        
        $response->assertStatus(200);
        
        // 3. Ajout d'une activité par le conseiller
        $activityData = Activity::factory()->raw([
            'prospect_id' => $prospect->id,
        ]);
        
        $response = $this->actingAs($this->conseiller)
            ->post('/admin/activities', $activityData);
        
        $response->assertStatus(200);
        
        // 4. Qualification du prospect
        $response = $this->actingAs($this->conseiller)
            ->patch("/admin/prospects/{$prospect->id}", [
                'status' => 'qualified',
            ]);
        
        $response->assertStatus(200);
        
        // 5. Conversion en client
        $response = $this->actingAs($this->manager)
            ->post("/admin/prospects/{$prospect->id}/convert", [
                'type' => 'standard',
            ]);
        
        $response->assertStatus(200);
        
        // Vérifications finales
        $this->assertDatabaseHas('clients', [
            'prospect_id' => $prospect->id,
        ]);
        
        // Vérifier que les événements ont été émis
        Event::assertDispatched('ProspectAssigned');
        Event::assertDispatched('ProspectQualified');
        Event::assertDispatched('ProspectConverted');
        
        // Vérifier que les notifications ont été envoyées
        Notification::assertSentTo(
            $this->conseiller,
            'ProspectAssignedNotification'
        );
    }

    /** @test */
    public function activity_notifications_workflow()
    {
        $prospect = Prospect::factory()->create([
            'user_id' => $this->conseiller->id,
        ]);
        
        // Créer une activité qui devrait déclencher une notification
        $activityData = Activity::factory()->raw([
            'prospect_id' => $prospect->id,
            'type' => 'important_meeting',
        ]);
        
        $response = $this->actingAs($this->conseiller)
            ->post('/admin/activities', $activityData);
        
        $response->assertStatus(200);
        
        // Vérifier que la notification a été envoyée au manager
        Notification::assertSentTo(
            $this->manager,
            'ImportantActivityNotification'
        );
    }

    /** @test */
    public function prospect_status_transition_validation()
    {
        $prospect = Prospect::factory()->create([
            'status' => 'new',
        ]);
        
        // Tentative de qualification sans attribution
        $response = $this->actingAs($this->conseiller)
            ->patch("/admin/prospects/{$prospect->id}", [
                'status' => 'qualified',
            ]);
        
        $response->assertStatus(422);
        
        // Attribution correcte
        $response = $this->actingAs($this->manager)
            ->patch("/admin/prospects/{$prospect->id}", [
                'user_id' => $this->conseiller->id,
                'status' => 'assigned',
            ]);
        
        $response->assertStatus(200);
        
        // Maintenant la qualification devrait fonctionner
        $response = $this->actingAs($this->conseiller)
            ->patch("/admin/prospects/{$prospect->id}", [
                'status' => 'qualified',
            ]);
        
        $response->assertStatus(200);
    }
}
