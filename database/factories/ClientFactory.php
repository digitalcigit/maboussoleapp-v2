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
            'status' => $this->faker->randomElement([
                Client::STATUS_ACTIVE,
                Client::STATUS_INACTIVE,
                Client::STATUS_PENDING,
                Client::STATUS_ARCHIVED,
            ]),
            'assigned_to' => $prospect->assigned_to,
            'commercial_code' => $prospect->commercial_code,
            'partner_id' => $prospect->partner_id,
            'last_action_at' => $this->faker->dateTimeBetween('-1 month'),
            'contract_start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'contract_end_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'passport_number' => strtoupper($this->faker->bothify('??######')),
            'passport_expiry' => $this->faker->dateTimeBetween('+1 year', '+10 years'),
            'visa_status' => Client::VISA_STATUS_NOT_STARTED,
            'travel_preferences' => [
                'preferred_airline' => $this->faker->randomElement(['Air France', 'Air Canada', 'Swiss Air']),
                'seat_preference' => $this->faker->randomElement(['fenêtre', 'couloir', 'milieu']),
                'meal_preference' => $this->faker->randomElement(['standard', 'végétarien', 'halal', 'casher']),
                'baggage_preference' => $this->faker->numberBetween(1, 3) . ' bagage(s)'
            ],
            'payment_status' => Client::PAYMENT_STATUS_PENDING,
            'total_amount' => $this->faker->randomFloat(2, 3000, 15000),
            'paid_amount' => 0,
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }

    /**
     * État : visa en cours de traitement
     */
    public function visaInProgress(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'visa_status' => Client::VISA_STATUS_IN_PROGRESS,
            ];
        });
    }

    /**
     * État : visa obtenu
     */
    public function visaObtained(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'visa_status' => Client::VISA_STATUS_OBTAINED,
            ];
        });
    }

    /**
     * État : visa refusé
     */
    public function visaRejected(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'visa_status' => Client::VISA_STATUS_REJECTED,
            ];
        });
    }

    /**
     * État : paiement partiel
     */
    public function partiallyPaid(): self
    {
        return $this->state(function (array $attributes) {
            $total = $attributes['total_amount'] ?? 5000;
            return [
                'payment_status' => Client::PAYMENT_STATUS_PARTIAL,
                'paid_amount' => $total * 0.5,
            ];
        });
    }

    /**
     * État : paiement complet
     */
    public function fullyPaid(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => Client::PAYMENT_STATUS_COMPLETED,
                'paid_amount' => $attributes['total_amount'],
            ];
        });
    }
}
