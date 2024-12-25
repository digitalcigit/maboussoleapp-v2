<?php

namespace Tests\Feature\Business;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProspectToClientConversionTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $conseiller;

    protected Prospect $prospect;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Créer un manager
        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        // Créer un conseiller
        $this->conseiller = User::factory()->create();
        $this->conseiller->assignRole('conseiller');

        // Créer un prospect qualifié
        $this->prospect = Prospect::factory()->create([
            'status' => Prospect::STATUS_QUALIFIED,
            'assigned_to' => $this->conseiller->id,
        ]);
    }

    /**
     * Génère un numéro de client unique
     */
    private function generateClientNumber(): string
    {
        $lastClient = Client::orderBy('id', 'desc')->first();
        $nextId = $lastClient ? $lastClient->id + 1 : 1;

        return 'CLI'.str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Test qu'un manager peut convertir un prospect en client
     */
    public function test_manager_can_convert_prospect(): void
    {
        $this->actingAs($this->manager);

        // Vérifier que le manager a la permission
        $this->assertTrue($this->manager->can('prospects.convert'));

        // Convertir le prospect en client
        $client = $this->prospect->convertToClient($this->manager);

        // Vérifications
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'email' => $this->prospect->email,
        ]);

        $this->assertDatabaseHas('prospects', [
            'id' => $this->prospect->id,
            'status' => Prospect::STATUS_CONVERTED,
        ]);

        $this->assertDatabaseHas('activities', [
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'title' => 'Conversion en client',
            'user_id' => $this->manager->id,
        ]);
    }

    /**
     * Test qu'un conseiller ne peut pas convertir un prospect en client
     */
    public function test_conseiller_cannot_convert_prospect(): void
    {
        $this->actingAs($this->conseiller);

        // Vérifier que le conseiller n'a pas la permission
        $this->assertFalse($this->conseiller->can('prospects.convert'));

        // Tenter de convertir le prospect
        $this->expectException(\Exception::class);
        $this->prospect->convertToClient($this->conseiller);
    }

    /**
     * Test qu'un prospect doit être qualifié avant d'être converti
     */
    public function test_only_qualified_prospects_can_be_converted(): void
    {
        $this->actingAs($this->manager);

        // Créer un prospect non qualifié
        $unqualifiedProspect = Prospect::factory()->create([
            'status' => Prospect::STATUS_NEW,
            'assigned_to' => $this->conseiller->id,
        ]);

        // Tenter de convertir le prospect non qualifié
        $this->expectException(\Exception::class);
        $unqualifiedProspect->convertToClient($this->manager);
    }

    /**
     * Test que le client hérite des informations du prospect
     */
    public function test_client_inherits_prospect_information(): void
    {
        $this->actingAs($this->manager);

        // Convertir le prospect en client
        $client = $this->prospect->convertToClient($this->manager);

        // Vérifier que les informations ont été correctement copiées
        $this->assertEquals($this->prospect->first_name, $client->first_name);
        $this->assertEquals($this->prospect->last_name, $client->last_name);
        $this->assertEquals($this->prospect->email, $client->email);
        $this->assertEquals($this->prospect->phone, $client->phone);
        $this->assertEquals($this->prospect->assigned_to, $client->assigned_to);
    }

    /**
     * Test que les activités sont correctement créées et liées
     */
    public function test_activities_are_properly_linked(): void
    {
        $this->actingAs($this->manager);

        // Créer une activité pour le prospect
        $prospectActivity = Activity::create([
            'title' => 'Premier contact',
            'subject_type' => Prospect::class,
            'subject_id' => $this->prospect->id,
            'type' => 'note',
            'description' => 'Premier contact avec le prospect',
            'user_id' => $this->conseiller->id,
            'status' => 'terminé',
        ]);

        // Convertir le prospect en client
        $client = $this->prospect->convertToClient($this->manager);

        // Vérifier que les activités sont correctement liées
        $this->assertDatabaseHas('activities', [
            'id' => $prospectActivity->id,
            'subject_type' => Prospect::class,
            'subject_id' => $this->prospect->id,
            'title' => 'Premier contact',
        ]);

        $this->assertDatabaseHas('activities', [
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'title' => 'Conversion en client',
            'user_id' => $this->manager->id,
        ]);
    }
}
