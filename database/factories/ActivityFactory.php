<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use App\Models\Prospect;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subject = $this->faker->randomElement([
            Prospect::factory()->create(),
            Client::factory()->create(),
        ]);

        $user = User::factory()->create();

        return [
            'title' => $this->faker->sentence(),
            'user_id' => $user->id,
            'created_by' => $user->id,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'type' => $this->faker->randomElement(['call', 'email', 'meeting', 'note', 'task']),
            'status' => $this->faker->randomElement(['planifié', 'en_cours', 'terminé', 'annulé']),
            'description' => $this->faker->paragraph(),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }

    /**
     * Configure the model factory to create an activity for a prospect.
     */
    public function forProspect(Prospect $prospect = null): Factory
    {
        return $this->state(function (array $attributes) use ($prospect) {
            return [
                'subject_type' => Prospect::class,
                'subject_id' => $prospect ? $prospect->id : Prospect::factory(),
            ];
        });
    }

    /**
     * Configure the model factory to create an activity for a client.
     */
    public function forClient(Client $client = null): Factory
    {
        return $this->state(function (array $attributes) use ($client) {
            return [
                'subject_type' => Client::class,
                'subject_id' => $client ? $client->id : Client::factory(),
            ];
        });
    }
}
