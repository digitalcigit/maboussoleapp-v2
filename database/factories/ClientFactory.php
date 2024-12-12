<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Prospect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prospect = Prospect::factory()->create();

        return [
            'prospect_id' => $prospect->id,
            'client_number' => 'CLI-' . $this->faker->unique()->randomNumber(5),
            'first_name' => $prospect->first_name,
            'last_name' => $prospect->last_name,
            'email' => $prospect->email,
            'phone' => $prospect->phone,
            'birth_date' => $prospect->birth_date,
            'profession' => $prospect->profession,
            'education_level' => $prospect->education_level,
            'current_location' => $prospect->current_location,
            'current_field' => $prospect->current_field,
            'desired_field' => $prospect->desired_field,
            'desired_destination' => $prospect->desired_destination,
            'emergency_contact' => $prospect->emergency_contact,
            'status' => $this->faker->randomElement(['active', 'inactive', 'completed']),
            'assigned_to' => $prospect->assigned_to,
            'commercial_code' => $prospect->commercial_code,
            'partner_id' => $prospect->partner_id,
            'last_action_at' => $this->faker->dateTimeBetween('-1 month'),
            'contract_start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'contract_end_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'passport_number' => $this->faker->bothify('P#####???'),
            'passport_expiry' => $this->faker->dateTimeBetween('+1 year', '+10 years'),
            'visa_status' => $this->faker->randomElement(['not_started', 'in_progress', 'obtained', 'rejected']),
            'travel_preferences' => json_encode([
                'preferred_airline' => $this->faker->randomElement(['Air France', 'Emirates', 'Turkish Airlines']),
                'meal_preference' => $this->faker->randomElement(['Regular', 'Vegetarian', 'Halal']),
                'seat_preference' => $this->faker->randomElement(['Window', 'Aisle', 'No Preference']),
            ]),
            'payment_status' => $this->faker->randomElement(['pending', 'partial', 'completed']),
            'total_amount' => $this->faker->numberBetween(5000, 20000),
            'paid_amount' => function (array $attributes) {
                return $attributes['payment_status'] === 'completed' 
                    ? $attributes['total_amount']
                    : ($attributes['payment_status'] === 'partial' 
                        ? $this->faker->numberBetween(1000, $attributes['total_amount'])
                        : 0);
            },
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }
}
