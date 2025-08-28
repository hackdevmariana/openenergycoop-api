<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AutomationRule;
use App\Models\User;
use Carbon\Carbon;

class AutomationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🤖 Creando reglas de automatización españolas para la cooperativa energética...');
        
        // Limpiar reglas existentes
        AutomationRule::query()->delete();
        
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Creando reglas sin usuario creador.');
            $users = collect([null]);
        }
        
        $this->createScheduledRules($users);
        $this->createEventDrivenRules($users);
        $this->createConditionBasedRules($users);
        $this->createManualRules($users);
        $this->createWebhookRules($users);
        
        $this->command->info('✅ AutomationRuleSeeder completado. Se crearon ' . AutomationRule::count() . ' reglas de automatización españolas.');
    }
    
    private function createScheduledRules($users): void
    {
        $this->command->info('⏰ Creando reglas programadas...');
        
        $scheduledRules = [
            [
                'name' => 'Reporte Diario de Consumo Energético',
                'description' => 'Genera y envía automáticamente un reporte diario del consumo energético de todos los miembros de la cooperativa a las 8:00 AM.',
                'rule_type' => 'scheduled',
                'trigger_type' => 'time',
                'trigger_conditions' => [
                    'time' => '08:00',
                    'timezone' => 'Europe/Madrid',
                    'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
                ],
                'action_type' => 'report',
                'action_parameters' => [
                    'report_type' => 'daily_consumption',
                    'recipients' => 'all_members',
                    'format' => 'pdf',
                    'include_charts' => true,
                    'include_comparison' => true
                ],
                'priority' => 6,
                'execution_frequency' => 'daily',
                'schedule_cron' => '0 8 * * *',
                'timezone' => 'Europe/Madrid',
                'is_active' => true,
                'tags' => ['reporte', 'consumo', 'diario', 'automatico', 'cooperativa']
            ],
            [
                'name' => 'Respaldo Semanal de Base de Datos',
                'description' => 'Realiza un respaldo completo de la base de datos todos los domingos a las 2:00 AM.',
                'rule_type' => 'scheduled',
                'trigger_type' => 'time',
                'trigger_conditions' => [
                    'time' => '02:00',
                    'timezone' => 'Europe/Madrid',
                    'days' => ['sunday']
                ],
                'action_type' => 'system_command',
                'action_parameters' => [
                    'command' => 'backup:database',
                    'storage' => 's3',
                    'retention_days' => 30,
                    'notify_on_completion' => true
                ],
                'priority' => 8,
                'execution_frequency' => 'weekly',
                'schedule_cron' => '0 2 * * 0',
                'timezone' => 'Europe/Madrid',
                'is_active' => true,
                'tags' => ['respaldo', 'base-datos', 'semanal', 'seguridad', 'sistema']
            ],
            [
                'name' => 'Limpieza Mensual de Logs',
                'description' => 'Limpia logs antiguos y archivos temporales el primer día de cada mes a las 3:00 AM.',
                'rule_type' => 'scheduled',
                'trigger_type' => 'time',
                'trigger_conditions' => [
                    'time' => '03:00',
                    'timezone' => 'Europe/Madrid',
                    'day_of_month' => 1
                ],
                'action_type' => 'system_command',
                'action_parameters' => [
                    'command' => 'logs:clean',
                    'retention_days' => 90,
                    'compress_old_logs' => true,
                    'notify_on_completion' => false
                ],
                'priority' => 5,
                'execution_frequency' => 'monthly',
                'schedule_cron' => '0 3 1 * *',
                'timezone' => 'Europe/Madrid',
                'is_active' => true,
                'tags' => ['limpieza', 'logs', 'mensual', 'mantenimiento', 'sistema']
            ],
            [
                'name' => 'Sincronización Horaria con Red Eléctrica',
                'description' => 'Sincroniza los datos de consumo con la red eléctrica nacional cada hora.',
                'rule_type' => 'scheduled',
                'trigger_type' => 'time',
                'trigger_conditions' => [
                    'time' => 'every_hour',
                    'timezone' => 'Europe/Madrid'
                ],
                'action_type' => 'api_call',
                'action_parameters' => [
                    'endpoint' => 'https://api.ree.es/consumo',
                    'method' => 'GET',
                    'headers' => ['Authorization' => 'Bearer {token}'],
                    'retry_on_failure' => true,
                    'max_retries' => 3
                ],
                'priority' => 7,
                'execution_frequency' => 'hourly',
                'schedule_cron' => '0 * * * *',
                'timezone' => 'Europe/Madrid',
                'is_active' => true,
                'tags' => ['sincronizacion', 'red-electrica', 'consumo', 'horario', 'api']
            ]
        ];
        
        foreach ($scheduledRules as $ruleData) {
            $user = $users->random();
            $this->createAutomationRule($ruleData, $user);
        }
    }
    
    private function createEventDrivenRules($users): void
    {
        $this->command->info('🎯 Creando reglas dirigidas por eventos...');
        
        $eventDrivenRules = [
            [
                'name' => 'Alerta de Consumo Excesivo',
                'description' => 'Envía alertas automáticas cuando un miembro supera su límite de consumo energético establecido.',
                'rule_type' => 'event_driven',
                'trigger_type' => 'threshold',
                'trigger_conditions' => [
                    'metric' => 'energy_consumption',
                    'threshold' => 'limit_exceeded',
                    'entity_type' => 'user',
                    'check_frequency' => 'realtime'
                ],
                'action_type' => 'notification',
                'action_parameters' => [
                    'notification_type' => 'alert',
                    'channels' => ['email', 'sms', 'push'],
                    'template' => 'consumption_alert',
                    'priority' => 'high',
                    'escalation' => true
                ],
                'priority' => 9,
                'execution_frequency' => 'custom',
                'is_active' => true,
                'tags' => ['alerta', 'consumo', 'excesivo', 'tiempo-real', 'notificacion']
            ],
            [
                'name' => 'Notificación de Mantenimiento Programado',
                'description' => 'Notifica automáticamente a los miembros cuando se programa mantenimiento en sus instalaciones.',
                'rule_type' => 'event_driven',
                'trigger_type' => 'event',
                'trigger_conditions' => [
                    'event_type' => 'maintenance_scheduled',
                    'entity_type' => 'installation',
                    'notification_timing' => '24h_before'
                ],
                'action_type' => 'email',
                'action_parameters' => [
                    'template' => 'maintenance_notification',
                    'subject' => 'Mantenimiento Programado - {installation_name}',
                    'recipients' => 'installation_owner',
                    'include_calendar_invite' => true,
                    'reminder_hours' => [2, 12, 24]
                ],
                'priority' => 7,
                'execution_frequency' => 'custom',
                'is_active' => true,
                'tags' => ['mantenimiento', 'notificacion', 'programado', 'email', 'calendario']
            ],
            [
                'name' => 'Activación de Generador de Emergencia',
                'description' => 'Activa automáticamente el generador de emergencia cuando se detecta un corte de energía.',
                'rule_type' => 'event_driven',
                'trigger_type' => 'condition',
                'trigger_conditions' => [
                    'condition' => 'power_outage_detected',
                    'duration_threshold' => '30_seconds',
                    'location' => 'cooperative_premises'
                ],
                'action_type' => 'system_command',
                'action_parameters' => [
                    'command' => 'emergency_generator:start',
                    'parameters' => ['fuel_level_check' => true, 'load_balancing' => true],
                    'confirmation_required' => false,
                    'auto_shutdown' => true
                ],
                'priority' => 10,
                'execution_frequency' => 'custom',
                'is_active' => true,
                'tags' => ['emergencia', 'generador', 'corte-energia', 'automatico', 'critico']
            ]
        ];
        
        foreach ($eventDrivenRules as $ruleData) {
            $user = $users->random();
            $this->createAutomationRule($ruleData, $user);
        }
    }
    
    private function createConditionBasedRules($users): void
    {
        $this->command->info('🔍 Creando reglas basadas en condiciones...');
        
        $conditionBasedRules = [
            [
                'name' => 'Optimización de Tarifas Dinámicas',
                'description' => 'Ajusta automáticamente el consumo energético basándose en las tarifas dinámicas de la red eléctrica.',
                'rule_type' => 'condition_based',
                'trigger_type' => 'threshold',
                'trigger_conditions' => [
                    'condition' => 'high_tariff_period',
                    'threshold' => 'tariff > 0.15',
                    'time_window' => 'peak_hours',
                    'check_frequency' => '15_minutes'
                ],
                'action_type' => 'database',
                'action_parameters' => [
                    'operation' => 'update_consumption_schedule',
                    'table' => 'energy_consumption_schedules',
                    'optimization_strategy' => 'load_shifting',
                    'target_reduction' => '20%',
                    'notify_user' => true
                ],
                'priority' => 8,
                'execution_frequency' => 'custom',
                'is_active' => true,
                'tags' => ['optimizacion', 'tarifas', 'dinamicas', 'consumo', 'inteligente']
            ],
            [
                'name' => 'Gestión Inteligente de Baterías',
                'description' => 'Gestiona automáticamente la carga y descarga de baterías basándose en la demanda y el precio de la energía.',
                'rule_type' => 'condition_based',
                'trigger_type' => 'condition',
                'trigger_conditions' => [
                    'condition' => 'battery_optimization_needed',
                    'battery_level' => 'battery_level < 20% OR battery_level > 80%',
                    'energy_price' => 'price < 0.08 OR price > 0.18',
                    'demand_forecast' => 'high_demand_predicted'
                ],
                'action_type' => 'system_command',
                'action_parameters' => [
                    'command' => 'battery:optimize',
                    'parameters' => [
                        'charge_strategy' => 'smart_charging',
                        'discharge_strategy' => 'peak_shaving',
                        'target_efficiency' => '95%'
                    ],
                    'learning_enabled' => true
                ],
                'priority' => 7,
                'execution_frequency' => 'custom',
                'is_active' => true,
                'tags' => ['baterias', 'gestion', 'inteligente', 'optimizacion', 'energia']
            ],
            [
                'name' => 'Análisis de Anomalías en Consumo',
                'description' => 'Detecta y reporta anomalías en el patrón de consumo energético de los miembros.',
                'rule_type' => 'condition_based',
                'trigger_type' => 'pattern',
                'trigger_conditions' => [
                    'condition' => 'consumption_anomaly_detected',
                    'pattern_deviation' => 'deviation > 2_standard_deviations',
                    'time_window' => '24_hours',
                    'confidence_threshold' => '85%'
                ],
                'action_type' => 'report',
                'action_parameters' => [
                    'report_type' => 'anomaly_detection',
                    'recipients' => ['technical_team', 'affected_user'],
                    'include_analysis' => true,
                    'suggested_actions' => true,
                    'escalation_rules' => ['high_deviation', 'multiple_anomalies']
                ],
                'priority' => 6,
                'execution_frequency' => 'custom',
                'is_active' => true,
                'tags' => ['anomalias', 'consumo', 'deteccion', 'analisis', 'reporte']
            ]
        ];
        
        foreach ($conditionBasedRules as $ruleData) {
            $user = $users->random();
            $this->createAutomationRule($ruleData, $user);
        }
    }
    
    private function createManualRules($users): void
    {
        $this->command->info('👤 Creando reglas manuales...');
        
        $manualRules = [
            [
                'name' => 'Generación de Reportes Personalizados',
                'description' => 'Permite a los usuarios generar reportes personalizados de su consumo energético bajo demanda.',
                'rule_type' => 'manual',
                'trigger_type' => 'event',
                'trigger_conditions' => [
                    'event_type' => 'user_request',
                    'trigger_method' => 'button_click',
                    'user_permission' => 'report_generation'
                ],
                'action_type' => 'report',
                'action_parameters' => [
                    'report_type' => 'custom',
                    'parameters' => ['date_range', 'metrics', 'format', 'comparison'],
                    'generation_timeout' => '5_minutes',
                    'notify_on_completion' => true,
                    'storage_duration' => '30_days'
                ],
                'priority' => 5,
                'execution_frequency' => 'custom',
                'is_active' => true,
                'tags' => ['reportes', 'personalizados', 'manual', 'usuario', 'demanda']
            ],
            [
                'name' => 'Configuración de Alertas Personalizadas',
                'description' => 'Permite a los usuarios configurar sus propias alertas de consumo energético.',
                'rule_type' => 'manual',
                'trigger_type' => 'event',
                'trigger_conditions' => [
                    'event_type' => 'user_configuration',
                    'trigger_method' => 'form_submission',
                    'user_permission' => 'alert_configuration'
                ],
                'action_type' => 'database',
                'action_parameters' => [
                    'operation' => 'create_user_alert',
                    'table' => 'user_alert_configurations',
                    'validation_rules' => ['threshold_limits', 'notification_preferences'],
                    'confirmation_required' => true,
                    'test_notification' => true
                ],
                'priority' => 4,
                'execution_frequency' => 'custom',
                'is_active' => true,
                'tags' => ['alertas', 'personalizadas', 'configuracion', 'usuario', 'preferencias']
            ]
        ];
        
        foreach ($manualRules as $ruleData) {
            $user = $users->random();
            $this->createAutomationRule($ruleData, $user);
        }
    }
    
    private function createWebhookRules($users): void
    {
        $this->command->info('🌐 Creando reglas de webhook...');
        
        $webhookRules = [
            [
                'name' => 'Integración con Red Eléctrica Española',
                'description' => 'Recibe actualizaciones en tiempo real de la red eléctrica nacional a través de webhooks.',
                'rule_type' => 'webhook',
                'trigger_type' => 'external',
                'trigger_conditions' => [
                    'webhook_endpoint' => '/webhooks/ree-updates',
                    'authentication' => 'bearer_token',
                    'rate_limit' => '100_requests_per_hour',
                    'validation' => ['signature_verification', 'timestamp_check']
                ],
                'action_type' => 'database',
                'action_parameters' => [
                    'operation' => 'update_grid_status',
                    'table' => 'grid_status_updates',
                    'data_mapping' => [
                        'timestamp' => 'received_at',
                        'grid_frequency' => 'frequency_hz',
                        'demand_mw' => 'demand_megawatts',
                        'renewable_percentage' => 'renewable_energy_percent'
                    ],
                    'notify_on_update' => false
                ],
                'priority' => 8,
                'execution_frequency' => 'custom',
                'webhook_url' => 'https://api.ree.es/webhooks/grid-status',
                'webhook_headers' => [
                    'Content-Type' => 'application/json',
                    'X-REE-Signature' => '{signature}',
                    'User-Agent' => 'OpenEnergyCoop/1.0'
                ],
                'is_active' => true,
                'tags' => ['webhook', 'red-electrica', 'integracion', 'tiempo-real', 'api']
            ],
            [
                'name' => 'Sincronización con Proveedores de Energía',
                'description' => 'Sincroniza datos con proveedores de energía renovable a través de webhooks.',
                'rule_type' => 'webhook',
                'trigger_type' => 'external',
                'trigger_conditions' => [
                    'webhook_endpoint' => '/webhooks/energy-providers',
                    'authentication' => 'api_key',
                    'rate_limit' => '50_requests_per_hour',
                    'validation' => ['api_key_verification', 'ip_whitelist']
                ],
                'action_type' => 'database',
                'action_parameters' => [
                    'operation' => 'sync_provider_data',
                    'table' => 'energy_provider_sync',
                    'data_mapping' => [
                        'provider_id' => 'external_provider_id',
                        'energy_available' => 'available_megawatts',
                        'price_per_mwh' => 'price_euros_mwh',
                        'delivery_time' => 'estimated_delivery_hours'
                    ],
                    'conflict_resolution' => 'latest_wins',
                    'notify_on_conflict' => true
                ],
                'priority' => 7,
                'execution_frequency' => 'custom',
                'webhook_url' => 'https://api.energy-providers.es/webhooks/supply-updates',
                'webhook_headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-Key' => '{api_key}',
                    'User-Agent' => 'OpenEnergyCoop/1.0'
                ],
                'is_active' => true,
                'tags' => ['webhook', 'proveedores', 'sincronizacion', 'energia', 'renovable']
            ]
        ];
        
        foreach ($webhookRules as $ruleData) {
            $user = $users->random();
            $this->createAutomationRule($ruleData, $user);
        }
    }
    
    private function createAutomationRule($ruleData, $user): void
    {
        $baseData = [
            'created_by' => $user ? $user->id : 1,
            'approved_by' => $user ? $user->id : 1,
            'approved_at' => now()->subDays(rand(1, 30)),
            'execution_count' => rand(0, 100),
            'max_executions' => rand(100, 1000),
            'success_count' => rand(0, 95),
            'failure_count' => rand(0, 5),
            'max_retries' => rand(1, 5),
            'retry_delay_minutes' => rand(1, 15),
            'retry_on_failure' => rand(0, 1) == 1,
            'notes' => $this->generateNotes($ruleData['rule_type']),
        ];
        
        // Generar fechas de ejecución realistas
        if ($ruleData['rule_type'] === 'scheduled') {
            $baseData['last_executed_at'] = now()->subHours(rand(1, 24));
            $baseData['next_execution_at'] = $this->calculateNextExecution($ruleData);
        } else {
            $baseData['last_executed_at'] = rand(0, 1) == 1 ? now()->subDays(rand(1, 7)) : null;
            $baseData['next_execution_at'] = null;
        }
        
        // Generar mensajes de error si hay fallos
        if ($baseData['failure_count'] > 0) {
            $baseData['last_error_message'] = $this->generateErrorMessage($ruleData['action_type']);
        }
        
        // Generar emails de notificación si es necesario
        if (in_array($ruleData['action_type'], ['email', 'notification'])) {
            $baseData['notification_emails'] = $this->generateNotificationEmails();
        }
        
        AutomationRule::create(array_merge($ruleData, $baseData));
    }
    
    private function calculateNextExecution($ruleData): ?string
    {
        if (!$ruleData['schedule_cron']) {
            return null;
        }
        
        $now = now();
        
        switch ($ruleData['execution_frequency']) {
            case 'hourly':
                return $now->addHour()->format('Y-m-d H:i:s');
            case 'daily':
                return $now->addDay()->format('Y-m-d H:i:s');
            case 'weekly':
                return $now->addWeek()->format('Y-m-d H:i:s');
            case 'monthly':
                return $now->addMonth()->format('Y-m-d H:i:s');
            default:
                return $now->addHours(rand(1, 24))->format('Y-m-d H:i:s');
        }
    }
    
    private function generateNotes($ruleType): string
    {
        $notes = [
            'scheduled' => 'Regla programada para ejecución automática. Verificar configuración de cron y zona horaria.',
            'event_driven' => 'Regla activada por eventos del sistema. Monitorear triggers y condiciones.',
            'condition_based' => 'Regla basada en condiciones complejas. Revisar lógica de evaluación regularmente.',
            'manual' => 'Regla de activación manual. Requiere intervención del usuario para ejecutarse.',
            'webhook' => 'Regla de webhook externo. Verificar conectividad y autenticación periódicamente.'
        ];
        
        return $notes[$ruleType] ?? 'Regla de automatización del sistema.';
    }
    
    private function generateErrorMessage($actionType): string
    {
        $errors = [
            'email' => 'Error al enviar email: Servidor SMTP no disponible. Reintentando en 5 minutos.',
            'sms' => 'Error al enviar SMS: Proveedor de SMS temporalmente no disponible.',
            'webhook' => 'Error en webhook: Timeout de conexión después de 30 segundos.',
            'database' => 'Error en base de datos: Constraint violation en tabla de destino.',
            'api_call' => 'Error en llamada API: Rate limit excedido. Reintentando en 1 hora.',
            'system_command' => 'Error en comando del sistema: Permisos insuficientes para ejecutar comando.',
            'notification' => 'Error en notificación: Servicio de notificaciones no disponible.',
            'report' => 'Error al generar reporte: Formato de datos inválido en la consulta.'
        ];
        
        return $errors[$actionType] ?? 'Error desconocido en la ejecución de la regla.';
    }
    
    private function generateNotificationEmails(): array
    {
        $emails = [
            'admin@aragon.es',
            'tecnico@aragon.es',
            'soporte@aragon.es',
            'emergencias@aragon.es'
        ];
        
        return array_slice($emails, 0, rand(1, 3));
    }
}
