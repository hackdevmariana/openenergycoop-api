<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyReading;
use App\Models\EnergyMeter;
use App\Models\User;
use App\Models\EnergyInstallation;
use App\Models\ConsumptionPoint;
use Carbon\Carbon;

class EnergyReadingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Creando lecturas energéticas...');

        $users = User::all();
        $meters = EnergyMeter::all();
        $installations = EnergyInstallation::all();
        $consumptionPoints = ConsumptionPoint::all();

        if ($users->isEmpty() || $meters->isEmpty()) {
            $this->command->error('❌ No hay usuarios o medidores disponibles.');
            return;
        }

        // Limpiar lecturas existentes
        EnergyReading::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🔌 Medidores disponibles: {$meters->count()}");
        $this->command->info("🏭 Instalaciones disponibles: {$installations->count()}");
        $this->command->info("📍 Puntos de consumo disponibles: {$consumptionPoints->count()}");

        // Crear diferentes tipos de lecturas
        $this->createInstantaneousReadings($users, $meters, $installations, $consumptionPoints);
        $this->createIntervalReadings($users, $meters, $installations, $consumptionPoints);
        $this->createCumulativeReadings($users, $meters, $installations, $consumptionPoints);
        $this->createDemandReadings($users, $meters, $installations, $consumptionPoints);
        $this->createEnergyReadings($users, $meters, $installations, $consumptionPoints);

        $this->command->info('✅ EnergyReadingSeeder completado. Se crearon ' . EnergyReading::count() . ' lecturas.');
    }

    private function createInstantaneousReadings($users, $meters, $installations, $consumptionPoints): void
    {
        $this->command->info('⚡ Creando lecturas instantáneas...');

        for ($i = 0; $i < 25; $i++) {
            $meter = $meters->random();
            $user = $users->random();
            $installation = $installations->isEmpty() ? null : $installations->random();
            $consumptionPoint = $consumptionPoints->isEmpty() ? null : $consumptionPoints->random();

            $readingValue = fake()->randomFloat(2, 10, 500);
            $previousValue = $readingValue - fake()->randomFloat(2, 1, 50);

            EnergyReading::create([
                'reading_number' => 'INST-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'meter_id' => $meter->id,
                'installation_id' => $installation?->id,
                'consumption_point_id' => $consumptionPoint?->id,
                'customer_id' => $user->id,
                'reading_type' => 'instantaneous',
                'reading_source' => fake()->randomElement(['automatic', 'remote', 'manual']),
                'reading_status' => fake()->randomElement(['valid', 'valid', 'valid', 'suspicious']),
                'reading_timestamp' => Carbon::now()->subMinutes(rand(0, 1440)),
                'reading_period' => '15 minutes',
                'reading_value' => $readingValue,
                'reading_unit' => 'kWh',
                'previous_reading_value' => $previousValue,
                'consumption_value' => $readingValue - $previousValue,
                'consumption_unit' => 'kWh',
                'active_energy' => $readingValue,
                'reactive_energy' => fake()->randomFloat(2, 0, 100),
                'instantaneous_power' => fake()->randomFloat(2, 5, 200),
                'apparent_power' => fake()->randomFloat(2, 10, 250),
                'reactive_power' => fake()->randomFloat(2, 0, 50),
                'power_factor' => fake()->randomFloat(2, 0.8, 1.0),
                'voltage_value' => fake()->randomFloat(1, 220, 400),
                'voltage_unit' => 'V',
                'current_value' => fake()->randomFloat(1, 1, 100),
                'current_unit' => 'A',
                'frequency_value' => fake()->randomFloat(1, 49.8, 50.2),
                'frequency_unit' => 'Hz',
                'temperature' => fake()->randomFloat(1, 15, 35),
                'temperature_unit' => '°C',
                'humidity' => fake()->randomFloat(1, 30, 80),
                'humidity_unit' => '%',
                'quality_score' => fake()->randomFloat(1, 85, 100),
                'quality_notes' => fake()->optional(0.3)->sentence,
                'is_validated' => fake()->boolean(80),
                'is_estimated' => fake()->boolean(10),
                'confidence_level' => fake()->randomFloat(1, 85, 100),
                'data_quality' => fake()->randomElement(['excellent', 'good', 'fair']),
                'data_source' => fake()->randomElement(['meter', 'api', 'scada']),
                'validation_notes' => fake()->optional(0.2)->sentence,
                'correction_notes' => fake()->optional(0.1)->sentence,
                'raw_data' => [
                    'raw_value' => $readingValue + fake()->randomFloat(2, -0.5, 0.5),
                    'timestamp' => now()->toISOString(),
                    'checksum' => fake()->md5(),
                ],
                'processed_data' => [
                    'calibrated_value' => $readingValue,
                    'correction_factor' => fake()->randomFloat(3, 0.98, 1.02),
                    'processing_time' => fake()->randomFloat(3, 0.001, 0.1),
                ],
                'alarms' => fake()->optional(0.1)->randomElements(['high_voltage', 'low_power_factor', 'overload'], rand(1, 2)),
                'events' => fake()->optional(0.2)->randomElements(['power_on', 'communication_lost', 'calibration_due'], rand(1, 2)),
                'tags' => fake()->optional(0.3)->randomElements(['peak_hours', 'off_peak', 'weekend', 'holiday'], rand(1, 3)),
                'metadata' => [
                    'firmware_version' => 'v' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'hardware_version' => 'HW-' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'communication_protocol' => fake()->randomElement(['Modbus TCP', 'DLMS/COSEM', 'IEC 61850']),
                ],
                'read_by' => $user->id,
                'validated_by' => fake()->optional(0.7)->randomElement($users->pluck('id')->toArray()),
                'validated_at' => fake()->optional(0.7)->dateTimeBetween('-1 hour', 'now'),
                'corrected_by' => fake()->optional(0.1)->randomElement($users->pluck('id')->toArray()),
                'corrected_at' => fake()->optional(0.1)->dateTimeBetween('-1 hour', 'now'),
                'created_by' => $user->id,
                'notes' => fake()->optional(0.2)->sentence,
            ]);

            $this->command->line("   ✅ Lectura instantánea: {$readingValue} kWh");
        }
    }

    private function createIntervalReadings($users, $meters, $installations, $consumptionPoints): void
    {
        $this->command->info('⏱️ Creando lecturas por intervalos...');

        for ($i = 0; $i < 20; $i++) {
            $meter = $meters->random();
            $user = $users->random();
            $installation = $installations->isEmpty() ? null : $installations->random();
            $consumptionPoint = $consumptionPoints->isEmpty() ? null : $consumptionPoints->random();

            $readingValue = fake()->randomFloat(2, 50, 1000);
            $previousValue = $readingValue - fake()->randomFloat(2, 10, 100);

            EnergyReading::create([
                'reading_number' => 'INT-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'meter_id' => $meter->id,
                'installation_id' => $installation?->id,
                'consumption_point_id' => $consumptionPoint?->id,
                'customer_id' => $user->id,
                'reading_type' => 'interval',
                'reading_source' => fake()->randomElement(['automatic', 'remote']),
                'reading_status' => fake()->randomElement(['valid', 'valid', 'valid', 'estimated']),
                'reading_timestamp' => Carbon::now()->subHours(rand(1, 24)),
                'reading_period' => '1 hour',
                'reading_value' => $readingValue,
                'reading_unit' => 'kWh',
                'previous_reading_value' => $previousValue,
                'consumption_value' => $readingValue - $previousValue,
                'consumption_unit' => 'kWh',
                'active_energy' => $readingValue,
                'reactive_energy' => fake()->randomFloat(2, 0, 200),
                'instantaneous_power' => fake()->randomFloat(2, 20, 500),
                'apparent_power' => fake()->randomFloat(2, 25, 600),
                'reactive_power' => fake()->randomFloat(2, 0, 100),
                'power_factor' => fake()->randomFloat(2, 0.85, 0.98),
                'voltage_value' => fake()->randomFloat(1, 220, 400),
                'voltage_unit' => 'V',
                'current_value' => fake()->randomFloat(1, 5, 150),
                'current_unit' => 'A',
                'frequency_value' => fake()->randomFloat(1, 49.9, 50.1),
                'frequency_unit' => 'Hz',
                'temperature' => fake()->randomFloat(1, 18, 32),
                'temperature_unit' => '°C',
                'humidity' => fake()->randomFloat(1, 40, 70),
                'humidity_unit' => '%',
                'quality_score' => fake()->randomFloat(1, 90, 100),
                'quality_notes' => fake()->optional(0.2)->sentence,
                'is_validated' => fake()->boolean(90),
                'is_estimated' => fake()->boolean(5),
                'confidence_level' => fake()->randomFloat(1, 90, 100),
                'data_quality' => fake()->randomElement(['excellent', 'good']),
                'data_source' => fake()->randomElement(['meter', 'api', 'scada']),
                'validation_notes' => fake()->optional(0.1)->sentence,
                'correction_notes' => fake()->optional(0.05)->sentence,
                'raw_data' => [
                    'raw_value' => $readingValue + fake()->randomFloat(2, -0.2, 0.2),
                    'timestamp' => now()->toISOString(),
                    'checksum' => fake()->md5(),
                ],
                'processed_data' => [
                    'calibrated_value' => $readingValue,
                    'correction_factor' => fake()->randomFloat(3, 0.99, 1.01),
                    'processing_time' => fake()->randomFloat(3, 0.001, 0.05),
                ],
                'alarms' => fake()->optional(0.05)->randomElements(['high_voltage', 'low_power_factor'], 1),
                'events' => fake()->optional(0.1)->randomElements(['power_on', 'communication_lost'], 1),
                'tags' => fake()->optional(0.2)->randomElements(['peak_hours', 'off_peak', 'weekend'], rand(1, 2)),
                'metadata' => [
                    'firmware_version' => 'v' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'hardware_version' => 'HW-' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'communication_protocol' => fake()->randomElement(['Modbus TCP', 'DLMS/COSEM', 'IEC 61850']),
                ],
                'read_by' => $user->id,
                'validated_by' => fake()->optional(0.8)->randomElement($users->pluck('id')->toArray()),
                'validated_at' => fake()->optional(0.8)->dateTimeBetween('-2 hours', 'now'),
                'corrected_by' => fake()->optional(0.05)->randomElement($users->pluck('id')->toArray()),
                'corrected_at' => fake()->optional(0.05)->dateTimeBetween('-2 hours', 'now'),
                'created_by' => $user->id,
                'notes' => fake()->optional(0.1)->sentence,
            ]);

            $this->command->line("   ✅ Lectura por intervalo: {$readingValue} kWh");
        }
    }

    private function createCumulativeReadings($users, $meters, $installations, $consumptionPoints): void
    {
        $this->command->info('📈 Creando lecturas acumulativas...');

        for ($i = 0; $i < 15; $i++) {
            $meter = $meters->random();
            $user = $users->random();
            $installation = $installations->isEmpty() ? null : $installations->random();
            $consumptionPoint = $consumptionPoints->isEmpty() ? null : $consumptionPoints->random();

            $readingValue = fake()->randomFloat(2, 1000, 50000);
            $previousValue = $readingValue - fake()->randomFloat(2, 100, 1000);

            EnergyReading::create([
                'reading_number' => 'CUM-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'meter_id' => $meter->id,
                'installation_id' => $installation?->id,
                'consumption_point_id' => $consumptionPoint?->id,
                'customer_id' => $user->id,
                'reading_type' => 'cumulative',
                'reading_source' => fake()->randomElement(['automatic', 'remote', 'manual']),
                'reading_status' => fake()->randomElement(['valid', 'valid', 'valid', 'corrected']),
                'reading_timestamp' => Carbon::now()->subDays(rand(1, 30)),
                'reading_period' => '1 day',
                'reading_value' => $readingValue,
                'reading_unit' => 'kWh',
                'previous_reading_value' => $previousValue,
                'consumption_value' => $readingValue - $previousValue,
                'consumption_unit' => 'kWh',
                'active_energy' => $readingValue,
                'reactive_energy' => fake()->randomFloat(2, 0, 5000),
                'instantaneous_power' => fake()->randomFloat(2, 50, 1000),
                'apparent_power' => fake()->randomFloat(2, 60, 1200),
                'reactive_power' => fake()->randomFloat(2, 0, 200),
                'power_factor' => fake()->randomFloat(2, 0.8, 0.95),
                'voltage_value' => fake()->randomFloat(1, 220, 400),
                'voltage_unit' => 'V',
                'current_value' => fake()->randomFloat(1, 10, 200),
                'current_unit' => 'A',
                'frequency_value' => fake()->randomFloat(1, 49.8, 50.2),
                'frequency_unit' => 'Hz',
                'temperature' => fake()->randomFloat(1, 15, 35),
                'temperature_unit' => '°C',
                'humidity' => fake()->randomFloat(1, 30, 80),
                'humidity_unit' => '%',
                'quality_score' => fake()->randomFloat(1, 85, 100),
                'quality_notes' => fake()->optional(0.3)->sentence,
                'is_validated' => fake()->boolean(85),
                'is_estimated' => fake()->boolean(8),
                'confidence_level' => fake()->randomFloat(1, 85, 100),
                'data_quality' => fake()->randomElement(['excellent', 'good', 'fair']),
                'data_source' => fake()->randomElement(['meter', 'manual', 'api']),
                'validation_notes' => fake()->optional(0.2)->sentence,
                'correction_notes' => fake()->optional(0.1)->sentence,
                'raw_data' => [
                    'raw_value' => $readingValue + fake()->randomFloat(2, -1, 1),
                    'timestamp' => now()->toISOString(),
                    'checksum' => fake()->md5(),
                ],
                'processed_data' => [
                    'calibrated_value' => $readingValue,
                    'correction_factor' => fake()->randomFloat(3, 0.98, 1.02),
                    'processing_time' => fake()->randomFloat(3, 0.001, 0.1),
                ],
                'alarms' => fake()->optional(0.1)->randomElements(['high_voltage', 'low_power_factor', 'overload'], rand(1, 2)),
                'events' => fake()->optional(0.15)->randomElements(['power_on', 'communication_lost', 'calibration_due'], rand(1, 2)),
                'tags' => fake()->optional(0.25)->randomElements(['peak_hours', 'off_peak', 'weekend', 'holiday'], rand(1, 3)),
                'metadata' => [
                    'firmware_version' => 'v' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'hardware_version' => 'HW-' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'communication_protocol' => fake()->randomElement(['Modbus TCP', 'DLMS/COSEM', 'IEC 61850']),
                ],
                'read_by' => $user->id,
                'validated_by' => fake()->optional(0.75)->randomElement($users->pluck('id')->toArray()),
                'validated_at' => fake()->optional(0.75)->dateTimeBetween('-1 day', 'now'),
                'corrected_by' => fake()->optional(0.1)->randomElement($users->pluck('id')->toArray()),
                'corrected_at' => fake()->optional(0.1)->dateTimeBetween('-1 day', 'now'),
                'created_by' => $user->id,
                'notes' => fake()->optional(0.15)->sentence,
            ]);

            $this->command->line("   ✅ Lectura acumulativa: {$readingValue} kWh");
        }
    }

    private function createDemandReadings($users, $meters, $installations, $consumptionPoints): void
    {
        $this->command->info('⚡ Creando lecturas de demanda...');

        for ($i = 0; $i < 12; $i++) {
            $meter = $meters->random();
            $user = $users->random();
            $installation = $installations->isEmpty() ? null : $installations->random();
            $consumptionPoint = $consumptionPoints->isEmpty() ? null : $consumptionPoints->random();

            $demandValue = fake()->randomFloat(2, 20, 800);

            EnergyReading::create([
                'reading_number' => 'DEM-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'meter_id' => $meter->id,
                'installation_id' => $installation?->id,
                'consumption_point_id' => $consumptionPoint?->id,
                'customer_id' => $user->id,
                'reading_type' => 'demand',
                'reading_source' => fake()->randomElement(['automatic', 'remote']),
                'reading_status' => fake()->randomElement(['valid', 'valid', 'valid', 'suspicious']),
                'reading_timestamp' => Carbon::now()->subHours(rand(1, 12)),
                'reading_period' => '15 minutes',
                'reading_value' => $demandValue,
                'reading_unit' => 'kW',
                'demand_value' => $demandValue,
                'demand_unit' => 'kW',
                'instantaneous_power' => $demandValue,
                'apparent_power' => $demandValue * fake()->randomFloat(2, 1.0, 1.2),
                'reactive_power' => $demandValue * fake()->randomFloat(2, 0.1, 0.3),
                'power_factor' => fake()->randomFloat(2, 0.85, 0.98),
                'voltage_value' => fake()->randomFloat(1, 220, 400),
                'voltage_unit' => 'V',
                'current_value' => $demandValue / fake()->randomFloat(1, 200, 400),
                'current_unit' => 'A',
                'frequency_value' => fake()->randomFloat(1, 49.9, 50.1),
                'frequency_unit' => 'Hz',
                'temperature' => fake()->randomFloat(1, 18, 32),
                'temperature_unit' => '°C',
                'humidity' => fake()->randomFloat(1, 40, 70),
                'humidity_unit' => '%',
                'quality_score' => fake()->randomFloat(1, 88, 100),
                'quality_notes' => fake()->optional(0.2)->sentence,
                'is_validated' => fake()->boolean(85),
                'is_estimated' => fake()->boolean(7),
                'confidence_level' => fake()->randomFloat(1, 88, 100),
                'data_quality' => fake()->randomElement(['excellent', 'good']),
                'data_source' => fake()->randomElement(['meter', 'api', 'scada']),
                'validation_notes' => fake()->optional(0.15)->sentence,
                'correction_notes' => fake()->optional(0.08)->sentence,
                'raw_data' => [
                    'raw_value' => $demandValue + fake()->randomFloat(2, -0.5, 0.5),
                    'timestamp' => now()->toISOString(),
                    'checksum' => fake()->md5(),
                ],
                'processed_data' => [
                    'calibrated_value' => $demandValue,
                    'correction_factor' => fake()->randomFloat(3, 0.99, 1.01),
                    'processing_time' => fake()->randomFloat(3, 0.001, 0.05),
                ],
                'alarms' => fake()->optional(0.08)->randomElements(['high_demand', 'overload'], 1),
                'events' => fake()->optional(0.12)->randomElements(['peak_demand', 'demand_response'], 1),
                'tags' => fake()->optional(0.2)->randomElements(['peak_hours', 'demand_charge', 'critical'], rand(1, 2)),
                'metadata' => [
                    'firmware_version' => 'v' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'hardware_version' => 'HW-' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'communication_protocol' => fake()->randomElement(['Modbus TCP', 'DLMS/COSEM', 'IEC 61850']),
                ],
                'read_by' => $user->id,
                'validated_by' => fake()->optional(0.8)->randomElement($users->pluck('id')->toArray()),
                'validated_at' => fake()->optional(0.8)->dateTimeBetween('-2 hours', 'now'),
                'corrected_by' => fake()->optional(0.08)->randomElement($users->pluck('id')->toArray()),
                'corrected_at' => fake()->optional(0.08)->dateTimeBetween('-2 hours', 'now'),
                'created_by' => $user->id,
                'notes' => fake()->optional(0.1)->sentence,
            ]);

            $this->command->line("   ✅ Lectura de demanda: {$demandValue} kW");
        }
    }

    private function createEnergyReadings($users, $meters, $installations, $consumptionPoints): void
    {
        $this->command->info('🔋 Creando lecturas de energía...');

        for ($i = 0; $i < 18; $i++) {
            $meter = $meters->random();
            $user = $users->random();
            $installation = $installations->isEmpty() ? null : $installations->random();
            $consumptionPoint = $consumptionPoints->isEmpty() ? null : $consumptionPoints->random();

            $energyValue = fake()->randomFloat(2, 100, 2000);

            EnergyReading::create([
                'reading_number' => 'ENG-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'meter_id' => $meter->id,
                'installation_id' => $installation?->id,
                'consumption_point_id' => $consumptionPoint?->id,
                'customer_id' => $user->id,
                'reading_type' => 'energy',
                'reading_source' => fake()->randomElement(['automatic', 'remote', 'manual']),
                'reading_status' => fake()->randomElement(['valid', 'valid', 'valid', 'estimated']),
                'reading_timestamp' => Carbon::now()->subHours(rand(2, 48)),
                'reading_period' => '1 hour',
                'reading_value' => $energyValue,
                'reading_unit' => 'kWh',
                'active_energy' => $energyValue,
                'reactive_energy' => fake()->randomFloat(2, 0, 300),
                'instantaneous_power' => fake()->randomFloat(2, 30, 600),
                'apparent_power' => fake()->randomFloat(2, 35, 700),
                'reactive_power' => fake()->randomFloat(2, 0, 150),
                'power_factor' => fake()->randomFloat(2, 0.82, 0.96),
                'voltage_value' => fake()->randomFloat(1, 220, 400),
                'voltage_unit' => 'V',
                'current_value' => fake()->randomFloat(1, 8, 180),
                'current_unit' => 'A',
                'frequency_value' => fake()->randomFloat(1, 49.8, 50.2),
                'frequency_unit' => 'Hz',
                'temperature' => fake()->randomFloat(1, 16, 34),
                'temperature_unit' => '°C',
                'humidity' => fake()->randomFloat(1, 35, 75),
                'humidity_unit' => '%',
                'quality_score' => fake()->randomFloat(1, 87, 100),
                'quality_notes' => fake()->optional(0.25)->sentence,
                'is_validated' => fake()->boolean(82),
                'is_estimated' => fake()->boolean(10),
                'confidence_level' => fake()->randomFloat(1, 87, 100),
                'data_quality' => fake()->randomElement(['excellent', 'good', 'fair']),
                'data_source' => fake()->randomElement(['meter', 'api', 'scada', 'manual']),
                'validation_notes' => fake()->optional(0.18)->sentence,
                'correction_notes' => fake()->optional(0.09)->sentence,
                'raw_data' => [
                    'raw_value' => $energyValue + fake()->randomFloat(2, -0.8, 0.8),
                    'timestamp' => now()->toISOString(),
                    'checksum' => fake()->md5(),
                ],
                'processed_data' => [
                    'calibrated_value' => $energyValue,
                    'correction_factor' => fake()->randomFloat(3, 0.98, 1.02),
                    'processing_time' => fake()->randomFloat(3, 0.001, 0.08),
                ],
                'alarms' => fake()->optional(0.09)->randomElements(['high_energy', 'low_power_factor'], 1),
                'events' => fake()->optional(0.14)->randomElements(['energy_threshold', 'efficiency_alert'], 1),
                'tags' => fake()->optional(0.22)->randomElements(['peak_hours', 'off_peak', 'efficiency', 'monitoring'], rand(1, 3)),
                'metadata' => [
                    'firmware_version' => 'v' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'hardware_version' => 'HW-' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                    'communication_protocol' => fake()->randomElement(['Modbus TCP', 'DLMS/COSEM', 'IEC 61850']),
                ],
                'read_by' => $user->id,
                'validated_by' => fake()->optional(0.78)->randomElement($users->pluck('id')->toArray()),
                'validated_at' => fake()->optional(0.78)->dateTimeBetween('-3 hours', 'now'),
                'corrected_by' => fake()->optional(0.09)->randomElement($users->pluck('id')->toArray()),
                'corrected_at' => fake()->optional(0.09)->dateTimeBetween('-3 hours', 'now'),
                'created_by' => $user->id,
                'notes' => fake()->optional(0.12)->sentence,
            ]);

            $this->command->line("   ✅ Lectura de energía: {$energyValue} kWh");
        }
    }
}
