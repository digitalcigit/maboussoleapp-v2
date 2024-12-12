<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProspectResource;
use App\Models\Prospect;
use App\Models\User;
use App\Models\Activity;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTestPermissions;

class ProspectResourceTest extends TestCase
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
    public function it_can_list_prospects()
    {
        $prospects = Prospect::factory()->count(5)->create();

        $response = $this->get(ProspectResource::getUrl('index'));

        $response->assertSuccessful();
        $response->assertSee($prospects[0]->first_name);
    }

    /** @test */
    public function it_can_create_prospect()
    {
        $newProspect = Prospect::factory()->make();

        $response = $this->post(ProspectResource::getUrl('create'), [
            'reference_number' => $newProspect->reference_number,
            'first_name' => $newProspect->first_name,
            'last_name' => $newProspect->last_name,
            'email' => $newProspect->email,
            'phone' => $newProspect->phone,
            'address' => $newProspect->address,
            'source' => $newProspect->source,
            'status' => $newProspect->status,
            'assigned_to' => $newProspect->assigned_to,
            'notes' => $newProspect->notes,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('prospects', [
            'email' => $newProspect->email,
            'reference_number' => $newProspect->reference_number,
        ]);
    }

    /** @test */
    public function it_can_edit_prospect()
    {
        $prospect = Prospect::factory()->create();
        $updatedData = Prospect::factory()->make();

        $response = $this->put(ProspectResource::getUrl('edit', ['record' => $prospect]), [
            'first_name' => $updatedData->first_name,
            'last_name' => $updatedData->last_name,
            'email' => $updatedData->email,
            'phone' => $updatedData->phone,
            'address' => $updatedData->address,
            'source' => $updatedData->source,
            'status' => $updatedData->status,
            'assigned_to' => $updatedData->assigned_to,
            'notes' => $updatedData->notes,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('prospects', [
            'id' => $prospect->id,
            'email' => $updatedData->email,
        ]);
    }

    /** @test */
    public function it_can_delete_prospect()
    {
        $prospect = Prospect::factory()->create();

        $response = $this->delete(ProspectResource::getUrl('delete', ['record' => $prospect]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('prospects', [
            'id' => $prospect->id,
        ]);
    }

    /** @test */
    public function it_can_view_prospect_details()
    {
        $prospect = Prospect::factory()->create();
        Activity::factory()->count(3)->create([
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
        ]);

        $response = $this->get(ProspectResource::getUrl('edit', ['record' => $prospect]));

        $response->assertSuccessful();
        $response->assertSee($prospect->reference_number);
        $response->assertSee($prospect->first_name);
        $response->assertSee($prospect->last_name);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post(ProspectResource::getUrl('create'), []);

        $response->assertSessionHasErrors([
            'reference_number',
            'first_name',
            'last_name',
            'email',
            'phone',
            'status',
        ]);
    }

    /** @test */
    public function it_validates_unique_reference_number()
    {
        $existingProspect = Prospect::factory()->create();

        $response = $this->post(ProspectResource::getUrl('create'), [
            'reference_number' => $existingProspect->reference_number,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'status' => 'new',
        ]);

        $response->assertSessionHasErrors(['reference_number']);
    }

    /** @test */
    public function it_can_filter_prospects_by_status()
    {
        $newProspect = Prospect::factory()->create(['status' => 'new']);
        $qualifiedProspect = Prospect::factory()->create(['status' => 'qualified']);

        $response = $this->get(ProspectResource::getUrl('index', [
            'filter' => [
                'status' => 'new',
            ],
        ]));

        $response->assertSuccessful();
        $response->assertSee($newProspect->reference_number);
        $response->assertDontSee($qualifiedProspect->reference_number);
    }

    /** @test */
    public function it_can_filter_prospects_by_source()
    {
        $websiteProspect = Prospect::factory()->create(['source' => 'website']);
        $referralProspect = Prospect::factory()->create(['source' => 'referral']);

        $response = $this->get(ProspectResource::getUrl('index', [
            'filter' => [
                'source' => 'website',
            ],
        ]));

        $response->assertSuccessful();
        $response->assertSee($websiteProspect->reference_number);
        $response->assertDontSee($referralProspect->reference_number);
    }

    /** @test */
    public function it_can_search_prospects()
    {
        $prospect1 = Prospect::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $prospect2 = Prospect::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        $response = $this->get(ProspectResource::getUrl('index', [
            'search' => 'John Doe',
        ]));

        $response->assertSuccessful();
        $response->assertSee($prospect1->reference_number);
        $response->assertDontSee($prospect2->reference_number);
    }

    /** @test */
    public function it_can_sort_prospects()
    {
        $prospect1 = Prospect::factory()->create(['created_at' => now()->subDays(2)]);
        $prospect2 = Prospect::factory()->create(['created_at' => now()]);

        $response = $this->get(ProspectResource::getUrl('index', [
            'sort' => 'created_at',
            'direction' => 'desc',
        ]));

        $response->assertSuccessful();
        $response->assertSeeInOrder([
            $prospect2->reference_number,
            $prospect1->reference_number,
        ]);
    }

    /** @test */
    public function it_can_convert_prospect_to_client()
    {
        $prospect = Prospect::factory()->create();

        $response = $this->post(ProspectResource::getUrl('convert', ['record' => $prospect]), [
            'client_number' => 'CLI001',
            'passport_number' => 'PASS123',
            'passport_expiry' => now()->addYears(5)->format('Y-m-d'),
            'total_amount' => 1000,
            'paid_amount' => 500,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'prospect_id' => $prospect->id,
            'client_number' => 'CLI001',
            'first_name' => $prospect->first_name,
            'last_name' => $prospect->last_name,
            'email' => $prospect->email,
            'phone' => $prospect->phone,
        ]);
    }
}
