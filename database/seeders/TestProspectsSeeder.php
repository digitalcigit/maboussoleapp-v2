<?php

namespace Database\Seeders;

use App\Models\Prospect;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestProspectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = ['nouveau', 'qualifié', 'traitement', 'bloqué', 'converti'];
        $educationLevels = ['BAC', 'BAC+2', 'BAC+3', 'BAC+4', 'BAC+5'];
        $destinations = ['France', 'Canada', 'Belgique', 'Suisse', 'Maroc'];
        $fields = ['Informatique', 'Management', 'Marketing Digital', 'Finance', 'Commerce International'];
        
        $conseillers = User::role('conseiller')->get();

        for ($i = 1; $i <= 10; $i++) {
            $prospect = Prospect::create([
                'reference_number' => 'PROS' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'birth_date' => fake()->dateTimeBetween('-30 years', '-18 years'),
                'profession' => fake()->jobTitle(),
                'education_level' => fake()->randomElement($educationLevels),
                'desired_field' => fake()->randomElement($fields),
                'desired_destination' => fake()->randomElement($destinations),
                'emergency_contact' => [
                    'name' => fake()->name(),
                    'relationship' => fake()->randomElement(['Parent', 'Frère/Sœur', 'Conjoint', 'Ami']),
                    'phone' => fake()->phoneNumber(),
                ],
                'assigned_to' => $conseillers->isNotEmpty() ? $conseillers->random()->id : null,
                'commercial_code' => 'COM' . fake()->numberBetween(100, 999),
                'analysis_deadline' => fake()->dateTimeBetween('now', '+30 days'),
                'notes' => fake()->paragraph(),
                'current_status' => fake()->randomElement($statuses),
                'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
                'updated_at' => fake()->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}
