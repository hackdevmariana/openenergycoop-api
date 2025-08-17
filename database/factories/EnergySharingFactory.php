<?php

namespace Database\Factories;

use App\Models\EnergySharing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergySharing>
 */
class EnergySharingFactory extends Factory
{
    protected $model = EnergySharing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $energyAmount = $this->faker->randomFloat(2, 10, 1000);
        $pricePerKwh = $this->faker->randomFloat(4, 0.05, 0.30);
        $proposedAt = $this->faker->dateTimeBetween('-1 month', '-1 day');
        $sharingStart = $this->faker->dateTimeBetween('+1 hour', '+1 week');
        $sharingEnd = $this->faker->dateTimeBetween($sharingStart, '+1 day');

        return [
            'provider_user_id' => User::factory(),
            'consumer_user_id' => User::factory(),
            'sharing_code' => 'ES-' . $this->faker->unique()->numerify('######'),
            'title' => $this->faker->randomElement([
                'Excedente Solar Disponible',
                'Energía Eólica para Compartir',
                'Intercambio de Emergencia'
            ]),
            'sharing_type' => $this->faker->randomElement(['direct', 'community', 'marketplace']),
            'status' => $this->faker->randomElement(['proposed', 'accepted', 'active', 'completed']),
            'energy_amount_kwh' => $energyAmount,
            'energy_delivered_kwh' => 0,
            'energy_remaining_kwh' => $energyAmount,
            'is_renewable' => $this->faker->boolean(80),
            'sharing_start_datetime' => $sharingStart,
            'sharing_end_datetime' => $sharingEnd,
            'proposal_expiry_datetime' => $this->faker->dateTimeBetween('+30 minutes', $sharingStart),
            'duration_hours' => $this->faker->numberBetween(1, 12),
            'price_per_kwh' => $pricePerKwh,
            'total_amount' => $energyAmount * $pricePerKwh,
            'currency' => 'EUR',
            'payment_method' => $this->faker->randomElement(['credits', 'bank_transfer', 'energy_tokens']),
            'allows_partial_delivery' => $this->faker->boolean(60),
            'certified_green_energy' => $this->faker->boolean(70),
            'real_time_tracking' => $this->faker->boolean(50),
            'proposed_at' => $proposedAt,
        ];
    }

    /**
     * Indicate that the energy sharing is in proposed state.
     */
    public function proposed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'proposed',
            'accepted_at' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the energy sharing is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'accepted_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
            'started_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    /**
     * Indicate that the energy sharing is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $energyDelivered = $this->faker->randomFloat(2, $attributes['energy_amount_kwh'] * 0.8, $attributes['energy_amount_kwh']);
            
            return [
                'status' => 'completed',
                'accepted_at' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
                'started_at' => $this->faker->dateTimeBetween('-1 week', '-2 days'),
                'completed_at' => $this->faker->dateTimeBetween('-2 days', 'now'),
                'energy_delivered_kwh' => $energyDelivered,
                'energy_remaining_kwh' => $attributes['energy_amount_kwh'] - $energyDelivered,
                'quality_score' => $this->faker->randomFloat(1, 3, 5),
            ];
        });
    }
}