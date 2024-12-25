<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnumColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_prospect_status_enum()
    {
        $prospect = Prospect::create([
            'status' => 'nouveau',
        ]);
        
        $this->assertEquals('nouveau', $prospect->status);
        
        // Test invalid value
        try {
            Prospect::create([
                'status' => 'invalid_status',
            ]);
            $this->fail('Should have thrown an exception for invalid enum value');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function test_client_status_enum()
    {
        $client = Client::create([
            'status' => 'actif',
            'payment_status' => 'en_attente',
        ]);
        
        $this->assertEquals('actif', $client->status);
        $this->assertEquals('en_attente', $client->payment_status);
    }

    public function test_activity_enums()
    {
        $activity = Activity::create([
            'status' => 'planifie',
            'type' => 'appel',
        ]);
        
        $this->assertEquals('planifie', $activity->status);
        $this->assertEquals('appel', $activity->type);
    }
}
