<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyConsumption;
use App\Models\User;
use App\Models\EnergyContract;
use Carbon\Carbon;

class EnergyConsumptionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('⚡ Creando datos de consumo energético...');

        $users = User::all();
        $contracts = EnergyContract::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar consumos existentes
        EnergyConsumption::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("📋 Contratos disponibles: {$contracts->count()}");

        // Crear diferentes tipos de consumos
        $this->createInstantConsumptions($users, $contracts);
        $this->createHourlyConsumptions($users, $contracts);
        $this->createDailyConsumptions($users, $contracts);
        $this->createMonthlyConsumptions($users, $contracts);
        $this->createBillingPeriodConsumptions($users, $contracts);

        $this->command->info('✅ EnergyConsumptionSeeder completado. Se crearon ' . EnergyConsumption::count() . ' registros de consumo.');
    }

    private function createInstantConsumptions($users, $contracts): void
    {
        $this->command->info('⚡ Creando consumos instantáneos...');

        foreach ($users as $user) {
            $userContracts = $contracts->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(5, 15); $i++) {
                $contract = $userContracts->isNotEmpty() ? $userContracts->random() : null;
                $measurementTime = Carbon::now()->subMinutes(rand(1, 1440)); // Últimas 24 horas
                
                $consumptionKwh = fake()->randomFloat(4, 0.1, 5.0);
                $peakPowerKw = fake()->randomFloat(3, 1.0, 15.0);
                $averagePowerKw = $consumptionKwh * fake()->randomFloat(3, 0.8, 1.2);
                
                EnergyConsumption::create([
                    'user_id' => $user->id,
                    'energy_contract_id' => $contract?->id,
                    'meter_id' => 'MTR-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'measurement_datetime' => $measurementTime,
                    'measurement_date' => $measurementTime->format('Y-m-d'),
                    'measurement_time' => $measurementTime->format('H:i:s'),
                    'period_type' => 'instant',
                    'consumption_kwh' => $consumptionKwh,
                    'peak_power_kw' => $peakPowerKw,
                    'average_power_kw' => $averagePowerKw,
                    'power_factor' => fake()->randomFloat(3, 0.85, 0.98),
                    'peak_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.3, 0.6),
                    'standard_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.2, 0.4),
                    'valley_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.1, 0.3),
                    'tariff_type' => fake()->randomElement(['2.0TD', '2.1TD', '3.0TD', '6.1TD']),
                    'unit_price_eur_kwh' => fake()->randomFloat(5, 0.08, 0.25),
                    'total_cost_eur' => $consumptionKwh * fake()->randomFloat(5, 0.08, 0.25),
                    'renewable_percentage' => fake()->randomFloat(2, 30, 100),
                    'grid_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.6, 1.0),
                    'self_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.0, 0.4),
                    'voltage_v' => fake()->randomFloat(2, 220, 400),
                    'frequency_hz' => fake()->randomFloat(3, 49.8, 50.2),
                    'thd_voltage_percentage' => fake()->randomFloat(2, 0.5, 3.0),
                    'thd_current_percentage' => fake()->randomFloat(2, 0.3, 2.5),
                    'efficiency_percentage' => fake()->randomFloat(2, 85, 98),
                    'estimated_co2_emissions_kg' => $consumptionKwh * fake()->randomFloat(3, 0.2, 0.4),
                    'carbon_intensity_kg_co2_kwh' => fake()->randomFloat(5, 0.2, 0.4),
                    'vs_previous_period_percentage' => fake()->randomFloat(2, -15, 15),
                    'vs_similar_users_percentage' => fake()->randomFloat(2, -10, 10),
                    'efficiency_score' => fake()->randomFloat(2, 75, 95),
                    'temperature_celsius' => fake()->randomFloat(2, 15, 35),
                    'humidity_percentage' => fake()->randomFloat(2, 30, 80),
                    'weather_condition' => fake()->randomElement(['sunny', 'cloudy', 'rainy', 'windy']),
                    'device_info' => json_encode([
                        'device_type' => 'smart_meter',
                        'firmware_version' => '2.1.3',
                        'last_calibration' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                        'communication_protocol' => 'DLMS/COSEM'
                    ]),
                    'data_quality' => fake()->randomElement(['excellent', 'good', 'fair']),
                    'is_estimated' => fake()->boolean(10),
                    'estimation_method' => fake()->optional(0.1)->randomElement(['linear_interpolation', 'historical_average', 'neural_network']),
                    'consumption_alert_triggered' => fake()->boolean(5),
                    'alert_threshold_kwh' => fake()->optional(0.05)->randomFloat(4, 3.0, 8.0),
                    'alert_message' => fake()->optional(0.05)->randomElement(['Consumo alto detectado', 'Pico de demanda', 'Anomalía en el consumo']),
                    'processed_at' => $measurementTime->addMinutes(rand(1, 5)),
                    'processing_metadata' => json_encode([
                        'processing_time_ms' => rand(50, 200),
                        'validation_rules_applied' => ['range_check', 'consistency_check'],
                        'data_source' => 'smart_meter_api'
                    ]),
                    'is_validated' => fake()->boolean(95),
                    'validation_notes' => fake()->optional(0.05)->randomElement(['Validación manual requerida', 'Datos verificados', 'Anomalía menor detectada']),
                    'created_at' => $measurementTime,
                    'updated_at' => $measurementTime,
                ]);
            }
        }

        $this->command->info("   ✅ Consumos instantáneos creados");
    }

    private function createHourlyConsumptions($users, $contracts): void
    {
        $this->command->info('🕐 Creando consumos por hora...');

        foreach ($users as $user) {
            $userContracts = $contracts->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(10, 30); $i++) {
                $contract = $userContracts->isNotEmpty() ? $userContracts->random() : null;
                $measurementTime = Carbon::now()->subHours(rand(1, 168)); // Última semana
                
                $consumptionKwh = fake()->randomFloat(4, 0.5, 15.0);
                $peakPowerKw = fake()->randomFloat(3, 2.0, 25.0);
                $averagePowerKw = $consumptionKwh;
                
                EnergyConsumption::create([
                    'user_id' => $user->id,
                    'energy_contract_id' => $contract?->id,
                    'meter_id' => 'MTR-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'measurement_datetime' => $measurementTime,
                    'measurement_date' => $measurementTime->format('Y-m-d'),
                    'measurement_time' => $measurementTime->format('H:i:s'),
                    'period_type' => 'hourly',
                    'consumption_kwh' => $consumptionKwh,
                    'peak_power_kw' => $peakPowerKw,
                    'average_power_kw' => $averagePowerKw,
                    'power_factor' => fake()->randomFloat(3, 0.88, 0.96),
                    'peak_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.4, 0.7),
                    'standard_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.2, 0.4),
                    'valley_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.1, 0.3),
                    'tariff_type' => fake()->randomElement(['2.0TD', '2.1TD', '3.0TD']),
                    'unit_price_eur_kwh' => fake()->randomFloat(5, 0.10, 0.30),
                    'total_cost_eur' => $consumptionKwh * fake()->randomFloat(5, 0.10, 0.30),
                    'renewable_percentage' => fake()->randomFloat(2, 40, 100),
                    'grid_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.5, 1.0),
                    'self_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.0, 0.5),
                    'voltage_v' => fake()->randomFloat(2, 220, 400),
                    'frequency_hz' => fake()->randomFloat(3, 49.9, 50.1),
                    'thd_voltage_percentage' => fake()->randomFloat(2, 0.3, 2.0),
                    'thd_current_percentage' => fake()->randomFloat(2, 0.2, 1.8),
                    'efficiency_percentage' => fake()->randomFloat(2, 88, 96),
                    'estimated_co2_emissions_kg' => $consumptionKwh * fake()->randomFloat(3, 0.25, 0.35),
                    'carbon_intensity_kg_co2_kwh' => fake()->randomFloat(5, 0.25, 0.35),
                    'vs_previous_period_percentage' => fake()->randomFloat(2, -12, 12),
                    'vs_similar_users_percentage' => fake()->randomFloat(2, -8, 8),
                    'efficiency_score' => fake()->randomFloat(2, 80, 92),
                    'temperature_celsius' => fake()->randomFloat(2, 18, 32),
                    'humidity_percentage' => fake()->randomFloat(2, 35, 75),
                    'weather_condition' => fake()->randomElement(['sunny', 'cloudy', 'partly_cloudy', 'rainy']),
                    'device_info' => json_encode([
                        'device_type' => 'smart_meter',
                        'firmware_version' => '2.1.3',
                        'last_calibration' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                        'communication_protocol' => 'DLMS/COSEM'
                    ]),
                    'data_quality' => fake()->randomElement(['excellent', 'good']),
                    'is_estimated' => fake()->boolean(5),
                    'estimation_method' => fake()->optional(0.05)->randomElement(['linear_interpolation', 'historical_average']),
                    'consumption_alert_triggered' => fake()->boolean(3),
                    'alert_threshold_kwh' => fake()->optional(0.03)->randomFloat(4, 8.0, 20.0),
                    'alert_message' => fake()->optional(0.03)->randomElement(['Consumo por encima del promedio', 'Pico de demanda horario']),
                    'processed_at' => $measurementTime->addMinutes(rand(2, 10)),
                    'processing_metadata' => json_encode([
                        'processing_time_ms' => rand(100, 500),
                        'validation_rules_applied' => ['range_check', 'consistency_check', 'trend_analysis'],
                        'data_source' => 'smart_meter_api'
                    ]),
                    'is_validated' => fake()->boolean(98),
                    'validation_notes' => fake()->optional(0.02)->randomElement(['Datos verificados', 'Consistencia confirmada']),
                    'created_at' => $measurementTime,
                    'updated_at' => $measurementTime,
                ]);
            }
        }

        $this->command->info("   ✅ Consumos por hora creados");
    }

    private function createDailyConsumptions($users, $contracts): void
    {
        $this->command->info('📅 Creando consumos diarios...');

        foreach ($users as $user) {
            $userContracts = $contracts->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(15, 45); $i++) {
                $contract = $userContracts->isNotEmpty() ? $userContracts->random() : null;
                $measurementTime = Carbon::now()->subDays(rand(1, 90)); // Últimos 3 meses
                
                $consumptionKwh = fake()->randomFloat(4, 5.0, 50.0);
                $peakPowerKw = fake()->randomFloat(3, 5.0, 40.0);
                $averagePowerKw = $consumptionKwh / 24;
                
                EnergyConsumption::create([
                    'user_id' => $user->id,
                    'energy_contract_id' => $contract?->id,
                    'meter_id' => 'MTR-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'measurement_datetime' => $measurementTime,
                    'measurement_date' => $measurementTime->format('Y-m-d'),
                    'measurement_time' => $measurementTime->format('H:i:s'),
                    'period_type' => 'daily',
                    'consumption_kwh' => $consumptionKwh,
                    'peak_power_kw' => $peakPowerKw,
                    'average_power_kw' => $averagePowerKw,
                    'power_factor' => fake()->randomFloat(3, 0.90, 0.97),
                    'peak_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.5, 0.8),
                    'standard_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.15, 0.35),
                    'valley_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.05, 0.15),
                    'tariff_type' => fake()->randomElement(['2.0TD', '2.1TD', '3.0TD', '6.1TD']),
                    'unit_price_eur_kwh' => fake()->randomFloat(5, 0.12, 0.28),
                    'total_cost_eur' => $consumptionKwh * fake()->randomFloat(5, 0.12, 0.28),
                    'renewable_percentage' => fake()->randomFloat(2, 50, 100),
                    'grid_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.4, 1.0),
                    'self_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.0, 0.6),
                    'voltage_v' => fake()->randomFloat(2, 220, 400),
                    'frequency_hz' => fake()->randomFloat(3, 49.95, 50.05),
                    'thd_voltage_percentage' => fake()->randomFloat(2, 0.2, 1.5),
                    'thd_current_percentage' => fake()->randomFloat(2, 0.1, 1.2),
                    'efficiency_percentage' => fake()->randomFloat(2, 90, 97),
                    'estimated_co2_emissions_kg' => $consumptionKwh * fake()->randomFloat(3, 0.22, 0.32),
                    'carbon_intensity_kg_co2_kwh' => fake()->randomFloat(5, 0.22, 0.32),
                    'vs_previous_period_percentage' => fake()->randomFloat(2, -20, 20),
                    'vs_similar_users_percentage' => fake()->randomFloat(2, -15, 15),
                    'efficiency_score' => fake()->randomFloat(2, 82, 94),
                    'temperature_celsius' => fake()->randomFloat(2, 16, 30),
                    'humidity_percentage' => fake()->randomFloat(2, 40, 70),
                    'weather_condition' => fake()->randomElement(['sunny', 'cloudy', 'partly_cloudy', 'rainy', 'windy']),
                    'device_info' => json_encode([
                        'device_type' => 'smart_meter',
                        'firmware_version' => '2.1.3',
                        'last_calibration' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                        'communication_protocol' => 'DLMS/COSEM'
                    ]),
                    'data_quality' => fake()->randomElement(['excellent', 'good', 'fair']),
                    'is_estimated' => fake()->boolean(3),
                    'estimation_method' => fake()->optional(0.03)->randomElement(['historical_average', 'weather_correction']),
                    'consumption_alert_triggered' => fake()->boolean(2),
                    'alert_threshold_kwh' => fake()->optional(0.02)->randomFloat(4, 30.0, 60.0),
                    'alert_message' => fake()->optional(0.02)->randomElement(['Consumo diario alto', 'Anomalía en el patrón de consumo']),
                    'processed_at' => $measurementTime->addHours(rand(1, 3)),
                    'processing_metadata' => json_encode([
                        'processing_time_ms' => rand(200, 800),
                        'validation_rules_applied' => ['range_check', 'consistency_check', 'trend_analysis', 'weather_correlation'],
                        'data_source' => 'smart_meter_api'
                    ]),
                    'is_validated' => fake()->boolean(99),
                    'validation_notes' => fake()->optional(0.01)->randomElement(['Datos verificados', 'Patrón normal']),
                    'created_at' => $measurementTime,
                    'updated_at' => $measurementTime,
                ]);
            }
        }

        $this->command->info("   ✅ Consumos diarios creados");
    }

    private function createMonthlyConsumptions($users, $contracts): void
    {
        $this->command->info('📊 Creando consumos mensuales...');

        foreach ($users as $user) {
            $userContracts = $contracts->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(6, 18); $i++) {
                $contract = $userContracts->isNotEmpty() ? $userContracts->random() : null;
                $measurementTime = Carbon::now()->subMonths(rand(1, 24)); // Últimos 2 años
                
                $consumptionKwh = fake()->randomFloat(4, 100.0, 800.0);
                $peakPowerKw = fake()->randomFloat(3, 10.0, 60.0);
                $averagePowerKw = $consumptionKwh / (24 * 30);
                
                EnergyConsumption::create([
                    'user_id' => $user->id,
                    'energy_contract_id' => $contract?->id,
                    'meter_id' => 'MTR-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'measurement_datetime' => $measurementTime,
                    'measurement_date' => $measurementTime->format('Y-m-d'),
                    'measurement_time' => $measurementTime->format('H:i:s'),
                    'period_type' => 'monthly',
                    'consumption_kwh' => $consumptionKwh,
                    'peak_power_kw' => $peakPowerKw,
                    'average_power_kw' => $averagePowerKw,
                    'power_factor' => fake()->randomFloat(3, 0.92, 0.98),
                    'peak_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.6, 0.85),
                    'standard_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.10, 0.25),
                    'valley_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.05, 0.15),
                    'tariff_type' => fake()->randomElement(['2.0TD', '2.1TD', '3.0TD', '6.1TD']),
                    'unit_price_eur_kwh' => fake()->randomFloat(5, 0.15, 0.35),
                    'total_cost_eur' => $consumptionKwh * fake()->randomFloat(5, 0.15, 0.35),
                    'renewable_percentage' => fake()->randomFloat(2, 60, 100),
                    'grid_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.3, 1.0),
                    'self_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.0, 0.7),
                    'voltage_v' => fake()->randomFloat(2, 220, 400),
                    'frequency_hz' => fake()->randomFloat(3, 49.98, 50.02),
                    'thd_voltage_percentage' => fake()->randomFloat(2, 0.1, 1.0),
                    'thd_current_percentage' => fake()->randomFloat(2, 0.05, 0.8),
                    'efficiency_percentage' => fake()->randomFloat(2, 92, 98),
                    'estimated_co2_emissions_kg' => $consumptionKwh * fake()->randomFloat(3, 0.20, 0.30),
                    'carbon_intensity_kg_co2_kwh' => fake()->randomFloat(5, 0.20, 0.30),
                    'vs_previous_period_percentage' => fake()->randomFloat(2, -25, 25),
                    'vs_similar_users_percentage' => fake()->randomFloat(2, -20, 20),
                    'efficiency_score' => fake()->randomFloat(2, 85, 96),
                    'temperature_celsius' => fake()->randomFloat(2, 12, 28),
                    'humidity_percentage' => fake()->randomFloat(2, 45, 65),
                    'weather_condition' => fake()->randomElement(['sunny', 'cloudy', 'partly_cloudy', 'rainy', 'windy', 'snowy']),
                    'device_info' => json_encode([
                        'device_type' => 'smart_meter',
                        'firmware_version' => '2.1.3',
                        'last_calibration' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                        'communication_protocol' => 'DLMS/COSEM'
                    ]),
                    'data_quality' => fake()->randomElement(['excellent', 'good']),
                    'is_estimated' => fake()->boolean(1),
                    'estimation_method' => fake()->optional(0.01)->randomElement(['historical_average', 'weather_correction', 'seasonal_adjustment']),
                    'consumption_alert_triggered' => fake()->boolean(1),
                    'alert_threshold_kwh' => fake()->optional(0.01)->randomFloat(4, 500.0, 1000.0),
                    'alert_message' => fake()->optional(0.01)->randomElement(['Consumo mensual alto', 'Anomalía estacional']),
                    'processed_at' => $measurementTime->addDays(rand(1, 5)),
                    'processing_metadata' => json_encode([
                        'processing_time_ms' => rand(500, 1500),
                        'validation_rules_applied' => ['range_check', 'consistency_check', 'trend_analysis', 'weather_correlation', 'seasonal_analysis'],
                        'data_source' => 'smart_meter_api'
                    ]),
                    'is_validated' => true,
                    'validation_notes' => fake()->optional(0.005)->randomElement(['Datos verificados', 'Análisis completado']),
                    'created_at' => $measurementTime,
                    'updated_at' => $measurementTime,
                ]);
            }
        }

        $this->command->info("   ✅ Consumos mensuales creados");
    }

    private function createBillingPeriodConsumptions($users, $contracts): void
    {
        $this->command->info('💳 Creando consumos por período de facturación...');

        foreach ($users as $user) {
            $userContracts = $contracts->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(3, 12); $i++) {
                $contract = $userContracts->isNotEmpty() ? $userContracts->random() : null;
                $measurementTime = Carbon::now()->subMonths(rand(1, 36)); // Últimos 3 años
                
                $consumptionKwh = fake()->randomFloat(4, 200.0, 1500.0);
                $peakPowerKw = fake()->randomFloat(3, 15.0, 80.0);
                $averagePowerKw = $consumptionKwh / (24 * 30 * 2); // Asumiendo período de 2 meses
                
                EnergyConsumption::create([
                    'user_id' => $user->id,
                    'energy_contract_id' => $contract?->id,
                    'meter_id' => 'MTR-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'measurement_datetime' => $measurementTime,
                    'measurement_date' => $measurementTime->format('Y-m-d'),
                    'measurement_time' => $measurementTime->format('H:i:s'),
                    'period_type' => 'billing_period',
                    'consumption_kwh' => $consumptionKwh,
                    'peak_power_kw' => $peakPowerKw,
                    'average_power_kw' => $averagePowerKw,
                    'power_factor' => fake()->randomFloat(3, 0.94, 0.99),
                    'peak_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.65, 0.90),
                    'standard_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.08, 0.20),
                    'valley_hours_consumption' => $consumptionKwh * fake()->randomFloat(4, 0.02, 0.10),
                    'tariff_type' => fake()->randomElement(['2.0TD', '2.1TD', '3.0TD', '6.1TD']),
                    'unit_price_eur_kwh' => fake()->randomFloat(5, 0.18, 0.40),
                    'total_cost_eur' => $consumptionKwh * fake()->randomFloat(5, 0.18, 0.40),
                    'renewable_percentage' => fake()->randomFloat(2, 70, 100),
                    'grid_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.2, 1.0),
                    'self_consumption_kwh' => $consumptionKwh * fake()->randomFloat(4, 0.0, 0.8),
                    'voltage_v' => fake()->randomFloat(2, 220, 400),
                    'frequency_hz' => fake()->randomFloat(3, 49.99, 50.01),
                    'thd_voltage_percentage' => fake()->randomFloat(2, 0.05, 0.8),
                    'thd_current_percentage' => fake()->randomFloat(2, 0.03, 0.6),
                    'efficiency_percentage' => fake()->randomFloat(2, 94, 99),
                    'estimated_co2_emissions_kg' => $consumptionKwh * fake()->randomFloat(3, 0.18, 0.28),
                    'carbon_intensity_kg_co2_kwh' => fake()->randomFloat(5, 0.18, 0.28),
                    'vs_previous_period_percentage' => fake()->randomFloat(2, -30, 30),
                    'vs_similar_users_percentage' => fake()->randomFloat(2, -25, 25),
                    'efficiency_score' => fake()->randomFloat(2, 88, 98),
                    'temperature_celsius' => fake()->randomFloat(2, 10, 26),
                    'humidity_percentage' => fake()->randomFloat(2, 50, 60),
                    'weather_condition' => fake()->randomElement(['sunny', 'cloudy', 'partly_cloudy', 'rainy', 'windy', 'snowy', 'stormy']),
                    'device_info' => json_encode([
                        'device_type' => 'smart_meter',
                        'firmware_version' => '2.1.3',
                        'last_calibration' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                        'communication_protocol' => 'DLMS/COSEM'
                    ]),
                    'data_quality' => 'excellent',
                    'is_estimated' => false,
                    'estimation_method' => null,
                    'consumption_alert_triggered' => fake()->boolean(0.5),
                    'alert_threshold_kwh' => fake()->optional(0.005)->randomFloat(4, 1000.0, 2000.0),
                    'alert_message' => fake()->optional(0.005)->randomElement(['Consumo por período alto', 'Anomalía en el patrón anual']),
                    'processed_at' => $measurementTime->addDays(rand(3, 10)),
                    'processing_metadata' => json_encode([
                        'processing_time_ms' => rand(1000, 3000),
                        'validation_rules_applied' => ['range_check', 'consistency_check', 'trend_analysis', 'weather_correlation', 'seasonal_analysis', 'billing_validation'],
                        'data_source' => 'smart_meter_api'
                    ]),
                    'is_validated' => true,
                    'validation_notes' => fake()->optional(0.002)->randomElement(['Datos verificados', 'Análisis completo']),
                    'created_at' => $measurementTime,
                    'updated_at' => $measurementTime,
                ]);
            }
        }

        $this->command->info("   ✅ Consumos por período de facturación creados");
    }
}
