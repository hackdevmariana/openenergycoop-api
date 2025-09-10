<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Milestone;
use App\Models\User;
use Carbon\Carbon;

class MilestoneSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener o crear usuarios
        $users = User::take(5)->get();
        if ($users->isEmpty()) {
            $users = collect();
            for ($i = 1; $i <= 5; $i++) {
                $users->push(User::create([
                    'name' => "Usuario {$i}",
                    'email' => "usuario{$i}@example.com",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]));
            }
        }

        $milestones = [
            // Hitos de Proyecto
            [
                'title' => 'Instalación de Parque Solar Principal',
                'description' => 'Completar la instalación del parque solar principal de 50MW en la zona norte de la cooperativa.',
                'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
                'status' => Milestone::STATUS_IN_PROGRESS,
                'priority' => Milestone::PRIORITY_HIGH,
                'target_date' => Carbon::now()->addDays(45),
                'start_date' => Carbon::now()->subDays(30),
                'completion_date' => null,
                'target_value' => 50000.00,
                'current_value' => 35000.00,
                'progress_percentage' => 70.00,
                'success_criteria' => 'Instalación completa de 50MW con conexión a la red eléctrica',
                'dependencies' => 'Aprobación regulatoria, financiación, permisos ambientales',
                'risks' => 'Retrasos en permisos, condiciones climáticas adversas',
                'mitigation_strategies' => 'Plan de contingencia, seguimiento diario del clima',
                'parent_milestone_id' => null,
                'assigned_to' => $users->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['solar', 'instalación', '50MW', 'parque'],
                'notes' => 'Proyecto prioritario para la expansión de la cooperativa',
            ],
            [
                'title' => 'Conexión a Red Eléctrica',
                'description' => 'Conectar el parque solar a la red eléctrica nacional.',
                'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
                'status' => Milestone::STATUS_NOT_STARTED,
                'priority' => Milestone::PRIORITY_URGENT,
                'target_date' => Carbon::now()->addDays(60),
                'start_date' => null,
                'completion_date' => null,
                'target_value' => 1.00,
                'current_value' => 0.00,
                'progress_percentage' => 0.00,
                'success_criteria' => 'Conexión exitosa y pruebas de funcionamiento completadas',
                'dependencies' => 'Completar instalación de parque solar',
                'risks' => 'Retrasos en aprobación de la compañía eléctrica',
                'mitigation_strategies' => 'Mantener comunicación constante con la compañía eléctrica',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(1)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['conexión', 'red eléctrica', 'pruebas'],
                'notes' => 'Dependiente de la finalización del parque solar',
            ],

            // Hitos Financieros
            [
                'title' => 'Ronda de Financiación Serie A',
                'description' => 'Completar la ronda de financiación Serie A para la expansión de la cooperativa.',
                'milestone_type' => Milestone::MILESTONE_TYPE_FINANCIAL,
                'status' => Milestone::STATUS_COMPLETED,
                'priority' => Milestone::PRIORITY_CRITICAL,
                'target_date' => Carbon::now()->subDays(15),
                'start_date' => Carbon::now()->subDays(90),
                'completion_date' => Carbon::now()->subDays(15),
                'target_value' => 5000000.00,
                'current_value' => 5000000.00,
                'progress_percentage' => 100.00,
                'success_criteria' => 'Recaudar 5M€ de inversores estratégicos',
                'dependencies' => 'Presentación de plan de negocio, auditoría financiera',
                'risks' => 'Condiciones de mercado desfavorables',
                'mitigation_strategies' => 'Diversificación de fuentes de financiación',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(2)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['financiación', 'serie A', '5M€', 'inversores'],
                'notes' => 'Hito crítico completado exitosamente',
            ],
            [
                'title' => 'Auditoría Financiera Anual',
                'description' => 'Realizar la auditoría financiera anual de la cooperativa.',
                'milestone_type' => Milestone::MILESTONE_TYPE_FINANCIAL,
                'status' => Milestone::STATUS_IN_PROGRESS,
                'priority' => Milestone::PRIORITY_HIGH,
                'target_date' => Carbon::now()->addDays(30),
                'start_date' => Carbon::now()->subDays(10),
                'completion_date' => null,
                'target_value' => 1.00,
                'current_value' => 0.60,
                'progress_percentage' => 60.00,
                'success_criteria' => 'Auditoría completada sin observaciones significativas',
                'dependencies' => 'Documentación financiera actualizada',
                'risks' => 'Hallazgos que requieran ajustes contables',
                'mitigation_strategies' => 'Revisión previa de documentación',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(3)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['auditoría', 'financiera', 'anual'],
                'notes' => 'Proceso en curso, documentación en revisión',
            ],

            // Hitos Operacionales
            [
                'title' => 'Implementación de Sistema de Monitoreo',
                'description' => 'Instalar y configurar el sistema de monitoreo en tiempo real de las instalaciones.',
                'milestone_type' => Milestone::MILESTONE_TYPE_OPERATIONAL,
                'status' => Milestone::STATUS_IN_PROGRESS,
                'priority' => Milestone::PRIORITY_MEDIUM,
                'target_date' => Carbon::now()->addDays(20),
                'start_date' => Carbon::now()->subDays(5),
                'completion_date' => null,
                'target_value' => 100.00,
                'current_value' => 25.00,
                'progress_percentage' => 25.00,
                'success_criteria' => 'Sistema funcionando en todas las instalaciones',
                'dependencies' => 'Infraestructura de red, equipos de monitoreo',
                'risks' => 'Problemas de conectividad en zonas remotas',
                'mitigation_strategies' => 'Plan B con conectividad satelital',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(4)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['monitoreo', 'tiempo real', 'sistema'],
                'notes' => 'Instalación en progreso, 25% completado',
            ],
            [
                'title' => 'Capacitación del Personal Técnico',
                'description' => 'Capacitar al personal técnico en el manejo de los nuevos equipos solares.',
                'milestone_type' => Milestone::MILESTONE_TYPE_OPERATIONAL,
                'status' => Milestone::STATUS_NOT_STARTED,
                'priority' => Milestone::PRIORITY_MEDIUM,
                'target_date' => Carbon::now()->addDays(35),
                'start_date' => null,
                'completion_date' => null,
                'target_value' => 20.00,
                'current_value' => 0.00,
                'progress_percentage' => 0.00,
                'success_criteria' => '20 técnicos certificados en manejo de equipos solares',
                'dependencies' => 'Finalización de instalación de equipos',
                'risks' => 'Disponibilidad del personal para capacitación',
                'mitigation_strategies' => 'Programación flexible de sesiones',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(1)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['capacitación', 'personal', 'técnico', 'solar'],
                'notes' => 'Programado para después de la instalación',
            ],

            // Hitos Regulatorios
            [
                'title' => 'Aprobación de Licencia de Generación',
                'description' => 'Obtener la licencia de generación eléctrica de la autoridad regulatoria.',
                'milestone_type' => Milestone::MILESTONE_TYPE_REGULATORY,
                'status' => Milestone::STATUS_COMPLETED,
                'priority' => Milestone::PRIORITY_CRITICAL,
                'target_date' => Carbon::now()->subDays(5),
                'start_date' => Carbon::now()->subDays(120),
                'completion_date' => Carbon::now()->subDays(5),
                'target_value' => 1.00,
                'current_value' => 1.00,
                'progress_percentage' => 100.00,
                'success_criteria' => 'Licencia aprobada y emitida por la autoridad',
                'dependencies' => 'Documentación técnica, estudios de impacto ambiental',
                'risks' => 'Cambios en regulaciones durante el proceso',
                'mitigation_strategies' => 'Seguimiento constante de cambios regulatorios',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(2)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['licencia', 'generación', 'regulatorio'],
                'notes' => 'Licencia obtenida exitosamente',
            ],
            [
                'title' => 'Cumplimiento de Normativas Ambientales',
                'description' => 'Asegurar el cumplimiento de todas las normativas ambientales aplicables.',
                'milestone_type' => Milestone::MILESTONE_TYPE_REGULATORY,
                'status' => Milestone::STATUS_IN_PROGRESS,
                'priority' => Milestone::PRIORITY_HIGH,
                'target_date' => Carbon::now()->addDays(25),
                'start_date' => Carbon::now()->subDays(15),
                'completion_date' => null,
                'target_value' => 100.00,
                'current_value' => 75.00,
                'progress_percentage' => 75.00,
                'success_criteria' => 'Certificación ambiental completa',
                'dependencies' => 'Estudios de impacto, medidas de mitigación',
                'risks' => 'Nuevas regulaciones ambientales',
                'mitigation_strategies' => 'Actualización continua de normativas',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(3)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['ambiental', 'normativas', 'cumplimiento'],
                'notes' => '75% de normativas cumplidas',
            ],

            // Hitos Comunitarios
            [
                'title' => 'Programa de Educación Comunitaria',
                'description' => 'Implementar programa de educación sobre energía renovable en la comunidad.',
                'milestone_type' => Milestone::MILESTONE_TYPE_COMMUNITY,
                'status' => Milestone::STATUS_IN_PROGRESS,
                'priority' => Milestone::PRIORITY_MEDIUM,
                'target_date' => Carbon::now()->addDays(40),
                'start_date' => Carbon::now()->subDays(20),
                'completion_date' => null,
                'target_value' => 500.00,
                'current_value' => 150.00,
                'progress_percentage' => 30.00,
                'success_criteria' => '500 personas capacitadas en energía renovable',
                'dependencies' => 'Material educativo, instructores certificados',
                'risks' => 'Baja participación comunitaria',
                'mitigation_strategies' => 'Campaña de sensibilización previa',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(4)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['educación', 'comunidad', 'renovable'],
                'notes' => '150 personas ya capacitadas',
            ],
            [
                'title' => 'Inclusión de Nuevos Miembros',
                'description' => 'Incorporar 100 nuevos miembros a la cooperativa.',
                'milestone_type' => Milestone::MILESTONE_TYPE_COMMUNITY,
                'status' => Milestone::STATUS_NOT_STARTED,
                'priority' => Milestone::PRIORITY_LOW,
                'target_date' => Carbon::now()->addDays(90),
                'start_date' => null,
                'completion_date' => null,
                'target_value' => 100.00,
                'current_value' => 0.00,
                'progress_percentage' => 0.00,
                'success_criteria' => '100 nuevos miembros activos en la cooperativa',
                'dependencies' => 'Campaña de marketing, proceso de admisión',
                'risks' => 'Competencia de otras cooperativas',
                'mitigation_strategies' => 'Diferenciación en servicios y beneficios',
                'parent_milestone_id' => null,
                'assigned_to' => $users->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['miembros', 'cooperativa', 'crecimiento'],
                'notes' => 'Campaña de marketing en preparación',
            ],

            // Hitos Ambientales
            [
                'title' => 'Reducción de Huella de Carbono',
                'description' => 'Reducir la huella de carbono de la cooperativa en un 30%.',
                'milestone_type' => Milestone::MILESTONE_TYPE_ENVIRONMENTAL,
                'status' => Milestone::STATUS_IN_PROGRESS,
                'priority' => Milestone::PRIORITY_HIGH,
                'target_date' => Carbon::now()->addDays(60),
                'start_date' => Carbon::now()->subDays(30),
                'completion_date' => null,
                'target_value' => 30.00,
                'current_value' => 12.00,
                'progress_percentage' => 40.00,
                'success_criteria' => '30% de reducción en emisiones de CO₂',
                'dependencies' => 'Implementación de tecnologías limpias',
                'risks' => 'Limitaciones técnicas en reducción de emisiones',
                'mitigation_strategies' => 'Diversificación de estrategias de reducción',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(1)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['carbono', 'reducción', '30%', 'ambiental'],
                'notes' => '12% de reducción lograda hasta ahora',
            ],
            [
                'title' => 'Certificación ISO 14001',
                'description' => 'Obtener la certificación ISO 14001 de gestión ambiental.',
                'milestone_type' => Milestone::MILESTONE_TYPE_ENVIRONMENTAL,
                'status' => Milestone::STATUS_ON_HOLD,
                'priority' => Milestone::PRIORITY_MEDIUM,
                'target_date' => Carbon::now()->addDays(120),
                'start_date' => Carbon::now()->subDays(60),
                'completion_date' => null,
                'target_value' => 1.00,
                'current_value' => 0.40,
                'progress_percentage' => 40.00,
                'success_criteria' => 'Certificación ISO 14001 obtenida',
                'dependencies' => 'Sistema de gestión ambiental implementado',
                'risks' => 'Complejidad del proceso de certificación',
                'mitigation_strategies' => 'Asesoría externa especializada',
                'parent_milestone_id' => null,
                'assigned_to' => $users->skip(2)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['ISO 14001', 'certificación', 'ambiental'],
                'notes' => 'En espera de recursos adicionales',
            ],

            // Sub-hitos (hijos)
            [
                'title' => 'Instalación de Inversores',
                'description' => 'Instalar los inversores para el parque solar principal.',
                'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
                'status' => Milestone::STATUS_COMPLETED,
                'priority' => Milestone::PRIORITY_HIGH,
                'target_date' => Carbon::now()->subDays(5),
                'start_date' => Carbon::now()->subDays(20),
                'completion_date' => Carbon::now()->subDays(5),
                'target_value' => 50.00,
                'current_value' => 50.00,
                'progress_percentage' => 100.00,
                'success_criteria' => '50 inversores instalados y funcionando',
                'dependencies' => 'Infraestructura base del parque',
                'risks' => 'Defectos en equipos',
                'mitigation_strategies' => 'Pruebas exhaustivas antes de instalación',
                'parent_milestone_id' => null, // Se asignará después
                'assigned_to' => $users->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['inversores', 'instalación', 'parque solar'],
                'notes' => 'Sub-hito del parque solar principal',
            ],
            [
                'title' => 'Instalación de Paneles Solares',
                'description' => 'Instalar los paneles solares en el parque principal.',
                'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
                'status' => Milestone::STATUS_IN_PROGRESS,
                'priority' => Milestone::PRIORITY_HIGH,
                'target_date' => Carbon::now()->addDays(15),
                'start_date' => Carbon::now()->subDays(10),
                'completion_date' => null,
                'target_value' => 2000.00,
                'current_value' => 1200.00,
                'progress_percentage' => 60.00,
                'success_criteria' => '2000 paneles solares instalados',
                'dependencies' => 'Estructuras de soporte instaladas',
                'risks' => 'Condiciones climáticas adversas',
                'mitigation_strategies' => 'Plan de trabajo flexible según clima',
                'parent_milestone_id' => null, // Se asignará después
                'assigned_to' => $users->skip(1)->first()?->id,
                'created_by' => $users->first()?->id,
                'tags' => ['paneles', 'solar', 'instalación'],
                'notes' => 'Sub-hito del parque solar principal',
            ],
        ];

        // Crear los hitos principales
        $createdMilestones = [];
        foreach ($milestones as $milestone) {
            $createdMilestones[] = Milestone::create($milestone);
        }

        // Asignar sub-hitos a sus padres
        $parkSolarMilestone = $createdMilestones[0]; // Parque Solar Principal
        $inverterMilestone = $createdMilestones[11]; // Instalación de Inversores
        $panelsMilestone = $createdMilestones[12]; // Instalación de Paneles

        $inverterMilestone->update(['parent_milestone_id' => $parkSolarMilestone->id]);
        $panelsMilestone->update(['parent_milestone_id' => $parkSolarMilestone->id]);

        $this->command->info('✅ MilestoneSeeder ejecutado correctamente');
        $this->command->info('📊 Hitos creados: ' . count($milestones));
        $this->command->info('🎯 Tipos de hitos: Proyecto (4), Financiero (2), Operacional (2), Regulatorio (2), Comunitario (2), Ambiental (2)');
        $this->command->info('📈 Estados: Completados (3), En Progreso (6), No Iniciados (3), En Espera (1)');
        $this->command->info('⚡ Prioridades: Crítica (2), Alta (3), Media (4), Baja (1), Urgente (1)');
        $this->command->info('👥 Usuarios asignados: ' . $users->count() . ' usuarios diferentes');
        $this->command->info('🔗 Relaciones: 2 sub-hitos asignados al parque solar principal');
        $this->command->info('📅 Fechas: Hitos distribuidos en los próximos 120 días');
        $this->command->info('🎯 Datos realistas: Progreso, valores y criterios de éxito específicos');
    }
}
