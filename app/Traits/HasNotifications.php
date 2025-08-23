<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

trait HasNotifications
{
    /**
     * Enviar notificación a un usuario
     */
    public function sendNotification(
        User $user,
        string $title,
        string $message,
        string $type = Notification::TYPE_INFO,
        array $channels = null
    ): ?Notification {
        try {
            // Crear la notificación
            $notification = Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
            ]);

            // Si no se especifican canales, usar los habilitados por defecto
            if ($channels === null) {
                $channels = $this->getDefaultChannelsForType($type);
            }

            // Enviar por los canales especificados
            foreach ($channels as $channel) {
                if ($this->isChannelEnabled($user->id, $channel, $this->getNotificationTypeFromTitle($title))) {
                    $this->sendToChannel($notification, $channel);
                }
            }

            // Marcar como entregada
            $notification->markAsDelivered();

            Log::info('Notification sent', [
                'user_id' => $user->id,
                'title' => $title,
                'type' => $type,
                'channels' => $channels,
            ]);

            return $notification;

        } catch (\Exception $e) {
            Log::error('Error sending notification', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Enviar notificación a múltiples usuarios
     */
    public function sendNotificationToMany(
        array $users,
        string $title,
        string $message,
        string $type = Notification::TYPE_INFO,
        array $channels = null
    ): array {
        $notifications = [];

        foreach ($users as $user) {
            $notification = $this->sendNotification($user, $title, $message, $type, $channels);
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    /**
     * Enviar notificación de wallet
     */
    public function sendWalletNotification(
        User $user,
        string $title,
        string $message,
        string $type = Notification::TYPE_INFO
    ): ?Notification {
        $channels = ['in_app', 'email'];
        
        // Agregar SMS para alertas críticas
        if (in_array($type, [Notification::TYPE_ERROR, Notification::TYPE_ALERT])) {
            $channels[] = 'sms';
        }

        return $this->sendNotification($user, $title, $message, $type, $channels);
    }

    /**
     * Enviar notificación de evento
     */
    public function sendEventNotification(
        User $user,
        string $title,
        string $message,
        string $type = Notification::TYPE_INFO
    ): ?Notification {
        $channels = ['in_app', 'email', 'push'];
        
        // Agregar SMS para eventos críticos
        if (in_array($type, [Notification::TYPE_ERROR, Notification::TYPE_ALERT])) {
            $channels[] = 'sms';
        }

        return $this->sendNotification($user, $title, $message, $type, $channels);
    }

    /**
     * Enviar notificación de mensaje
     */
    public function sendMessageNotification(
        User $user,
        string $title,
        string $message,
        string $type = Notification::TYPE_INFO
    ): ?Notification {
        $channels = ['in_app', 'email', 'push'];
        
        return $this->sendNotification($user, $title, $message, $type, $channels);
    }

    /**
     * Enviar notificación general
     */
    public function sendGeneralNotification(
        User $user,
        string $title,
        string $message,
        string $type = Notification::TYPE_INFO
    ): ?Notification {
        $channels = ['in_app', 'email'];
        
        return $this->sendNotification($user, $title, $message, $type, $channels);
    }

    /**
     * Obtener canales por defecto para un tipo de notificación
     */
    private function getDefaultChannelsForType(string $type): array
    {
        return match($type) {
            Notification::TYPE_ERROR, Notification::TYPE_ALERT => ['in_app', 'email', 'push', 'sms'],
            Notification::TYPE_WARNING => ['in_app', 'email', 'push'],
            Notification::TYPE_SUCCESS => ['in_app', 'email'],
            default => ['in_app', 'email'],
        };
    }

    /**
     * Obtener el tipo de notificación basado en el título
     */
    private function getNotificationTypeFromTitle(string $title): string
    {
        $title = strtolower($title);
        
        if (str_contains($title, 'wallet') || str_contains($title, 'pago') || str_contains($title, 'crédito')) {
            return 'wallet';
        }
        
        if (str_contains($title, 'evento') || str_contains($title, 'event') || str_contains($title, 'calendario')) {
            return 'event';
        }
        
        if (str_contains($title, 'mensaje') || str_contains($title, 'message') || str_contains($title, 'chat')) {
            return 'message';
        }
        
        return 'general';
    }

    /**
     * Verificar si un canal está habilitado para un usuario y tipo
     */
    private function isChannelEnabled(int $userId, string $channel, string $type): bool
    {
        return NotificationSetting::isChannelEnabled($userId, $channel, $type);
    }

    /**
     * Enviar notificación a un canal específico
     */
    private function sendToChannel(Notification $notification, string $channel): void
    {
        try {
            switch ($channel) {
                case 'email':
                    $this->sendEmailNotification($notification);
                    break;
                    
                case 'push':
                    $this->sendPushNotification($notification);
                    break;
                    
                case 'sms':
                    $this->sendSmsNotification($notification);
                    break;
                    
                case 'in_app':
                    // Las notificaciones in-app se manejan automáticamente
                    break;
                    
                default:
                    Log::warning('Unknown notification channel', ['channel' => $channel]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending to channel', [
                'channel' => $channel,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enviar notificación por email
     */
    private function sendEmailNotification(Notification $notification): void
    {
        // Aquí implementarías la lógica para enviar emails
        // Por ejemplo, usando Laravel Mail o un servicio externo
        Log::info('Email notification sent', [
            'notification_id' => $notification->id,
            'user_email' => $notification->user->email,
        ]);
    }

    /**
     * Enviar notificación push
     */
    private function sendPushNotification(Notification $notification): void
    {
        // Aquí implementarías la lógica para enviar notificaciones push
        // Por ejemplo, usando Firebase Cloud Messaging
        Log::info('Push notification sent', [
            'notification_id' => $notification->id,
            'user_id' => $notification->user_id,
        ]);
    }

    /**
     * Enviar notificación por SMS
     */
    private function sendSmsNotification(Notification $notification): void
    {
        // Aquí implementarías la lógica para enviar SMS
        // Por ejemplo, usando Twilio o un servicio similar
        Log::info('SMS notification sent', [
            'notification_id' => $notification->id,
            'user_id' => $notification->user_id,
        ]);
    }

    /**
     * Marcar notificación como leída
     */
    public function markNotificationAsRead(Notification $notification): bool
    {
        return $notification->markAsRead();
    }

    /**
     * Marcar múltiples notificaciones como leídas
     */
    public function markMultipleNotificationsAsRead(array $notificationIds): int
    {
        return Notification::markMultipleAsRead($notificationIds);
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public function markAllNotificationsAsRead(int $userId): int
    {
        return Notification::markAllAsRead($userId);
    }

    /**
     * Limpiar notificaciones antiguas
     */
    public function cleanupOldNotifications(int $days = 30): int
    {
        return Notification::cleanupOld($days);
    }

    /**
     * Obtener estadísticas de notificaciones para un usuario
     */
    public function getNotificationStats(int $userId): array
    {
        return Notification::getUserStats($userId);
    }

    /**
     * Obtener estadísticas de configuraciones de notificación para un usuario
     */
    public function getNotificationSettingStats(int $userId): array
    {
        return NotificationSetting::getUserStats($userId);
    }
}
