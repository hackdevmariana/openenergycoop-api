<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceTask;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;

class MaintenanceTaskSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(5)->get();
        $vendors = Vendor::take(3)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando MaintenanceTaskSeeder.');
            return;
        }

        if ($vendors->isEmpty()) {
            $this->command->warn('⚠️ No hay proveedores disponibles. Saltando MaintenanceTaskSeeder.');
            return;
        }

        $tasks = [
            [
                'title' => 'Mantenimiento Preventivo Paneles Solares',
                'description' => 'Limpieza y revisión general de los paneles solares del parque fotovoltaico.',
                'task_type' => MaintenanceTask::TASK_TYPE_CLEANING,
                'priority' => MaintenanceTask::PRIORITY_MEDIUM,
                'status' => MaintenanceTask::STATUS_PENDING,
                'assigned_to' => $users->random()->id,
                'assigned_by' => $users->first()->id,
                'due_date' => Carbon::now()->addDays(7),
                'start_date' => Carbon::now()->addDays(1),
                'estimated_hours' => 8.0,
                'equipment_id' => 1,
                'equipment_type' => 'SolarPanel',
                'location_id' => 1,
                'location_type' => 'SolarFarm',
                'checklist_items' => json_encode(['Limpiar superficie de paneles', 'Verificar conexiones eléctricas']),
                'required_tools' => json_encode(['Escalera', 'Cepillo suave', 'Multímetro']),
                'required_parts' => json_encode(['Cables de conexión', 'Juntas de goma']),
                'safety_notes' => 'Usar arnés de seguridad y verificar condiciones meteorológicas.',
                'technical_notes' => 'Revisar especialmente los paneles de la zona norte.',
                'cost_estimate' => 450.00,
                'vendor_id' => $vendors->first()->id,
                'warranty_work' => false,
                'recurring' => true,
                'recurrence_pattern' => 'monthly',
                'next_recurrence_date' => Carbon::now()->addMonth(),
                'attachments' => json_encode(['manual_mantenimiento.pdf']),
                'tags' => json_encode(['solar', 'preventivo', 'limpieza']),
                'notes' => 'Tarea programada para el primer lunes del mes.',
                'work_order_number' => 'WO-SOLAR-001',
                'department' => 'Mantenimiento',
                'created_by' => $users->first()->id,
            ],
            [
                'title' => 'Reparación Turbina Eólica',
                'description' => 'Reparación de la turbina eólica #3 que presenta vibraciones anómalas.',
                'task_type' => MaintenanceTask::TASK_TYPE_REPAIR,
                'priority' => MaintenanceTask::PRIORITY_HIGH,
                'status' => MaintenanceTask::STATUS_IN_PROGRESS,
                'assigned_to' => $users->random()->id,
                'assigned_by' => $users->skip(1)->first()->id,
                'due_date' => Carbon::now()->addDays(3),
                'start_date' => Carbon::now()->subDays(1),
                'estimated_hours' => 24.0,
                'actual_hours' => 12.0,
                'equipment_id' => 3,
                'equipment_type' => 'WindTurbine',
                'location_id' => 2,
                'location_type' => 'WindFarm',
                'checklist_items' => json_encode(['Diagnosticar causa de vibraciones', 'Revisar rodamientos']),
                'required_tools' => json_encode(['Grúa', 'Herramientas especializadas']),
                'required_parts' => json_encode(['Rodamientos', 'Aceite hidráulico']),
                'safety_notes' => 'Parada total de la turbina y bloqueo de seguridad.',
                'technical_notes' => 'Posible desgaste en rodamientos principales.',
                'cost_estimate' => 15000.00,
                'actual_cost' => 8500.00,
                'vendor_id' => $vendors->skip(1)->first()->id,
                'warranty_work' => true,
                'recurring' => false,
                'recurrence_pattern' => null,
                'next_recurrence_date' => null,
                'attachments' => json_encode(['diagnostico_vibraciones.pdf']),
                'tags' => json_encode(['eolica', 'correctivo', 'reparacion']),
                'notes' => 'Trabajo urgente - turbina fuera de servicio.',
                'work_order_number' => 'WO-WIND-002',
                'department' => 'Mantenimiento',
                'created_by' => $users->skip(1)->first()->id,
            ],
        ];

        foreach ($tasks as $task) {
            MaintenanceTask::create($task);
        }

        $this->command->info('✅ MaintenanceTaskSeeder ejecutado correctamente');
        $this->command->info('📊 Tareas de mantenimiento creadas: ' . count($tasks));
        $this->command->info('🔧 Tipos: Preventivo, Correctivo');
        $this->command->info('⭐ Prioridades: Media, Alta');
        $this->command->info('📈 Estados: Pendiente, En Progreso');
        $this->command->info('⚡ Equipos: Paneles Solares, Turbinas Eólicas');
        $this->command->info('💰 Costo total estimado: €' . number_format(collect($tasks)->sum('cost_estimate'), 2));
    }
}
