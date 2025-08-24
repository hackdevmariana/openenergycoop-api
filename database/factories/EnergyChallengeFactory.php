<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyChallenge>
 */
class EnergyChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('now', '+1 month');
        $endsAt = $this->faker->dateTimeBetween($startsAt, '+3 months');
        
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'type' => $this->faker->randomElement(['individual', 'colectivo']),
            'goal_kwh' => $this->faker->randomFloat(2, 10, 1000),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'reward_type' => $this->faker->randomElement(['symbolic', 'energy_donation', 'badge']),
            'reward_details' => [
                'points' => $this->faker->numberBetween(100, 1000),
                'description' => $this->faker->sentence(),
            ],
            'is_active' => $this->faker->boolean(80), // 80% de probabilidad de estar activo
        ];
    }

    /**
     * Indica que el desafío es individual
     */
    public function individual()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'individual',
            ];
        });
    }

    /**
     * Indica que el desafío es colectivo
     */
    public function collective()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'colectivo',
            ];
        });
    }

    /**
     * Indica que el desafío está activo
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
                'starts_at' => now()->subDays(rand(1, 10)),
                'ends_at' => now()->addDays(rand(1, 30)),
            ];
        });
    }

    /**
     * Indica que el desafío está próximo
     */
    public function upcoming()
    {
        return $this->state(function (array $attributes) {
            return [
                'starts_at' => now()->addDays(rand(1, 30)),
                'ends_at' => now()->addDays(rand(31, 60)),
            ];
        });
    }

    /**
     * Indica que el desafío ha terminado
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'starts_at' => now()->subDays(rand(31, 60)),
                'ends_at' => now()->subDays(rand(1, 30)),
            ];
        });
    }
}
