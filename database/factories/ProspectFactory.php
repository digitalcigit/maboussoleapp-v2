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
            'reference_number' => 'PROS-'.random_int(10000, 99999),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'birth_date' => $this->faker->date(),
            'profession' => $this->faker->jobTitle(),
            'education_level' => $this->faker->randomElement(['Bac', 'Bac+2', 'Licence', 'Master', 'Doctorat']),
            'desired_field' => $this->faker->randomElement(['IT', 'Finance', 'Marketing', 'RH', 'Santé']),
            'desired_destination' => $this->faker->randomElement(['Canada', 'France', 'Belgique', 'Suisse']),
            'emergency_contact' => [
                'name' => $this->faker->name(),
                'phone' => $this->faker->phoneNumber(),
                'relationship' => $this->faker->randomElement(['Conjoint(e)', 'Parent', 'Frère/Sœur', 'Ami(e)']),
            ],
            'documents' => [],
            'assigned_to' => User::factory(),
            'commercial_code' => 'COM'.random_int(100, 999),
            'partner_id' => null,
            'analysis_deadline' => $this->faker->dateTimeBetween('now', '+30 days'),
            'notes' => $this->faker->optional()->paragraph(),
            'converted_to_dossier' => false,
            'converted_at' => null,
            'dossier_reference' => null,
        ];
    }

    /**
     * État : prospect en analyse
     */
    public function analyzing(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'last_action_at' => now(),
            ];
        });
    }

    /**
     * État : prospect approuvé
     */
    public function approved(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'last_action_at' => now(),
            ];
        });
    }

    /**
     * État : prospect refusé
     */
    public function rejected(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'last_action_at' => now(),
            ];
        });
    }

    /**
     * État : prospect converti en client
     */
    public function converted(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'last_action_at' => now(),
            ];
        });
    }
}
