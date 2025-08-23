<?php

namespace Database\Seeders;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los usuarios existentes
        $users = User::all();

        foreach ($users as $user) {
            // Crear configuraciones por defecto para cada usuario
            $this->createDefaultSettings($user->id);
        }
    }

    private function createDefaultSettings(int $userId): void
    {
        $channels = NotificationSetting::getChannels();
        $types = NotificationSetting::getNotificationTypes();

        foreach ($channels as $channel) {
            foreach ($types as $type) {
                NotificationSetting::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'channel' => $channel,
                        'notification_type' => $type,
                    ],
                    [
                        'enabled' => $this->getDefaultEnabledState($channel, $type),
                    ]
                );
            }
        }
    }

    private function getDefaultEnabledState(string $channel, string $type): bool
    {
        // Configuraciones por defecto más inteligentes
        return match(true) {
            // Notificaciones in-app siempre habilitadas por defecto
            $channel === NotificationSetting::CHANNEL_IN_APP => true,
            
            // Email habilitado para la mayoría de tipos
            $channel === NotificationSetting::CHANNEL_EMAIL => true,
            
            // Push habilitado para eventos y mensajes
            $channel === NotificationSetting::CHANNEL_PUSH && in_array($type, [
                NotificationSetting::TYPE_EVENT,
                NotificationSetting::TYPE_MESSAGE
            ]) => true,
            
            // SMS solo para alertas críticas (wallet)
            $channel === NotificationSetting::CHANNEL_SMS && $type === NotificationSetting::TYPE_WALLET => true,
            
            // Por defecto, deshabilitar
            default => false,
        };
    }
}
