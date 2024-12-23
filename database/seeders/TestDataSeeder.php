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
                    'reference_number' => 'PROS-' . Str::random(8),
                    'first_name' => "Prospect $i",
                    'last_name' => "Test",
                    'email' => "prospect$i@test.com",
                    'phone' => "+33612345678",
                    'status' => Prospect::STATUS_NEW,
                    'source' => 'site_web',
                    'assigned_to' => $advisor->id,
                    'partner_id' => $partners[array_rand($partners)]->id,
                    'commercial_code' => 'COM-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                ]);

                // Création d'activités pour chaque prospect
                Activity::create([
                    'title' => "Premier contact avec Prospect $i",
                    'user_id' => $advisor->id,
                    'subject_type' => Prospect::class,
                    'subject_id' => $prospect->id,
                    'type' => Activity::TYPE_CALL,
                    'status' => Activity::STATUS_COMPLETED,
                    'description' => "Discussion initiale sur les besoins",
                    'scheduled_at' => Carbon::now(),
                    'completed_at' => Carbon::now(),
                ]);
            }
        }

        // Création des clients de test
        foreach ($advisors as $advisor) {
            for ($i = 1; $i <= 3; $i++) {
                $client = Client::create([
                    'client_number' => 'CLI-' . Str::random(8),
                    'first_name' => "Client $i",
                    'last_name' => "Test",
                    'email' => "client$i@test.com",
                    'phone' => "+33612345678",
                    'status' => Client::STATUS_ACTIVE,
                    'assigned_to' => $advisor->id,
                    'partner_id' => $partners[array_rand($partners)]->id,
                    'commercial_code' => 'COM-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'payment_status' => Client::PAYMENT_STATUS_PENDING,
                    'total_amount' => 5000.00,
                    'paid_amount' => 0.00,
                ]);

                // Création d'activités pour chaque client
                Activity::create([
                    'title' => "Suivi Client $i",
                    'user_id' => $advisor->id,
                    'subject_type' => Client::class,
                    'subject_id' => $client->id,
                    'type' => Activity::TYPE_MEETING,
                    'status' => Activity::STATUS_PENDING,
                    'description' => "Point d'avancement mensuel",
                    'scheduled_at' => Carbon::now()->addDays(rand(1, 7)),
                    'completed_at' => null,
                ]);
            }
        }
    }
}
