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
        return [
            'prospect_id' => Prospect::factory(),
            'client_number' => 'CLI-' . $this->faker->unique()->randomNumber(5),
            'passport_number' => strtoupper($this->faker->lexify('??')) . $this->faker->unique()->randomNumber(6),
            'passport_expiry' => $this->faker->dateTimeBetween('+1 year', '+10 years'),
            'visa_status' => $this->faker->randomElement([
                Client::VISA_STATUS_NOT_STARTED,
                Client::VISA_STATUS_IN_PROGRESS,
                Client::VISA_STATUS_OBTAINED,
                Client::VISA_STATUS_REJECTED,
            ]),
            'travel_preferences' => [
                'preferred_airline' => $this->faker->randomElement(['Air France', 'Air Canada', 'Swiss Air']),
                'seat_preference' => $this->faker->randomElement(['fenêtre', 'couloir', 'milieu']),
                'meal_preference' => $this->faker->randomElement(['standard', 'végétarien', 'halal', 'casher']),
                'baggage_preference' => $this->faker->randomElement(['1 bagage(s)', '2 bagage(s)', '3 bagage(s)']),
            ],
            'payment_status' => $this->faker->randomElement([
                Client::PAYMENT_STATUS_PENDING,
                Client::PAYMENT_STATUS_PARTIAL,
                Client::PAYMENT_STATUS_COMPLETED,
            ]),
            'total_amount' => $this->faker->randomFloat(2, 3000, 15000),
            'paid_amount' => function (array $attributes) {
                return $this->faker->randomFloat(2, 0, $attributes['total_amount']);
            },
            'status' => $this->faker->randomElement([
                Client::STATUS_ACTIVE,
                Client::STATUS_INACTIVE,
                Client::STATUS_PENDING,
                Client::STATUS_ARCHIVED,
            ]),
        ];
    }

    /**
     * Indicate that the client is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Client::STATUS_ACTIVE,
        ]);
    }

    /**
     * Indicate that the client has a pending payment.
     */
    public function pendingPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => Client::PAYMENT_STATUS_PENDING,
            'paid_amount' => 0,
        ]);
    }

    /**
     * Indicate that the client has not started visa process.
     */
    public function noVisa(): static
    {
        return $this->state(fn (array $attributes) => [
            'visa_status' => Client::VISA_STATUS_NOT_STARTED,
        ]);
    }
}
