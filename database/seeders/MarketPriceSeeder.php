<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketPrice;
use Carbon\Carbon;

class MarketPriceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💰 Creando precios de mercado energético...');

        // Limpiar datos existentes
        MarketPrice::query()->delete();

        // Crear diferentes tipos de precios de mercado
        $this->createElectricityPrices();
        $this->createCarbonCreditPrices();
        $this->createNaturalGasPrices();
        $this->createRenewableCertificatePrices();
        $this->createCapacityPrices();

        $this->command->info('✅ MarketPriceSeeder completado. Se crearon ' . MarketPrice::count() . ' precios de mercado.');
    }

    private function createElectricityPrices(): void
    {
        $this->command->info('⚡ Creando precios de electricidad...');

        $markets = [
            ['name' => 'OMIE', 'country' => 'España', 'region' => 'Península'],
            ['name' => 'EPEX SPOT', 'country' => 'Alemania', 'region' => 'Alemania'],
            ['name' => 'Nord Pool', 'country' => 'Noruega', 'region' => 'Nórdico'],
            ['name' => 'PJM', 'country' => 'Estados Unidos', 'region' => 'Noreste'],
            ['name' => 'ERCOT', 'country' => 'Estados Unidos', 'region' => 'Texas'],
        ];

        $products = ['Base Load', 'Peak Load', 'Off-Peak', 'Weekend', 'Day-Ahead', 'Intraday'];

        foreach ($markets as $market) {
            for ($i = 0; $i < 20; $i++) {
                $priceDateTime = Carbon::now()->subDays(rand(0, 30))->setHour(rand(0, 23))->setMinute(rand(0, 59));
                $basePrice = fake()->randomFloat(4, 20, 150);
                $highPrice = $basePrice * fake()->randomFloat(2, 1.05, 1.25);
                $lowPrice = $basePrice * fake()->randomFloat(2, 0.75, 0.95);

                MarketPrice::create([
                    'market_name' => $market['name'],
                    'market_code' => strtoupper(substr($market['name'], 0, 3)) . fake()->numerify('###'),
                    'country' => $market['country'],
                    'region' => $market['region'],
                    'zone' => fake()->optional(0.7)->randomElement(['North', 'South', 'East', 'West', 'Central']),
                    'commodity_type' => 'electricity',
                    'product_name' => fake()->randomElement($products),
                    'product_description' => fake()->optional(0.8)->sentence,
                    'price_datetime' => $priceDateTime,
                    'price_date' => $priceDateTime->toDateString(),
                    'price_time' => $priceDateTime->toTimeString(),
                    'period_type' => fake()->randomElement(['real_time', 'hourly', 'daily']),
                    'delivery_start_date' => $priceDateTime->addHours(rand(1, 24)),
                    'delivery_end_date' => $priceDateTime->addHours(rand(25, 48)),
                    'delivery_period' => fake()->randomElement(['spot', 'next_day', 'current_week', 'next_week']),
                    'price' => $basePrice,
                    'currency' => 'EUR',
                    'unit' => 'EUR/MWh',
                    'opening_price' => $basePrice * fake()->randomFloat(2, 0.95, 1.05),
                    'closing_price' => $basePrice * fake()->randomFloat(2, 0.95, 1.05),
                    'high_price' => $highPrice,
                    'low_price' => $lowPrice,
                    'weighted_average_price' => $basePrice * fake()->randomFloat(2, 0.98, 1.02),
                    'volume' => fake()->randomFloat(4, 1000, 50000),
                    'volume_unit' => 'MWh',
                    'number_of_transactions' => fake()->numberBetween(50, 500),
                    'bid_price' => $basePrice * fake()->randomFloat(2, 0.99, 1.01),
                    'ask_price' => $basePrice * fake()->randomFloat(2, 0.99, 1.01),
                    'spread' => fake()->randomFloat(4, 0.1, 2.0),
                    'price_change_absolute' => fake()->randomFloat(4, -10, 10),
                    'price_change_percentage' => fake()->randomFloat(4, -15, 15),
                    'volatility' => fake()->randomFloat(2, 5, 30),
                    'vs_previous_day' => fake()->randomFloat(4, -20, 20),
                    'vs_previous_week' => fake()->randomFloat(4, -25, 25),
                    'vs_previous_month' => fake()->randomFloat(4, -30, 30),
                    'vs_previous_year' => fake()->randomFloat(4, -40, 40),
                    'demand_mw' => fake()->randomFloat(2, 20000, 80000),
                    'supply_mw' => fake()->randomFloat(2, 22000, 85000),
                    'renewable_generation_mw' => fake()->randomFloat(2, 5000, 30000),
                    'conventional_generation_mw' => fake()->randomFloat(2, 15000, 50000),
                    'imports_mw' => fake()->randomFloat(2, 0, 10000),
                    'exports_mw' => fake()->randomFloat(2, 0, 8000),
                    'system_margin_mw' => fake()->randomFloat(2, 2000, 15000),
                    'reserve_margin_percentage' => fake()->randomFloat(2, 5, 25),
                    'system_condition' => fake()->randomElement(['normal', 'tight', 'emergency', 'surplus']),
                    'temperature_celsius' => fake()->randomFloat(2, -10, 40),
                    'wind_generation_factor' => fake()->randomFloat(4, 0.1, 0.8),
                    'solar_generation_factor' => fake()->randomFloat(4, 0.1, 0.9),
                    'hydro_reservoir_level' => fake()->randomFloat(2, 20, 90),
                    'natural_gas_price' => fake()->randomFloat(4, 20, 80),
                    'coal_price' => fake()->randomFloat(4, 50, 150),
                    'oil_price' => fake()->randomFloat(4, 60, 120),
                    'co2_price' => fake()->randomFloat(4, 15, 100),
                    'data_source' => fake()->randomElement(['Official Exchange', 'Market Operator', 'Broker Platform']),
                    'data_provider' => fake()->company,
                    'data_quality' => fake()->randomElement(['official', 'preliminary', 'estimated']),
                    'feed_id' => 'FEED-' . fake()->numerify('######'),
                    'api_metadata' => json_encode([
                        'api_version' => 'v2.1',
                        'update_frequency' => 'hourly',
                        'last_update' => $priceDateTime->toISOString()
                    ]),
                    'data_retrieved_at' => $priceDateTime->addMinutes(rand(1, 30)),
                    'sma_7' => $basePrice * fake()->randomFloat(2, 0.95, 1.05),
                    'sma_30' => $basePrice * fake()->randomFloat(2, 0.90, 1.10),
                    'ema_7' => $basePrice * fake()->randomFloat(2, 0.95, 1.05),
                    'ema_30' => $basePrice * fake()->randomFloat(2, 0.90, 1.10),
                    'rsi' => fake()->randomFloat(2, 20, 80),
                    'price_spike_detected' => fake()->boolean(5),
                    'spike_threshold' => fake()->optional(0.1)->randomFloat(4, 100, 200),
                    'unusual_volume_detected' => fake()->boolean(3),
                    'market_alerts' => fake()->optional(0.2)->sentence ? json_encode([
                        'alert_type' => 'price_volatility',
                        'severity' => 'medium',
                        'message' => 'Increased price volatility detected'
                    ]) : null,
                    'forecast_next_hour' => $basePrice * fake()->randomFloat(2, 0.95, 1.05),
                    'forecast_next_day' => $basePrice * fake()->randomFloat(2, 0.90, 1.10),
                    'forecast_confidence' => fake()->randomFloat(2, 70, 95),
                    'forecast_model' => fake()->optional(0.7)->randomElement(['ARIMA', 'LSTM', 'Prophet']),
                    'regulated_price' => fake()->boolean(20),
                    'regulatory_period' => fake()->optional(0.2)->randomElement(['Q1-2024', 'Q2-2024', 'Q3-2024']),
                    'regulatory_adjustments' => fake()->optional(0.2)->sentence ? json_encode([
                        'adjustment_type' => 'capacity_payment',
                        'amount' => fake()->randomFloat(4, 1, 5)
                    ]) : null,
                    'market_status' => fake()->randomElement(['open', 'open', 'open', 'closed', 'pre_opening']),
                    'additional_data' => json_encode([
                        'market_liquidity' => fake()->randomElement(['high', 'medium', 'low']),
                        'participant_count' => fake()->numberBetween(50, 200),
                        'trading_session' => fake()->randomElement(['morning', 'afternoon', 'evening'])
                    ]),
                    'notes' => fake()->optional(0.3)->sentence,
                    'is_holiday' => fake()->boolean(10),
                    'day_type' => fake()->randomElement(['working_day', 'working_day', 'working_day', 'weekend', 'holiday']),
                ]);
            }
        }
    }

    private function createCarbonCreditPrices(): void
    {
        $this->command->info('🌱 Creando precios de créditos de carbono...');

        $markets = [
            ['name' => 'EU ETS', 'country' => 'Unión Europea', 'region' => 'EU'],
            ['name' => 'California Cap-and-Trade', 'country' => 'Estados Unidos', 'region' => 'California'],
            ['name' => 'RGGI', 'country' => 'Estados Unidos', 'region' => 'Noreste'],
            ['name' => 'UK ETS', 'country' => 'Reino Unido', 'region' => 'UK'],
        ];

        for ($i = 0; $i < 25; $i++) {
            $priceDateTime = Carbon::now()->subDays(rand(0, 30))->setHour(rand(0, 23))->setMinute(rand(0, 59));
            $basePrice = fake()->randomFloat(4, 15, 100);
            $market = fake()->randomElement($markets);

            MarketPrice::create([
                'market_name' => $market['name'],
                'market_code' => strtoupper(substr($market['name'], 0, 3)) . fake()->numerify('###'),
                'country' => $market['country'],
                'region' => $market['region'],
                'commodity_type' => 'carbon_credits',
                'product_name' => fake()->randomElement(['EUA', 'CER', 'ERU', 'CCA', 'RGGI Allowance']),
                'product_description' => fake()->optional(0.8)->sentence,
                'price_datetime' => $priceDateTime,
                'price_date' => $priceDateTime->toDateString(),
                'price_time' => $priceDateTime->toTimeString(),
                'period_type' => fake()->randomElement(['real_time', 'hourly', 'daily']),
                'delivery_start_date' => $priceDateTime->addDays(rand(1, 30)),
                'delivery_end_date' => $priceDateTime->addDays(rand(31, 365)),
                'delivery_period' => fake()->randomElement(['spot', 'next_month', 'current_quarter', 'next_quarter']),
                'price' => $basePrice,
                'currency' => 'EUR',
                'unit' => 'EUR/tCO2',
                'high_price' => $basePrice * fake()->randomFloat(2, 1.02, 1.15),
                'low_price' => $basePrice * fake()->randomFloat(2, 0.85, 0.98),
                'volume' => fake()->randomFloat(2, 100, 5000),
                'volume_unit' => 'tCO2',
                'number_of_transactions' => fake()->numberBetween(10, 200),
                'price_change_percentage' => fake()->randomFloat(4, -10, 10),
                'volatility' => fake()->randomFloat(2, 3, 20),
                'vs_previous_day' => fake()->randomFloat(4, -8, 8),
                'vs_previous_week' => fake()->randomFloat(4, -15, 15),
                'vs_previous_month' => fake()->randomFloat(4, -25, 25),
                'vs_previous_year' => fake()->randomFloat(4, -50, 50),
                'data_source' => fake()->randomElement(['Official Exchange', 'Market Operator', 'Broker Platform']),
                'data_provider' => fake()->company,
                'data_quality' => fake()->randomElement(['official', 'preliminary', 'estimated']),
                'market_status' => fake()->randomElement(['open', 'open', 'open', 'closed']),
                'additional_data' => json_encode([
                    'auction_type' => fake()->randomElement(['daily', 'weekly', 'monthly']),
                    'supply_cap' => fake()->randomFloat(2, 1000000, 5000000),
                    'demand_forecast' => fake()->randomFloat(2, 800000, 4500000)
                ]),
                'notes' => fake()->optional(0.2)->sentence,
                'is_holiday' => fake()->boolean(5),
                'day_type' => fake()->randomElement(['working_day', 'working_day', 'working_day', 'weekend']),
            ]);
        }
    }

    private function createNaturalGasPrices(): void
    {
        $this->command->info('🔥 Creando precios de gas natural...');

        $markets = [
            ['name' => 'TTF', 'country' => 'Países Bajos', 'region' => 'Europa'],
            ['name' => 'NBP', 'country' => 'Reino Unido', 'region' => 'UK'],
            ['name' => 'Henry Hub', 'country' => 'Estados Unidos', 'region' => 'Louisiana'],
            ['name' => 'JKM', 'country' => 'Japón', 'region' => 'Asia'],
        ];

        for ($i = 0; $i < 20; $i++) {
            $priceDateTime = Carbon::now()->subDays(rand(0, 30))->setHour(rand(0, 23))->setMinute(rand(0, 59));
            $basePrice = fake()->randomFloat(4, 20, 80);
            $market = fake()->randomElement($markets);

            MarketPrice::create([
                'market_name' => $market['name'],
                'market_code' => strtoupper(substr($market['name'], 0, 3)) . fake()->numerify('###'),
                'country' => $market['country'],
                'region' => $market['region'],
                'commodity_type' => 'natural_gas',
                'product_name' => fake()->randomElement(['Day-Ahead', 'Month-Ahead', 'Quarter-Ahead', 'Year-Ahead']),
                'product_description' => fake()->optional(0.8)->sentence,
                'price_datetime' => $priceDateTime,
                'price_date' => $priceDateTime->toDateString(),
                'price_time' => $priceDateTime->toTimeString(),
                'period_type' => fake()->randomElement(['real_time', 'hourly', 'daily']),
                'delivery_start_date' => $priceDateTime->addDays(rand(1, 30)),
                'delivery_end_date' => $priceDateTime->addDays(rand(31, 365)),
                'delivery_period' => fake()->randomElement(['spot', 'next_day', 'current_month', 'next_month']),
                'price' => $basePrice,
                'currency' => 'EUR',
                'unit' => 'EUR/MWh',
                'high_price' => $basePrice * fake()->randomFloat(2, 1.03, 1.20),
                'low_price' => $basePrice * fake()->randomFloat(2, 0.80, 0.97),
                'volume' => fake()->randomFloat(2, 1000, 50000),
                'volume_unit' => 'MWh',
                'number_of_transactions' => fake()->numberBetween(20, 300),
                'price_change_percentage' => fake()->randomFloat(4, -15, 15),
                'volatility' => fake()->randomFloat(2, 8, 35),
                'vs_previous_day' => fake()->randomFloat(4, -12, 12),
                'vs_previous_week' => fake()->randomFloat(4, -20, 20),
                'vs_previous_month' => fake()->randomFloat(4, -30, 30),
                'vs_previous_year' => fake()->randomFloat(4, -60, 60),
                'data_source' => fake()->randomElement(['Official Exchange', 'Market Operator', 'Broker Platform']),
                'data_provider' => fake()->company,
                'data_quality' => fake()->randomElement(['official', 'preliminary', 'estimated']),
                'market_status' => fake()->randomElement(['open', 'open', 'open', 'closed']),
                'additional_data' => json_encode([
                    'storage_level' => fake()->randomFloat(2, 20, 90),
                    'lng_imports' => fake()->randomFloat(2, 0, 100),
                    'pipeline_flows' => fake()->randomFloat(2, 50, 200)
                ]),
                'notes' => fake()->optional(0.2)->sentence,
                'is_holiday' => fake()->boolean(5),
                'day_type' => fake()->randomElement(['working_day', 'working_day', 'working_day', 'weekend']),
            ]);
        }
    }

    private function createRenewableCertificatePrices(): void
    {
        $this->command->info('🌿 Creando precios de certificados renovables...');

        $markets = [
            ['name' => 'GO Market', 'country' => 'Unión Europea', 'region' => 'EU'],
            ['name' => 'REC Market', 'country' => 'Estados Unidos', 'region' => 'Nacional'],
            ['name' => 'LGC Market', 'country' => 'Australia', 'region' => 'Australia'],
        ];

        for ($i = 0; $i < 15; $i++) {
            $priceDateTime = Carbon::now()->subDays(rand(0, 30))->setHour(rand(0, 23))->setMinute(rand(0, 59));
            $basePrice = fake()->randomFloat(4, 0.5, 5.0);
            $market = fake()->randomElement($markets);

            MarketPrice::create([
                'market_name' => $market['name'],
                'market_code' => strtoupper(substr($market['name'], 0, 3)) . fake()->numerify('###'),
                'country' => $market['country'],
                'region' => $market['region'],
                'commodity_type' => 'renewable_certificates',
                'product_name' => fake()->randomElement(['GO', 'REC', 'LGC', 'Solar REC', 'Wind REC']),
                'product_description' => fake()->optional(0.8)->sentence,
                'price_datetime' => $priceDateTime,
                'price_date' => $priceDateTime->toDateString(),
                'price_time' => $priceDateTime->toTimeString(),
                'period_type' => fake()->randomElement(['real_time', 'hourly', 'daily']),
                'delivery_start_date' => $priceDateTime->addDays(rand(1, 30)),
                'delivery_end_date' => $priceDateTime->addDays(rand(31, 365)),
                'delivery_period' => fake()->randomElement(['spot', 'next_month', 'current_quarter']),
                'price' => $basePrice,
                'currency' => 'EUR',
                'unit' => 'EUR/MWh',
                'high_price' => $basePrice * fake()->randomFloat(2, 1.05, 1.25),
                'low_price' => $basePrice * fake()->randomFloat(2, 0.75, 0.95),
                'volume' => fake()->randomFloat(2, 100, 2000),
                'volume_unit' => 'MWh',
                'number_of_transactions' => fake()->numberBetween(5, 100),
                'price_change_percentage' => fake()->randomFloat(4, -8, 8),
                'volatility' => fake()->randomFloat(2, 2, 15),
                'vs_previous_day' => fake()->randomFloat(4, -5, 5),
                'vs_previous_week' => fake()->randomFloat(4, -10, 10),
                'vs_previous_month' => fake()->randomFloat(4, -15, 15),
                'vs_previous_year' => fake()->randomFloat(4, -25, 25),
                'data_source' => fake()->randomElement(['Official Exchange', 'Market Operator', 'Broker Platform']),
                'data_provider' => fake()->company,
                'data_quality' => fake()->randomElement(['official', 'preliminary', 'estimated']),
                'market_status' => fake()->randomElement(['open', 'open', 'open', 'closed']),
                'additional_data' => json_encode([
                    'renewable_type' => fake()->randomElement(['solar', 'wind', 'hydro', 'biomass']),
                    'vintage_year' => fake()->numberBetween(2020, 2024),
                    'issuance_date' => $priceDateTime->subDays(rand(1, 365))->toDateString()
                ]),
                'notes' => fake()->optional(0.2)->sentence,
                'is_holiday' => fake()->boolean(5),
                'day_type' => fake()->randomElement(['working_day', 'working_day', 'working_day', 'weekend']),
            ]);
        }
    }

    private function createCapacityPrices(): void
    {
        $this->command->info('⚡ Creando precios de capacidad...');

        $markets = [
            ['name' => 'Capacity Market UK', 'country' => 'Reino Unido', 'region' => 'UK'],
            ['name' => 'PJM Capacity', 'country' => 'Estados Unidos', 'region' => 'Noreste'],
            ['name' => 'NYISO Capacity', 'country' => 'Estados Unidos', 'region' => 'Nueva York'],
        ];

        for ($i = 0; $i < 10; $i++) {
            $priceDateTime = Carbon::now()->subDays(rand(0, 30))->setHour(rand(0, 23))->setMinute(rand(0, 59));
            $basePrice = fake()->randomFloat(4, 10, 100);
            $market = fake()->randomElement($markets);

            MarketPrice::create([
                'market_name' => $market['name'],
                'market_code' => strtoupper(substr($market['name'], 0, 3)) . fake()->numerify('###'),
                'country' => $market['country'],
                'region' => $market['region'],
                'commodity_type' => 'capacity',
                'product_name' => fake()->randomElement(['Capacity Auction', 'Reliability Pricing', 'Resource Adequacy']),
                'product_description' => fake()->optional(0.8)->sentence,
                'price_datetime' => $priceDateTime,
                'price_date' => $priceDateTime->toDateString(),
                'price_time' => $priceDateTime->toTimeString(),
                'period_type' => fake()->randomElement(['real_time', 'daily', 'monthly']),
                'delivery_start_date' => $priceDateTime->addMonths(rand(1, 12)),
                'delivery_end_date' => $priceDateTime->addMonths(rand(13, 24)),
                'delivery_period' => fake()->randomElement(['current_year', 'next_year']),
                'price' => $basePrice,
                'currency' => 'EUR',
                'unit' => 'EUR/MW',
                'high_price' => $basePrice * fake()->randomFloat(2, 1.02, 1.15),
                'low_price' => $basePrice * fake()->randomFloat(2, 0.85, 0.98),
                'volume' => fake()->randomFloat(2, 1000, 10000),
                'volume_unit' => 'MW',
                'number_of_transactions' => fake()->numberBetween(10, 50),
                'price_change_percentage' => fake()->randomFloat(4, -5, 5),
                'volatility' => fake()->randomFloat(2, 1, 10),
                'vs_previous_day' => fake()->randomFloat(4, -3, 3),
                'vs_previous_week' => fake()->randomFloat(4, -5, 5),
                'vs_previous_month' => fake()->randomFloat(4, -8, 8),
                'vs_previous_year' => fake()->randomFloat(4, -15, 15),
                'data_source' => fake()->randomElement(['Official Exchange', 'Market Operator', 'Regulator']),
                'data_provider' => fake()->company,
                'data_quality' => fake()->randomElement(['official', 'preliminary', 'estimated']),
                'market_status' => fake()->randomElement(['open', 'auction', 'closed']),
                'additional_data' => json_encode([
                    'auction_type' => fake()->randomElement(['T-1', 'T-2', 'T-3', 'T-4']),
                    'clearing_price' => $basePrice * fake()->randomFloat(2, 0.95, 1.05),
                    'demand_curve' => 'inelastic'
                ]),
                'notes' => fake()->optional(0.2)->sentence,
                'is_holiday' => fake()->boolean(5),
                'day_type' => fake()->randomElement(['working_day', 'working_day', 'working_day', 'weekend']),
            ]);
        }
    }
}
