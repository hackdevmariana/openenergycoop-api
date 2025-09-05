<?php

namespace Database\Seeders;

use App\Models\EnergyTransfer;
use App\Models\User;
use App\Models\EnergyMeter;
use App\Models\EnergyCooperative;
use App\Models\EnergyInstallation;
use App\Models\EnergyStorage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnergyTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios para las relaciones
        $users = User::take(5)->get();
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios disponibles. Creando usuarios de ejemplo...');
            $users = collect([
                User::factory()->create(['name' => 'Admin Sistema', 'email' => 'admin@openenergycoop.com']),
                User::factory()->create(['name' => 'Operador Energía', 'email' => 'operador@openenergycoop.com']),
                User::factory()->create(['name' => 'Supervisor Transferencias', 'email' => 'supervisor@openenergycoop.com']),
            ]);
        }

        // Obtener medidores de energía
        $meters = EnergyMeter::take(10)->get();
        if ($meters->isEmpty()) {
            $this->command->warn('No hay medidores disponibles. Creando medidores de ejemplo...');
            $meters = collect([
                EnergyMeter::factory()->create(['name' => 'Medidor Solar Principal']),
                EnergyMeter::factory()->create(['name' => 'Medidor Eólico Norte']),
                EnergyMeter::factory()->create(['name' => 'Medidor Consumo Industrial']),
                EnergyMeter::factory()->create(['name' => 'Medidor Almacenamiento Baterías']),
                EnergyMeter::factory()->create(['name' => 'Medidor Red Eléctrica']),
            ]);
        }

        // Obtener cooperativas de energía
        $cooperatives = EnergyCooperative::take(3)->get();
        if ($cooperatives->isEmpty()) {
            $this->command->warn('No hay cooperativas disponibles. Creando cooperativas de ejemplo...');
            $cooperatives = collect([
                EnergyCooperative::factory()->create(['name' => 'Cooperativa Solar Madrid']),
                EnergyCooperative::factory()->create(['name' => 'Cooperativa Eólica Barcelona']),
                EnergyCooperative::factory()->create(['name' => 'Cooperativa Mixta Valencia']),
            ]);
        }

        // Obtener instalaciones de energía
        $installations = EnergyInstallation::take(5)->get();
        if ($installations->isEmpty()) {
            $this->command->warn('No hay instalaciones disponibles. Creando instalaciones de ejemplo...');
            $installations = collect([
                EnergyInstallation::factory()->create(['name' => 'Planta Solar Fotovoltaica']),
                EnergyInstallation::factory()->create(['name' => 'Parque Eólico']),
                EnergyInstallation::factory()->create(['name' => 'Central Hidroeléctrica']),
            ]);
        }

        // Obtener sistemas de almacenamiento
        $storages = EnergyStorage::take(3)->get();
        if ($storages->isEmpty()) {
            $this->command->warn('No hay sistemas de almacenamiento disponibles. Creando sistemas de ejemplo...');
            $storages = collect([
                EnergyStorage::factory()->create(['name' => 'Sistema Baterías Li-ion']),
                EnergyStorage::factory()->create(['name' => 'Almacenamiento Hidráulico']),
                EnergyStorage::factory()->create(['name' => 'Baterías de Flujo']),
            ]);
        }

        $this->command->info('Creando transferencias de energía de ejemplo...');

        // Crear transferencias de generación
        $this->createGenerationTransfers($users, $meters, $cooperatives, $installations);

        // Crear transferencias de consumo
        $this->createConsumptionTransfers($users, $meters, $cooperatives);

        // Crear transferencias de almacenamiento
        $this->createStorageTransfers($users, $meters, $storages);

        // Crear transferencias de red (importación/exportación)
        $this->createGridTransfers($users, $meters, $cooperatives);

        // Crear transferencias peer-to-peer
        $this->createPeerToPeerTransfers($users, $meters, $cooperatives);

        // Crear transferencias virtuales
        $this->createVirtualTransfers($users, $meters, $cooperatives);

        $this->command->info('Transferencias de energía creadas exitosamente.');
    }

    private function createGenerationTransfers($users, $meters, $cooperatives, $installations): void
    {
        $transferTypes = [
            EnergyTransfer::TRANSFER_TYPE_GENERATION,
            EnergyTransfer::TRANSFER_TYPE_PHYSICAL,
        ];

        foreach ($transferTypes as $transferType) {
            for ($i = 1; $i <= 8; $i++) {
                $startTime = now()->addDays(rand(-30, 30))->addHours(rand(0, 23));
                $endTime = $startTime->copy()->addHours(rand(1, 8));
                $amountKwh = rand(100, 5000);
                $amountMwh = $amountKwh / 1000;
                $efficiency = rand(85, 98);
                $lossPercentage = 100 - $efficiency;
                $lossAmount = $amountKwh * ($lossPercentage / 100);
                $netAmount = $amountKwh - $lossAmount;

                EnergyTransfer::create([
                    'transfer_number' => 'GEN-' . strtoupper(Str::random(8)),
                    'name' => "Transferencia de Generación {$i}",
                    'description' => "Transferencia de energía generada desde {$transferType}",
                    'transfer_type' => $transferType,
                    'status' => $this->getRandomStatus(),
                    'priority' => $this->getRandomPriority(),
                    'source_id' => $installations->random()->id,
                    'source_type' => EnergyInstallation::class,
                    'destination_id' => $cooperatives->random()->id,
                    'destination_type' => EnergyCooperative::class,
                    'source_meter_id' => $meters->random()->id,
                    'destination_meter_id' => $meters->random()->id,
                    'transfer_amount_kwh' => $amountKwh,
                    'transfer_amount_mwh' => $amountMwh,
                    'transfer_rate_kw' => rand(50, 500),
                    'transfer_rate_mw' => rand(1, 10),
                    'transfer_unit' => 'kWh',
                    'scheduled_start_time' => $startTime,
                    'scheduled_end_time' => $endTime,
                    'actual_start_time' => $this->getRandomActualTime($startTime),
                    'actual_end_time' => $this->getRandomActualTime($endTime),
                    'completion_time' => $this->getRandomCompletionTime(),
                    'duration_hours' => $startTime->diffInHours($endTime),
                    'efficiency_percentage' => $efficiency,
                    'loss_percentage' => $lossPercentage,
                    'loss_amount_kwh' => $lossAmount,
                    'net_transfer_amount_kwh' => $netAmount,
                    'net_transfer_amount_mwh' => $netAmount / 1000,
                    'cost_per_kwh' => rand(50, 200) / 1000, // €0.05 - €0.20 por kWh
                    'total_cost' => $amountKwh * (rand(50, 200) / 1000),
                    'currency' => 'EUR',
                    'exchange_rate' => 1.0,
                    'transfer_method' => 'Automático',
                    'transfer_medium' => 'Red Eléctrica',
                    'transfer_protocol' => 'IEC 61850',
                    'is_automated' => rand(0, 1),
                    'requires_approval' => rand(0, 1),
                    'is_approved' => rand(0, 1),
                    'is_verified' => rand(0, 1),
                    'transfer_conditions' => 'Condiciones estándar de transferencia',
                    'safety_requirements' => 'Cumplimiento de normativas de seguridad',
                    'quality_standards' => 'Estándares de calidad IEC',
                    'transfer_parameters' => [
                        'voltage' => rand(220, 400),
                        'frequency' => 50,
                        'power_factor' => rand(85, 99) / 100,
                    ],
                    'monitoring_data' => [
                        'temperature' => rand(15, 35),
                        'humidity' => rand(40, 80),
                        'pressure' => rand(1000, 1020),
                    ],
                    'alarm_settings' => [
                        'max_voltage' => 450,
                        'min_voltage' => 200,
                        'max_current' => 1000,
                    ],
                    'event_logs' => [
                        'start_event' => 'Transferencia iniciada',
                        'monitoring_event' => 'Monitoreo activo',
                    ],
                    'performance_metrics' => [
                        'availability' => rand(95, 99),
                        'reliability' => rand(98, 100),
                    ],
                    'tags' => ['generación', 'renovable', 'solar'],
                    'scheduled_by' => $users->random()->id,
                    'initiated_by' => $users->random()->id,
                    'approved_by' => $users->random()->id,
                    'approved_at' => now()->subDays(rand(1, 10)),
                    'verified_by' => $users->random()->id,
                    'verified_at' => now()->subDays(rand(1, 5)),
                    'completed_by' => $users->random()->id,
                    'completed_at' => now()->subDays(rand(1, 3)),
                    'created_by' => $users->random()->id,
                    'notes' => "Notas de la transferencia de generación {$i}",
                ]);
            }
        }
    }

    private function createConsumptionTransfers($users, $meters, $cooperatives): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $startTime = now()->addDays(rand(-20, 20))->addHours(rand(0, 23));
            $endTime = $startTime->copy()->addHours(rand(2, 12));
            $amountKwh = rand(50, 2000);
            $amountMwh = $amountKwh / 1000;
            $efficiency = rand(90, 99);
            $lossPercentage = 100 - $efficiency;
            $lossAmount = $amountKwh * ($lossPercentage / 100);
            $netAmount = $amountKwh - $lossAmount;

            EnergyTransfer::create([
                'transfer_number' => 'CONS-' . strtoupper(Str::random(8)),
                'name' => "Transferencia de Consumo {$i}",
                'description' => "Transferencia de energía para consumo",
                'transfer_type' => EnergyTransfer::TRANSFER_TYPE_CONSUMPTION,
                'status' => $this->getRandomStatus(),
                'priority' => $this->getRandomPriority(),
                'source_id' => $cooperatives->random()->id,
                'source_type' => EnergyCooperative::class,
                'destination_id' => $cooperatives->random()->id,
                'destination_type' => EnergyCooperative::class,
                'source_meter_id' => $meters->random()->id,
                'destination_meter_id' => $meters->random()->id,
                'transfer_amount_kwh' => $amountKwh,
                'transfer_amount_mwh' => $amountMwh,
                'transfer_rate_kw' => rand(25, 300),
                'transfer_rate_mw' => rand(1, 5),
                'transfer_unit' => 'kWh',
                'scheduled_start_time' => $startTime,
                'scheduled_end_time' => $endTime,
                'actual_start_time' => $this->getRandomActualTime($startTime),
                'actual_end_time' => $this->getRandomActualTime($endTime),
                'completion_time' => $this->getRandomCompletionTime(),
                'duration_hours' => $startTime->diffInHours($endTime),
                'efficiency_percentage' => $efficiency,
                'loss_percentage' => $lossPercentage,
                'loss_amount_kwh' => $lossAmount,
                'net_transfer_amount_kwh' => $netAmount,
                'net_transfer_amount_mwh' => $netAmount / 1000,
                'cost_per_kwh' => rand(80, 300) / 1000, // €0.08 - €0.30 por kWh
                'total_cost' => $amountKwh * (rand(80, 300) / 1000),
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'transfer_method' => 'Manual',
                'transfer_medium' => 'Red Eléctrica',
                'transfer_protocol' => 'Modbus',
                'is_automated' => rand(0, 1),
                'requires_approval' => rand(0, 1),
                'is_approved' => rand(0, 1),
                'is_verified' => rand(0, 1),
                'transfer_conditions' => 'Condiciones de consumo estándar',
                'safety_requirements' => 'Protecciones de consumo',
                'quality_standards' => 'Estándares de calidad para consumo',
                'transfer_parameters' => [
                    'voltage' => rand(220, 400),
                    'frequency' => 50,
                    'power_factor' => rand(80, 95) / 100,
                ],
                'monitoring_data' => [
                    'temperature' => rand(18, 30),
                    'humidity' => rand(45, 75),
                    'pressure' => rand(1005, 1015),
                ],
                'alarm_settings' => [
                    'max_voltage' => 450,
                    'min_voltage' => 200,
                    'max_current' => 800,
                ],
                'event_logs' => [
                    'start_event' => 'Consumo iniciado',
                    'monitoring_event' => 'Monitoreo de consumo activo',
                ],
                'performance_metrics' => [
                    'availability' => rand(95, 99),
                    'reliability' => rand(97, 100),
                ],
                'tags' => ['consumo', 'industrial', 'residencial'],
                'scheduled_by' => $users->random()->id,
                'initiated_by' => $users->random()->id,
                'approved_by' => $users->random()->id,
                'approved_at' => now()->subDays(rand(1, 8)),
                'verified_by' => $users->random()->id,
                'verified_at' => now()->subDays(rand(1, 4)),
                'completed_by' => $users->random()->id,
                'completed_at' => now()->subDays(rand(1, 2)),
                'created_by' => $users->random()->id,
                'notes' => "Notas de la transferencia de consumo {$i}",
            ]);
        }
    }

    private function createStorageTransfers($users, $meters, $storages): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $startTime = now()->addDays(rand(-15, 15))->addHours(rand(0, 23));
            $endTime = $startTime->copy()->addHours(rand(1, 6));
            $amountKwh = rand(200, 3000);
            $amountMwh = $amountKwh / 1000;
            $efficiency = rand(88, 96);
            $lossPercentage = 100 - $efficiency;
            $lossAmount = $amountKwh * ($lossPercentage / 100);
            $netAmount = $amountKwh - $lossAmount;

            EnergyTransfer::create([
                'transfer_number' => 'STOR-' . strtoupper(Str::random(8)),
                'name' => "Transferencia de Almacenamiento {$i}",
                'description' => "Transferencia de energía para almacenamiento",
                'transfer_type' => EnergyTransfer::TRANSFER_TYPE_STORAGE,
                'status' => $this->getRandomStatus(),
                'priority' => $this->getRandomPriority(),
                'source_id' => $storages->random()->id,
                'source_type' => EnergyStorage::class,
                'destination_id' => $storages->random()->id,
                'destination_type' => EnergyStorage::class,
                'source_meter_id' => $meters->random()->id,
                'destination_meter_id' => $meters->random()->id,
                'transfer_amount_kwh' => $amountKwh,
                'transfer_amount_mwh' => $amountMwh,
                'transfer_rate_kw' => rand(100, 800),
                'transfer_rate_mw' => rand(2, 8),
                'transfer_unit' => 'kWh',
                'scheduled_start_time' => $startTime,
                'scheduled_end_time' => $endTime,
                'actual_start_time' => $this->getRandomActualTime($startTime),
                'actual_end_time' => $this->getRandomActualTime($endTime),
                'completion_time' => $this->getRandomCompletionTime(),
                'duration_hours' => $startTime->diffInHours($endTime),
                'efficiency_percentage' => $efficiency,
                'loss_percentage' => $lossPercentage,
                'loss_amount_kwh' => $lossAmount,
                'net_transfer_amount_kwh' => $netAmount,
                'net_transfer_amount_mwh' => $netAmount / 1000,
                'cost_per_kwh' => rand(60, 180) / 1000, // €0.06 - €0.18 por kWh
                'total_cost' => $amountKwh * (rand(60, 180) / 1000),
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'transfer_method' => 'Automático',
                'transfer_medium' => 'Sistema de Almacenamiento',
                'transfer_protocol' => 'CAN Bus',
                'is_automated' => true,
                'requires_approval' => rand(0, 1),
                'is_approved' => rand(0, 1),
                'is_verified' => rand(0, 1),
                'transfer_conditions' => 'Condiciones de almacenamiento',
                'safety_requirements' => 'Protecciones de baterías',
                'quality_standards' => 'Estándares de almacenamiento',
                'transfer_parameters' => [
                    'voltage' => rand(48, 400),
                    'frequency' => 50,
                    'power_factor' => rand(90, 99) / 100,
                    'soc' => rand(20, 95), // State of Charge
                ],
                'monitoring_data' => [
                    'temperature' => rand(10, 40),
                    'humidity' => rand(30, 70),
                    'pressure' => rand(1000, 1020),
                    'battery_voltage' => rand(45, 55),
                ],
                'alarm_settings' => [
                    'max_voltage' => 450,
                    'min_voltage' => 200,
                    'max_current' => 1000,
                    'max_temperature' => 45,
                ],
                'event_logs' => [
                    'start_event' => 'Almacenamiento iniciado',
                    'monitoring_event' => 'Monitoreo de baterías activo',
                ],
                'performance_metrics' => [
                    'availability' => rand(96, 99),
                    'reliability' => rand(98, 100),
                    'cycle_count' => rand(100, 1000),
                ],
                'tags' => ['almacenamiento', 'baterías', 'energía'],
                'scheduled_by' => $users->random()->id,
                'initiated_by' => $users->random()->id,
                'approved_by' => $users->random()->id,
                'approved_at' => now()->subDays(rand(1, 7)),
                'verified_by' => $users->random()->id,
                'verified_at' => now()->subDays(rand(1, 3)),
                'completed_by' => $users->random()->id,
                'completed_at' => now()->subDays(rand(1, 2)),
                'created_by' => $users->random()->id,
                'notes' => "Notas de la transferencia de almacenamiento {$i}",
            ]);
        }
    }

    private function createGridTransfers($users, $meters, $cooperatives): void
    {
        $gridTypes = [
            EnergyTransfer::TRANSFER_TYPE_GRID_IMPORT,
            EnergyTransfer::TRANSFER_TYPE_GRID_EXPORT,
        ];

        foreach ($gridTypes as $gridType) {
            for ($i = 1; $i <= 4; $i++) {
                $startTime = now()->addDays(rand(-25, 25))->addHours(rand(0, 23));
                $endTime = $startTime->copy()->addHours(rand(1, 10));
                $amountKwh = rand(500, 8000);
                $amountMwh = $amountKwh / 1000;
                $efficiency = rand(92, 99);
                $lossPercentage = 100 - $efficiency;
                $lossAmount = $amountKwh * ($lossPercentage / 100);
                $netAmount = $amountKwh - $lossAmount;

                EnergyTransfer::create([
                    'transfer_number' => ($gridType === EnergyTransfer::TRANSFER_TYPE_GRID_IMPORT ? 'IMP-' : 'EXP-') . strtoupper(Str::random(8)),
                    'name' => "Transferencia de Red " . ($gridType === EnergyTransfer::TRANSFER_TYPE_GRID_IMPORT ? 'Importación' : 'Exportación') . " {$i}",
                    'description' => "Transferencia de energía " . ($gridType === EnergyTransfer::TRANSFER_TYPE_GRID_IMPORT ? 'desde la red' : 'hacia la red'),
                    'transfer_type' => $gridType,
                    'status' => $this->getRandomStatus(),
                    'priority' => $this->getRandomPriority(),
                    'source_id' => $cooperatives->random()->id,
                    'source_type' => EnergyCooperative::class,
                    'destination_id' => $cooperatives->random()->id,
                    'destination_type' => EnergyCooperative::class,
                    'source_meter_id' => $meters->random()->id,
                    'destination_meter_id' => $meters->random()->id,
                    'transfer_amount_kwh' => $amountKwh,
                    'transfer_amount_mwh' => $amountMwh,
                    'transfer_rate_kw' => rand(200, 2000),
                    'transfer_rate_mw' => rand(5, 20),
                    'transfer_unit' => 'kWh',
                    'scheduled_start_time' => $startTime,
                    'scheduled_end_time' => $endTime,
                    'actual_start_time' => $this->getRandomActualTime($startTime),
                    'actual_end_time' => $this->getRandomActualTime($endTime),
                    'completion_time' => $this->getRandomCompletionTime(),
                    'duration_hours' => $startTime->diffInHours($endTime),
                    'efficiency_percentage' => $efficiency,
                    'loss_percentage' => $lossPercentage,
                    'loss_amount_kwh' => $lossAmount,
                    'net_transfer_amount_kwh' => $netAmount,
                    'net_transfer_amount_mwh' => $netAmount / 1000,
                    'cost_per_kwh' => rand(100, 400) / 1000, // €0.10 - €0.40 por kWh
                    'total_cost' => $amountKwh * (rand(100, 400) / 1000),
                    'currency' => 'EUR',
                    'exchange_rate' => 1.0,
                    'transfer_method' => 'Automático',
                    'transfer_medium' => 'Red Nacional',
                    'transfer_protocol' => 'IEC 61850',
                    'is_automated' => true,
                    'requires_approval' => true,
                    'is_approved' => rand(0, 1),
                    'is_verified' => rand(0, 1),
                    'transfer_conditions' => 'Condiciones de red nacional',
                    'safety_requirements' => 'Protecciones de red',
                    'quality_standards' => 'Estándares nacionales',
                    'transfer_parameters' => [
                        'voltage' => rand(220, 400),
                        'frequency' => 50,
                        'power_factor' => rand(85, 99) / 100,
                    ],
                    'monitoring_data' => [
                        'temperature' => rand(15, 35),
                        'humidity' => rand(40, 80),
                        'pressure' => rand(1000, 1020),
                    ],
                    'alarm_settings' => [
                        'max_voltage' => 450,
                        'min_voltage' => 200,
                        'max_current' => 2000,
                    ],
                    'event_logs' => [
                        'start_event' => 'Transferencia de red iniciada',
                        'monitoring_event' => 'Monitoreo de red activo',
                    ],
                    'performance_metrics' => [
                        'availability' => rand(98, 100),
                        'reliability' => rand(99, 100),
                    ],
                    'tags' => ['red', 'nacional', $gridType === EnergyTransfer::TRANSFER_TYPE_GRID_IMPORT ? 'importación' : 'exportación'],
                    'scheduled_by' => $users->random()->id,
                    'initiated_by' => $users->random()->id,
                    'approved_by' => $users->random()->id,
                    'approved_at' => now()->subDays(rand(1, 12)),
                    'verified_by' => $users->random()->id,
                    'verified_at' => now()->subDays(rand(1, 6)),
                    'completed_by' => $users->random()->id,
                    'completed_at' => now()->subDays(rand(1, 4)),
                    'created_by' => $users->random()->id,
                    'notes' => "Notas de la transferencia de red " . ($gridType === EnergyTransfer::TRANSFER_TYPE_GRID_IMPORT ? 'importación' : 'exportación') . " {$i}",
                ]);
            }
        }
    }

    private function createPeerToPeerTransfers($users, $meters, $cooperatives): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $startTime = now()->addDays(rand(-18, 18))->addHours(rand(0, 23));
            $endTime = $startTime->copy()->addHours(rand(1, 8));
            $amountKwh = rand(100, 1500);
            $amountMwh = $amountKwh / 1000;
            $efficiency = rand(90, 97);
            $lossPercentage = 100 - $efficiency;
            $lossAmount = $amountKwh * ($lossPercentage / 100);
            $netAmount = $amountKwh - $lossAmount;

            EnergyTransfer::create([
                'transfer_number' => 'P2P-' . strtoupper(Str::random(8)),
                'name' => "Transferencia P2P {$i}",
                'description' => "Transferencia peer-to-peer entre cooperativas",
                'transfer_type' => EnergyTransfer::TRANSFER_TYPE_PEER_TO_PEER,
                'status' => $this->getRandomStatus(),
                'priority' => $this->getRandomPriority(),
                'source_id' => $cooperatives->random()->id,
                'source_type' => EnergyCooperative::class,
                'destination_id' => $cooperatives->random()->id,
                'destination_type' => EnergyCooperative::class,
                'source_meter_id' => $meters->random()->id,
                'destination_meter_id' => $meters->random()->id,
                'transfer_amount_kwh' => $amountKwh,
                'transfer_amount_mwh' => $amountMwh,
                'transfer_rate_kw' => rand(50, 500),
                'transfer_rate_mw' => rand(1, 5),
                'transfer_unit' => 'kWh',
                'scheduled_start_time' => $startTime,
                'scheduled_end_time' => $endTime,
                'actual_start_time' => $this->getRandomActualTime($startTime),
                'actual_end_time' => $this->getRandomActualTime($endTime),
                'completion_time' => $this->getRandomCompletionTime(),
                'duration_hours' => $startTime->diffInHours($endTime),
                'efficiency_percentage' => $efficiency,
                'loss_percentage' => $lossPercentage,
                'loss_amount_kwh' => $lossAmount,
                'net_transfer_amount_kwh' => $netAmount,
                'net_transfer_amount_mwh' => $netAmount / 1000,
                'cost_per_kwh' => rand(70, 250) / 1000, // €0.07 - €0.25 por kWh
                'total_cost' => $amountKwh * (rand(70, 250) / 1000),
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'transfer_method' => 'Manual',
                'transfer_medium' => 'Red Cooperativa',
                'transfer_protocol' => 'Blockchain',
                'is_automated' => rand(0, 1),
                'requires_approval' => rand(0, 1),
                'is_approved' => rand(0, 1),
                'is_verified' => rand(0, 1),
                'transfer_conditions' => 'Condiciones P2P',
                'safety_requirements' => 'Protecciones P2P',
                'quality_standards' => 'Estándares cooperativos',
                'transfer_parameters' => [
                    'voltage' => rand(220, 400),
                    'frequency' => 50,
                    'power_factor' => rand(85, 95) / 100,
                ],
                'monitoring_data' => [
                    'temperature' => rand(15, 30),
                    'humidity' => rand(45, 75),
                    'pressure' => rand(1005, 1015),
                ],
                'alarm_settings' => [
                    'max_voltage' => 450,
                    'min_voltage' => 200,
                    'max_current' => 1000,
                ],
                'event_logs' => [
                    'start_event' => 'Transferencia P2P iniciada',
                    'monitoring_event' => 'Monitoreo P2P activo',
                ],
                'performance_metrics' => [
                    'availability' => rand(95, 99),
                    'reliability' => rand(97, 100),
                ],
                'tags' => ['p2p', 'cooperativa', 'peer-to-peer'],
                'scheduled_by' => $users->random()->id,
                'initiated_by' => $users->random()->id,
                'approved_by' => $users->random()->id,
                'approved_at' => now()->subDays(rand(1, 9)),
                'verified_by' => $users->random()->id,
                'verified_at' => now()->subDays(rand(1, 5)),
                'completed_by' => $users->random()->id,
                'completed_at' => now()->subDays(rand(1, 3)),
                'created_by' => $users->random()->id,
                'notes' => "Notas de la transferencia P2P {$i}",
            ]);
        }
    }

    private function createVirtualTransfers($users, $meters, $cooperatives): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $startTime = now()->addDays(rand(-12, 12))->addHours(rand(0, 23));
            $endTime = $startTime->copy()->addHours(rand(1, 6));
            $amountKwh = rand(50, 800);
            $amountMwh = $amountKwh / 1000;
            $efficiency = rand(95, 100);
            $lossPercentage = 100 - $efficiency;
            $lossAmount = $amountKwh * ($lossPercentage / 100);
            $netAmount = $amountKwh - $lossAmount;

            EnergyTransfer::create([
                'transfer_number' => 'VIRT-' . strtoupper(Str::random(8)),
                'name' => "Transferencia Virtual {$i}",
                'description' => "Transferencia virtual de energía",
                'transfer_type' => EnergyTransfer::TRANSFER_TYPE_VIRTUAL,
                'status' => $this->getRandomStatus(),
                'priority' => $this->getRandomPriority(),
                'source_id' => $cooperatives->random()->id,
                'source_type' => EnergyCooperative::class,
                'destination_id' => $cooperatives->random()->id,
                'destination_type' => EnergyCooperative::class,
                'source_meter_id' => $meters->random()->id,
                'destination_meter_id' => $meters->random()->id,
                'transfer_amount_kwh' => $amountKwh,
                'transfer_amount_mwh' => $amountMwh,
                'transfer_rate_kw' => rand(25, 200),
                'transfer_rate_mw' => rand(1, 2),
                'transfer_unit' => 'kWh',
                'scheduled_start_time' => $startTime,
                'scheduled_end_time' => $endTime,
                'actual_start_time' => $this->getRandomActualTime($startTime),
                'actual_end_time' => $this->getRandomActualTime($endTime),
                'completion_time' => $this->getRandomCompletionTime(),
                'duration_hours' => $startTime->diffInHours($endTime),
                'efficiency_percentage' => $efficiency,
                'loss_percentage' => $lossPercentage,
                'loss_amount_kwh' => $lossAmount,
                'net_transfer_amount_kwh' => $netAmount,
                'net_transfer_amount_mwh' => $netAmount / 1000,
                'cost_per_kwh' => rand(50, 150) / 1000, // €0.05 - €0.15 por kWh
                'total_cost' => $amountKwh * (rand(50, 150) / 1000),
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'transfer_method' => 'Virtual',
                'transfer_medium' => 'Plataforma Digital',
                'transfer_protocol' => 'API REST',
                'is_automated' => true,
                'requires_approval' => false,
                'is_approved' => true,
                'is_verified' => true,
                'transfer_conditions' => 'Condiciones virtuales',
                'safety_requirements' => 'Protecciones digitales',
                'quality_standards' => 'Estándares virtuales',
                'transfer_parameters' => [
                    'voltage' => rand(220, 400),
                    'frequency' => 50,
                    'power_factor' => rand(90, 99) / 100,
                ],
                'monitoring_data' => [
                    'temperature' => rand(20, 25),
                    'humidity' => rand(50, 70),
                    'pressure' => rand(1010, 1015),
                ],
                'alarm_settings' => [
                    'max_voltage' => 450,
                    'min_voltage' => 200,
                    'max_current' => 500,
                ],
                'event_logs' => [
                    'start_event' => 'Transferencia virtual iniciada',
                    'monitoring_event' => 'Monitoreo virtual activo',
                ],
                'performance_metrics' => [
                    'availability' => rand(98, 100),
                    'reliability' => rand(99, 100),
                ],
                'tags' => ['virtual', 'digital', 'plataforma'],
                'scheduled_by' => $users->random()->id,
                'initiated_by' => $users->random()->id,
                'approved_by' => $users->random()->id,
                'approved_at' => now()->subDays(rand(1, 6)),
                'verified_by' => $users->random()->id,
                'verified_at' => now()->subDays(rand(1, 3)),
                'completed_by' => $users->random()->id,
                'completed_at' => now()->subDays(rand(1, 2)),
                'created_by' => $users->random()->id,
                'notes' => "Notas de la transferencia virtual {$i}",
            ]);
        }
    }

    private function getRandomStatus(): string
    {
        $statuses = [
            EnergyTransfer::STATUS_PENDING,
            EnergyTransfer::STATUS_SCHEDULED,
            EnergyTransfer::STATUS_IN_PROGRESS,
            EnergyTransfer::STATUS_COMPLETED,
            EnergyTransfer::STATUS_CANCELLED,
            EnergyTransfer::STATUS_FAILED,
            EnergyTransfer::STATUS_ON_HOLD,
        ];

        return $statuses[array_rand($statuses)];
    }

    private function getRandomPriority(): string
    {
        $priorities = [
            EnergyTransfer::PRIORITY_LOW,
            EnergyTransfer::PRIORITY_NORMAL,
            EnergyTransfer::PRIORITY_HIGH,
            EnergyTransfer::PRIORITY_URGENT,
            EnergyTransfer::PRIORITY_CRITICAL,
        ];

        return $priorities[array_rand($priorities)];
    }

    private function getRandomActualTime($scheduledTime): ?\Carbon\Carbon
    {
        // 70% de probabilidad de tener tiempo real
        if (rand(1, 10) <= 7) {
            $variation = rand(-30, 30); // ±30 minutos de variación
            return $scheduledTime->copy()->addMinutes($variation);
        }

        return null;
    }

    private function getRandomCompletionTime(): ?\Carbon\Carbon
    {
        // 60% de probabilidad de estar completado
        if (rand(1, 10) <= 6) {
            return now()->subDays(rand(1, 10))->subHours(rand(0, 23));
        }

        return null;
    }
}

