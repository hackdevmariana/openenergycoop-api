<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserChallengeProgress>
 */
class UserChallengeProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'challenge_id' => \App\Models\EnergyChallenge::factory(),
            'progress_kwh' => $this->faker->randomFloat(2, 0, 100),
            'completed_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indica que el progreso está completo
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * Indica que el progreso está en curso
     */
    public function inProgress()
    {
        return $this->state(function (array $attributes) {
            return [
                'completed_at' => null,
            ];
        });
    }

    /**
     * Indica que el progreso está cerca de completarse
     */
    public function nearCompletion()
    {
        return $this->state(function (array $attributes) {
            return [
                'progress_kwh' => $this->faker->randomFloat(2, 80, 95),
                'completed_at' => null,
            ];
        });
    }

    /**
     * Indica que el progreso está en sus inicios
     */
    public function justStarted()
    {
        return $this->state(function (array $attributes) {
            return [
                'progress_kwh' => $this->faker->randomFloat(2, 0, 20),
                'completed_at' => null,
            ];
        });
    }
}
