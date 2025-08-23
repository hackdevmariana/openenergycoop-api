<?php

namespace Database\Factories;

use App\Models\EnergyTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnergyTransferFactory extends Factory
{
    protected $model = EnergyTransfer::class;

    public function definition(): array
    {
        $transferAmountKwh = $this->faker->randomFloat(2, 100, 10000);
        $lossPercentage = $this->faker->randomFloat(2, 0, 10);
        $lossAmountKwh = ($transferAmountKwh * $lossPercentage) / 100;
        $netTransferAmountKwh = $transferAmountKwh - $lossAmountKwh;
        $efficiencyPercentage = 100 - $lossPercentage;
        
        $scheduledStartTime = $this->faker->dateTimeBetween('now', '+1 month');
        $scheduledEndTime = (clone $scheduledStartTime)->modify('+' . $this->faker->numberBetween(1, 24) . ' hours');
        $durationHours = $scheduledStartTime->diffInHours($scheduledEndTime);
        
        $costPerKwh = $this->faker->randomFloat(4, 0.05, 0.50);
        $totalCost = $transferAmountKwh * $costPerKwh;

        return [
            'transfer_number' => 'TRF-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'transfer_type' => $this->faker->randomElement(array_keys(EnergyTransfer::getTransferTypes())),
            'status' => $this->faker->randomElement(array_keys(EnergyTransfer::getStatuses())),
            'priority' => $this->faker->randomElement(array_keys(EnergyTransfer::getPriorities())),
            'source_id' => $this->faker->numberBetween(1, 100),
            'source_type' => $this->faker->randomElement(['App\\Models\\User', 'App\\Models\\Company', 'App\\Models\\EnergyInstallation']),
            'destination_id' => $this->faker->numberBetween(1, 100),
            'destination_type' => $this->faker->randomElement(['App\\Models\\User', 'App\\Models\\Company', 'App\\Models\\EnergyInstallation']),
            'source_meter_id' => $this->faker->optional()->numberBetween(1, 100),
            'destination_meter_id' => $this->faker->optional()->numberBetween(1, 100),
            'transfer_amount_kwh' => $transferAmountKwh,
            'transfer_amount_mwh' => $transferAmountKwh / 1000,
            'transfer_rate_kw' => $this->faker->randomFloat(2, 10, 1000),
            'transfer_rate_mw' => $this->faker->randomFloat(2, 0.01, 1),
            'transfer_unit' => $this->faker->randomElement(['kWh', 'MWh', 'GWh']),
            'scheduled_start_time' => $scheduledStartTime,
            'scheduled_end_time' => $scheduledEndTime,
            'actual_start_time' => $this->faker->optional()->dateTimeBetween($scheduledStartTime, $scheduledEndTime),
            'actual_end_time' => $this->faker->optional()->dateTimeBetween($scheduledStartTime, $scheduledEndTime),
            'completion_time' => $this->faker->optional()->dateTimeBetween($scheduledStartTime, $scheduledEndTime),
            'duration_hours' => $durationHours,
            'efficiency_percentage' => $efficiencyPercentage,
            'loss_percentage' => $lossPercentage,
            'loss_amount_kwh' => $lossAmountKwh,
            'net_transfer_amount_kwh' => $netTransferAmountKwh,
            'net_transfer_amount_mwh' => $netTransferAmountKwh / 1000,
            'cost_per_kwh' => $costPerKwh,
            'total_cost' => $totalCost,
            'currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP']),
            'exchange_rate' => $this->faker->randomFloat(6, 0.8, 1.2),
            'transfer_method' => $this->faker->randomElement(['direct', 'grid', 'storage', 'virtual']),
            'transfer_medium' => $this->faker->randomElement(['electrical', 'battery', 'hydrogen', 'thermal']),
            'transfer_protocol' => $this->faker->randomElement(['standard', 'secure', 'fast', 'eco']),
            'is_automated' => $this->faker->boolean(80),
            'requires_approval' => $this->faker->boolean(70),
            'is_approved' => $this->faker->boolean(60),
            'is_verified' => $this->faker->boolean(50),
            'approved_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'verified_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'transfer_conditions' => $this->faker->optional()->sentence(),
            'safety_requirements' => $this->faker->optional()->sentence(),
            'quality_standards' => $this->faker->optional()->sentence(),
            'transfer_parameters' => $this->faker->optional()->array(),
            'monitoring_data' => $this->faker->optional()->array(),
            'alarm_settings' => $this->faker->optional()->array(),
            'event_logs' => $this->faker->optional()->array(),
            'performance_metrics' => $this->faker->optional()->array(),
            'tags' => $this->faker->optional()->words(3),
            'scheduled_by' => User::factory(),
            'initiated_by' => $this->faker->optional()->randomElement([User::factory(), null]),
            'approved_by' => $this->faker->optional()->randomElement([User::factory(), null]),
            'verified_by' => $this->faker->optional()->randomElement([User::factory(), null]),
            'completed_by' => $this->faker->optional()->randomElement([User::factory(), null]),
            'created_by' => User::factory(),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'actual_start_time' => null,
            'actual_end_time' => null,
            'completion_time' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'actual_start_time' => null,
            'actual_end_time' => null,
            'completion_time' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'actual_start_time' => now(),
            'actual_end_time' => null,
            'completion_time' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'actual_start_time' => now()->subHours(2),
            'actual_end_time' => now()->subHour(),
            'completion_time' => now()->subHour(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'actual_start_time' => null,
            'actual_end_time' => null,
            'completion_time' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'actual_start_time' => null,
            'actual_end_time' => null,
            'completion_time' => null,
        ]);
    }

    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_hold',
            'actual_start_time' => null,
            'actual_end_time' => null,
            'completion_time' => null,
        ]);
    }

    public function reversed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reversed',
            'actual_start_time' => null,
            'actual_end_time' => null,
            'completion_time' => null,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    public function normalPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'normal',
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'low',
        ]);
    }

    public function generation(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_type' => 'generation',
        ]);
    }

    public function consumption(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_type' => 'consumption',
        ]);
    }

    public function storage(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_type' => 'storage',
        ]);
    }

    public function gridImport(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_type' => 'grid_import',
        ]);
    }

    public function gridExport(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_type' => 'grid_export',
        ]);
    }

    public function peerToPeer(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_type' => 'peer_to_peer',
        ]);
    }

    public function virtual(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_type' => 'virtual',
        ]);
    }

    public function physical(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_type' => 'physical',
        ]);
    }

    public function contractual(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_type' => 'contractual',
        ]);
    }

    public function automated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_automated' => true,
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_automated' => false,
        ]);
    }

    public function requiresApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_approval' => true,
            'is_approved' => false,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_approval' => true,
            'is_approved' => true,
            'approved_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'verified_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'scheduled_end_time' => now()->subHours(rand(1, 24)),
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'scheduled_start_time' => now()->addHours(rand(1, 6)),
        ]);
    }

    public function highEfficiency(): static
    {
        return $this->state(fn (array $attributes) => [
            'efficiency_percentage' => $this->faker->randomFloat(2, 90, 99.9),
            'loss_percentage' => $this->faker->randomFloat(2, 0.1, 10),
        ]);
    }

    public function lowEfficiency(): static
    {
        return $this->state(fn (array $attributes) => [
            'efficiency_percentage' => $this->faker->randomFloat(2, 60, 80),
            'loss_percentage' => $this->faker->randomFloat(2, 20, 40),
        ]);
    }

    public function smallAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_amount_kwh' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }

    public function mediumAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_amount_kwh' => $this->faker->randomFloat(2, 100, 1000),
        ]);
    }

    public function largeAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_amount_kwh' => $this->faker->randomFloat(2, 1000, 10000),
        ]);
    }

    public function shortDuration(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_start_time' => now()->addHour(),
            'scheduled_end_time' => now()->addHours(rand(1, 4)),
        ]);
    }

    public function longDuration(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_start_time' => now()->addHour(),
            'scheduled_end_time' => now()->addHours(rand(8, 48)),
        ]);
    }

    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_per_kwh' => $this->faker->randomFloat(4, 0.30, 0.80),
        ]);
    }

    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_per_kwh' => $this->faker->randomFloat(4, 0.05, 0.20),
        ]);
    }

    public function eur(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'EUR',
        ]);
    }

    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'USD',
        ]);
    }

    public function gbp(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'GBP',
        ]);
    }

    public function withTags(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $this->faker->words(rand(2, 5)),
        ]);
    }

    public function withParameters(): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_parameters' => [
                'voltage' => $this->faker->randomFloat(2, 220, 400),
                'frequency' => $this->faker->randomFloat(2, 49.8, 50.2),
                'power_factor' => $this->faker->randomFloat(2, 0.85, 0.99),
            ],
        ]);
    }

    public function withMonitoringData(): static
    {
        return $this->state(fn (array $attributes) => [
            'monitoring_data' => [
                'temperature' => $this->faker->randomFloat(2, 15, 35),
                'humidity' => $this->faker->randomFloat(2, 30, 70),
                'pressure' => $this->faker->randomFloat(2, 1000, 1020),
            ],
        ]);
    }

    public function withPerformanceMetrics(): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_metrics' => [
                'uptime' => $this->faker->randomFloat(2, 95, 99.9),
                'response_time' => $this->faker->randomFloat(2, 0.1, 2.0),
                'throughput' => $this->faker->randomFloat(2, 80, 120),
            ],
        ]);
    }
}
