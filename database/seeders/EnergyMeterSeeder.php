<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyMeter;
use App\Models\User;
use App\Models\EnergyInstallation;
use App\Models\ConsumptionPoint;
use Carbon\Carbon;

class EnergyMeterSeeder extends Seeder
{
    private $serialCounter = 100000;

    public function run(): void
    {
        $this->command->info('🔌 Creando medidores energéticos...');

        $users = User::all();
        $installations = EnergyInstallation::all();
        $consumptionPoints = ConsumptionPoint::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar tabla antes de crear nuevos datos
        EnergyMeter::query()->delete();
        
        // Resetear contador
        $this->serialCounter = 100000;

        // 1. Medidores Inteligentes (Smart Meters)
        $this->createSmartMeters($users, $installations, $consumptionPoints);

        // 2. Medidores Digitales
        $this->createDigitalMeters($users, $installations, $consumptionPoints);

        // 3. Medidores Analógicos
        $this->createAnalogMeters($users, $installations, $consumptionPoints);

        // 4. Medidores de Alta Precisión
        $this->createHighAccuracyMeters($users, $installations, $consumptionPoints);

        // 5. Medidores Industriales
        $this->createIndustrialMeters($users, $installations, $consumptionPoints);

        // 6. Medidores Residenciales
        $this->createResidentialMeters($users, $installations, $consumptionPoints);

        $this->command->info('✅ Seeder completado. Total: ' . EnergyMeter::count() . ' medidores.');
    }

    private function getNextSerialNumber(): string
    {
        return 'SN-' . $this->serialCounter++;
    }

