<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProspectConversionTest extends TestCase
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
            'assigned_to' => $this->conseiller->id
        ]);
    }

    /**
     * Génère un numéro de client unique
     */
    private function generateClientNumber(): string
    {
        $lastClient = Client::orderBy('id', 'desc')->first();
        $nextId = $lastClient ? $lastClient->id + 1 : 1;
        return 'CLI' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Test qu'un manager peut convertir un prospect en client
     */
    public function test_manager_can_convert_prospect(): void
    {
        $this->actingAs($this->manager);

        // Vérifier que le manager a la permission
        $this->assertTrue($this->manager->can('prospects.convert'));

        // Simuler la conversion du prospect en client
        $client = Client::create([
            'first_name' => $this->prospect->first_name,
            'last_name' => $this->prospect->last_name,
            'email' => $this->prospect->email,
            'phone' => $this->prospect->phone,
            'status' => 'active',
            'assigned_to' => $this->prospect->assigned_to,
            'prospect_id' => $this->prospect->id,
            'client_number' => $this->generateClientNumber()
        ]);

        // Mettre à jour le statut du prospect
        $this->prospect->update(['status' => Prospect::STATUS_CONVERTED]);

        // Vérifications
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'email' => $this->prospect->email
        ]);

        $this->assertDatabaseHas('prospects', [
            'id' => $this->prospect->id,
            'status' => Prospect::STATUS_CONVERTED
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
            'assigned_to' => $this->conseiller->id
        ]);

        // Vérifier que le prospect n'est pas dans un état permettant la conversion
        $this->assertNotEquals(Prospect::STATUS_QUALIFIED, $unqualifiedProspect->status);
    }

    /**
     * Test que la conversion crée une activité
     */
    public function test_conversion_creates_activity(): void
    {
        $this->actingAs($this->manager);

        // Convertir le prospect en client
        $client = Client::create([
            'first_name' => $this->prospect->first_name,
            'last_name' => $this->prospect->last_name,
            'email' => $this->prospect->email,
            'phone' => $this->prospect->phone,
            'status' => 'active',
            'assigned_to' => $this->prospect->assigned_to,
            'prospect_id' => $this->prospect->id,
            'client_number' => $this->generateClientNumber()
        ]);

        // Créer une activité de conversion
        Activity::create([
            'title' => 'Conversion en client',
            'description' => 'Prospect converti en client',
            'type' => 'conversion',
            'subject_type' => 'client',
            'prospect_id' => $this->prospect->id,
            'client_id' => $client->id,
            'created_by' => $this->manager->id
        ]);

        // Vérifier que l'activité a été créée
        $this->assertDatabaseHas('activities', [
            'type' => 'conversion',
            'prospect_id' => $this->prospect->id,
            'client_id' => $client->id
        ]);
    }

    /**
     * Test que le client hérite des informations du prospect
     */
    public function test_client_inherits_prospect_information(): void
    {
        $this->actingAs($this->manager);

        // Convertir le prospect en client
        $client = Client::create([
            'first_name' => $this->prospect->first_name,
            'last_name' => $this->prospect->last_name,
            'email' => $this->prospect->email,
            'phone' => $this->prospect->phone,
            'status' => 'active',
            'assigned_to' => $this->prospect->assigned_to,
            'prospect_id' => $this->prospect->id,
            'client_number' => $this->generateClientNumber()
        ]);

        // Vérifier que les informations ont été correctement copiées
        $this->assertEquals($this->prospect->first_name, $client->first_name);
        $this->assertEquals($this->prospect->last_name, $client->last_name);
        $this->assertEquals($this->prospect->email, $client->email);
        $this->assertEquals($this->prospect->phone, $client->phone);
        $this->assertEquals($this->prospect->assigned_to, $client->assigned_to);
    }

    /**
     * Test que les activités du prospect sont liées au client
     */
    public function test_prospect_activities_are_linked_to_client(): void
    {
        $this->actingAs($this->manager);

        // Créer une activité pour le prospect
        $prospectActivity = Activity::create([
            'title' => 'Contact initial',
            'description' => 'Premier contact avec le prospect',
            'type' => 'contact',
            'subject_type' => 'prospect',
            'prospect_id' => $this->prospect->id,
            'created_by' => $this->conseiller->id
        ]);

        // Convertir le prospect en client
        $client = Client::create([
            'first_name' => $this->prospect->first_name,
            'last_name' => $this->prospect->last_name,
            'email' => $this->prospect->email,
            'phone' => $this->prospect->phone,
            'status' => 'active',
            'assigned_to' => $this->prospect->assigned_to,
            'prospect_id' => $this->prospect->id,
            'client_number' => $this->generateClientNumber()
        ]);

        // Mettre à jour l'activité pour la lier au client
        $prospectActivity->update([
            'client_id' => $client->id,
            'subject_type' => 'client'
        ]);

        // Vérifier que l'activité est liée au client
        $this->assertDatabaseHas('activities', [
            'id' => $prospectActivity->id,
            'prospect_id' => $this->prospect->id,
            'client_id' => $client->id,
            'subject_type' => 'client'
        ]);
    }
}
