<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        $subject = $this->faker->randomElement([
            Prospect::factory()->create(),
            Client::factory()->create(),
        ]);

        return [
            'user_id' => User::factory(),
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'type' => $this->faker->randomElement([
                Activity::TYPE_NOTE,
                Activity::TYPE_CALL,
                Activity::TYPE_EMAIL,
                Activity::TYPE_MEETING,
                Activity::TYPE_DOCUMENT,
            ]),
            'description' => $this->faker->paragraph(),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'completed_at' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * État : note
     */
    public function note(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Activity::TYPE_NOTE,
                'scheduled_at' => null,
            ];
        });
    }

    /**
     * État : appel
     */
    public function call(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Activity::TYPE_CALL,
                'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            ];
        });
    }

    /**
     * État : email
     */
    public function email(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Activity::TYPE_EMAIL,
                'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            ];
        });
    }

    /**
     * État : rendez-vous
     */
    public function meeting(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Activity::TYPE_MEETING,
                'scheduled_at' => $this->faker->dateTimeBetween('now', '+2 weeks'),
            ];
        });
    }

    /**
     * État : document
     */
    public function document(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Activity::TYPE_DOCUMENT,
                'scheduled_at' => null,
            ];
        });
    }
}
