<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Carbon\Carbon;

class ChecklistTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(3)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando ChecklistTemplateSeeder.');
            return;
        }

        $templates = [
            [
                'name' => 'Checklist Mantenimiento Solar',
                'description' => 'Lista de verificación para mantenimiento preventivo de paneles solares.',
                'template_type' => ChecklistTemplate::TEMPLATE_TYPE_MAINTENANCE,
                'category' => 'Solar',
                'subcategory' => 'Preventivo',
                'checklist_items' => json_encode([
                    'Verificar limpieza de paneles',
                    'Comprobar conexiones eléctricas',
                    'Revisar estructura de soporte',
                    'Medir rendimiento energético',
                    'Verificar sistema de monitoreo'
                ]),
                'required_items' => json_encode([
                    'Arnés de seguridad',
                    'Cepillo suave',
                    'Multímetro',
                    'Cámara térmica'
                ]),
                'optional_items' => json_encode([
                    'Limpieza adicional',
                    'Documentación fotográfica'
                ]),
                'conditional_items' => json_encode([
                    'Si hay suciedad: limpiar con agua destilada',
                    'Si hay conexiones sueltas: apretar tornillos'
                ]),
                'item_order' => json_encode([1, 2, 3, 4, 5]),
                'scoring_system' => 'binary',
                'pass_threshold' => 80,
                'fail_threshold' => 60,
                'is_active' => true,
                'is_standard' => true,
                'version' => '1.0',
                'created_by' => $users->first()->id,
                'approved_by' => $users->first()->id,
                'approved_at' => Carbon::now()->subMonths(6),
                'tags' => json_encode(['solar', 'mantenimiento', 'preventivo']),
                'notes' => 'Checklist aprobado para uso estándar.',
                'department' => 'Mantenimiento',
                'priority' => 'medium',
                'risk_level' => 'low',
                'compliance_requirements' => 'Cumplir normativa de seguridad laboral',
                'quality_standards' => 'ISO 9001, IEC 61215',
                'safety_requirements' => 'Arnés de seguridad, verificación meteorológica',
                'training_required' => true,
                'certification_required' => true,
                'documentation_required' => 'Informe de mantenimiento, fotos del estado',
                'environmental_considerations' => 'Uso de productos de limpieza ecológicos',
                'budget_code' => 'CHK-SOLAR-001',
                'cost_center' => 'Mantenimiento Solar',
                'project_code' => 'PROJ-SOLAR-CHK',
                'estimated_completion_time' => 8.0,
            ],
            [
                'name' => 'Checklist Inspección Eólica',
                'description' => 'Lista de verificación para inspección de turbinas eólicas.',
                'template_type' => ChecklistTemplate::TEMPLATE_TYPE_INSPECTION,
                'category' => 'Eólica',
                'subcategory' => 'Inspección',
                'checklist_items' => json_encode([
                    'Verificar estado de palas',
                    'Comprobar rodamientos',
                    'Revisar sistema hidráulico',
                    'Verificar sistema eléctrico',
                    'Comprobar sistema de frenado'
                ]),
                'required_items' => json_encode([
                    'Equipo de seguridad',
                    'Herramientas de inspección',
                    'Equipo de medición',
                    'Cámara de inspección'
                ]),
                'optional_items' => json_encode([
                    'Análisis de vibraciones',
                    'Pruebas de funcionamiento'
                ]),
                'conditional_items' => json_encode([
                    'Si hay desgaste en palas: programar reparación',
                    'Si hay fugas hidráulicas: reemplazar juntas'
                ]),
                'item_order' => json_encode([1, 2, 3, 4, 5]),
                'scoring_system' => 'weighted',
                'pass_threshold' => 85,
                'fail_threshold' => 70,
                'is_active' => true,
                'is_standard' => true,
                'version' => '2.0',
                'created_by' => $users->skip(1)->first()->id,
                'approved_by' => $users->first()->id,
                'approved_at' => Carbon::now()->subMonths(3),
                'tags' => json_encode(['eolica', 'inspeccion', 'turbina']),
                'notes' => 'Checklist actualizado con nuevos protocolos.',
                'department' => 'Mantenimiento',
                'priority' => 'high',
                'risk_level' => 'high',
                'compliance_requirements' => 'Cumplir normativa de seguridad eólica',
                'quality_standards' => 'ISO 9001, EN 61400',
                'safety_requirements' => 'Parada total, bloqueo de seguridad',
                'training_required' => true,
                'certification_required' => true,
                'documentation_required' => 'Informe de inspección, certificado de pruebas',
                'environmental_considerations' => 'Gestión adecuada de residuos',
                'budget_code' => 'CHK-EOLIC-001',
                'cost_center' => 'Mantenimiento Eólico',
                'project_code' => 'PROJ-EOLIC-CHK',
                'estimated_completion_time' => 12.0,
            ],
        ];

        foreach ($templates as $template) {
            ChecklistTemplate::create($template);
        }

        $this->command->info('✅ ChecklistTemplateSeeder ejecutado correctamente');
        $this->command->info('📊 Plantillas de checklist creadas: ' . count($templates));
        $this->command->info('🔧 Tipos: Mantenimiento, Inspección');
        $this->command->info('⚡ Categorías: Solar, Eólica');
        $this->command->info('⭐ Prioridades: Media, Alta');
        $this->command->info('⚠️ Niveles de riesgo: Bajo, Alto');
        $this->command->info('📊 Sistemas de puntuación: Binario, Ponderado');
        $this->command->info('⏱️ Tiempo total estimado: ' . collect($templates)->sum('estimated_completion_time') . ' horas');
    }
}
