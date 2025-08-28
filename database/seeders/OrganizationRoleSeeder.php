<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrganizationRole;
use App\Models\Organization;
use Illuminate\Support\Str;

class OrganizationRoleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Creando roles de organización españoles para la cooperativa energética...');
        
        // Limpiar roles existentes
        OrganizationRole::query()->delete();
        
        $organizations = Organization::all();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No hay organizaciones disponibles. No se pueden crear roles.');
            return;
        }
        
        foreach ($organizations as $organization) {
            $this->createRolesForOrganization($organization);
        }
        
        $this->command->info('✅ OrganizationRoleSeeder completado. Se crearon ' . OrganizationRole::count() . ' roles de organización españoles.');
    }
    
    private function createRolesForOrganization(Organization $organization): void
    {
        $this->command->info("🏢 Creando roles para la organización: {$organization->name}");
        
        // Roles estándar para todas las organizaciones
        $standardRoles = [
            [
                'name' => 'Administrador General',
                'description' => 'Responsable de la administración y gestión general de la organización. Tiene acceso completo a todas las funcionalidades.',
                'permissions' => [
                    'organization.manage',
                    'user.manage',
                    'role.manage',
                    'project.manage',
                    'customer.manage',
                    'billing.manage',
                    'report.manage',
                    'settings.manage',
                    'energy.manage',
                    'maintenance.manage',
                    'financial.manage',
                    'security.manage'
                ]
            ],
            [
                'name' => 'Gestor de Proyectos',
                'description' => 'Responsable de gestionar y coordinar proyectos energéticos de la organización. Coordina equipos y recursos.',
                'permissions' => [
                    'project.view',
                    'project.create',
                    'project.update',
                    'project.manage',
                    'team.manage',
                    'resource.manage',
                    'timeline.manage',
                    'report.view',
                    'report.create',
                    'customer.view',
                    'energy.view',
                    'maintenance.view'
                ]
            ],
            [
                'name' => 'Técnico de Instalación',
                'description' => 'Responsable de realizar instalaciones técnicas, mantenimientos y reparaciones de equipos energéticos.',
                'permissions' => [
                    'project.view',
                    'project.update',
                    'maintenance.view',
                    'maintenance.create',
                    'maintenance.update',
                    'equipment.view',
                    'equipment.update',
                    'report.view',
                    'report.create',
                    'safety.view',
                    'quality.view'
                ]
            ],
            [
                'name' => 'Atención al Cliente',
                'description' => 'Responsable de la atención y gestión de clientes, soporte técnico y resolución de consultas.',
                'permissions' => [
                    'customer.view',
                    'customer.create',
                    'customer.update',
                    'ticket.view',
                    'ticket.create',
                    'ticket.update',
                    'project.view',
                    'report.view',
                    'communication.manage',
                    'feedback.manage'
                ]
            ],
            [
                'name' => 'Comercial',
                'description' => 'Responsable de ventas, captación de clientes y gestión comercial de la organización.',
                'permissions' => [
                    'customer.view',
                    'customer.create',
                    'customer.update',
                    'lead.view',
                    'lead.create',
                    'lead.update',
                    'opportunity.view',
                    'opportunity.create',
                    'opportunity.update',
                    'project.view',
                    'project.create',
                    'report.view',
                    'report.create',
                    'billing.view'
                ]
            ],
            [
                'name' => 'Facturación',
                'description' => 'Responsable de la gestión de facturación, cobros y administración financiera de clientes.',
                'permissions' => [
                    'billing.view',
                    'billing.create',
                    'billing.update',
                    'billing.manage',
                    'customer.view',
                    'payment.view',
                    'payment.create',
                    'payment.update',
                    'invoice.view',
                    'invoice.create',
                    'invoice.update',
                    'report.view',
                    'report.create',
                    'report.export',
                    'financial.view'
                ]
            ],
            [
                'name' => 'Analista de Reportes',
                'description' => 'Responsable de la generación, análisis y presentación de reportes y métricas de la organización.',
                'permissions' => [
                    'report.view',
                    'report.create',
                    'report.update',
                    'report.export',
                    'analytics.view',
                    'analytics.create',
                    'dashboard.view',
                    'dashboard.customize',
                    'data.view',
                    'data.export',
                    'project.view',
                    'customer.view',
                    'energy.view',
                    'financial.view'
                ]
            ],
            [
                'name' => 'Especialista en Energía',
                'description' => 'Experto en sistemas energéticos, optimización de consumo y tecnologías renovables.',
                'permissions' => [
                    'energy.view',
                    'energy.manage',
                    'energy.optimize',
                    'project.view',
                    'project.create',
                    'project.update',
                    'maintenance.view',
                    'maintenance.create',
                    'maintenance.update',
                    'equipment.view',
                    'equipment.manage',
                    'report.view',
                    'report.create',
                    'analytics.view',
                    'sustainability.view'
                ]
            ],
            [
                'name' => 'Supervisor de Mantenimiento',
                'description' => 'Responsable de supervisar y coordinar las actividades de mantenimiento preventivo y correctivo.',
                'permissions' => [
                    'maintenance.view',
                    'maintenance.create',
                    'maintenance.update',
                    'maintenance.manage',
                    'schedule.view',
                    'schedule.create',
                    'schedule.update',
                    'team.manage',
                    'equipment.view',
                    'equipment.update',
                    'safety.view',
                    'quality.view',
                    'report.view',
                    'report.create',
                    'project.view'
                ]
            ],
            [
                'name' => 'Coordinador de Equipos',
                'description' => 'Responsable de coordinar equipos de trabajo, asignar tareas y supervisar el rendimiento.',
                'permissions' => [
                    'team.view',
                    'team.create',
                    'team.update',
                    'team.manage',
                    'user.view',
                    'user.update',
                    'project.view',
                    'project.update',
                    'task.view',
                    'task.create',
                    'task.update',
                    'schedule.view',
                    'schedule.update',
                    'report.view',
                    'performance.view'
                ]
            ]
        ];
        
        // Roles especializados según el tipo de organización
        $specializedRoles = $this->getSpecializedRolesForOrganization($organization);
        
        // Combinar roles estándar y especializados
        $allRoles = array_merge($standardRoles, $specializedRoles);
        
        foreach ($allRoles as $roleData) {
            $this->createRole($organization, $roleData);
        }
    }
    
    private function getSpecializedRolesForOrganization(Organization $organization): array
    {
        $organizationName = strtolower($organization->name);
        
        // Roles para cooperativas energéticas principales
        if (str_contains($organizationName, 'cooperativa') || str_contains($organizationName, 'energética')) {
            return [
                [
                    'name' => 'Director Técnico',
                    'description' => 'Responsable de la dirección técnica, planificación estratégica y supervisión de proyectos energéticos.',
                    'permissions' => [
                        'organization.manage',
                        'strategy.view',
                        'strategy.create',
                        'strategy.update',
                        'project.manage',
                        'team.manage',
                        'budget.manage',
                        'quality.manage',
                        'compliance.manage',
                        'report.manage',
                        'analytics.manage'
                    ]
                ],
                [
                    'name' => 'Responsable de Sostenibilidad',
                    'description' => 'Responsable de implementar y supervisar iniciativas de sostenibilidad y eficiencia energética.',
                    'permissions' => [
                        'sustainability.view',
                        'sustainability.manage',
                        'energy.view',
                        'energy.manage',
                        'project.view',
                        'project.create',
                        'project.update',
                        'report.view',
                        'report.create',
                        'analytics.view',
                        'compliance.view',
                        'compliance.manage'
                    ]
                ]
            ];
        }
        
        // Roles para organizaciones provinciales
        if (str_contains($organizationName, 'zaragoza') || str_contains($organizationName, 'huesca') || str_contains($organizationName, 'teruel')) {
            return [
                [
                    'name' => 'Coordinador Provincial',
                    'description' => 'Responsable de coordinar actividades y proyectos en la provincia específica.',
                    'permissions' => [
                        'province.manage',
                        'project.view',
                        'project.create',
                        'project.update',
                        'team.manage',
                        'customer.manage',
                        'report.view',
                        'report.create',
                        'local.view',
                        'local.manage',
                        'communication.manage'
                    ]
                ]
            ];
        }
        
        // Roles para organizaciones especializadas
        if (str_contains($organizationName, 'solar') || str_contains($organizationName, 'eólica') || str_contains($organizationName, 'hidroeléctrica')) {
            return [
                [
                    'name' => 'Especialista en Tecnología',
                    'description' => 'Experto en la tecnología específica de la organización (solar, eólica, hidroeléctrica).',
                    'permissions' => [
                        'technology.view',
                        'technology.manage',
                        'energy.view',
                        'energy.manage',
                        'project.view',
                        'project.create',
                        'project.update',
                        'maintenance.view',
                        'maintenance.manage',
                        'equipment.view',
                        'equipment.manage',
                        'report.view',
                        'report.create',
                        'innovation.view',
                        'innovation.manage'
                    ]
                ]
            ];
        }
        
        // Roles para organizaciones comunitarias
        if (str_contains($organizationName, 'comunidad') || str_contains($organizationName, 'vecinal')) {
            return [
                [
                    'name' => 'Coordinador Comunitario',
                    'description' => 'Responsable de coordinar actividades comunitarias y participación ciudadana.',
                    'permissions' => [
                        'community.view',
                        'community.manage',
                        'participation.view',
                        'participation.manage',
                        'event.view',
                        'event.create',
                        'event.manage',
                        'communication.view',
                        'communication.manage',
                        'project.view',
                        'project.create',
                        'report.view',
                        'report.create'
                    ]
                ]
            ];
        }
        
        return [];
    }
    
    private function createRole(Organization $organization, array $roleData): void
    {
        $slug = Str::slug($roleData['name']) . '-' . Str::slug($organization->name);
        
        OrganizationRole::create([
            'organization_id' => $organization->id,
            'name' => $roleData['name'],
            'slug' => $slug,
            'description' => $roleData['description'],
            'permissions' => $roleData['permissions'],
        ]);
        
        $this->command->info("  ✅ Creado rol: {$roleData['name']}");
    }
}
