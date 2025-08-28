<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Organization;
use App\Models\ApiClient;
use App\Models\Collaborator;
use App\Models\FormSubmission;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📋 Creando logs de auditoría para el sistema...');
        
        // Limpiar logs existentes
        AuditLog::query()->delete();
        
        // Obtener usuarios y organizaciones disponibles
        $users = User::all();
        $organizations = Organization::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. No se pueden crear logs de auditoría.');
            return;
        }
        
        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏢 Organizaciones disponibles: {$organizations->count()}");
        
        // Crear logs de auditoría para diferentes tipos de acciones
        $this->createUserActivityLogs($users);
        $this->createSystemActivityLogs($users);
        $this->createApiActivityLogs($users);
        $this->createDataModificationLogs($users, $organizations);
        $this->createSecurityEventLogs($users);
        $this->createBulkOperationLogs($users);
        
        $this->command->info('✅ AuditLogSeeder completado. Se crearon ' . AuditLog::count() . ' logs de auditoría.');
    }
    
    private function createUserActivityLogs($users): void
    {
        $this->command->info('👤 Creando logs de actividad de usuarios...');
        
        foreach ($users as $user) {
            // Login exitoso
            $this->createAuditLog([
                'user_id' => $user->id,
                'actor_type' => 'user',
                'actor_identifier' => $user->id,
                'action' => 'login',
                'description' => "Usuario {$user->name} inició sesión exitosamente",
                'ip_address' => $this->generateRandomIP(),
                'user_agent' => $this->generateRandomUserAgent(),
                'url' => '/login',
                'method' => 'POST',
                'response_code' => 200,
                'session_id' => Str::random(40),
                'request_id' => Str::uuid(),
                'metadata' => [
                    'login_method' => 'credentials',
                    'browser' => $this->getRandomBrowser(),
                    'platform' => $this->getRandomPlatform(),
                    'location' => $this->getRandomLocation()
                ]
            ], $user);
            
            // Cambio de contraseña
            if (rand(1, 10) <= 3) { // 30% probabilidad
                $this->createAuditLog([
                    'user_id' => $user->id,
                    'actor_type' => 'user',
                    'actor_identifier' => $user->id,
                    'action' => 'password_change',
                    'description' => "Usuario {$user->name} cambió su contraseña",
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'url' => '/profile/change-password',
                    'method' => 'POST',
                    'response_code' => 200,
                    'session_id' => Str::random(40),
                    'request_id' => Str::uuid(),
                    'metadata' => [
                        'change_reason' => 'security_update',
                        'password_strength' => 'strong',
                        'two_factor_enabled' => rand(0, 1)
                    ]
                ], $user);
            }
            
            // Logout
            if (rand(1, 10) <= 7) { // 70% probabilidad
                $this->createAuditLog([
                    'user_id' => $user->id,
                    'actor_type' => 'user',
                    'actor_identifier' => $user->id,
                    'action' => 'logout',
                    'description' => "Usuario {$user->name} cerró sesión",
                    'ip_address' => $this->generateRandomIP(),
                    'user_agent' => $this->generateRandomUserAgent(),
                    'url' => '/logout',
                    'method' => 'POST',
                    'response_code' => 200,
                    'session_id' => Str::random(40),
                    'request_id' => Str::uuid(),
                    'metadata' => [
                        'session_duration' => rand(15, 480), // minutos
                        'logout_reason' => 'user_initiated'
                    ]
                ], $user);
            }
        }
        
        $this->command->info("👤 Logs de actividad de usuarios creados");
    }
    
    private function createSystemActivityLogs($users): void
    {
        $this->command->info('⚙️ Creando logs de actividad del sistema...');
        
        $systemActions = [
            'database_backup' => 'Respaldo de base de datos completado',
            'cache_clear' => 'Caché del sistema limpiado',
            'maintenance_mode' => 'Modo mantenimiento activado',
            'system_update' => 'Actualización del sistema aplicada',
            'log_rotation' => 'Rotación de logs completada',
            'security_scan' => 'Escaneo de seguridad ejecutado',
            'performance_monitoring' => 'Monitoreo de rendimiento ejecutado',
            'backup_verification' => 'Verificación de respaldos completada'
        ];
        
        foreach ($systemActions as $action => $description) {
            $adminUser = $users->random();
            
            $this->createAuditLog([
                'user_id' => $adminUser->id,
                'actor_type' => 'system',
                'actor_identifier' => 'system_cron',
                'action' => $action,
                'description' => $description,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'System Cron Job',
                'url' => '/system/' . str_replace('_', '-', $action),
                'method' => 'POST',
                'response_code' => 200,
                'session_id' => null,
                'request_id' => Str::uuid(),
                'metadata' => [
                    'execution_time' => rand(1, 300), // segundos
                    'memory_usage' => rand(50, 500), // MB
                    'cpu_usage' => rand(5, 95), // porcentaje
                    'status' => 'completed'
                ]
            ], $adminUser);
        }
        
        $this->command->info("⚙️ Logs de actividad del sistema creados");
    }
    
    private function createApiActivityLogs($users): void
    {
        $this->command->info('🔌 Creando logs de actividad de API...');
        
        $apiActions = [
            'user_creation' => 'Usuario creado vía API',
            'data_export' => 'Datos exportados vía API',
            'bulk_update' => 'Actualización masiva vía API',
            'webhook_delivery' => 'Webhook entregado exitosamente',
            'rate_limit_exceeded' => 'Límite de tasa excedido',
            'authentication_failed' => 'Autenticación de API fallida',
            'permission_denied' => 'Permiso denegado en API',
            'data_validation_error' => 'Error de validación de datos'
        ];
        
        foreach ($apiActions as $action => $description) {
            $apiUser = $users->random();
            
            $this->createAuditLog([
                'user_id' => $apiUser->id,
                'actor_type' => 'api',
                'actor_identifier' => 'api_client_' . rand(1, 100),
                'action' => $action,
                'description' => $description,
                'ip_address' => $this->generateRandomIP(),
                'user_agent' => 'API Client v2.1',
                'url' => '/api/v1/' . str_replace('_', '/', $action),
                'method' => $this->getRandomHttpMethod(),
                'response_code' => $this->getRandomResponseCode(),
                'session_id' => null,
                'request_id' => Str::uuid(),
                'metadata' => [
                    'api_version' => 'v1',
                    'endpoint' => str_replace('_', '/', $action),
                    'response_time' => rand(50, 2000), // ms
                    'payload_size' => rand(100, 50000), // bytes
                    'rate_limit_remaining' => rand(0, 1000)
                ]
            ], $apiUser);
        }
        
        $this->command->info("🔌 Logs de actividad de API creados");
    }
    
    private function createDataModificationLogs($users, $organizations): void
    {
        $this->command->info('📝 Creando logs de modificación de datos...');
        
        // Logs de creación de organizaciones
        foreach ($organizations as $organization) {
            $creator = $users->random();
            
            $this->createAuditLog([
                'user_id' => $creator->id,
                'actor_type' => 'user',
                'actor_identifier' => $creator->id,
                'action' => 'create',
                'description' => "Organización '{$organization->name}' creada",
                'auditable_type' => 'App\Models\Organization',
                'auditable_id' => $organization->id,
                'old_values' => null,
                'new_values' => [
                    'name' => $organization->name,
                    'domain' => $organization->domain,
                    'contact_email' => $organization->contact_email
                ],
                'ip_address' => $this->generateRandomIP(),
                'user_agent' => $this->generateRandomUserAgent(),
                'url' => '/admin/organizations',
                'method' => 'POST',
                'response_code' => 201,
                'session_id' => Str::random(40),
                'request_id' => Str::uuid(),
                'metadata' => [
                    'model_type' => 'Organization',
                    'operation' => 'create',
                    'fields_modified' => ['name', 'domain', 'contact_email']
                ]
            ], $creator);
        }
        
        // Logs de actualización de usuarios
        foreach ($users->take(10) as $user) {
            $updater = $users->random();
            
            $this->createAuditLog([
                'user_id' => $updater->id,
                'actor_type' => 'user',
                'actor_identifier' => $updater->id,
                'action' => 'update',
                'description' => "Usuario '{$user->name}' actualizado",
                'auditable_type' => 'App\Models\User',
                'auditable_id' => $user->id,
                'old_values' => [
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'new_values' => [
                    'name' => $user->name . ' (actualizado)',
                    'email' => $user->email
                ],
                'ip_address' => $this->generateRandomIP(),
                'user_agent' => $this->generateRandomUserAgent(),
                'url' => '/admin/users/' . $user->id,
                'method' => 'PUT',
                'response_code' => 200,
                'session_id' => Str::random(40),
                'request_id' => Str::uuid(),
                'metadata' => [
                    'model_type' => 'User',
                    'operation' => 'update',
                    'fields_modified' => ['name']
                ]
            ], $updater);
        }
        
        $this->command->info("📝 Logs de modificación de datos creados");
    }
    
    private function createSecurityEventLogs($users): void
    {
        $this->command->info('🔒 Creando logs de eventos de seguridad...');
        
        $securityEvents = [
            'login_failed' => 'Intento de inicio de sesión fallido',
            'permission_denied' => 'Acceso denegado a recurso',
            'suspicious_activity' => 'Actividad sospechosa detectada',
            'file_access_denied' => 'Acceso a archivo denegado',
            'admin_action' => 'Acción administrativa ejecutada',
            'data_export' => 'Exportación de datos realizada',
            'bulk_delete' => 'Eliminación masiva de registros',
            'system_config_change' => 'Cambio en configuración del sistema'
        ];
        
        foreach ($securityEvents as $action => $description) {
            $user = $users->random();
            
            $this->createAuditLog([
                'user_id' => $user->id,
                'actor_type' => 'user',
                'actor_identifier' => $user->id,
                'action' => $action,
                'description' => $description,
                'ip_address' => $this->generateRandomIP(),
                'user_agent' => $this->generateRandomUserAgent(),
                'url' => '/admin/security/' . str_replace('_', '-', $action),
                'method' => 'POST',
                'response_code' => $action === 'login_failed' ? 401 : 200,
                'session_id' => Str::random(40),
                'request_id' => Str::uuid(),
                'metadata' => [
                    'security_level' => $this->getSecurityLevel($action),
                    'risk_score' => $this->getRiskScore($action),
                    'requires_review' => in_array($action, ['suspicious_activity', 'admin_action']),
                    'location' => $this->getRandomLocation()
                ]
            ], $user);
        }
        
        $this->command->info("🔒 Logs de eventos de seguridad creados");
    }
    
    private function createBulkOperationLogs($users): void
    {
        $this->command->info('📦 Creando logs de operaciones masivas...');
        
        $bulkOperations = [
            'bulk_user_import' => 'Importación masiva de usuarios',
            'bulk_data_export' => 'Exportación masiva de datos',
            'bulk_status_update' => 'Actualización masiva de estados',
            'bulk_permission_grant' => 'Concesión masiva de permisos',
            'bulk_notification_send' => 'Envío masivo de notificaciones',
            'bulk_backup_creation' => 'Creación masiva de respaldos',
            'bulk_cleanup' => 'Limpieza masiva de datos',
            'bulk_sync' => 'Sincronización masiva de datos'
        ];
        
        foreach ($bulkOperations as $action => $description) {
            $user = $users->random();
            
            $this->createAuditLog([
                'user_id' => $user->id,
                'actor_type' => 'user',
                'actor_identifier' => $user->id,
                'action' => 'bulk_action',
                'description' => $description,
                'ip_address' => $this->generateRandomIP(),
                'user_agent' => $this->generateRandomUserAgent(),
                'url' => '/admin/bulk/' . str_replace('_', '-', $action),
                'method' => 'POST',
                'response_code' => 200,
                'session_id' => Str::random(40),
                'request_id' => Str::uuid(),
                'metadata' => [
                    'operation_type' => $action,
                    'records_processed' => rand(100, 10000),
                    'execution_time' => rand(5, 300), // segundos
                    'success_rate' => rand(95, 100), // porcentaje
                    'errors_count' => rand(0, 50)
                ]
            ], $user);
        }
        
        $this->command->info("📦 Logs de operaciones masivas creados");
    }
    
    private function createAuditLog(array $data, $user): void
    {
        // Ajustar fechas para que sean realistas
        $data['created_at'] = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
        $data['updated_at'] = $data['created_at'];
        
        AuditLog::create($data);
    }
    
    private function generateRandomIP(): string
    {
        $ips = [
            '192.168.1.' . rand(1, 254),
            '10.0.0.' . rand(1, 254),
            '172.16.' . rand(0, 31) . '.' . rand(1, 254),
            '203.0.113.' . rand(1, 254),
            '198.51.100.' . rand(1, 254)
        ];
        
        return $ips[array_rand($ips)];
    }
    
    private function generateRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15'
        ];
        
        return $userAgents[array_rand($userAgents)];
    }
    
    private function getRandomBrowser(): string
    {
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
        return $browsers[array_rand($browsers)];
    }
    
    private function getRandomPlatform(): string
    {
        $platforms = ['Windows', 'macOS', 'Linux', 'iOS', 'Android'];
        return $platforms[array_rand($platforms)];
    }
    
    private function getRandomLocation(): string
    {
        $locations = ['Madrid, España', 'Barcelona, España', 'Valencia, España', 'Sevilla, España', 'Zaragoza, España'];
        return $locations[array_rand($locations)];
    }
    
    private function getRandomHttpMethod(): string
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        return $methods[array_rand($methods)];
    }
    
    private function getRandomResponseCode(): int
    {
        $codes = [200, 201, 400, 401, 403, 404, 422, 500];
        return $codes[array_rand($codes)];
    }
    
    private function getSecurityLevel(string $action): string
    {
        $levels = [
            'login_failed' => 'high',
            'permission_denied' => 'medium',
            'suspicious_activity' => 'critical',
            'file_access_denied' => 'medium',
            'admin_action' => 'high',
            'data_export' => 'medium',
            'bulk_delete' => 'high',
            'system_config_change' => 'critical'
        ];
        
        return $levels[$action] ?? 'low';
    }
    
    private function getRiskScore(string $action): int
    {
        $scores = [
            'login_failed' => 75,
            'permission_denied' => 50,
            'suspicious_activity' => 90,
            'file_access_denied' => 60,
            'admin_action' => 80,
            'data_export' => 40,
            'bulk_delete' => 85,
            'system_config_change' => 95
        ];
        
        return $scores[$action] ?? 25;
    }
}
