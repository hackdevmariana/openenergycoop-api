<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\Team;
use App\Models\Device;
use App\Models\Vendor;
use App\Models\TaskTemplate;
use App\Models\ChecklistTemplate;
use Carbon\Carbon;

class MaintenanceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔧 Creando horarios de mantenimiento españoles para la cooperativa energética...');
        
        // Limpiar horarios existentes
        MaintenanceSchedule::query()->delete();
        
        $users = User::all();
        $teams = Team::all();
        $devices = Device::all();
        $vendors = Vendor::all();
        $taskTemplates = TaskTemplate::all();
        $checklistTemplates = ChecklistTemplate::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Creando horarios sin usuario creador.');
            $users = collect([null]);
        }
        
        if ($teams->isEmpty()) {
            $this->command->warn('⚠️ No hay equipos disponibles. Creando horarios sin equipo asignado.');
            $teams = collect([null]);
        }
        
        if ($devices->isEmpty()) {
            $this->command->warn('⚠️ No hay dispositivos disponibles. Creando horarios sin dispositivo.');
            $devices = collect([null]);
        }
        
        if ($vendors->isEmpty()) {
            $this->command->warn('⚠️ No hay proveedores disponibles. Creando horarios sin proveedor.');
            $vendors = collect([null]);
        }
        
        if ($taskTemplates->isEmpty()) {
            $this->command->warn('⚠️ No hay plantillas de tareas disponibles. Creando horarios sin plantilla.');
            $taskTemplates = collect([null]);
        }
        
        if ($checklistTemplates->isEmpty()) {
            $this->command->warn('⚠️ No hay plantillas de checklist disponibles. Creando horarios sin plantilla.');
            $checklistTemplates = collect([null]);
        }
        
        $this->createDailyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        $this->createWeeklyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        $this->createMonthlyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        $this->createQuarterlyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        $this->createYearlyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        $this->createCustomMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        
        $this->command->info('✅ MaintenanceScheduleSeeder completado. Se crearon ' . MaintenanceSchedule::count() . ' horarios de mantenimiento españoles.');
    }
    
    private function createDailyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates): void
    {
        $this->command->info('📅 Creando horarios de mantenimiento diarios...');
        
        $dailySchedules = [
            [
                'name' => 'Inspección Diaria de Paneles Solares',
                'description' => 'Verificación diaria del estado de los paneles solares, limpieza básica y registro de rendimiento.',
                'schedule_type' => 'daily',
                'cron_expression' => '0 8 * * *',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento preventivo diario para optimizar el rendimiento de los paneles solares. Incluye limpieza de polvo y verificación de conexiones.'
            ],
            [
                'name' => 'Verificación Diaria de Baterías',
                'description' => 'Control diario del estado de carga, temperatura y voltaje de las baterías de almacenamiento.',
                'schedule_type' => 'daily',
                'cron_expression' => '0 9 * * *',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Monitoreo diario de baterías para prevenir fallos y optimizar la vida útil del sistema de almacenamiento.'
            ],
            [
                'name' => 'Revisión Diaria de Contadores Inteligentes',
                'description' => 'Verificación diaria de la comunicación y precisión de los contadores inteligentes.',
                'schedule_type' => 'daily',
                'cron_expression' => '0 10 * * *',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Control diario de contadores para asegurar la precisión en la medición del consumo energético.'
            ]
        ];
        
        foreach ($dailySchedules as $scheduleData) {
            $this->createMaintenanceSchedule($scheduleData, $users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        }
    }
    
    private function createWeeklyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates): void
    {
        $this->command->info('📅 Creando horarios de mantenimiento semanales...');
        
        $weeklySchedules = [
            [
                'name' => 'Mantenimiento Semanal de Aerogeneradores',
                'description' => 'Inspección semanal de aerogeneradores, verificación de palas y lubricación de componentes.',
                'schedule_type' => 'weekly',
                'cron_expression' => '0 8 * * 1',
                'start_date' => now()->subWeeks(4),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento preventivo semanal para aerogeneradores. Incluye verificación de vibraciones, lubricación y limpieza.'
            ],
            [
                'name' => 'Limpieza Semanal de Sistemas de Refrigeración',
                'description' => 'Limpieza semanal de filtros y verificación del sistema de refrigeración de equipos críticos.',
                'schedule_type' => 'weekly',
                'cron_expression' => '0 9 * * 2',
                'start_date' => now()->subWeeks(4),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento semanal del sistema de refrigeración para prevenir sobrecalentamiento de equipos.'
            ],
            [
                'name' => 'Verificación Semanal de Sistemas de Seguridad',
                'description' => 'Prueba semanal de sistemas de seguridad, alarmas y extinción de incendios.',
                'schedule_type' => 'weekly',
                'cron_expression' => '0 10 * * 3',
                'start_date' => now()->subWeeks(4),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Verificación semanal de todos los sistemas de seguridad para garantizar la protección de instalaciones y personal.'
            ],
            [
                'name' => 'Calibración Semanal de Instrumentos de Medición',
                'description' => 'Calibración semanal de instrumentos de medición y verificación de precisión.',
                'schedule_type' => 'weekly',
                'cron_expression' => '0 11 * * 4',
                'start_date' => now()->subWeeks(4),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Calibración semanal para mantener la precisión de todos los instrumentos de medición del sistema.'
            ]
        ];
        
        foreach ($weeklySchedules as $scheduleData) {
            $this->createMaintenanceSchedule($scheduleData, $users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        }
    }
    
    private function createMonthlyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates): void
    {
        $this->command->info('📅 Creando horarios de mantenimiento mensuales...');
        
        $monthlySchedules = [
            [
                'name' => 'Mantenimiento Mensual de Transformadores',
                'description' => 'Inspección mensual de transformadores, verificación de aceite y conexiones eléctricas.',
                'schedule_type' => 'monthly',
                'cron_expression' => '0 8 1 * *',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento preventivo mensual de transformadores para garantizar la estabilidad del sistema eléctrico.'
            ],
            [
                'name' => 'Revisión Mensual de Sistemas de Control',
                'description' => 'Verificación mensual de sistemas de control, SCADA y automatización.',
                'schedule_type' => 'monthly',
                'cron_expression' => '0 9 5 * *',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento mensual de sistemas de control para asegurar la operación eficiente de la planta.'
            ],
            [
                'name' => 'Limpieza Mensual de Sistemas de Ventilación',
                'description' => 'Limpieza mensual de sistemas de ventilación y filtros de aire.',
                'schedule_type' => 'monthly',
                'cron_expression' => '0 10 10 * *',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento mensual de ventilación para mantener la calidad del aire en instalaciones críticas.'
            ],
            [
                'name' => 'Verificación Mensual de Sistemas de Comunicación',
                'description' => 'Prueba mensual de sistemas de comunicación, radio y telefonía.',
                'schedule_type' => 'monthly',
                'cron_expression' => '0 11 15 * *',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Verificación mensual de sistemas de comunicación para garantizar la conectividad en emergencias.'
            ],
            [
                'name' => 'Mantenimiento Mensual de Sistemas de Iluminación',
                'description' => 'Revisión mensual de sistemas de iluminación de emergencia y señalización.',
                'schedule_type' => 'monthly',
                'cron_expression' => '0 12 20 * *',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento mensual de iluminación para garantizar la seguridad en todas las instalaciones.'
            ]
        ];
        
        foreach ($monthlySchedules as $scheduleData) {
            $this->createMaintenanceSchedule($scheduleData, $users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        }
    }
    
    private function createQuarterlyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates): void
    {
        $this->command->info('📅 Creando horarios de mantenimiento trimestrales...');
        
        $quarterlySchedules = [
            [
                'name' => 'Mantenimiento Trimestral de Turbinas',
                'description' => 'Mantenimiento preventivo trimestral de turbinas hidroeléctricas y de gas.',
                'schedule_type' => 'quarterly',
                'cron_expression' => '0 8 1 */3 *',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento preventivo trimestral de turbinas para optimizar rendimiento y prevenir fallos.'
            ],
            [
                'name' => 'Revisión Trimestral de Sistemas de Protección',
                'description' => 'Verificación trimestral de sistemas de protección eléctrica y relés.',
                'schedule_type' => 'quarterly',
                'cron_expression' => '0 9 15 */3 *',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Verificación trimestral de sistemas de protección para garantizar la seguridad del sistema eléctrico.'
            ],
            [
                'name' => 'Calibración Trimestral de Sensores',
                'description' => 'Calibración trimestral de sensores de temperatura, presión y flujo.',
                'schedule_type' => 'quarterly',
                'cron_expression' => '0 10 1 */3 *',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Calibración trimestral de sensores para mantener la precisión de las mediciones críticas.'
            ]
        ];
        
        foreach ($quarterlySchedules as $scheduleData) {
            $this->createMaintenanceSchedule($scheduleData, $users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        }
    }
    
    private function createYearlyMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates): void
    {
        $this->command->info('📅 Creando horarios de mantenimiento anuales...');
        
        $yearlySchedules = [
            [
                'name' => 'Mantenimiento Anual de Generadores',
                'description' => 'Mantenimiento preventivo anual completo de generadores eléctricos.',
                'schedule_type' => 'yearly',
                'cron_expression' => '0 8 1 1 *',
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento preventivo anual de generadores incluyendo cambio de aceite, filtros y verificación completa.'
            ],
            [
                'name' => 'Revisión Anual de Sistemas de Distribución',
                'description' => 'Inspección anual completa de sistemas de distribución eléctrica.',
                'schedule_type' => 'yearly',
                'cron_expression' => '0 9 15 3 *',
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Revisión anual de sistemas de distribución para identificar y corregir posibles problemas.'
            ],
            [
                'name' => 'Mantenimiento Anual de Sistemas de Refrigeración',
                'description' => 'Mantenimiento anual de sistemas de refrigeración y aire acondicionado.',
                'schedule_type' => 'yearly',
                'cron_expression' => '0 10 1 6 *',
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento anual de refrigeración incluyendo limpieza de condensadores y verificación de refrigerante.'
            ],
            [
                'name' => 'Verificación Anual de Sistemas de Seguridad',
                'description' => 'Verificación anual completa de todos los sistemas de seguridad.',
                'schedule_type' => 'yearly',
                'cron_expression' => '0 11 15 9 *',
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Verificación anual de sistemas de seguridad incluyendo pruebas de funcionamiento completas.'
            ]
        ];
        
        foreach ($yearlySchedules as $scheduleData) {
            $this->createMaintenanceSchedule($scheduleData, $users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        }
    }
    
    private function createCustomMaintenanceSchedules($users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates): void
    {
        $this->command->info('📅 Creando horarios de mantenimiento personalizados...');
        
        $customSchedules = [
            [
                'name' => 'Mantenimiento por Horas de Operación - Turbinas',
                'description' => 'Mantenimiento basado en horas de operación de turbinas (cada 2000 horas).',
                'schedule_type' => 'custom',
                'cron_expression' => null,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento basado en horas de operación para optimizar la vida útil de las turbinas.'
            ],
            [
                'name' => 'Mantenimiento por Ciclos de Carga - Baterías',
                'description' => 'Mantenimiento basado en ciclos de carga de baterías (cada 500 ciclos).',
                'schedule_type' => 'custom',
                'cron_expression' => null,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento basado en ciclos de carga para maximizar la vida útil de las baterías.'
            ],
            [
                'name' => 'Mantenimiento por Condición - Paneles Solares',
                'description' => 'Mantenimiento basado en la condición y rendimiento de los paneles solares.',
                'schedule_type' => 'custom',
                'cron_expression' => null,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addYears(5),
                'is_active' => true,
                'notes' => 'Mantenimiento basado en la condición real de los paneles para optimizar el rendimiento.'
            ]
        ];
        
        foreach ($customSchedules as $scheduleData) {
            $this->createMaintenanceSchedule($scheduleData, $users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates);
        }
    }
    
    private function createMaintenanceSchedule($scheduleData, $users, $teams, $devices, $vendors, $taskTemplates, $checklistTemplates): void
    {
        $baseData = [
            'created_by' => $users->random() ? $users->random()->id : 1,
            'approved_by' => $users->random() ? $users->random()->id : 1,
            'approved_at' => now()->subDays(rand(1, 30)),
            'equipment_id' => $devices->random() ? $devices->random()->id : null,
            'equipment_type' => 'App\\Models\\Device',
            'location_id' => rand(1, 10),
            'location_type' => 'App\\Models\\Location',
            'assigned_team_id' => $teams->random() ? $teams->random()->id : null,
            'vendor_id' => $vendors->random() ? $vendors->random()->id : null,
            'task_template_id' => $taskTemplates->random() ? $taskTemplates->random()->id : null,
            'checklist_template_id' => $checklistTemplates->random() ? $checklistTemplates->random()->id : null,
            'schedule_config' => json_encode($this->generateScheduleConfig($scheduleData['schedule_type'])),
        ];
        
        MaintenanceSchedule::create(array_merge($scheduleData, $baseData));
    }
    
    private function generateScheduleConfig($scheduleType): array
    {
        $configs = [
            'daily' => [
                'time_window' => '08:00-16:00',
                'estimated_duration' => '2 horas',
                'required_personnel' => 2,
                'weather_dependent' => false,
                'priority' => 'medium'
            ],
            'weekly' => [
                'time_window' => '08:00-17:00',
                'estimated_duration' => '4 horas',
                'required_personnel' => 3,
                'weather_dependent' => false,
                'priority' => 'medium'
            ],
            'monthly' => [
                'time_window' => '08:00-18:00',
                'estimated_duration' => '8 horas',
                'required_personnel' => 4,
                'weather_dependent' => false,
                'priority' => 'high'
            ],
            'quarterly' => [
                'time_window' => '08:00-18:00',
                'estimated_duration' => '16 horas',
                'required_personnel' => 5,
                'weather_dependent' => false,
                'priority' => 'high'
            ],
            'yearly' => [
                'time_window' => '08:00-18:00',
                'estimated_duration' => '24 horas',
                'required_personnel' => 6,
                'weather_dependent' => false,
                'priority' => 'critical'
            ],
            'custom' => [
                'time_window' => '08:00-17:00',
                'estimated_duration' => '6 horas',
                'required_personnel' => 3,
                'weather_dependent' => true,
                'priority' => 'medium'
            ]
        ];
        
        return $configs[$scheduleType] ?? $configs['monthly'];
    }
}
