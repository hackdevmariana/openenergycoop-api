<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel',
        'notification_type',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    // Constantes para canales de notificación
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_PUSH = 'push';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_IN_APP = 'in_app';

    // Constantes para tipos de notificación
    const TYPE_WALLET = 'wallet';
    const TYPE_EVENT = 'event';
    const TYPE_MESSAGE = 'message';
    const TYPE_GENERAL = 'general';

    /**
     * Obtener todos los canales disponibles
     */
    public static function getChannels(): array
    {
        return [
            self::CHANNEL_EMAIL,
            self::CHANNEL_PUSH,
            self::CHANNEL_SMS,
            self::CHANNEL_IN_APP,
        ];
    }

    /**
     * Obtener todos los tipos de notificación disponibles
     */
    public static function getNotificationTypes(): array
    {
        return [
            self::TYPE_WALLET,
            self::TYPE_EVENT,
            self::TYPE_MESSAGE,
            self::TYPE_GENERAL,
        ];
    }

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para configuraciones habilitadas
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope para configuraciones deshabilitadas
     */
    public function scopeDisabled($query)
    {
        return $query->where('enabled', false);
    }

    /**
     * Scope para configuraciones por canal
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope para configuraciones por tipo de notificación
     */
    public function scopeByNotificationType($query, string $notificationType)
    {
        return $query->where('notification_type', $notificationType);
    }

    /**
     * Scope para configuraciones por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Verificar si está habilitado
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Verificar si está deshabilitado
     */
    public function isDisabled(): bool
    {
        return !$this->enabled;
    }

    /**
     * Habilitar la configuración
     */
    public function enable(): bool
    {
        return $this->update(['enabled' => true]);
    }

    /**
     * Deshabilitar la configuración
     */
    public function disable(): bool
    {
        return $this->update(['enabled' => false]);
    }

    /**
     * Alternar el estado de habilitación
     */
    public function toggle(): bool
    {
        return $this->update(['enabled' => !$this->enabled]);
    }

    /**
     * Verificar si es canal de email
     */
    public function isEmailChannel(): bool
    {
        return $this->channel === self::CHANNEL_EMAIL;
    }

    /**
     * Verificar si es canal push
     */
    public function isPushChannel(): bool
    {
        return $this->channel === self::CHANNEL_PUSH;
    }

    /**
     * Verificar si es canal SMS
     */
    public function isSmsChannel(): bool
    {
        return $this->channel === self::CHANNEL_SMS;
    }

    /**
     * Verificar si es canal in-app
     */
    public function isInAppChannel(): bool
    {
        return $this->channel === self::CHANNEL_IN_APP;
    }

    /**
     * Verificar si es tipo wallet
     */
    public function isWalletType(): bool
    {
        return $this->notification_type === self::TYPE_WALLET;
    }

    /**
     * Verificar si es tipo event
     */
    public function isEventType(): bool
    {
        return $this->notification_type === self::TYPE_EVENT;
    }

    /**
     * Verificar si es tipo message
     */
    public function isMessageType(): bool
    {
        return $this->notification_type === self::TYPE_MESSAGE;
    }

    /**
     * Verificar si es tipo general
     */
    public function isGeneralType(): bool
    {
        return $this->notification_type === self::TYPE_GENERAL;
    }

    /**
     * Obtener la etiqueta del canal
     */
    public function getChannelLabelAttribute(): string
    {
        return match($this->channel) {
            self::CHANNEL_EMAIL => 'Email',
            self::CHANNEL_PUSH => 'Push',
            self::CHANNEL_SMS => 'SMS',
            self::CHANNEL_IN_APP => 'In-App',
            default => 'Unknown',
        };
    }

    /**
     * Obtener la etiqueta del tipo de notificación
     */
    public function getNotificationTypeLabelAttribute(): string
    {
        return match($this->notification_type) {
            self::TYPE_WALLET => 'Wallet',
            self::TYPE_EVENT => 'Event',
            self::TYPE_MESSAGE => 'Message',
            self::TYPE_GENERAL => 'General',
            default => 'Unknown',
        };
    }

    /**
     * Obtener la clase CSS para el estado
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->enabled ? 'badge-success' : 'badge-secondary';
    }

    /**
     * Obtener el icono para el canal
     */
    public function getChannelIconAttribute(): string
    {
        return match($this->channel) {
            self::CHANNEL_EMAIL => 'mail',
            self::CHANNEL_PUSH => 'bell',
            self::CHANNEL_SMS => 'chat',
            self::CHANNEL_IN_APP => 'app-window',
            default => 'question-mark-circle',
        };
    }

    /**
     * Obtener el icono para el tipo de notificación
     */
    public function getNotificationTypeIconAttribute(): string
    {
        return match($this->notification_type) {
            self::TYPE_WALLET => 'credit-card',
            self::TYPE_EVENT => 'calendar',
            self::TYPE_MESSAGE => 'chat-bubble-left-right',
            self::TYPE_GENERAL => 'information-circle',
            default => 'question-mark-circle',
        };
    }

    /**
     * Obtener el color para el canal
     */
    public function getChannelColorAttribute(): string
    {
        return match($this->channel) {
            self::CHANNEL_EMAIL => 'blue',
            self::CHANNEL_PUSH => 'green',
            self::CHANNEL_SMS => 'purple',
            self::CHANNEL_IN_APP => 'orange',
            default => 'gray',
        };
    }

    /**
     * Obtener el color para el tipo de notificación
     */
    public function getNotificationTypeColorAttribute(): string
    {
        return match($this->notification_type) {
            self::TYPE_WALLET => 'emerald',
            self::TYPE_EVENT => 'indigo',
            self::TYPE_MESSAGE => 'sky',
            self::TYPE_GENERAL => 'slate',
            default => 'gray',
        };
    }

    /**
     * Verificar si es un canal móvil (push, SMS)
     */
    public function isMobileChannel(): bool
    {
        return in_array($this->channel, [self::CHANNEL_PUSH, self::CHANNEL_SMS]);
    }

    /**
     * Verificar si es un canal digital (email, in-app)
     */
    public function isDigitalChannel(): bool
    {
        return in_array($this->channel, [self::CHANNEL_EMAIL, self::CHANNEL_IN_APP]);
    }

    /**
     * Verificar si es un tipo financiero (wallet)
     */
    public function isFinancialType(): bool
    {
        return $this->notification_type === self::TYPE_WALLET;
    }

    /**
     * Verificar si es un tipo de comunicación (message, general)
     */
    public function isCommunicationType(): bool
    {
        return in_array($this->notification_type, [self::TYPE_MESSAGE, self::TYPE_GENERAL]);
    }

    /**
     * Obtener configuraciones por defecto para un usuario
     */
    public static function getDefaultSettings(int $userId): array
    {
        $settings = [];
        
        foreach (self::getChannels() as $channel) {
            foreach (self::getNotificationTypes() as $type) {
                $settings[] = [
                    'user_id' => $userId,
                    'channel' => $channel,
                    'notification_type' => $type,
                    'enabled' => self::getDefaultEnabledState($channel, $type),
                ];
            }
        }
        
        return $settings;
    }

    /**
     * Obtener el estado habilitado por defecto para un canal y tipo
     */
    private static function getDefaultEnabledState(string $channel, string $type): bool
    {
        // Por defecto, habilitar notificaciones in-app y email para tipos generales
        if ($channel === self::CHANNEL_IN_APP || $channel === self::CHANNEL_EMAIL) {
            return true;
        }
        
        // Habilitar push para eventos y mensajes
        if ($channel === self::CHANNEL_PUSH && in_array($type, [self::TYPE_EVENT, self::TYPE_MESSAGE])) {
            return true;
        }
        
        // Habilitar SMS solo para alertas críticas (wallet)
        if ($channel === self::CHANNEL_SMS && $type === self::TYPE_WALLET) {
            return true;
        }
        
        return false;
    }

    /**
     * Crear o actualizar configuración de notificación
     */
    public static function updateOrCreateSetting(int $userId, string $channel, string $type, bool $enabled): self
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'channel' => $channel,
                'notification_type' => $type,
            ],
            [
                'enabled' => $enabled,
            ]
        );
    }

    /**
     * Obtener configuraciones habilitadas para un usuario y tipo
     */
    public static function getEnabledChannelsForType(int $userId, string $type): array
    {
        return self::byUser($userId)
            ->byNotificationType($type)
            ->enabled()
            ->pluck('channel')
            ->toArray();
    }

    /**
     * Verificar si un canal está habilitado para un usuario y tipo
     */
    public static function isChannelEnabled(int $userId, string $channel, string $type): bool
    {
        return self::byUser($userId)
            ->byChannel($channel)
            ->byNotificationType($type)
            ->enabled()
            ->exists();
    }

    /**
     * Obtener estadísticas de configuraciones para un usuario
     */
    public static function getUserStats(int $userId): array
    {
        $settings = self::byUser($userId)->get();
        
        return [
            'total_settings' => $settings->count(),
            'enabled_settings' => $settings->where('enabled', true)->count(),
            'disabled_settings' => $settings->where('enabled', false)->count(),
            'by_channel' => $settings->groupBy('channel')->map->count(),
            'by_type' => $settings->groupBy('notification_type')->map->count(),
            'enabled_by_channel' => $settings->where('enabled', true)->groupBy('channel')->map->count(),
            'enabled_by_type' => $settings->where('enabled', true)->groupBy('notification_type')->map->count(),
        ];
    }

    /**
     * Habilitar todas las configuraciones de un usuario
     */
    public static function enableAllForUser(int $userId): int
    {
        return self::byUser($userId)->update(['enabled' => true]);
    }

    /**
     * Deshabilitar todas las configuraciones de un usuario
     */
    public static function disableAllForUser(int $userId): int
    {
        return self::byUser($userId)->update(['enabled' => false]);
    }

    /**
     * Habilitar todas las configuraciones de un tipo para un usuario
     */
    public static function enableTypeForUser(int $userId, string $type): int
    {
        return self::byUser($userId)
            ->byNotificationType($type)
            ->update(['enabled' => true]);
    }

    /**
     * Deshabilitar todas las configuraciones de un tipo para un usuario
     */
    public static function disableTypeForUser(int $userId, string $type): int
    {
        return self::byUser($userId)
            ->byNotificationType($type)
            ->update(['enabled' => false]);
    }
}
