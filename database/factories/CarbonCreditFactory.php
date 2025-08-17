<?php

namespace Database\Factories;

use App\Models\CarbonCredit;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CarbonCredit>
 */
class CarbonCreditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalCredits = $this->faker->randomFloat(2, 100, 10000);
        $retiredCredits = $this->faker->randomFloat(2, 0, $totalCredits * 0.3);
        $transferredCredits = $this->faker->randomFloat(2, 0, ($totalCredits - $retiredCredits) * 0.2);
        $availableCredits = $totalCredits - $retiredCredits - $transferredCredits;
        
        return [
            'user_id' => User::factory(),
            'provider_id' => Provider::factory(),
            'credit_id' => 'CC-' . strtoupper($this->faker->bothify('####-??-####')),
            'credit_type' => $this->faker->randomElement(['vcs', 'gold_standard', 'cdm', 'vcu', 'cer', 'rgu', 'custom']),
            'project_name' => $this->faker->words(4, true) . ' Green Project',
            'project_description' => $this->faker->paragraphs(2, true),
            'project_type' => $this->faker->randomElement([
                'Reforestación', 'Energía Solar', 'Energía Eólica', 'Biomasa',
                'Eficiencia Energética', 'Conservación Forestal', 'Metano'
            ]),
            'project_country' => $this->faker->randomElement([
                'España', 'Brasil', 'India', 'China', 'Indonesia', 'Perú', 'Colombia'
            ]),
            'project_location' => $this->faker->city . ', ' . $this->faker->state,
            'total_credits' => $totalCredits,
            'available_credits' => $availableCredits,
            'retired_credits' => $retiredCredits,
            'transferred_credits' => $transferredCredits,
            'status' => $this->faker->randomElement(['pending', 'verified', 'issued', 'available', 'retired', 'cancelled', 'expired']),
            'vintage_year' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
            'credit_period_start' => $this->faker->dateTimeBetween('-3 years', '-1 year'),
            'credit_period_end' => $this->faker->dateTimeBetween('-1 year', '+1 year'),
            'purchase_price_per_credit' => $this->faker->randomFloat(2, 5, 50),
            'current_market_price' => $this->faker->randomFloat(2, 8, 60),
            'registry_id' => 'REG-' . $this->faker->numerify('######'),
            'serial_number' => $this->faker->numerify('SN-##########'),
            'blockchain_hash' => $this->faker->optional(0.6)->sha256,
            'additionality_demonstrated' => $this->faker->boolean(70),
            'methodology' => $this->faker->randomElement([
                'VM0007', 'VM0012', 'VM0015', 'ACM0002', 'AMS-I.D', 'CDM-AR-AM0010'
            ]),
            'verifier_name' => $this->faker->company . ' Verification Services',
            'verification_date' => $this->faker->optional(0.8)->dateTimeBetween('-2 years', 'now'),
            'actual_co2_reduced' => $this->faker->randomFloat(2, $totalCredits * 0.8, $totalCredits * 1.2),
            'leakage_percentage' => $this->faker->randomFloat(2, 0, 15),
            'permanence_risk_assessment' => $this->faker->randomElement(['Low', 'Medium', 'High']),
            'monitoring_frequency' => $this->faker->randomElement(['Annual', 'Biennial', 'Continuous']),
            'transaction_history' => $this->faker->optional(0.4)->randomElements([
                ['date' => '2023-01-15', 'type' => 'purchase', 'amount' => 100],
                ['date' => '2023-06-20', 'type' => 'transfer', 'amount' => 50],
            ], $this->faker->numberBetween(1, 3)),
        ];
    }

    public function verified(): static
    {
        return $this->state([
            'status' => 'verified',
            'verification_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'additionality_demonstrated' => true,
        ]);
    }

    public function available(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'available',
                'available_credits' => $attributes['total_credits'],
                'retired_credits' => 0,
                'transferred_credits' => 0,
            ];
        });
    }

    public function goldStandard(): static
    {
        return $this->state([
            'credit_type' => 'gold_standard',
            'additionality_demonstrated' => true,
            'current_market_price' => $this->faker->randomFloat(2, 15, 80),
        ]);
    }
}