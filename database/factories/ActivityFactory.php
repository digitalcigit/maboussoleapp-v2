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
        $faker = $this->faker;
        $user = User::factory()->create();
        $prospect = Prospect::factory()->create();

        return [
            'title' => $faker->sentence(),
            'description' => $faker->text(),
            'type' => $faker->randomElement([
                Activity::TYPE_CALL,
                Activity::TYPE_EMAIL,
                Activity::TYPE_MEETING,
                Activity::TYPE_NOTE,
                Activity::TYPE_TASK,
            ]),
            'status' => $faker->randomElement([
                Activity::STATUS_PENDING,
                Activity::STATUS_IN_PROGRESS,
                Activity::STATUS_COMPLETED,
                Activity::STATUS_CANCELLED,
            ]),
            'scheduled_at' => $faker->dateTimeBetween('now', '+1 month'),
            'completed_at' => $faker->optional()->dateTimeBetween('-1 month', 'now'),
            'user_id' => $user->id,
            'created_by' => User::factory(),
            'subject_type' => Prospect::class,
            'subject_id' => $prospect->id,
            'created_at' => $faker->dateTimeBetween('-1 year', '-1 month'),
            'updated_at' => $faker->dateTimeBetween('-1 month', 'now'),
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
