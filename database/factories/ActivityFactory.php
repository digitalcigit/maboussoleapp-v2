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
            'status' => Activity::STATUS_PENDING,
            'created_by' => User::factory(),
        ];
    }

    /**
     * État : activité en cours
     */
    public function inProgress(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Activity::STATUS_IN_PROGRESS,
                'scheduled_at' => now(),
            ];
        });
    }

    /**
     * État : activité terminée
     */
    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            $scheduledAt = $attributes['scheduled_at'] ?? now()->subHour();

            return [
                'status' => Activity::STATUS_COMPLETED,
                'scheduled_at' => $scheduledAt,
                'completed_at' => $scheduledAt->addHour(),
            ];
        });
    }

    /**
     * État : activité annulée
     */
    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Activity::STATUS_CANCELLED,
                'completed_at' => now(),
            ];
        });
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
            ];
        });
    }

    /**
     * État : réunion
     */
    public function meeting(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Activity::TYPE_MEETING,
                'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 week'),
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
            ];
        });
    }

    /**
     * État : conversion
     */
    public function conversion(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Activity::TYPE_CONVERSION,
                'status' => Activity::STATUS_COMPLETED,
                'completed_at' => now(),
            ];
        });
    }
}
