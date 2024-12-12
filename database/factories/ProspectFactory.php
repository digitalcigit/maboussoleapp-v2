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
            'reference_number' => 'PROS-' . $this->faker->unique()->randomNumber(5),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'birth_date' => $this->faker->date(),
            'profession' => $this->faker->jobTitle(),
            'education_level' => $this->faker->randomElement(['Bac', 'Bac+2', 'Bac+3', 'Bac+4', 'Bac+5']),
            'current_location' => $this->faker->city(),
            'current_field' => $this->faker->randomElement(['IT', 'Finance', 'Marketing', 'Education', 'Healthcare']),
            'desired_field' => $this->faker->randomElement(['IT', 'Finance', 'Marketing', 'Education', 'Healthcare']),
            'desired_destination' => $this->faker->country(),
            'emergency_contact' => json_encode([
                'name' => $this->faker->name(),
                'relationship' => $this->faker->randomElement(['Parent', 'Spouse', 'Sibling']),
                'phone' => $this->faker->phoneNumber(),
            ]),
            'status' => $this->faker->randomElement(['new', 'analyzing', 'qualified', 'converted', 'rejected']),
            'assigned_to' => User::factory(),
            'commercial_code' => $this->faker->optional()->bothify('COM-####'),
            'partner_id' => null,
            'last_action_at' => $this->faker->dateTimeBetween('-1 month'),
            'analysis_deadline' => $this->faker->dateTimeBetween('now', '+1 month'),
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }
}
