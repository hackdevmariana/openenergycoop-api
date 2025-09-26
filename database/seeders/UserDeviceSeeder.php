<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserDevice;
use App\Models\User;
use Carbon\Carbon;

class UserDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(10)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando UserDeviceSeeder.');
            return;
        }

        $devices = [];

        // Crear dispositivos para cada usuario (1-3 dispositivos por usuario)
        foreach ($users as $user) {
            $deviceCount = fake()->numberBetween(1, 3);
            
            for ($i = 0; $i < $deviceCount; $i++) {
                $deviceType = fake()->randomElement(['web', 'mobile', 'tablet', 'desktop']);
                $platform = fake()->randomElement(['ios', 'android', 'windows', 'macos', 'linux', 'web']);
                
                $isCurrent = $i === 0; // El primer dispositivo es el actual
                $lastSeenAt = Carbon::now()->subHours(rand(1, 72));
                $revokedAt = null;
                
                // Algunos dispositivos pueden estar revocados
                if (fake()->boolean(10)) {
                    $revokedAt = Carbon::now()->subDays(rand(1, 30));
                    $isCurrent = false;
                }

                $devices[] = [
                    'user_id' => $user->id,
                    'device_name' => $this->generateDeviceName($deviceType, $platform),
                    'device_type' => $deviceType,
                    'platform' => $platform,
                    'last_seen_at' => $lastSeenAt,
                    'push_token' => fake()->optional(0.8)->sha256(),
                    'user_agent' => $this->generateUserAgent($platform),
                    'ip_address' => fake()->ipv4(),
                    'is_current' => $isCurrent,
                    'revoked_at' => $revokedAt,
                ];
            }
        }

        foreach ($devices as $device) {
            UserDevice::create($device);
        }

        $this->command->info('✅ UserDeviceSeeder ejecutado correctamente');
        $this->command->info('📊 Dispositivos de usuario creados: ' . count($devices));
        $this->command->info('👥 Usuarios con dispositivos: ' . $users->count());
        $this->command->info('📱 Dispositivos móviles: ' . collect($devices)->where('device_type', 'mobile')->count());
        $this->command->info('💻 Dispositivos de escritorio: ' . collect($devices)->where('device_type', 'desktop')->count());
        $this->command->info('🌐 Dispositivos web: ' . collect($devices)->where('device_type', 'web')->count());
        $this->command->info('📟 Tablets: ' . collect($devices)->where('device_type', 'tablet')->count());
        $this->command->info('🔧 Plataformas: ' . collect($devices)->pluck('platform')->unique()->count());
        $this->command->info('✅ Dispositivos actuales: ' . collect($devices)->where('is_current', true)->count());
        $this->command->info('❌ Dispositivos revocados: ' . collect($devices)->whereNotNull('revoked_at')->count());
        $this->command->info('🔑 Tokens push: ' . collect($devices)->whereNotNull('push_token')->count());
    }

    private function generateDeviceName(string $deviceType, string $platform): string
    {
        $names = [
            'mobile' => ['iPhone 14', 'Samsung Galaxy S23', 'Google Pixel 7', 'OnePlus 11', 'Xiaomi 13'],
            'tablet' => ['iPad Pro', 'Samsung Galaxy Tab', 'Surface Pro', 'iPad Air', 'Lenovo Tab'],
            'desktop' => ['Mac Studio', 'Dell OptiPlex', 'HP Pavilion', 'Custom PC', 'iMac', 'MacBook Pro', 'Dell XPS', 'HP Spectre', 'Lenovo ThinkPad', 'ASUS ZenBook'],
            'web' => ['Chrome Browser', 'Firefox Browser', 'Safari Browser', 'Edge Browser', 'Opera Browser'],
        ];

        $platformNames = [
            'ios' => ['iOS Device', 'iPhone', 'iPad'],
            'android' => ['Android Device', 'Samsung Device', 'Google Device'],
            'windows' => ['Windows PC', 'Surface Device', 'Dell PC'],
            'macos' => ['Mac', 'MacBook', 'iMac'],
            'linux' => ['Linux PC', 'Ubuntu Device', 'Fedora PC'],
            'web' => ['Web Browser', 'Chrome', 'Firefox', 'Safari'],
        ];

        if (isset($names[$deviceType])) {
            return fake()->randomElement($names[$deviceType]);
        }

        if (isset($platformNames[$platform])) {
            return fake()->randomElement($platformNames[$platform]);
        }

        return ucfirst($deviceType) . ' Device';
    }

    private function generateUserAgent(string $platform): string
    {
        $userAgents = [
            'ios' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
                'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
            ],
            'android' => [
                'Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
                'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
            ],
            'windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0',
            ],
            'macos' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Safari/605.1.15',
            ],
            'linux' => [
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
                'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/112.0',
            ],
            'web' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
            ],
        ];

        if (isset($userAgents[$platform])) {
            return fake()->randomElement($userAgents[$platform]);
        }

        return 'Mozilla/5.0 (compatible; Web Browser)';
    }
}