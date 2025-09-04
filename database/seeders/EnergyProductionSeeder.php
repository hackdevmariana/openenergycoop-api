<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyProduction;
use App\Models\User;
use App\Models\UserAsset;
use App\Models\EnergyStorage;
use App\Models\Provider;
use Carbon\Carbon;

class EnergyProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('⚡ Creando datos de producción energética...');

        $users = User::all();
        $userAssets = UserAsset::all();
        $energyStorages = EnergyStorage::all();
        $providers = Provider::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar datos existentes
        EnergyProduction::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏠 Activos de usuario disponibles: {$userAssets->count()}");
        $this->command->info("🔋 Almacenamientos disponibles: {$energyStorages->count()}");
        $this->command->info("🏢 Proveedores disponibles: {$providers->count()}");

        // Crear diferentes tipos de producción
        $this->createSolarProduction($users, $userAssets, $energyStorages, $providers);
        $this->createWindProduction($users, $userAssets, $energyStorages, $providers);
        $this->createHydroProduction($users, $userAssets, $energyStorages, $providers);
        $this->createBiomassProduction($users, $userAssets, $energyStorages, $providers);
        $this->createCombinedProduction($users, $userAssets, $energyStorages, $providers);

        $this->command->info('✅ EnergyProductionSeeder completado. Se crearon ' . EnergyProduction::count() . ' registros de producción.');
    }

    private function createSolarProduction($users, $userAssets, $energyStorages, $providers): void
    {
        $this->command->info('☀️ Creando producción solar...');

        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $userAsset = $userAssets->isEmpty() ? null : $userAssets->random();
            $energyStorage = $energyStorages->isEmpty() ? null : $energyStorages->random();
            $provider = $providers->isEmpty() ? null : $providers->random();

            $productionDateTime = Carbon::now()->subDays(rand(0, 90))->setHour(rand(6, 18))->setMinute(rand(0, 59));
            $productionKwh = fake()->randomFloat(4, 0.1, 50);
            $peakPowerKw = fake()->randomFloat(3, 1, 20);
            $irradiance = fake()->randomFloat(2, 200, 1200);

            EnergyProduction::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'user_asset_id' => $userAsset?->id,
                'energy_storage_id' => $energyStorage?->id,
                'system_id' => 'SOLAR-' . strtoupper(fake()->bothify('##??####')),
                'inverter_id' => 'INV-' . strtoupper(fake()->bothify('##??####')),
                'production_datetime' => $productionDateTime,
                'production_date' => $productionDateTime->toDateString(),
                'production_time' => $productionDateTime->toTimeString(),
                'period_type' => fake()->randomElement(['instant', 'hourly', 'daily']),
                'energy_source' => 'solar_pv',
                'production_kwh' => $productionKwh,
                'peak_power_kw' => $peakPowerKw,
                'average_power_kw' => $productionKwh / rand(1, 24),
                'instantaneous_power_kw' => fake()->randomFloat(3, 0.5, $peakPowerKw),
                'self_consumption_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.7),
                'grid_injection_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.8),
                'storage_charge_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.3),
                'curtailed_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.1),
                'system_efficiency' => fake()->randomFloat(2, 85, 98),
                'inverter_efficiency' => fake()->randomFloat(2, 90, 99),
                'performance_ratio' => fake()->randomFloat(2, 70, 95),
                'capacity_factor' => fake()->randomFloat(2, 15, 85),
                'irradiance_w_m2' => $irradiance,
                'ambient_temperature' => fake()->randomFloat(2, 15, 35),
                'module_temperature' => fake()->randomFloat(2, 25, 65),
                'humidity_percentage' => fake()->randomFloat(2, 30, 80),
                'feed_in_tariff_eur_kwh' => fake()->randomFloat(5, 0.05, 0.15),
                'market_price_eur_kwh' => fake()->randomFloat(5, 0.08, 0.25),
                'revenue_eur' => $productionKwh * fake()->randomFloat(4, 0.05, 0.15),
                'savings_eur' => fake()->randomFloat(2, 5, 50),
                'voltage_v' => fake()->randomFloat(2, 220, 400),
                'frequency_hz' => fake()->randomFloat(3, 49.8, 50.2),
                'power_factor' => fake()->randomFloat(3, 0.95, 1.0),
                'co2_avoided_kg' => $productionKwh * 0.5,
                'carbon_intensity_avoided' => fake()->randomFloat(5, 0.3, 0.8),
                'renewable_percentage' => 100,
                'operational_status' => fake()->randomElement(['online', 'online', 'online', 'maintenance']),
                'status_notes' => fake()->optional(0.2)->sentence,
                'underperformance_alert' => fake()->boolean(5),
                'cleaning_required' => fake()->boolean(10),
                'last_cleaning_date' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
                'soiling_losses_percentage' => fake()->optional(0.3)->randomFloat(2, 1, 8),
                'shading_losses_percentage' => fake()->optional(0.2)->randomFloat(2, 1, 15),
                'inverter_temperature' => fake()->randomFloat(2, 25, 55),
                'inverter_status' => fake()->randomElement(['normal', 'normal', 'normal', 'warning']),
                'data_quality' => fake()->randomElement(['measured', 'measured', 'measured', 'estimated']),
                'is_validated' => fake()->boolean(95),
                'processed_at' => $productionDateTime->addMinutes(rand(1, 30)),
                'data_source' => fake()->randomElement(['inverter', 'monitoring_system', 'api']),
            ]);
        }
    }

    private function createWindProduction($users, $userAssets, $energyStorages, $providers): void
    {
        $this->command->info('💨 Creando producción eólica...');

        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $userAsset = $userAssets->isEmpty() ? null : $userAssets->random();
            $energyStorage = $energyStorages->isEmpty() ? null : $energyStorages->random();
            $provider = $providers->isEmpty() ? null : $providers->random();

            $productionDateTime = Carbon::now()->subDays(rand(0, 90))->setHour(rand(0, 23))->setMinute(rand(0, 59));
            $productionKwh = fake()->randomFloat(4, 0.1, 100);
            $windSpeed = fake()->randomFloat(2, 3, 25);

            EnergyProduction::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'user_asset_id' => $userAsset?->id,
                'energy_storage_id' => $energyStorage?->id,
                'system_id' => 'WIND-' . strtoupper(fake()->bothify('##??####')),
                'inverter_id' => 'INV-' . strtoupper(fake()->bothify('##??####')),
                'production_datetime' => $productionDateTime,
                'production_date' => $productionDateTime->toDateString(),
                'production_time' => $productionDateTime->toTimeString(),
                'period_type' => fake()->randomElement(['instant', 'hourly', 'daily']),
                'energy_source' => 'wind',
                'production_kwh' => $productionKwh,
                'peak_power_kw' => fake()->randomFloat(3, 5, 100),
                'average_power_kw' => $productionKwh / rand(1, 24),
                'instantaneous_power_kw' => fake()->randomFloat(3, 1, 80),
                'self_consumption_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.6),
                'grid_injection_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.9),
                'storage_charge_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.2),
                'curtailed_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.05),
                'system_efficiency' => fake()->randomFloat(2, 80, 95),
                'inverter_efficiency' => fake()->randomFloat(2, 88, 98),
                'performance_ratio' => fake()->randomFloat(2, 65, 90),
                'capacity_factor' => fake()->randomFloat(2, 20, 75),
                'wind_speed_ms' => $windSpeed,
                'wind_direction_degrees' => fake()->randomFloat(1, 0, 360),
                'ambient_temperature' => fake()->randomFloat(2, -5, 35),
                'humidity_percentage' => fake()->randomFloat(2, 40, 90),
                'atmospheric_pressure' => fake()->randomFloat(2, 980, 1020),
                'feed_in_tariff_eur_kwh' => fake()->randomFloat(5, 0.04, 0.12),
                'market_price_eur_kwh' => fake()->randomFloat(5, 0.06, 0.20),
                'revenue_eur' => $productionKwh * fake()->randomFloat(4, 0.04, 0.12),
                'savings_eur' => fake()->randomFloat(2, 10, 100),
                'voltage_v' => fake()->randomFloat(2, 220, 400),
                'frequency_hz' => fake()->randomFloat(3, 49.8, 50.2),
                'power_factor' => fake()->randomFloat(3, 0.92, 1.0),
                'co2_avoided_kg' => $productionKwh * 0.6,
                'carbon_intensity_avoided' => fake()->randomFloat(5, 0.4, 0.9),
                'renewable_percentage' => 100,
                'operational_status' => fake()->randomElement(['online', 'online', 'online', 'maintenance']),
                'status_notes' => fake()->optional(0.1)->sentence,
                'underperformance_alert' => fake()->boolean(3),
                'data_quality' => fake()->randomElement(['measured', 'measured', 'measured', 'estimated']),
                'is_validated' => fake()->boolean(98),
                'processed_at' => $productionDateTime->addMinutes(rand(1, 30)),
                'data_source' => fake()->randomElement(['scada', 'monitoring_system', 'api']),
            ]);
        }
    }

    private function createHydroProduction($users, $userAssets, $energyStorages, $providers): void
    {
        $this->command->info('💧 Creando producción hidroeléctrica...');

        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $userAsset = $userAssets->isEmpty() ? null : $userAssets->random();
            $energyStorage = $energyStorages->isEmpty() ? null : $energyStorages->random();
            $provider = $providers->isEmpty() ? null : $providers->random();

            $productionDateTime = Carbon::now()->subDays(rand(0, 90))->setHour(rand(0, 23))->setMinute(rand(0, 59));
            $productionKwh = fake()->randomFloat(4, 1, 200);

            EnergyProduction::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'user_asset_id' => $userAsset?->id,
                'energy_storage_id' => $energyStorage?->id,
                'system_id' => 'HYDRO-' . strtoupper(fake()->bothify('##??####')),
                'inverter_id' => 'INV-' . strtoupper(fake()->bothify('##??####')),
                'production_datetime' => $productionDateTime,
                'production_date' => $productionDateTime->toDateString(),
                'production_time' => $productionDateTime->toTimeString(),
                'period_type' => fake()->randomElement(['instant', 'hourly', 'daily']),
                'energy_source' => 'hydro',
                'production_kwh' => $productionKwh,
                'peak_power_kw' => fake()->randomFloat(3, 10, 500),
                'average_power_kw' => $productionKwh / rand(1, 24),
                'instantaneous_power_kw' => fake()->randomFloat(3, 5, 400),
                'self_consumption_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.5),
                'grid_injection_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.95),
                'storage_charge_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.1),
                'curtailed_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.02),
                'system_efficiency' => fake()->randomFloat(2, 85, 98),
                'inverter_efficiency' => fake()->randomFloat(2, 92, 99),
                'performance_ratio' => fake()->randomFloat(2, 75, 95),
                'capacity_factor' => fake()->randomFloat(2, 30, 90),
                'ambient_temperature' => fake()->randomFloat(2, 5, 25),
                'humidity_percentage' => fake()->randomFloat(2, 60, 95),
                'feed_in_tariff_eur_kwh' => fake()->randomFloat(5, 0.03, 0.10),
                'market_price_eur_kwh' => fake()->randomFloat(5, 0.05, 0.18),
                'revenue_eur' => $productionKwh * fake()->randomFloat(4, 0.03, 0.10),
                'savings_eur' => fake()->randomFloat(2, 20, 200),
                'voltage_v' => fake()->randomFloat(2, 220, 400),
                'frequency_hz' => fake()->randomFloat(3, 49.8, 50.2),
                'power_factor' => fake()->randomFloat(3, 0.95, 1.0),
                'co2_avoided_kg' => $productionKwh * 0.4,
                'carbon_intensity_avoided' => fake()->randomFloat(5, 0.2, 0.6),
                'renewable_percentage' => 100,
                'operational_status' => fake()->randomElement(['online', 'online', 'online', 'maintenance']),
                'status_notes' => fake()->optional(0.1)->sentence,
                'underperformance_alert' => fake()->boolean(2),
                'data_quality' => fake()->randomElement(['measured', 'measured', 'measured', 'estimated']),
                'is_validated' => fake()->boolean(99),
                'processed_at' => $productionDateTime->addMinutes(rand(1, 30)),
                'data_source' => fake()->randomElement(['scada', 'monitoring_system', 'api']),
            ]);
        }
    }

    private function createBiomassProduction($users, $userAssets, $energyStorages, $providers): void
    {
        $this->command->info('🌱 Creando producción de biomasa...');

        for ($i = 0; $i < 15; $i++) {
            $user = $users->random();
            $userAsset = $userAssets->isEmpty() ? null : $userAssets->random();
            $energyStorage = $energyStorages->isEmpty() ? null : $energyStorages->random();
            $provider = $providers->isEmpty() ? null : $providers->random();

            $productionDateTime = Carbon::now()->subDays(rand(0, 90))->setHour(rand(0, 23))->setMinute(rand(0, 59));
            $productionKwh = fake()->randomFloat(4, 0.5, 50);

            EnergyProduction::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'user_asset_id' => $userAsset?->id,
                'energy_storage_id' => $energyStorage?->id,
                'system_id' => 'BIOMASS-' . strtoupper(fake()->bothify('##??####')),
                'inverter_id' => 'INV-' . strtoupper(fake()->bothify('##??####')),
                'production_datetime' => $productionDateTime,
                'production_date' => $productionDateTime->toDateString(),
                'production_time' => $productionDateTime->toTimeString(),
                'period_type' => fake()->randomElement(['instant', 'hourly', 'daily']),
                'energy_source' => 'biomass',
                'production_kwh' => $productionKwh,
                'peak_power_kw' => fake()->randomFloat(3, 2, 30),
                'average_power_kw' => $productionKwh / rand(1, 24),
                'instantaneous_power_kw' => fake()->randomFloat(3, 1, 25),
                'self_consumption_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.8),
                'grid_injection_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.6),
                'storage_charge_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.1),
                'curtailed_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.05),
                'system_efficiency' => fake()->randomFloat(2, 70, 90),
                'inverter_efficiency' => fake()->randomFloat(2, 85, 96),
                'performance_ratio' => fake()->randomFloat(2, 60, 85),
                'capacity_factor' => fake()->randomFloat(2, 40, 85),
                'ambient_temperature' => fake()->randomFloat(2, 15, 35),
                'humidity_percentage' => fake()->randomFloat(2, 40, 80),
                'feed_in_tariff_eur_kwh' => fake()->randomFloat(5, 0.06, 0.14),
                'market_price_eur_kwh' => fake()->randomFloat(5, 0.08, 0.22),
                'revenue_eur' => $productionKwh * fake()->randomFloat(4, 0.06, 0.14),
                'savings_eur' => fake()->randomFloat(2, 5, 80),
                'voltage_v' => fake()->randomFloat(2, 220, 400),
                'frequency_hz' => fake()->randomFloat(3, 49.8, 50.2),
                'power_factor' => fake()->randomFloat(3, 0.90, 1.0),
                'co2_avoided_kg' => $productionKwh * 0.3,
                'carbon_intensity_avoided' => fake()->randomFloat(5, 0.1, 0.4),
                'renewable_percentage' => 100,
                'operational_status' => fake()->randomElement(['online', 'online', 'online', 'maintenance']),
                'status_notes' => fake()->optional(0.1)->sentence,
                'underperformance_alert' => fake()->boolean(3),
                'data_quality' => fake()->randomElement(['measured', 'measured', 'measured', 'estimated']),
                'is_validated' => fake()->boolean(95),
                'processed_at' => $productionDateTime->addMinutes(rand(1, 30)),
                'data_source' => fake()->randomElement(['monitoring_system', 'api', 'scada']),
            ]);
        }
    }

    private function createCombinedProduction($users, $userAssets, $energyStorages, $providers): void
    {
        $this->command->info('🔗 Creando producción combinada...');

        for ($i = 0; $i < 25; $i++) {
            $user = $users->random();
            $userAsset = $userAssets->isEmpty() ? null : $userAssets->random();
            $energyStorage = $energyStorages->isEmpty() ? null : $energyStorages->random();
            $provider = $providers->isEmpty() ? null : $providers->random();

            $productionDateTime = Carbon::now()->subDays(rand(0, 90))->setHour(rand(0, 23))->setMinute(rand(0, 59));
            $productionKwh = fake()->randomFloat(4, 1, 150);

            EnergyProduction::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'user_asset_id' => $userAsset?->id,
                'energy_storage_id' => $energyStorage?->id,
                'system_id' => 'COMBINED-' . strtoupper(fake()->bothify('##??####')),
                'inverter_id' => 'INV-' . strtoupper(fake()->bothify('##??####')),
                'production_datetime' => $productionDateTime,
                'production_date' => $productionDateTime->toDateString(),
                'production_time' => $productionDateTime->toTimeString(),
                'period_type' => fake()->randomElement(['instant', 'hourly', 'daily']),
                'energy_source' => 'combined',
                'production_kwh' => $productionKwh,
                'peak_power_kw' => fake()->randomFloat(3, 5, 100),
                'average_power_kw' => $productionKwh / rand(1, 24),
                'instantaneous_power_kw' => fake()->randomFloat(3, 2, 80),
                'self_consumption_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.7),
                'grid_injection_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.8),
                'storage_charge_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.2),
                'curtailed_kwh' => fake()->randomFloat(4, 0, $productionKwh * 0.08),
                'system_efficiency' => fake()->randomFloat(2, 75, 95),
                'inverter_efficiency' => fake()->randomFloat(2, 88, 98),
                'performance_ratio' => fake()->randomFloat(2, 65, 90),
                'capacity_factor' => fake()->randomFloat(2, 25, 80),
                'irradiance_w_m2' => fake()->optional(0.4)->randomFloat(2, 100, 1200),
                'wind_speed_ms' => fake()->optional(0.3)->randomFloat(2, 0, 25),
                'ambient_temperature' => fake()->randomFloat(2, 10, 35),
                'humidity_percentage' => fake()->randomFloat(2, 35, 85),
                'feed_in_tariff_eur_kwh' => fake()->randomFloat(5, 0.04, 0.12),
                'market_price_eur_kwh' => fake()->randomFloat(5, 0.06, 0.20),
                'revenue_eur' => $productionKwh * fake()->randomFloat(4, 0.04, 0.12),
                'savings_eur' => fake()->randomFloat(2, 10, 120),
                'voltage_v' => fake()->randomFloat(2, 220, 400),
                'frequency_hz' => fake()->randomFloat(3, 49.8, 50.2),
                'power_factor' => fake()->randomFloat(3, 0.92, 1.0),
                'co2_avoided_kg' => $productionKwh * 0.45,
                'carbon_intensity_avoided' => fake()->randomFloat(5, 0.25, 0.7),
                'renewable_percentage' => fake()->randomFloat(2, 70, 100),
                'operational_status' => fake()->randomElement(['online', 'online', 'online', 'maintenance']),
                'status_notes' => fake()->optional(0.1)->sentence,
                'underperformance_alert' => fake()->boolean(4),
                'data_quality' => fake()->randomElement(['measured', 'measured', 'measured', 'estimated']),
                'is_validated' => fake()->boolean(96),
                'processed_at' => $productionDateTime->addMinutes(rand(1, 30)),
                'data_source' => fake()->randomElement(['monitoring_system', 'api', 'scada']),
            ]);
        }
    }
}