    private function createSmartMeters($users, $installations, $consumptionPoints): void
    {
        $this->command->info('🧠 Creando medidores inteligentes...');

        // Crear medidores inteligentes
        for ($i = 0; $i < 14; $i++) {
            $meter = EnergyMeter::create([
                'meter_number' => 'SMART-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => 'Smart Meter ' . fake()->randomElement(['Siemens', 'ABB', 'Schneider', 'Honeywell']) . ' ' . fake()->bothify('??-####'),
                'description' => fake()->optional(0.8)->sentence,
                'meter_type' => 'smart_meter',
                'status' => fake()->randomElement(['active', 'maintenance']),
                'meter_category' => 'electricity',
                'manufacturer' => fake()->randomElement(['Siemens', 'ABB', 'Schneider Electric', 'Honeywell']),
                'model' => fake()->bothify('??-####'),
                'serial_number' => $this->getNextSerialNumber(),
                'firmware_version' => 'v' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9) . '.' . fake()->numberBetween(0, 9),
                'hardware_version' => 'HW-' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                'voltage_rating' => fake()->randomElement([230, 400, 480]),
                'current_rating' => fake()->randomFloat(1, 16, 200),
                'phase_type' => fake()->randomElement(['single_phase', 'three_phase']),
                'connection_type' => 'direct',
                'accuracy_class' => fake()->randomFloat(1, 0.5, 1.0),
                'measurement_range_min' => 0.00,
                'measurement_range_max' => fake()->randomFloat(0, 100, 600),
                'measurement_unit' => 'kWh',
                'pulse_constant' => fake()->randomFloat(0, 100, 1000),
                'pulse_unit' => 'imp/kWh',
                'is_smart_meter' => true,
                'has_remote_reading' => true,
                'has_two_way_communication' => true,
                'communication_protocol' => fake()->randomElement(['Modbus TCP', 'DLMS/COSEM', 'IEC 61850', 'OPC UA']),
                'communication_frequency' => fake()->randomElement(['5 minutes', '15 minutes', '30 minutes']),
                'data_logging_interval' => fake()->randomElement([60, 300, 900]),
                'data_retention_days' => fake()->randomElement([365, 730, 1095]),
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'consumption_point_id' => $consumptionPoints->isEmpty() ? null : $consumptionPoints->random()->id,
                'customer_id' => $users->random()->id,
                'installation_date' => Carbon::now()->subDays(rand(30, 365)),
                'commissioning_date' => Carbon::now()->subDays(rand(25, 360)),
                'next_calibration_date' => Carbon::now()->addDays(rand(180, 730)),
                'warranty_expiry_date' => Carbon::now()->addDays(rand(365, 1825)),
                'installed_by' => $users->random()->id,
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Medidor inteligente: {$meter->name}");
        }
    }

    private function createDigitalMeters($users, $installations, $consumptionPoints): void
    {
        $this->command->info('📱 Creando medidores digitales...');

        // Crear medidores digitales
        for ($i = 0; $i < 8; $i++) {
            $meter = EnergyMeter::create([
                'meter_number' => 'DIGITAL-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => 'Digital Meter ' . fake()->randomElement(['Landis+Gyr', 'Itron', 'Elster', 'Sagemcom']) . ' ' . fake()->bothify('??-####'),
                'description' => fake()->optional(0.8)->sentence,
                'meter_type' => 'digital_meter',
                'status' => fake()->randomElement(['active', 'maintenance']),
                'meter_category' => 'electricity',
                'manufacturer' => fake()->randomElement(['Landis+Gyr', 'Itron', 'Elster', 'Sagemcom']),
                'model' => fake()->bothify('??-####'),
                'serial_number' => $this->getNextSerialNumber(),
                'firmware_version' => 'v' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9) . '.' . fake()->numberBetween(0, 9),
                'hardware_version' => 'HW-' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9),
                'voltage_rating' => fake()->randomElement([230, 400, 480]),
                'current_rating' => fake()->randomFloat(1, 16, 200),
                'phase_type' => fake()->randomElement(['single_phase', 'three_phase']),
                'connection_type' => 'direct',
                'accuracy_class' => fake()->randomFloat(1, 0.5, 1.0),
                'measurement_range_min' => 0.00,
                'measurement_range_max' => fake()->randomFloat(0, 100, 600),
                'measurement_unit' => 'kWh',
                'pulse_constant' => fake()->randomFloat(0, 100, 1000),
                'pulse_unit' => 'imp/kWh',
                'is_smart_meter' => false,
                'has_remote_reading' => fake()->boolean(70),
                'has_two_way_communication' => false,
                'communication_protocol' => fake()->optional(0.7)->randomElement(['Modbus RTU', 'M-Bus', 'DLMS/COSEM']),
                'communication_frequency' => fake()->optional(0.7)->randomElement(['15 minutes', '30 minutes', '1 hour']),
                'data_logging_interval' => fake()->optional(0.7)->randomElement([300, 900, 1800]),
                'data_retention_days' => fake()->optional(0.7)->randomElement([90, 180, 365]),
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'consumption_point_id' => $consumptionPoints->isEmpty() ? null : $consumptionPoints->random()->id,
                'customer_id' => $users->random()->id,
                'installation_date' => Carbon::now()->subDays(rand(30, 365)),
                'commissioning_date' => Carbon::now()->subDays(rand(25, 360)),
                'next_calibration_date' => Carbon::now()->addDays(rand(180, 730)),
                'warranty_expiry_date' => Carbon::now()->addDays(rand(365, 1825)),
                'installed_by' => $users->random()->id,
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Medidor digital: {$meter->name}");
        }
    }

    private function createAnalogMeters($users, $installations, $consumptionPoints): void
    {
        $this->command->info('🔢 Creando medidores analógicos...');

        // Crear medidores analógicos
        for ($i = 0; $i < 5; $i++) {
            $meter = EnergyMeter::create([
                'meter_number' => 'ANALOG-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => 'Analog Meter ' . fake()->randomElement(['Ferraris', 'Electromechanical', 'Induction']) . ' ' . fake()->bothify('??-####'),
                'description' => fake()->optional(0.6)->sentence,
                'meter_type' => 'analog_meter',
                'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
                'meter_category' => 'electricity',
                'manufacturer' => fake()->randomElement(['General Electric', 'Westinghouse', 'Sangamo']),
                'model' => fake()->bothify('??-####'),
                'serial_number' => $this->getNextSerialNumber(),
                'voltage_rating' => fake()->randomElement([220, 230]),
                'current_rating' => fake()->randomFloat(1, 16, 63),
                'phase_type' => 'single_phase',
                'connection_type' => 'direct',
                'accuracy_class' => fake()->randomFloat(1, 1.5, 2.5),
                'measurement_range_min' => 0.00,
                'measurement_range_max' => fake()->randomFloat(0, 50, 100),
                'measurement_unit' => 'kWh',
                'pulse_constant' => fake()->randomFloat(0, 100, 1000),
                'pulse_unit' => 'imp/kWh',
                'is_smart_meter' => false,
                'has_remote_reading' => false,
                'has_two_way_communication' => false,
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'consumption_point_id' => $consumptionPoints->isEmpty() ? null : $consumptionPoints->random()->id,
                'customer_id' => $users->random()->id,
                'installation_date' => Carbon::now()->subDays(rand(365, 2555)),
                'commissioning_date' => Carbon::now()->subDays(rand(363, 2553)),
                'next_calibration_date' => Carbon::now()->addDays(rand(30, 180)),
                'warranty_expiry_date' => Carbon::now()->subDays(rand(365, 1825)),
                'installed_by' => $users->random()->id,
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Medidor analógico: {$meter->name}");
        }
    }

    private function createHighAccuracyMeters($users, $installations, $consumptionPoints): void
    {
        $this->command->info('🎯 Creando medidores de alta precisión...');

        // Crear medidores de alta precisión
        for ($i = 0; $i < 3; $i++) {
            $meter = EnergyMeter::create([
                'meter_number' => 'PREC-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => 'High Accuracy Meter ' . fake()->randomElement(['Siemens', 'ABB', 'Schneider']) . ' ' . fake()->bothify('??-####') . ' ' . ($i + 1),
                'description' => 'Medidor de alta precisión para aplicaciones críticas',
                'meter_type' => fake()->randomElement(['smart_meter', 'digital_meter']),
                'status' => 'active',
                'meter_category' => 'electricity',
                'manufacturer' => fake()->randomElement(['Siemens', 'ABB', 'Schneider Electric']),
                'model' => fake()->bothify('??-####'),
                'serial_number' => $this->getNextSerialNumber(),
                'firmware_version' => 'v' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9) . '.' . fake()->numberBetween(0, 9),
                'voltage_rating' => fake()->randomElement([230, 400, 480]),
                'current_rating' => fake()->randomFloat(1, 16, 400),
                'phase_type' => fake()->randomElement(['single_phase', 'three_phase']),
                'connection_type' => 'direct',
                'accuracy_class' => fake()->randomFloat(1, 0.1, 0.5),
                'measurement_range_min' => 0.00,
                'measurement_range_max' => fake()->randomFloat(0, 100, 1000),
                'measurement_unit' => 'kWh',
                'pulse_constant' => fake()->randomFloat(0, 100, 1000),
                'pulse_unit' => 'imp/kWh',
                'is_smart_meter' => fake()->boolean(70),
                'has_remote_reading' => true,
                'has_two_way_communication' => fake()->boolean(60),
                'communication_protocol' => fake()->optional(0.7)->randomElement(['Modbus TCP', 'IEC 61850', 'OPC UA']),
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'consumption_point_id' => $consumptionPoints->isEmpty() ? null : $consumptionPoints->random()->id,
                'customer_id' => $users->random()->id,
                'installation_date' => Carbon::now()->subDays(rand(30, 730)),
                'commissioning_date' => Carbon::now()->subDays(rand(25, 725)),
                'next_calibration_date' => Carbon::now()->addDays(rand(90, 365)),
                'warranty_expiry_date' => Carbon::now()->addDays(rand(365, 1825)),
                'installed_by' => $users->random()->id,
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Medidor alta precisión: {$meter->name} (Clase {$meter->accuracy_class})");
        }
    }

    private function createIndustrialMeters($users, $installations, $consumptionPoints): void
    {
        $this->command->info('🏭 Creando medidores industriales...');

        // Crear medidores industriales
        for ($i = 0; $i < 2; $i++) {
            $meter = EnergyMeter::create([
                'meter_number' => 'IND-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => 'Industrial Meter ' . fake()->randomElement(['Siemens', 'ABB', 'Schneider']) . ' ' . fake()->bothify('??-####'),
                'description' => 'Medidor industrial de alta capacidad',
                'meter_type' => fake()->randomElement(['smart_meter', 'digital_meter']),
                'status' => fake()->randomElement(['active', 'maintenance']),
                'meter_category' => 'electricity',
                'manufacturer' => fake()->randomElement(['Siemens', 'ABB', 'Schneider Electric']),
                'model' => fake()->bothify('??-####'),
                'serial_number' => $this->getNextSerialNumber(),
                'firmware_version' => 'v' . fake()->numberBetween(1, 3) . '.' . fake()->numberBetween(0, 9) . '.' . fake()->numberBetween(0, 9),
                'voltage_rating' => fake()->randomElement([400, 480, 660]),
                'current_rating' => fake()->randomFloat(1, 200, 1000),
                'phase_type' => 'three_phase',
                'connection_type' => 'direct',
                'accuracy_class' => fake()->randomFloat(1, 0.2, 1.0),
                'measurement_range_min' => 0.00,
                'measurement_range_max' => fake()->randomFloat(0, 500, 2000),
                'measurement_unit' => 'kWh',
                'pulse_constant' => fake()->randomFloat(0, 100, 1000),
                'pulse_unit' => 'imp/kWh',
                'is_smart_meter' => true,
                'has_remote_reading' => true,
                'has_two_way_communication' => true,
                'communication_protocol' => fake()->randomElement(['Modbus TCP', 'IEC 61850', 'OPC UA']),
                'communication_frequency' => '5 minutes',
                'data_logging_interval' => 60,
                'data_retention_days' => 1095,
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'consumption_point_id' => $consumptionPoints->isEmpty() ? null : $consumptionPoints->random()->id,
                'customer_id' => $users->random()->id,
                'installation_date' => Carbon::now()->subDays(rand(90, 1095)),
                'commissioning_date' => Carbon::now()->subDays(rand(85, 1090)),
                'next_calibration_date' => Carbon::now()->addDays(rand(180, 365)),
                'warranty_expiry_date' => Carbon::now()->addDays(rand(365, 1825)),
                'installed_by' => $users->random()->id,
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Medidor industrial: {$meter->name} ({$meter->current_rating}A)");
        }
    }

    private function createResidentialMeters($users, $installations, $consumptionPoints): void
    {
        $this->command->info('🏠 Creando medidores residenciales...');

        // Crear medidores residenciales
        for ($i = 0; $i < 5; $i++) {
            $meter = EnergyMeter::create([
                'meter_number' => 'RES-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => 'Residential Meter ' . fake()->randomElement(['ABB', 'Schneider', 'GE']) . ' ' . fake()->bothify('??-####'),
                'description' => 'Medidor residencial estándar',
                'meter_type' => fake()->randomElement(['smart_meter', 'digital_meter', 'analog_meter']),
                'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
                'meter_category' => 'electricity',
                'manufacturer' => fake()->randomElement(['ABB', 'Schneider Electric', 'General Electric']),
                'model' => fake()->bothify('??-####'),
                'serial_number' => $this->getNextSerialNumber(),
                'voltage_rating' => fake()->randomElement([220, 230]),
                'current_rating' => fake()->randomFloat(1, 16, 63),
                'phase_type' => 'single_phase',
                'connection_type' => 'direct',
                'accuracy_class' => fake()->randomFloat(1, 1.0, 2.0),
                'measurement_range_min' => 0.00,
                'measurement_range_max' => fake()->randomFloat(0, 50, 100),
                'measurement_unit' => 'kWh',
                'pulse_constant' => fake()->randomFloat(0, 100, 1000),
                'pulse_unit' => 'imp/kWh',
                'is_smart_meter' => fake()->boolean(40),
                'has_remote_reading' => fake()->boolean(50),
                'has_two_way_communication' => fake()->boolean(30),
                'communication_protocol' => fake()->optional(0.4)->randomElement(['DLMS/COSEM', 'Modbus']),
                'installation_id' => $installations->isEmpty() ? null : $installations->random()->id,
                'consumption_point_id' => $consumptionPoints->isEmpty() ? null : $consumptionPoints->random()->id,
                'customer_id' => $users->random()->id,
                'installation_date' => Carbon::now()->subDays(rand(30, 1825)),
                'commissioning_date' => Carbon::now()->subDays(rand(25, 1820)),
                'next_calibration_date' => Carbon::now()->addDays(rand(90, 365)),
                'warranty_expiry_date' => Carbon::now()->addDays(rand(365, 1825)),
                'installed_by' => $users->random()->id,
                'managed_by' => $users->random()->id,
                'created_by' => $users->random()->id,
            ]);
            $this->command->line("   ✅ Medidor residencial: {$meter->name}");
        }
    }
}
