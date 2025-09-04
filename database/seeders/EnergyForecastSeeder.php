<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyForecast;
use App\Models\User;
use App\Models\EnergySource;
use Carbon\Carbon;

class EnergyForecastSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔮 Creando pronósticos energéticos...');

        $users = User::all();
        $energySources = EnergySource::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar datos existentes
        EnergyForecast::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("⚡ Fuentes de energía disponibles: {$energySources->count()}");

        // Crear diferentes tipos de pronósticos
        $this->createGenerationForecasts($users, $energySources);
        $this->createDemandForecasts($users, $energySources);
        $this->createPriceForecasts($users, $energySources);
        $this->createWeatherForecasts($users, $energySources);
        $this->createConsumptionForecasts($users, $energySources);

        $this->command->info('✅ EnergyForecastSeeder completado. Se crearon ' . EnergyForecast::count() . ' pronósticos.');
    }

    private function createGenerationForecasts($users, $energySources): void
    {
        $this->command->info('🏭 Creando pronósticos de generación...');

        for ($i = 0; $i < 25; $i++) {
            $creator = $users->random();
            $energySource = $energySources->isEmpty() ? null : $energySources->random();
            $approver = fake()->optional(0.7)->randomElement($users->pluck('id')->toArray());
            $validator = fake()->optional(0.6)->randomElement($users->pluck('id')->toArray());

            $forecastStartTime = Carbon::now()->addDays(rand(1, 30));
            $forecastEndTime = $forecastStartTime->copy()->addDays(rand(1, 90));
            $generationTime = Carbon::now()->subHours(rand(1, 48));

            $totalValue = fake()->randomFloat(2, 1000, 10000);

            EnergyForecast::create([
                'forecast_number' => 'GEN-' . strtoupper(fake()->bothify('####-??')),
                'name' => 'Pronóstico de Generación ' . fake()->words(3, true),
                'description' => fake()->sentence,
                'forecast_type' => 'generation',
                'forecast_horizon' => fake()->randomElement(['daily', 'weekly', 'monthly']),
                'forecast_method' => fake()->randomElement(['statistical', 'machine_learning', 'physical_model', 'hybrid']),
                'forecast_status' => fake()->randomElement(['active', 'validated', 'draft']),
                'accuracy_level' => fake()->randomElement(['medium', 'high', 'very_high']),
                'accuracy_score' => fake()->randomFloat(2, 75, 95),
                'confidence_interval_lower' => $totalValue * 0.9,
                'confidence_interval_upper' => $totalValue * 1.1,
                'confidence_level' => fake()->randomFloat(2, 80, 98),
                'source_id' => $energySource?->id,
                'source_type' => 'App\Models\EnergySource',
                'target_id' => fake()->optional(0.5)->numberBetween(1, 50),
                'target_type' => fake()->optional(0.5)->randomElement(['App\Models\EnergyInstallation', 'App\Models\EnergyPool']),
                'forecast_start_time' => $forecastStartTime,
                'forecast_end_time' => $forecastEndTime,
                'generation_time' => $generationTime,
                'valid_from' => $generationTime->copy()->addHours(1),
                'valid_until' => fake()->optional(0.7)->dateTimeBetween($forecastEndTime, '+6 months'),
                'expiry_time' => fake()->optional(0.8)->dateTimeBetween($forecastEndTime, '+1 year'),
                'time_zone' => 'Europe/Madrid',
                'time_resolution' => fake()->randomElement(['1h', '6h', '24h']),
                'forecast_periods' => rand(24, 720),
                'total_forecasted_value' => $totalValue,
                'forecast_unit' => 'MWh',
                'baseline_value' => $totalValue * fake()->randomFloat(2, 0.8, 1.2),
                'trend_value' => $totalValue * fake()->randomFloat(2, -0.1, 0.1),
                'seasonal_value' => $totalValue * fake()->randomFloat(2, -0.15, 0.15),
                'cyclical_value' => $totalValue * fake()->randomFloat(2, -0.05, 0.05),
                'irregular_value' => $totalValue * fake()->randomFloat(2, -0.02, 0.02),
                'forecast_data' => json_encode([
                    'hourly_values' => array_map(fn() => fake()->randomFloat(2, 50, 500), range(1, 24)),
                    'daily_averages' => array_map(fn() => fake()->randomFloat(2, 200, 1000), range(1, 7)),
                    'peak_hours' => ['08:00', '12:00', '18:00'],
                    'min_generation' => fake()->randomFloat(2, 100, 300),
                    'max_generation' => fake()->randomFloat(2, 800, 1200)
                ]),
                'baseline_data' => json_encode(['baseline_avg' => $totalValue * 0.9]),
                'weather_data' => json_encode([
                    'temperature' => fake()->randomFloat(2, 15, 30),
                    'wind_speed' => fake()->randomFloat(2, 5, 15),
                    'solar_radiation' => fake()->randomFloat(2, 200, 800),
                    'cloud_cover' => fake()->randomFloat(2, 10, 70)
                ]),
                'input_variables' => json_encode([
                    'installed_capacity' => fake()->randomFloat(2, 100, 1000),
                    'efficiency_factor' => fake()->randomFloat(2, 0.8, 0.95),
                    'maintenance_schedule' => 'normal'
                ]),
                'model_parameters' => json_encode([
                    'algorithm' => fake()->randomElement(['ARIMA', 'LSTM', 'Random Forest']),
                    'training_period' => '12 months',
                    'update_frequency' => 'daily'
                ]),
                'validation_metrics' => json_encode([
                    'mae' => fake()->randomFloat(2, 50, 200),
                    'rmse' => fake()->randomFloat(2, 100, 300),
                    'mape' => fake()->randomFloat(2, 5, 15)
                ]),
                'tags' => json_encode(['generation', 'renewable', 'forecast', 'planning']),
                'created_by' => $creator->id,
                'approved_by' => $approver,
                'approved_at' => $approver ? fake()->optional(0.8)->dateTimeBetween($generationTime, 'now') : null,
                'validated_by' => $validator,
                'validated_at' => $validator ? fake()->optional(0.6)->dateTimeBetween($generationTime, 'now') : null,
                'notes' => fake()->optional(0.4)->sentence,
            ]);
        }
    }

    private function createDemandForecasts($users, $energySources): void
    {
        $this->command->info('📈 Creando pronósticos de demanda...');

        for ($i = 0; $i < 20; $i++) {
            $creator = $users->random();
            $energySource = $energySources->isEmpty() ? null : $energySources->random();
            $approver = fake()->optional(0.8)->randomElement($users->pluck('id')->toArray());
            $validator = fake()->optional(0.7)->randomElement($users->pluck('id')->toArray());

            $forecastStartTime = Carbon::now()->addDays(rand(1, 15));
            $forecastEndTime = $forecastStartTime->copy()->addDays(rand(7, 60));
            $generationTime = Carbon::now()->subHours(rand(1, 24));

            $totalValue = fake()->randomFloat(2, 2000, 15000);

            EnergyForecast::create([
                'forecast_number' => 'DEM-' . strtoupper(fake()->bothify('####-??')),
                'name' => 'Pronóstico de Demanda ' . fake()->words(2, true),
                'description' => fake()->sentence,
                'forecast_type' => 'demand',
                'forecast_horizon' => fake()->randomElement(['hourly', 'daily', 'weekly']),
                'forecast_method' => fake()->randomElement(['machine_learning', 'statistical', 'hybrid']),
                'forecast_status' => fake()->randomElement(['active', 'validated', 'draft']),
                'accuracy_level' => fake()->randomElement(['high', 'very_high', 'medium']),
                'accuracy_score' => fake()->randomFloat(2, 70, 92),
                'confidence_interval_lower' => $totalValue * 0.85,
                'confidence_interval_upper' => $totalValue * 1.15,
                'confidence_level' => fake()->randomFloat(2, 75, 95),
                'source_id' => $energySource?->id,
                'forecast_start_time' => $forecastStartTime,
                'forecast_end_time' => $forecastEndTime,
                'generation_time' => $generationTime,
                'valid_from' => $generationTime->copy()->addMinutes(30),
                'valid_until' => fake()->optional(0.8)->dateTimeBetween($forecastEndTime, '+3 months'),
                'time_zone' => 'Europe/Madrid',
                'time_resolution' => fake()->randomElement(['1h', '3h', '6h']),
                'forecast_periods' => rand(24, 168),
                'total_forecasted_value' => $totalValue,
                'forecast_unit' => 'MW',
                'baseline_value' => $totalValue * fake()->randomFloat(2, 0.9, 1.1),
                'trend_value' => $totalValue * fake()->randomFloat(2, -0.05, 0.05),
                'seasonal_value' => $totalValue * fake()->randomFloat(2, -0.2, 0.2),
                'forecast_data' => json_encode([
                    'peak_demand' => fake()->randomFloat(2, 1000, 2000),
                    'base_demand' => fake()->randomFloat(2, 500, 800),
                    'demand_curve' => array_map(fn() => fake()->randomFloat(2, 600, 1800), range(1, 24))
                ]),
                'weather_data' => json_encode([
                    'temperature_forecast' => fake()->randomFloat(2, 10, 35),
                    'humidity' => fake()->randomFloat(2, 40, 80)
                ]),
                'input_variables' => json_encode([
                    'population' => fake()->numberBetween(10000, 500000),
                    'economic_activity' => fake()->randomElement(['high', 'medium', 'low']),
                    'season' => fake()->randomElement(['winter', 'spring', 'summer', 'autumn'])
                ]),
                'tags' => json_encode(['demand', 'consumption', 'peak', 'planning']),
                'created_by' => $creator->id,
                'approved_by' => $approver,
                'approved_at' => $approver ? fake()->optional(0.9)->dateTimeBetween($generationTime, 'now') : null,
                'validated_by' => $validator,
                'validated_at' => $validator ? fake()->optional(0.7)->dateTimeBetween($generationTime, 'now') : null,
                'notes' => fake()->optional(0.5)->sentence,
            ]);
        }
    }

    private function createPriceForecasts($users, $energySources): void
    {
        $this->command->info('💰 Creando pronósticos de precios...');

        for ($i = 0; $i < 15; $i++) {
            $creator = $users->random();
            $energySource = $energySources->isEmpty() ? null : $energySources->random();
            $approver = fake()->optional(0.6)->randomElement($users->pluck('id')->toArray());

            $forecastStartTime = Carbon::now()->addDays(rand(1, 7));
            $forecastEndTime = $forecastStartTime->copy()->addDays(rand(7, 30));
            $generationTime = Carbon::now()->subHours(rand(2, 12));

            $totalValue = fake()->randomFloat(2, 0.05, 0.35);

            EnergyForecast::create([
                'forecast_number' => 'PRC-' . strtoupper(fake()->bothify('####-??')),
                'name' => 'Pronóstico de Precios ' . fake()->words(2, true),
                'description' => fake()->sentence,
                'forecast_type' => 'price',
                'forecast_horizon' => fake()->randomElement(['hourly', 'daily']),
                'forecast_method' => fake()->randomElement(['statistical', 'machine_learning']),
                'forecast_status' => fake()->randomElement(['active', 'validated']),
                'accuracy_level' => fake()->randomElement(['medium', 'high']),
                'accuracy_score' => fake()->randomFloat(2, 65, 85),
                'confidence_level' => fake()->randomFloat(2, 70, 90),
                'source_id' => $energySource?->id,
                'forecast_start_time' => $forecastStartTime,
                'forecast_end_time' => $forecastEndTime,
                'generation_time' => $generationTime,
                'valid_from' => $generationTime->copy()->addHours(2),
                'time_zone' => 'Europe/Madrid',
                'time_resolution' => '1h',
                'forecast_periods' => rand(24, 168),
                'total_forecasted_value' => $totalValue,
                'forecast_unit' => 'EUR/MWh',
                'baseline_value' => $totalValue * fake()->randomFloat(2, 0.9, 1.1),
                'forecast_data' => json_encode([
                    'hourly_prices' => array_map(fn() => fake()->randomFloat(3, 0.04, 0.40), range(1, 24)),
                    'peak_price' => fake()->randomFloat(3, 0.25, 0.45),
                    'off_peak_price' => fake()->randomFloat(3, 0.03, 0.15)
                ]),
                'input_variables' => json_encode([
                    'demand_level' => fake()->randomElement(['high', 'medium', 'low']),
                    'fuel_costs' => fake()->randomFloat(2, 30, 80),
                    'carbon_price' => fake()->randomFloat(2, 20, 60)
                ]),
                'tags' => json_encode(['price', 'market', 'trading', 'cost']),
                'created_by' => $creator->id,
                'approved_by' => $approver,
                'approved_at' => $approver ? fake()->optional(0.7)->dateTimeBetween($generationTime, 'now') : null,
            ]);
        }
    }

    private function createWeatherForecasts($users, $energySources): void
    {
        $this->command->info('🌤️ Creando pronósticos meteorológicos...');

        for ($i = 0; $i < 12; $i++) {
            $creator = $users->random();
            $energySource = $energySources->isEmpty() ? null : $energySources->random();

            $forecastStartTime = Carbon::now()->addHours(rand(6, 48));
            $forecastEndTime = $forecastStartTime->copy()->addDays(rand(3, 14));
            $generationTime = Carbon::now()->subHours(rand(1, 6));

            EnergyForecast::create([
                'forecast_number' => 'WTH-' . strtoupper(fake()->bothify('####-??')),
                'name' => 'Pronóstico Meteorológico ' . fake()->words(2, true),
                'description' => fake()->sentence,
                'forecast_type' => 'weather',
                'forecast_horizon' => fake()->randomElement(['hourly', 'daily']),
                'forecast_method' => fake()->randomElement(['physical_model', 'hybrid']),
                'forecast_status' => 'active',
                'accuracy_level' => fake()->randomElement(['high', 'very_high']),
                'accuracy_score' => fake()->randomFloat(2, 80, 95),
                'confidence_level' => fake()->randomFloat(2, 85, 98),
                'source_id' => $energySource?->id,
                'forecast_start_time' => $forecastStartTime,
                'forecast_end_time' => $forecastEndTime,
                'generation_time' => $generationTime,
                'valid_from' => $generationTime->copy()->addMinutes(15),
                'time_zone' => 'Europe/Madrid',
                'time_resolution' => fake()->randomElement(['1h', '3h', '6h']),
                'forecast_periods' => rand(24, 336),
                'forecast_unit' => 'various',
                'weather_data' => json_encode([
                    'temperature' => fake()->randomFloat(1, 10, 30),
                    'wind_speed' => fake()->randomFloat(1, 2, 20),
                    'wind_direction' => fake()->randomFloat(0, 0, 360),
                    'solar_radiation' => fake()->randomFloat(2, 100, 900),
                    'cloud_cover' => fake()->randomFloat(1, 0, 100),
                    'precipitation' => fake()->randomFloat(1, 0, 15),
                    'humidity' => fake()->randomFloat(1, 30, 90),
                    'pressure' => fake()->randomFloat(1, 990, 1025)
                ]),
                'input_variables' => json_encode([
                    'satellite_data' => true,
                    'ground_stations' => fake()->numberBetween(5, 20),
                    'model_resolution' => fake()->randomElement(['1km', '5km', '10km'])
                ]),
                'tags' => json_encode(['weather', 'meteorology', 'renewable', 'solar', 'wind']),
                'created_by' => $creator->id,
            ]);
        }
    }

    private function createConsumptionForecasts($users, $energySources): void
    {
        $this->command->info('⚡ Creando pronósticos de consumo...');

        for ($i = 0; $i < 18; $i++) {
            $creator = $users->random();
            $energySource = $energySources->isEmpty() ? null : $energySources->random();
            $approver = fake()->optional(0.7)->randomElement($users->pluck('id')->toArray());
            $validator = fake()->optional(0.5)->randomElement($users->pluck('id')->toArray());

            $forecastStartTime = Carbon::now()->addDays(rand(1, 20));
            $forecastEndTime = $forecastStartTime->copy()->addDays(rand(7, 45));
            $generationTime = Carbon::now()->subHours(rand(1, 36));

            $totalValue = fake()->randomFloat(2, 1500, 12000);

            EnergyForecast::create([
                'forecast_number' => 'CON-' . strtoupper(fake()->bothify('####-??')),
                'name' => 'Pronóstico de Consumo ' . fake()->words(2, true),
                'description' => fake()->sentence,
                'forecast_type' => 'consumption',
                'forecast_horizon' => fake()->randomElement(['daily', 'weekly', 'monthly']),
                'forecast_method' => fake()->randomElement(['machine_learning', 'statistical', 'hybrid']),
                'forecast_status' => fake()->randomElement(['active', 'validated', 'draft']),
                'accuracy_level' => fake()->randomElement(['medium', 'high']),
                'accuracy_score' => fake()->randomFloat(2, 70, 90),
                'confidence_level' => fake()->randomFloat(2, 75, 92),
                'source_id' => $energySource?->id,
                'forecast_start_time' => $forecastStartTime,
                'forecast_end_time' => $forecastEndTime,
                'generation_time' => $generationTime,
                'valid_from' => $generationTime->copy()->addHours(1),
                'valid_until' => fake()->optional(0.6)->dateTimeBetween($forecastEndTime, '+4 months'),
                'time_zone' => 'Europe/Madrid',
                'time_resolution' => fake()->randomElement(['1h', '6h', '24h']),
                'forecast_periods' => rand(24, 720),
                'total_forecasted_value' => $totalValue,
                'forecast_unit' => 'kWh',
                'baseline_value' => $totalValue * fake()->randomFloat(2, 0.85, 1.15),
                'trend_value' => $totalValue * fake()->randomFloat(2, -0.08, 0.08),
                'seasonal_value' => $totalValue * fake()->randomFloat(2, -0.18, 0.18),
                'forecast_data' => json_encode([
                    'residential_consumption' => fake()->randomFloat(2, 500, 2000),
                    'commercial_consumption' => fake()->randomFloat(2, 800, 3000),
                    'industrial_consumption' => fake()->randomFloat(2, 1000, 5000),
                    'peak_hours' => ['09:00-12:00', '18:00-22:00']
                ]),
                'input_variables' => json_encode([
                    'customer_count' => fake()->numberBetween(1000, 50000),
                    'average_consumption' => fake()->randomFloat(2, 250, 600),
                    'seasonal_factor' => fake()->randomFloat(2, 0.8, 1.3)
                ]),
                'tags' => json_encode(['consumption', 'demand', 'residential', 'commercial']),
                'created_by' => $creator->id,
                'approved_by' => $approver,
                'approved_at' => $approver ? fake()->optional(0.8)->dateTimeBetween($generationTime, 'now') : null,
                'validated_by' => $validator,
                'validated_at' => $validator ? fake()->optional(0.5)->dateTimeBetween($generationTime, 'now') : null,
                'notes' => fake()->optional(0.6)->sentence,
            ]);
        }
    }
}
