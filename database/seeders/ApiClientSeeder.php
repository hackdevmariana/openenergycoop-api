<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApiClient;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiClientSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔑 Creando clientes API para la cooperativa energética...');
        
        // Limpiar clientes existentes
        ApiClient::query()->delete();
        
        // Obtener organizaciones disponibles
        $organizations = Organization::all();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No hay organizaciones disponibles. No se pueden crear clientes API.');
            return;
        }
        
        $this->command->info("🏢 Organizaciones disponibles: {$organizations->count()}");
        
        // Crear clientes API para cada organización
        foreach ($organizations as $organization) {
            $this->createApiClientsForOrganization($organization);
        }
        
        // Crear algunos clientes API globales
        $this->createGlobalApiClients();
        
        $this->command->info('✅ ApiClientSeeder completado. Se crearon ' . ApiClient::count() . ' clientes API.');
    }
    
    private function createApiClientsForOrganization(Organization $organization): void
    {
        $this->command->info("🏢 Creando clientes API para la organización: {$organization->name}");
        
        // Cliente API principal de la organización
        $this->createMainApiClient($organization);
        
        // Cliente API para integraciones de terceros
        $this->createThirdPartyApiClient($organization);
        
        // Cliente API para dispositivos IoT
        $this->createIoTApiClient($organization);
        
        // Cliente API para aplicaciones móviles
        $this->createMobileApiClient($organization);
        
        // Cliente API para webhooks
        $this->createWebhookApiClient($organization);
    }
    
    private function createMainApiClient(Organization $organization): void
    {
        $scopes = ['read', 'write', 'organization', 'metrics'];
        $rateLimits = [
            'requests_per_minute' => 1000,
            'requests_per_hour' => 50000,
            'requests_per_day' => 1000000
        ];
        
        ApiClient::create([
            'organization_id' => $organization->id,
            'name' => "API Principal - {$organization->name}",
            'token' => $this->generateSecureToken(),
            'scopes' => $scopes,
            'status' => 'active',
            'allowed_ips' => ['0.0.0.0/0'], // Permitir desde cualquier IP
            'callback_url' => "https://{$organization->domain}/api/callback",
            'expires_at' => Carbon::now()->addYears(2),
            'description' => 'Cliente API principal para operaciones generales de la organización',
            'rate_limits' => $rateLimits,
            'webhook_config' => [
                'enabled' => true,
                'retry_attempts' => 3,
                'timeout' => 30
            ],
            'permissions' => [
                'user_management' => true,
                'energy_data' => true,
                'billing' => true,
                'reports' => true
            ],
            'version' => '2.0',
            'metadata' => [
                'created_by' => 'system',
                'environment' => 'production',
                'priority' => 'high'
            ]
        ]);
        
        $this->command->info("🔑 Cliente API principal creado para {$organization->name}");
    }
    
    private function createThirdPartyApiClient(Organization $organization): void
    {
        $scopes = ['read', 'metrics'];
        $rateLimits = [
            'requests_per_minute' => 100,
            'requests_per_hour' => 5000,
            'requests_per_day' => 100000
        ];
        
        ApiClient::create([
            'organization_id' => $organization->id,
            'name' => "Integración Terceros - {$organization->name}",
            'token' => $this->generateSecureToken(),
            'scopes' => $scopes,
            'status' => 'active',
            'allowed_ips' => ['192.168.1.0/24', '10.0.0.0/8'],
            'callback_url' => null,
            'expires_at' => Carbon::now()->addYear(),
            'description' => 'Cliente API para integraciones con servicios de terceros',
            'rate_limits' => $rateLimits,
            'webhook_config' => [
                'enabled' => false
            ],
            'permissions' => [
                'energy_data' => true,
                'reports' => true,
                'user_management' => false,
                'billing' => false
            ],
            'version' => '1.5',
            'metadata' => [
                'created_by' => 'system',
                'environment' => 'production',
                'priority' => 'medium'
            ]
        ]);
        
        $this->command->info("🔗 Cliente API de terceros creado para {$organization->name}");
    }
    
    private function createIoTApiClient(Organization $organization): void
    {
        $scopes = ['write', 'device', 'metrics'];
        $rateLimits = [
            'requests_per_minute' => 500,
            'requests_per_hour' => 25000,
            'requests_per_day' => 500000
        ];
        
        ApiClient::create([
            'organization_id' => $organization->id,
            'name' => "IoT Devices - {$organization->name}",
            'token' => $this->generateSecureToken(),
            'scopes' => $scopes,
            'status' => 'active',
            'allowed_ips' => ['0.0.0.0/0'], // Dispositivos IoT pueden estar en cualquier IP
            'callback_url' => null,
            'expires_at' => Carbon::now()->addYears(5), // Larga duración para dispositivos
            'description' => 'Cliente API para dispositivos IoT y sensores de energía',
            'rate_limits' => $rateLimits,
            'webhook_config' => [
                'enabled' => true,
                'retry_attempts' => 5,
                'timeout' => 10
            ],
            'permissions' => [
                'device_management' => true,
                'energy_data' => true,
                'metrics' => true,
                'user_management' => false
            ],
            'version' => '1.0',
            'metadata' => [
                'created_by' => 'system',
                'environment' => 'production',
                'priority' => 'high',
                'device_type' => 'iot'
            ]
        ]);
        
        $this->command->info("📱 Cliente API IoT creado para {$organization->name}");
    }
    
    private function createMobileApiClient(Organization $organization): void
    {
        $scopes = ['read', 'write', 'user', 'organization'];
        $rateLimits = [
            'requests_per_minute' => 200,
            'requests_per_hour' => 10000,
            'requests_per_day' => 200000
        ];
        
        ApiClient::create([
            'organization_id' => $organization->id,
            'name' => "Mobile App - {$organization->name}",
            'token' => $this->generateSecureToken(),
            'scopes' => $scopes,
            'status' => 'active',
            'allowed_ips' => ['0.0.0.0/0'], // Apps móviles pueden estar en cualquier IP
            'callback_url' => "https://{$organization->domain}/mobile/callback",
            'expires_at' => Carbon::now()->addYears(3),
            'description' => 'Cliente API para aplicaciones móviles de la organización',
            'rate_limits' => $rateLimits,
            'webhook_config' => [
                'enabled' => true,
                'retry_attempts' => 3,
                'timeout' => 15
            ],
            'permissions' => [
                'user_management' => true,
                'energy_data' => true,
                'billing' => true,
                'reports' => true,
                'notifications' => true
            ],
            'version' => '2.1',
            'metadata' => [
                'created_by' => 'system',
                'environment' => 'production',
                'priority' => 'high',
                'platform' => 'mobile'
            ]
        ]);
        
        $this->command->info("📱 Cliente API móvil creado para {$organization->name}");
    }
    
    private function createWebhookApiClient(Organization $organization): void
    {
        $scopes = ['read', 'webhook'];
        $rateLimits = [
            'requests_per_minute' => 50,
            'requests_per_hour' => 2000,
            'requests_per_day' => 50000
        ];
        
        ApiClient::create([
            'organization_id' => $organization->id,
            'name' => "Webhooks - {$organization->name}",
            'token' => $this->generateSecureToken(),
            'scopes' => $scopes,
            'status' => 'active',
            'allowed_ips' => ['0.0.0.0/0'],
            'callback_url' => "https://{$organization->domain}/webhooks/receive",
            'expires_at' => Carbon::now()->addYears(4),
            'description' => 'Cliente API especializado en webhooks y notificaciones',
            'rate_limits' => $rateLimits,
            'webhook_config' => [
                'enabled' => true,
                'retry_attempts' => 10,
                'timeout' => 60,
                'signature_verification' => true
            ],
            'permissions' => [
                'webhook_management' => true,
                'notifications' => true,
                'energy_data' => false,
                'user_management' => false
            ],
            'version' => '1.2',
            'metadata' => [
                'created_by' => 'system',
                'environment' => 'production',
                'priority' => 'medium',
                'purpose' => 'webhooks'
            ]
        ]);
        
        $this->command->info("🔔 Cliente API de webhooks creado para {$organization->name}");
    }
    
    private function createGlobalApiClients(): void
    {
        $this->command->info("🌍 Creando clientes API globales...");
        
        // Obtener la primera organización para asignar los clientes globales
        $firstOrganization = Organization::first();
        
        if (!$firstOrganization) {
            $this->command->warn('⚠️ No hay organizaciones disponibles para asignar clientes globales.');
            return;
        }
        
        // Cliente API para administración del sistema
        ApiClient::create([
            'organization_id' => $firstOrganization->id, // Asignar a la primera organización
            'name' => 'Sistema de Administración Global',
            'token' => $this->generateSecureToken(),
            'scopes' => ['admin', 'read', 'write', 'delete'],
            'status' => 'active',
            'allowed_ips' => ['127.0.0.1', '::1', '192.168.1.0/24'],
            'callback_url' => null,
            'expires_at' => Carbon::now()->addYears(10), // Muy larga duración para sistema
            'description' => 'Cliente API para administración global del sistema',
            'rate_limits' => [
                'requests_per_minute' => 10000,
                'requests_per_hour' => 500000,
                'requests_per_day' => 10000000
            ],
            'webhook_config' => [
                'enabled' => true,
                'retry_attempts' => 5,
                'timeout' => 30
            ],
            'permissions' => [
                'system_admin' => true,
                'user_management' => true,
                'organization_management' => true,
                'all_data' => true
            ],
            'version' => '3.0',
            'metadata' => [
                'created_by' => 'system',
                'environment' => 'production',
                'priority' => 'critical',
                'scope' => 'global'
            ]
        ]);
        
        // Cliente API para monitoreo y métricas
        ApiClient::create([
            'organization_id' => $firstOrganization->id, // Asignar a la primera organización
            'name' => 'Sistema de Monitoreo y Métricas',
            'token' => $this->generateSecureToken(),
            'scopes' => ['read', 'metrics', 'admin'],
            'status' => 'active',
            'allowed_ips' => ['127.0.0.1', '::1', '10.0.0.0/8'],
            'callback_url' => null,
            'expires_at' => Carbon::now()->addYears(8),
            'description' => 'Cliente API para monitoreo del sistema y recopilación de métricas',
            'rate_limits' => [
                'requests_per_minute' => 5000,
                'requests_per_hour' => 200000,
                'requests_per_day' => 5000000
            ],
            'webhook_config' => [
                'enabled' => true,
                'retry_attempts' => 3,
                'timeout' => 20
            ],
            'permissions' => [
                'system_metrics' => true,
                'performance_data' => true,
                'health_checks' => true,
                'alerts' => true
            ],
            'version' => '2.5',
            'metadata' => [
                'created_by' => 'system',
                'environment' => 'production',
                'priority' => 'high',
                'scope' => 'global'
            ]
        ]);
        
        $this->command->info("🌍 Clientes API globales creados y asignados a {$firstOrganization->name}");
    }
    
    private function generateSecureToken(): string
    {
        return Str::random(64);
    }
}
