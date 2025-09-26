<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskTemplate;
use App\Models\User;
use Carbon\Carbon;

class TaskTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(3)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando TaskTemplateSeeder.');
            return;
        }

        $templates = [
            [
                'name' => 'Mantenimiento Preventivo Solar',
                'description' => 'Plantilla estándar para mantenimiento preventivo de paneles solares.',
                'template_type' => TaskTemplate::TEMPLATE_TYPE_MAINTENANCE,
                'category' => 'Solar',
                'subcategory' => 'Preventivo',
                'estimated_duration_hours' => 8.0,
                'estimated_cost' => 500.00,
                'required_skills' => json_encode(['Técnico solar', 'Seguridad en altura']),
                'required_tools' => json_encode(['Escalera', 'Cepillo suave', 'Multímetro']),
                'required_parts' => json_encode(['Cables de conexión', 'Juntas de goma']),
                'safety_requirements' => 'Arnés de seguridad, verificación meteorológica',
                'technical_requirements' => 'Conocimiento de sistemas fotovoltaicos',
                'quality_standards' => 'ISO 9001, IEC 61215',
                'checklist_items' => json_encode([
                    'Limpiar superficie de paneles',
                    'Verificar conexiones eléctricas',
                    'Revisar estructura de soporte',
                    'Comprobar rendimiento energético'
                ]),
                'work_instructions' => 'Seguir protocolo de limpieza y verificación técnica.',
                'is_active' => true,
                'is_standard' => true,
                'version' => '1.0',
                'tags' => json_encode(['solar', 'preventivo', 'mantenimiento']),
                'notes' => 'Plantilla aprobada para uso estándar.',
                'department' => 'Mantenimiento',
                'priority' => 'medium',
                'risk_level' => 'low',
                'compliance_requirements' => 'Cumplir normativa de seguridad laboral',
                'documentation_required' => 'Informe de mantenimiento, fotos del estado',
                'training_required' => true,
                'certification_required' => true,
                'environmental_considerations' => 'Uso de productos de limpieza ecológicos',
                'budget_code' => 'MANT-SOLAR-001',
                'cost_center' => 'Mantenimiento Solar',
                'project_code' => 'PROJ-SOLAR-MANT',
                'created_by' => $users->first()->id,
                'approved_by' => $users->first()->id,
                'approved_at' => Carbon::now()->subMonths(6),
            ],
            [
                'name' => 'Reparación Turbina Eólica',
                'description' => 'Plantilla para reparación de turbinas eólicas con problemas mecánicos.',
                'template_type' => TaskTemplate::TEMPLATE_TYPE_REPAIR,
                'category' => 'Eólica',
                'subcategory' => 'Correctivo',
                'estimated_duration_hours' => 24.0,
                'estimated_cost' => 15000.00,
                'required_skills' => json_encode(['Técnico eólico', 'Mecánico especializado']),
                'required_tools' => json_encode(['Grúa', 'Herramientas especializadas', 'Equipo de medición']),
                'required_parts' => json_encode(['Rodamientos', 'Aceite hidráulico', 'Filtros']),
                'safety_requirements' => 'Parada total, bloqueo de seguridad, equipo de protección',
                'technical_requirements' => 'Conocimiento de turbinas eólicas, certificación técnica',
                'quality_standards' => 'ISO 9001, EN 61400',
                'checklist_items' => json_encode([
                    'Diagnosticar problema',
                    'Revisar rodamientos',
                    'Verificar alineación',
                    'Probar funcionamiento'
                ]),
                'work_instructions' => 'Seguir protocolo de reparación y pruebas de funcionamiento.',
                'is_active' => true,
                'is_standard' => true,
                'version' => '2.0',
                'tags' => json_encode(['eolica', 'reparacion', 'correctivo']),
                'notes' => 'Plantilla actualizada con nuevos protocolos de seguridad.',
                'department' => 'Mantenimiento',
                'priority' => 'high',
                'risk_level' => 'high',
                'compliance_requirements' => 'Cumplir normativa de seguridad eólica',
                'documentation_required' => 'Informe de reparación, certificado de pruebas',
                'training_required' => true,
                'certification_required' => true,
                'environmental_considerations' => 'Gestión adecuada de aceites y residuos',
                'budget_code' => 'REP-EOLIC-001',
                'cost_center' => 'Mantenimiento Eólico',
                'project_code' => 'PROJ-EOLIC-REP',
                'created_by' => $users->skip(1)->first()->id,
                'approved_by' => $users->first()->id,
                'approved_at' => Carbon::now()->subMonths(3),
            ],
        ];

        foreach ($templates as $template) {
            TaskTemplate::create($template);
        }

        $this->command->info('✅ TaskTemplateSeeder ejecutado correctamente');
        $this->command->info('📊 Plantillas de tareas creadas: ' . count($templates));
        $this->command->info('🔧 Tipos: Mantenimiento, Reparación');
        $this->command->info('⚡ Categorías: Solar, Eólica');
        $this->command->info('⭐ Prioridades: Media, Alta');
        $this->command->info('⚠️ Niveles de riesgo: Bajo, Alto');
        $this->command->info('💰 Costo total estimado: €' . number_format(collect($templates)->sum('estimated_cost'), 2));
        $this->command->info('⏱️ Duración total estimada: ' . collect($templates)->sum('estimated_duration_hours') . ' horas');
    }
}
