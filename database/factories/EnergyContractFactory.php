<?php

namespace Database\Factories;

use App\Models\EnergyContract;
use App\Models\User;
use App\Models\Provider;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyContract>
 */
class EnergyContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', '+6 months');
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 years');
        
        return [
            'user_id' => User::factory(),
            'provider_id' => Provider::factory(),
            'product_id' => Product::factory(),
            'contract_number' => 'CTR-' . strtoupper($this->faker->bothify('##??####')),
            'name' => $this->faker->words(3, true) . ' Energy Contract',
            'description' => $this->faker->paragraphs(2, true),
            'type' => $this->faker->randomElement(['supply', 'generation', 'storage', 'hybrid']),
            'status' => $this->faker->randomElement(['draft', 'pending', 'active', 'suspended', 'terminated', 'expired']),
            'total_value' => $this->faker->randomFloat(2, 1000, 100000),
            'monthly_payment' => $this->faker->randomFloat(2, 50, 2000),
            'currency' => 'EUR',
            'deposit_amount' => $this->faker->randomFloat(2, 100, 5000),
            'deposit_paid' => $this->faker->boolean(70),
            'contracted_power' => $this->faker->randomFloat(2, 5, 100),
            'estimated_annual_consumption' => $this->faker->randomFloat(2, 2000, 50000),
            'guaranteed_supply_percentage' => $this->faker->randomFloat(2, 85, 100),
            'green_energy_percentage' => $this->faker->randomFloat(2, 20, 100),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'signed_date' => $this->faker->optional(0.8)->dateTimeBetween($startDate, 'now'),
            'activation_date' => $this->faker->optional(0.6)->dateTimeBetween($startDate, 'now'),
            'terms_conditions' => $this->faker->paragraphs(5, true),
            'special_clauses' => $this->faker->optional(0.4)->randomElements([
                'Cláusula de revisión anual de precios',
                'Garantía de suministro renovable al 100%',
                'Descuento por consumo eficiente',
                'Bonificación por instalación de sistemas de almacenamiento'
            ], $this->faker->numberBetween(1, 3)),
            'auto_renewal' => $this->faker->boolean(60),
            'renewal_period_months' => $this->faker->randomElement([12, 24, 36]),
            'early_termination_fee' => $this->faker->randomFloat(2, 0, 1000),
            'billing_frequency' => $this->faker->randomElement(['monthly', 'quarterly', 'semi_annual', 'annual']),
            'estimated_co2_reduction' => $this->faker->randomFloat(2, 500, 10000),
            'sustainability_certifications' => $this->faker->optional(0.7)->randomElements([
                'ISO 14001 - Gestión Ambiental',
                'Certificado Energía Renovable',
                'Carbon Neutral Certified',
                'LEED Certified'
            ], $this->faker->numberBetween(1, 3)),
            'carbon_neutral' => $this->faker->boolean(40),
            'custom_fields' => $this->faker->optional(0.3)->randomElements([
                'priority_support' => true,
                'smart_meter_included' => $this->faker->boolean(),
                'installation_support' => $this->faker->boolean()
            ]),
            'notes' => $this->faker->optional(0.5)->sentence(),
        ];
    }

    /**
     * Contract in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'signed_date' => null,
            'activation_date' => null,
        ]);
    }

    /**
     * Contract pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'signed_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'activation_date' => null,
        ]);
    }

    /**
     * Active contract.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'signed_date' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            'activation_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Carbon neutral contract.
     */
    public function carbonNeutral(): static
    {
        return $this->state(fn (array $attributes) => [
            'carbon_neutral' => true,
            'green_energy_percentage' => $this->faker->randomFloat(2, 90, 100),
            'sustainability_certifications' => [
                'Carbon Neutral Certified',
                'Certificado Energía Renovable',
                'ISO 14001 - Gestión Ambiental'
            ],
        ]);
    }

    /**
     * High value contract.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_value' => $this->faker->randomFloat(2, 50000, 500000),
            'monthly_payment' => $this->faker->randomFloat(2, 2000, 20000),
            'contracted_power' => $this->faker->randomFloat(2, 50, 500),
        ]);
    }

    /**
     * Contract with auto-renewal.
     */
    public function autoRenewal(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_renewal' => true,
            'renewal_period_months' => $this->faker->randomElement([12, 24]),
        ]);
    }
}