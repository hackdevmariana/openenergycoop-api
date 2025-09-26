<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserSettings;
use App\Models\User;
use Carbon\Carbon;

class UserSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(10)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando UserSettingsSeeder.');
            return;
        }

        $settings = [];

        foreach ($users as $index => $user) {
            $settings[] = [
                'user_id' => $user->id,
                'language' => fake()->randomElement(['es', 'en', 'fr', 'de', 'it']),
                'timezone' => fake()->randomElement(['Europe/Madrid', 'Europe/London', 'America/New_York', 'Asia/Tokyo', 'Australia/Sydney']),
                'theme' => fake()->randomElement(['light', 'dark', 'auto']),
                'notifications_enabled' => fake()->boolean(80),
                'email_notifications' => fake()->boolean(70),
                'push_notifications' => fake()->boolean(60),
                'sms_notifications' => fake()->boolean(30),
                'marketing_emails' => fake()->boolean(40),
                'newsletter_subscription' => fake()->boolean(50),
                'privacy_level' => fake()->randomElement(['public', 'private', 'friends', 'organization']),
                'profile_visibility' => fake()->randomElement(['public', 'private', 'friends', 'organization']),
                'show_achievements' => fake()->boolean(75),
                'show_statistics' => fake()->boolean(80),
                'show_activity' => fake()->boolean(65),
                'date_format' => fake()->randomElement(['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y']),
                'time_format' => fake()->randomElement(['24h', '12h']),
                'currency' => fake()->randomElement(['EUR', 'USD', 'GBP', 'JPY', 'AUD']),
                'measurement_unit' => fake()->randomElement(['metric', 'imperial']),
                'energy_unit' => fake()->randomElement(['kWh', 'MWh', 'GWh']),
                'custom_settings' => json_encode([
                    'dashboard_widgets' => fake()->randomElements(['energy_production', 'consumption', 'savings', 'rankings'], rand(2, 4)),
                    'default_view' => fake()->randomElement(['grid', 'list', 'chart']),
                    'auto_refresh' => fake()->boolean(60),
                    'notifications_sound' => fake()->boolean(40),
                    'compact_mode' => fake()->boolean(30),
                ]),
            ];
        }

        foreach ($settings as $setting) {
            UserSettings::create($setting);
        }

        $this->command->info('✅ UserSettingsSeeder ejecutado correctamente');
        $this->command->info('📊 Configuraciones de usuario creadas: ' . count($settings));
        $this->command->info('👥 Usuarios con configuraciones: ' . $users->count());
        $this->command->info('🌍 Idiomas configurados: ' . collect($settings)->pluck('language')->unique()->count());
        $this->command->info('🌐 Zonas horarias: ' . collect($settings)->pluck('timezone')->unique()->count());
        $this->command->info('🎨 Temas: ' . collect($settings)->pluck('theme')->unique()->count());
        $this->command->info('🔔 Notificaciones habilitadas: ' . collect($settings)->where('notifications_enabled', true)->count());
        $this->command->info('📧 Notificaciones por email: ' . collect($settings)->where('email_notifications', true)->count());
        $this->command->info('📱 Notificaciones push: ' . collect($settings)->where('push_notifications', true)->count());
        $this->command->info('🔒 Niveles de privacidad: ' . collect($settings)->pluck('privacy_level')->unique()->count());
        $this->command->info('💰 Monedas: ' . collect($settings)->pluck('currency')->unique()->count());
    }
}