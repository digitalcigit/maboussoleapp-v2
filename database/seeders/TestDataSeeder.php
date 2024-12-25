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
        for ($i = 1; $i <= 5; $i++) {
            $prospect = Prospect::create([
                'reference_number' => 'PROS' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => "Prospect",
                'last_name' => "Test $i",
                'email' => "prospect$i@test.com",
                'phone' => '+33612345678',
                'birth_date' => Carbon::now()->subYears(rand(20, 50)),
                'profession' => 'Profession Test',
                'education_level' => 'Bac+2',
                'current_location' => 'France',
                'current_field' => 'Test Field',
                'desired_field' => 'Desired Field',
                'desired_destination' => 'Canada',
                'emergency_contact' => [
                    'name' => 'Contact Test',
                    'phone' => '+33612345678',
                    'relationship' => 'Parent'
                ],
                'status' => Prospect::STATUS_NEW,
                'assigned_to' => $advisors[array_rand($advisors)]->id,
                'commercial_code' => 'COM' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'partner_id' => $partners[array_rand($partners)]->id,
            ]);

            // Création d'activités pour chaque prospect
            for ($j = 1; $j <= 3; $j++) {
                Activity::create([
                    'user_id' => $advisors[array_rand($advisors)]->id,
                    'subject_type' => Prospect::class,
                    'subject_id' => $prospect->id,
                    'type' => Activity::TYPE_NOTE,
                    'description' => "Note de test $j pour le prospect $i",
                    'scheduled_at' => Carbon::now()->addDays(rand(1, 30)),
                    'status' => Activity::STATUS_PENDING,
                    'created_by' => $advisors[array_rand($advisors)]->id,
                ]);
            }
        }

        // Création des clients de test
        for ($i = 1; $i <= 3; $i++) {
            $client = Client::create([
                'prospect_id' => Prospect::inRandomOrder()->first()->id,
                'client_number' => 'CLI' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'passport_number' => 'PASS' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'passport_expiry' => Carbon::now()->addYears(5),
                'visa_status' => Client::VISA_STATUS_NOT_STARTED,
                'travel_preferences' => [
                    'destination' => 'Canada',
                    'budget' => rand(5000, 15000),
                    'duration' => '2 ans'
                ],
                'payment_status' => Client::PAYMENT_STATUS_PENDING,
                'total_amount' => rand(5000, 15000),
                'paid_amount' => 0,
                'status' => Client::STATUS_ACTIVE,
            ]);

            // Création d'activités pour chaque client
            for ($j = 1; $j <= 3; $j++) {
                Activity::create([
                    'user_id' => $advisors[array_rand($advisors)]->id,
                    'subject_type' => Client::class,
                    'subject_id' => $client->id,
                    'type' => Activity::TYPE_NOTE,
                    'description' => "Note de test $j pour le client $i",
                    'scheduled_at' => Carbon::now()->addDays(rand(1, 30)),
                    'status' => Activity::STATUS_PENDING,
                    'created_by' => $advisors[array_rand($advisors)]->id,
                ]);
            }
        }
    }
}
