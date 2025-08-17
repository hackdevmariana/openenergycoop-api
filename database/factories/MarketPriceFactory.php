<?php

namespace Database\Factories;

use App\Models\MarketPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MarketPrice>
 */
class MarketPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'market_name' => $this->faker->randomElement(['OMIE', 'EPEX SPOT', 'Nord Pool', 'PJM', 'ERCOT', 'AEMO']),
            'country' => $this->faker->randomElement(['España', 'Francia', 'Alemania', 'Noruega', 'Estados Unidos', 'Australia']),
            'commodity_type' => $this->faker->randomElement(['electricity', 'natural_gas', 'carbon_credits', 'renewable_certificates', 'capacity', 'balancing']),
            'product_name' => $this->faker->randomElement(['Base Load', 'Peak Load', 'Off-Peak', 'Weekend', 'Spot Price', 'Day-Ahead']),
            'price_datetime' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'price_date' => function (array $attributes) {
                return $attributes['price_datetime']->format('Y-m-d');
            },
            'price_time' => function (array $attributes) {
                return $attributes['price_datetime']->format('H:i:s');
            },
            'period_type' => $this->faker->randomElement(['real_time', 'hourly', 'daily', 'weekly', 'monthly', 'quarterly', 'annual']),
            'delivery_start_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'delivery_end_date' => $this->faker->dateTimeBetween('+1 day', '+2 years'),
            'delivery_period' => $this->faker->randomElement(['spot', 'next_day', 'current_week', 'next_week', 'current_month', 'next_month', 'current_quarter', 'next_quarter', 'current_year', 'next_year']),
            'price' => $this->faker->randomFloat(4, 10, 300),
            'currency' => 'EUR',
            'unit' => $this->faker->randomElement(['EUR/MWh', 'EUR/tCO2', 'EUR/MWh/day', 'EUR/MW']),
            'volume' => $this->faker->randomFloat(2, 100, 10000),
            'high_price' => function (array $attributes) {
                return $attributes['price'] * $this->faker->randomFloat(2, 1.01, 1.2);
            },
            'low_price' => function (array $attributes) {
                return $attributes['price'] * $this->faker->randomFloat(2, 0.8, 0.99);
            },
            'price_change_percentage' => $this->faker->randomFloat(2, -20, 20),
            'volatility' => $this->faker->randomFloat(2, 5, 50),
            'data_source' => $this->faker->randomElement(['Official Exchange', 'Market Operator', 'Broker Platform', 'Third Party Provider']),
            'market_status' => $this->faker->randomElement(['open', 'closed', 'pre_opening', 'auction', 'suspended', 'maintenance']),
        ];
    }

    public function electricity(): static
    {
        return $this->state([
            'commodity_type' => 'electricity',
            'unit' => 'EUR/MWh',
            'price' => $this->faker->randomFloat(4, 20, 200),
        ]);
    }

    public function carbonCredits(): static
    {
        return $this->state([
            'commodity_type' => 'carbon_credits',
            'unit' => 'EUR/tCO2',
            'price' => $this->faker->randomFloat(4, 15, 100),
        ]);
    }
}
