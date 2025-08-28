<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrganizationFeature;
use App\Models\Organization;
use Carbon\Carbon;

class OrganizationFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('⚙️ Creando características de organizaciones españolas para la cooperativa energética...');
        
        // Limpiar características existentes
        OrganizationFeature::query()->delete();
        
        $organizations = Organization::all();
        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No hay organizaciones disponibles. Creando características sin organización.');
            $organizations = collect([null]);
        }
        
        $this->createEnergyManagementFeatures($organizations);
        $this->createRenewableEnergyFeatures($organizations);
        $this->createSmartGridFeatures($organizations);
        $this->createCustomerExperienceFeatures($organizations);
        $this->createAnalyticsFeatures($organizations);
        $this->createIntegrationFeatures($organizations);
        $this->createSecurityFeatures($organizations);
        $this->createCollaborationFeatures($organizations);
        
        $this->command->info('✅ OrganizationFeatureSeeder completado. Se crearon ' . OrganizationFeature::count() . ' características de organizaciones españolas.');
    }
    
    private function createEnergyManagementFeatures($organizations): void
    {
        $this->command->info('⚡ Creando características de gestión energética...');
        
        $energyFeatures = [
            [
                'feature_key' => 'energy_management',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Sistema central de gestión energética para monitoreo y control del consumo en tiempo real.'
            ],
            [
                'feature_key' => 'consumption_analytics',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Análisis avanzado del consumo energético con gráficos, tendencias y comparativas históricas.'
            ],
            [
                'feature_key' => 'demand_forecasting',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Predicción de demanda energética basada en IA y patrones históricos para optimización de recursos.'
            ],
            [
                'feature_key' => 'load_balancing',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Equilibrado inteligente de carga energética entre diferentes fuentes y consumidores.'
            ],
            [
                'feature_key' => 'peak_shaving',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Reducción automática de picos de consumo durante horas de alta demanda.'
            ]
        ];
        
        foreach ($energyFeatures as $featureData) {
            $this->createFeatureForOrganizations($featureData, $organizations, 0.9);
        }
    }
    
    private function createRenewableEnergyFeatures($organizations): void
    {
        $this->command->info('🌱 Creando características de energía renovable...');
        
        $renewableFeatures = [
            [
                'feature_key' => 'solar_panels',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Gestión y monitoreo de paneles solares con seguimiento de rendimiento y mantenimiento predictivo.'
            ],
            [
                'feature_key' => 'wind_turbines',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Control de aerogeneradores con optimización de orientación y velocidad según condiciones meteorológicas.'
            ],
            [
                'feature_key' => 'battery_storage',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Sistema de almacenamiento de baterías con gestión inteligente de carga/descarga y estado de salud.'
            ],
            [
                'feature_key' => 'hydroelectric',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Control de microcentrales hidroeléctricas con gestión de flujo y nivel de embalses.'
            ],
            [
                'feature_key' => 'biomass_management',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Gestión de plantas de biomasa con control de combustión y eficiencia térmica.'
            ]
        ];
        
        foreach ($renewableFeatures as $featureData) {
            $this->createFeatureForOrganizations($featureData, $organizations, 0.8);
        }
    }
    
    private function createSmartGridFeatures($organizations): void
    {
        $this->command->info('🔌 Creando características de red inteligente...');
        
        $smartGridFeatures = [
            [
                'feature_key' => 'smart_meters',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Contadores inteligentes con lectura automática y comunicación bidireccional en tiempo real.'
            ],
            [
                'feature_key' => 'grid_monitoring',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Monitoreo continuo del estado de la red eléctrica con detección de fallos y alertas automáticas.'
            ],
            [
                'feature_key' => 'voltage_control',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Control automático de voltaje para mantener estabilidad en la red y optimizar distribución.'
            ],
            [
                'feature_key' => 'fault_detection',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Detección automática de fallos en la red con localización precisa y notificación inmediata.'
            ],
            [
                'feature_key' => 'power_quality',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Monitoreo de calidad de energía con análisis de armónicos, interrupciones y fluctuaciones.'
            ]
        ];
        
        foreach ($smartGridFeatures as $featureData) {
            $this->createFeatureForOrganizations($featureData, $organizations, 0.85);
        }
    }
    
    private function createCustomerExperienceFeatures($organizations): void
    {
        $this->command->info('👥 Creando características de experiencia del cliente...');
        
        $customerFeatures = [
            [
                'feature_key' => 'customer_portal',
                'enabled_dashboard' => false,
                'enabled_web' => true,
                'notes' => 'Portal web para clientes con acceso a facturas, consumo en tiempo real y gestión de servicios.'
            ],
            [
                'feature_key' => 'mobile_app',
                'enabled_dashboard' => false,
                'enabled_web' => true,
                'notes' => 'Aplicación móvil nativa para iOS y Android con funcionalidades completas de gestión energética.'
            ],
            [
                'feature_key' => 'billing_integration',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Integración completa con sistemas de facturación y contabilidad para automatización de procesos.'
            ],
            [
                'feature_key' => 'payment_processing',
                'enabled_dashboard' => false,
                'enabled_web' => true,
                'notes' => 'Procesamiento de pagos online con múltiples métodos y gestión de suscripciones.'
            ],
            [
                'feature_key' => 'customer_support',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Sistema de soporte al cliente con tickets, chat en vivo y base de conocimientos.'
            ]
        ];
        
        foreach ($customerFeatures as $featureData) {
            $this->createFeatureForOrganizations($featureData, $organizations, 0.9);
        }
    }
    
    private function createAnalyticsFeatures($organizations): void
    {
        $this->command->info('📊 Creando características de análisis...');
        
        $analyticsFeatures = [
            [
                'feature_key' => 'advanced_reporting',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Generación de reportes avanzados con personalización, programación automática y exportación múltiple.'
            ],
            [
                'feature_key' => 'real_time_dashboard',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Dashboard en tiempo real con métricas clave, alertas y visualizaciones interactivas.'
            ],
            [
                'feature_key' => 'predictive_analytics',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Análisis predictivo basado en machine learning para optimización de operaciones y mantenimiento.'
            ],
            [
                'feature_key' => 'performance_metrics',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Métricas de rendimiento con KPIs personalizables y comparativas entre períodos.'
            ],
            [
                'feature_key' => 'data_visualization',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Visualización avanzada de datos con gráficos interactivos, mapas de calor y análisis multidimensional.'
            ]
        ];
        
        foreach ($analyticsFeatures as $featureData) {
            $this->createFeatureForOrganizations($featureData, $organizations, 0.8);
        }
    }
    
    private function createIntegrationFeatures($organizations): void
    {
        $this->command->info('🔗 Creando características de integración...');
        
        $integrationFeatures = [
            [
                'feature_key' => 'api_access',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'API RESTful completa con documentación, autenticación OAuth2 y rate limiting configurable.'
            ],
            [
                'feature_key' => 'third_party_integrations',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Integraciones con sistemas externos como ERPs, CRMs y plataformas de contabilidad.'
            ],
            [
                'feature_key' => 'webhook_support',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Soporte para webhooks con configuración de endpoints, autenticación y reintentos automáticos.'
            ],
            [
                'feature_key' => 'data_export',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Exportación de datos en múltiples formatos (CSV, Excel, JSON, XML) con programación automática.'
            ],
            [
                'feature_key' => 'cloud_sync',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Sincronización automática con servicios en la nube para backup y colaboración.'
            ]
        ];
        
        foreach ($integrationFeatures as $featureData) {
            $this->createFeatureForOrganizations($featureData, $organizations, 0.75);
        }
    }
    
    private function createSecurityFeatures($organizations): void
    {
        $this->command->info('🔒 Creando características de seguridad...');
        
        $securityFeatures = [
            [
                'feature_key' => 'audit_logs',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Registros de auditoría completos con trazabilidad de todas las acciones del sistema.'
            ],
            [
                'feature_key' => 'role_based_access',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Control de acceso basado en roles con permisos granulares y gestión de usuarios avanzada.'
            ],
            [
                'feature_key' => 'two_factor_auth',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Autenticación de dos factores con soporte para TOTP, SMS y aplicaciones autenticadoras.'
            ],
            [
                'feature_key' => 'data_encryption',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Cifrado de datos en reposo y en tránsito con algoritmos AES-256 y gestión de claves.'
            ],
            [
                'feature_key' => 'backup_restore',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Sistema de respaldo automático con restauración granular y verificación de integridad.'
            ]
        ];
        
        foreach ($securityFeatures as $featureData) {
            $this->createFeatureForOrganizations($featureData, $organizations, 0.95);
        }
    }
    
    private function createCollaborationFeatures($organizations): void
    {
        $this->command->info('🤝 Creando características de colaboración...');
        
        $collaborationFeatures = [
            [
                'feature_key' => 'team_collaboration',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Herramientas de colaboración en equipo con chat, videoconferencias y gestión de proyectos.'
            ],
            [
                'feature_key' => 'document_management',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Gestión documental con versionado, control de acceso y flujos de trabajo aprobación.'
            ],
            [
                'feature_key' => 'workflow_automation',
                'enabled_dashboard' => true,
                'enabled_web' => false,
                'notes' => 'Automatización de flujos de trabajo con notificaciones, escalación y seguimiento de tareas.'
            ],
            [
                'feature_key' => 'knowledge_base',
                'enabled_dashboard' => false,
                'enabled_web' => true,
                'notes' => 'Base de conocimientos con artículos, tutoriales y búsqueda inteligente para usuarios y soporte.'
            ],
            [
                'feature_key' => 'notification_system',
                'enabled_dashboard' => true,
                'enabled_web' => true,
                'notes' => 'Sistema de notificaciones multicanal con personalización, programación y gestión de preferencias.'
            ]
        ];
        
        foreach ($collaborationFeatures as $featureData) {
            $this->createFeatureForOrganizations($featureData, $organizations, 0.8);
        }
    }
    
    private function createFeatureForOrganizations($featureData, $organizations, $enableProbability): void
    {
        foreach ($organizations as $organization) {
            if (!$organization) continue;
            
            // Determinar si la característica estará habilitada basándose en la probabilidad
            $enabledDashboard = rand(1, 100) <= ($enableProbability * 100);
            $enabledWeb = rand(1, 100) <= ($enableProbability * 100);
            
            // Asegurar que al menos una plataforma esté habilitada para características críticas
            if ($featureData['feature_key'] === 'energy_management' || 
                $featureData['feature_key'] === 'smart_meters' ||
                $featureData['feature_key'] === 'audit_logs') {
                $enabledDashboard = true;
                $enabledWeb = true;
            }
            
            OrganizationFeature::create([
                'organization_id' => $organization->id,
                'feature_key' => $featureData['feature_key'],
                'enabled_dashboard' => $enabledDashboard,
                'enabled_web' => $enabledWeb,
                'notes' => $featureData['notes'],
            ]);
        }
    }
}
