<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Création des conseillers de test
        $advisors = [];
        for ($i = 1; $i <= 3; $i++) {
            $advisor = User::create([
                'name' => "Conseiller Test $i",
                'email' => "conseiller$i@maboussole-crm.com",
                'password' => Hash::make('password'),
            ]);
            $advisor->assignRole('conseiller');
            $advisors[] = $advisor;
        }

        // Création des partenaires de test
        $partners = [];
        for ($i = 1; $i <= 2; $i++) {
            $partner = User::create([
                'name' => "Partenaire Test $i",
                'email' => "partenaire$i@maboussole-crm.com",
                'password' => Hash::make('password'),
            ]);
            $partner->assignRole('partenaire');
            $partners[] = $partner;
        }

        // Création des commerciaux de test
        $salespeople = [];
        for ($i = 1; $i <= 2; $i++) {
            $salesperson = User::create([
                'name' => "Commercial Test $i",
                'email' => "commercial$i@maboussole-crm.com",
                'password' => Hash::make('password'),
            ]);
            $salesperson->assignRole('commercial');
            $salespeople[] = $salesperson;
        }

        // Création des managers de test
        $managers = [];
        for ($i = 1; $i <= 2; $i++) {
            $manager = User::create([
                'name' => "Manager Test $i",
                'email' => "manager$i@maboussole-crm.com",
                'password' => Hash::make('password'),
            ]);
            $manager->assignRole('manager');
            $managers[] = $manager;
        }

        // Création des prospects de test
        foreach ($advisors as $advisor) {
            for ($i = 1; $i <= 5; $i++) {
                $prospect = Prospect::create([
                    'name' => "Prospect Test $i",
                    'email' => "prospect$i@test.com",
                    'phone' => "+33612345678",
                    'birth_date' => Carbon::now()->subYears(rand(20, 50)),
                    'profession' => 'Profession Test',
                    'education_level' => 'Bac+' . rand(2, 5),
                    'current_location' => 'France',
                    'current_field' => 'Test Field',
                    'desired_field' => 'Desired Field',
                    'desired_destination' => 'Canada',
                    'emergency_contact' => json_encode([
                        'name' => 'Contact Test',
                        'phone' => '+33612345678',
                        'relationship' => 'Parent'
                    ]),
                    'status' => Prospect::STATUS_NEW,
                    'created_by' => $advisor->id,
                ]);

                // Création d'activités pour chaque prospect
                Activity::create([
                    'title' => "Premier contact avec Prospect $i",
                    'description' => "Discussion initiale sur les besoins",
                    'type' => Activity::TYPE_CALL,
                    'status' => Activity::STATUS_COMPLETED,
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addHour(),
                    'prospect_id' => $prospect->id,
                    'created_by' => $advisor->id,
                ]);
            }
        }

        // Création des clients de test
        foreach ($advisors as $advisor) {
            for ($i = 1; $i <= 3; $i++) {
                $client = Client::create([
                    'name' => "Client Test $i",
                    'email' => "client$i@test.com",
                    'phone' => "+33612345678",
                    'address' => "123 Rue Test",
                    'city' => "Ville Test",
                    'postal_code' => "75000",
                    'country' => "France",
                    'notes' => "Notes de test pour le client $i",
                    'status' => Client::STATUS_ACTIVE,
                    'contract_start_date' => Carbon::now(),
                    'contract_end_date' => Carbon::now()->addYear(),
                    'created_by' => $advisor->id,
                ]);

                // Création d'activités pour chaque client
                Activity::create([
                    'title' => "Suivi Client $i",
                    'description' => "Point mensuel de suivi",
                    'type' => Activity::TYPE_MEETING,
                    'status' => Activity::STATUS_PENDING,
                    'start_date' => Carbon::now()->addDays(rand(1, 30)),
                    'end_date' => Carbon::now()->addDays(rand(1, 30))->addHour(),
                    'client_id' => $client->id,
                    'created_by' => $advisor->id,
                ]);
            }
        }
    }
}
