<?php

namespace Tests\Feature\Business;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use App\Services\ProspectConversionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProspectConversionTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $conseiller;
    protected Prospect $prospect;
    protected ProspectConversionService $conversionService;

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

        // Créer un prospect approuvé
        $this->prospect = Prospect::factory()->create([
            'reference_number' => 'PROS-' . random_int(10000, 99999),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+33612345678',
            'birth_date' => '1990-01-01',
            'profession' => 'Developer',
            'education_level' => 'Master',
            'current_location' => 'Paris',
            'current_field' => 'IT',
            'desired_field' => 'Data Science',
            'desired_destination' => 'Canada',
            'emergency_contact' => json_encode([
                'name' => 'Jane Doe',
                'phone' => '+33612345679',
                'relationship' => 'Spouse',
            ]),
            'status' => Prospect::STATUS_APPROVED,
            'assigned_to' => $this->conseiller->id,
            'commercial_code' => 'COM001',
            'partner_id' => null,
        ]);

        $this->conversionService = new ProspectConversionService();
    }

    /** @test */
    public function manager_can_convert_prospect(): void
    {
        $this->actingAs($this->manager);

        // Vérifier que le manager a la permission
        $this->assertTrue($this->manager->can('prospects.convert'));

        // Données pour la conversion
        $clientData = [
            'passport_number' => 'PASS123456',
            'passport_expiry' => '2025-12-31',
            'travel_preferences' => [
                'preferred_airline' => 'Air France',
                'seat_preference' => 'window',
            ],
            'total_amount' => 5000.00,
        ];

        // Convertir le prospect
        $client = $this->conversionService->convertToClient($this->prospect, $clientData);

        // Vérifications
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'prospect_id' => $this->prospect->id,
            'passport_number' => 'PASS123456',
            'visa_status' => Client::VISA_STATUS_NOT_STARTED,
            'payment_status' => Client::PAYMENT_STATUS_PENDING,
            'total_amount' => 5000.00,
            'paid_amount' => 0,
        ]);

        $this->assertDatabaseHas('prospects', [
            'id' => $this->prospect->id,
            'status' => Prospect::STATUS_CONVERTED,
        ]);

        // Vérifier que le client a accès aux informations du prospect
        $this->assertEquals('John', $client->prospect->first_name);
        $this->assertEquals('Doe', $client->prospect->last_name);
        $this->assertEquals('john@example.com', $client->prospect->email);

        // Vérifier les libellés traduits
        $this->assertEquals('Non démarré', $client->getVisaStatusLabel());
        $this->assertEquals('En attente', $client->getPaymentStatusLabel());
        $this->assertEquals('Converti', $client->prospect->getStatusLabel());
    }

    /** @test */
    public function conseiller_cannot_convert_prospect(): void
    {
        $this->actingAs($this->conseiller);

        // Vérifier que le conseiller n'a pas la permission
        $this->assertFalse($this->conseiller->can('prospects.convert'));

        // Tenter de convertir le prospect
        $this->expectException(AuthorizationException::class);
        $this->conversionService->convertToClient($this->prospect);
    }

    /** @test */
    public function only_approved_prospects_can_be_converted(): void
    {
        $this->actingAs($this->manager);

        // Créer un prospect non approuvé
        $newProspect = Prospect::factory()->create([
            'status' => Prospect::STATUS_NEW,
            'assigned_to' => $this->conseiller->id,
        ]);

        // Vérifier le libellé du statut
        $this->assertEquals('Nouveau', $newProspect->getStatusLabel());

        // Tenter de convertir le prospect non approuvé
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Seuls les prospects approuvés peuvent être convertis en clients.');
        $this->conversionService->convertToClient($newProspect);
    }

    /** @test */
    public function conversion_creates_activity(): void
    {
        $this->actingAs($this->manager);

        // Convertir le prospect
        $client = $this->conversionService->convertToClient($this->prospect);

        // Vérifier qu'une activité de conversion a été créée
        $this->assertDatabaseHas('activities', [
            'type' => Activity::TYPE_CONVERSION,
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'status' => Activity::STATUS_COMPLETED,
            'created_by' => $this->manager->id,
        ]);

        // Récupérer l'activité et vérifier ses libellés
        $activity = Activity::where('subject_id', $client->id)
            ->where('type', Activity::TYPE_CONVERSION)
            ->first();

        $this->assertEquals('Conversion', $activity->getTypeLabel());
        $this->assertEquals('Terminé', $activity->getStatusLabel());
    }

    /** @test */
    public function client_number_is_generated_correctly(): void
    {
        $this->actingAs($this->manager);

        // Créer un premier client
        $client1 = $this->conversionService->convertToClient($this->prospect);

        // Créer un second prospect et le convertir
        $prospect2 = Prospect::factory()->create([
            'status' => Prospect::STATUS_APPROVED,
            'assigned_to' => $this->conseiller->id,
        ]);
        $client2 = $this->conversionService->convertToClient($prospect2);

        // Vérifier le format des numéros de client
        $this->assertMatchesRegularExpression('/^CLI\d{6}$/', $client1->client_number);
        $this->assertMatchesRegularExpression('/^CLI\d{6}$/', $client2->client_number);
        
        // Vérifier que le second numéro est plus grand que le premier
        $this->assertGreaterThan(
            intval(substr($client1->client_number, 3)),
            intval(substr($client2->client_number, 3))
        );
    }

    /** @test */
    public function client_inherits_prospect_information_through_relationship(): void
    {
        $this->actingAs($this->manager);

        // Convertir le prospect
        $client = $this->conversionService->convertToClient($this->prospect);

        // Vérifier que les informations sont accessibles via la relation
        $this->assertEquals($this->prospect->first_name, $client->prospect->first_name);
        $this->assertEquals($this->prospect->last_name, $client->prospect->last_name);
        $this->assertEquals($this->prospect->email, $client->prospect->email);
        $this->assertEquals($this->prospect->phone, $client->prospect->phone);
        $this->assertEquals($this->prospect->assigned_to, $client->prospect->assigned_to);

        // Vérifier l'accesseur fullName
        $this->assertEquals(
            "{$this->prospect->first_name} {$this->prospect->last_name}",
            $client->full_name
        );
    }
}
