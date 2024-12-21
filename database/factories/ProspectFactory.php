<?php

namespace Database\Factories;

use App\Models\Prospect;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prospect>
 */
class ProspectFactory extends Factory
{
    protected $model = Prospect::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_number' => 'PROS-' . fake()->unique()->numberBetween(10000, 99999),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'birth_date' => fake()->date(),
            'profession' => fake()->jobTitle(),
            'education_level' => fake()->randomElement(['Bac', 'Bac+2', 'Bac+3', 'Bac+4', 'Bac+5']),
            'current_location' => fake()->city(),
            'current_field' => fake()->randomElement(['IT', 'Finance', 'Marketing', 'Education', 'Healthcare']),
            'desired_field' => fake()->randomElement(['IT', 'Finance', 'Marketing', 'Education', 'Healthcare']),
            'desired_destination' => fake()->country(),
            'emergency_contact' => json_encode([
                'name' => fake()->name(),
                'relationship' => fake()->randomElement(['Parent', 'Spouse', 'Sibling']),
                'phone' => fake()->phoneNumber(),
            ]),
            'status' => fake()->randomElement(['nouveau', 'en_cours', 'converti', 'rejeté', 'autre']),
            'assigned_to' => User::factory(),
            'commercial_code' => fake()->optional()->bothify('COM-####'),
            'partner_id' => null,
            'last_action_at' => fake()->dateTimeBetween('-1 month'),
            'analysis_deadline' => fake()->dateTimeBetween('now', '+1 month'),
            'created_at' => fake()->dateTimeBetween('-1 year'),
            'updated_at' => fake()->dateTimeBetween('-1 month'),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
