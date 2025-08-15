<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Configuraciones predefinidas con sus valores por defecto
     */
    public const DEFAULT_SETTINGS = [
        'dashboard_view' => 'grid', // grid, list, compact
        'theme' => 'auto', // light, dark, auto
        'language' => 'es', // es, en, ca, etc.
        'timezone' => 'Europe/Madrid',
        'date_format' => 'd/m/Y',
        'time_format' => 'H:i',
        'currency' => 'EUR',
        'notifications' => [
            'email' => [
                'system' => true,
                'marketing' => false,
                'achievements' => true,
                'team_updates' => true,
                'challenges' => true,
            ],
            'push' => [
                'system' => true,
                'achievements' => true,
                'team_updates' => false,
                'challenges' => true,
            ],
            'sms' => [
                'system' => false,
                'security' => true,
            ],
        ],
        'privacy' => [
            'show_in_rankings' => true,
            'show_profile_public' => false,
            'share_achievements' => true,
        ],
        'dashboard_widgets' => [
            'energy_overview' => true,
            'recent_achievements' => true,
            'team_progress' => true,
            'challenges' => true,
            'leaderboard' => false,
        ],
        'energy_units' => 'kwh', // kwh, mwh, wh
        'chart_preferences' => [
            'default_period' => 'month', // week, month, quarter, year
            'show_comparisons' => true,
            'show_goals' => true,
        ],
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para obtener configuraciones por usuario
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por clave de configuración
     */
    public function scopeForKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    /**
     * Obtener una configuración específica de un usuario
     */
    public static function getUserSetting(int $userId, string $key, $default = null)
    {
        $setting = self::forUser($userId)->forKey($key)->first();
        
        if ($setting) {
            return $setting->value;
        }

        // Devolver valor por defecto predefinido si existe
        return self::DEFAULT_SETTINGS[$key] ?? $default;
    }

    /**
     * Establecer una configuración para un usuario
     */
    public static function setUserSetting(int $userId, string $key, $value): self
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Obtener todas las configuraciones de un usuario
     */
    public static function getUserSettings(int $userId): Collection
    {
        $userSettings = self::forUser($userId)->get()->keyBy('key');
        $allSettings = collect();

        // Combinar configuraciones por defecto con las del usuario
        foreach (self::DEFAULT_SETTINGS as $key => $defaultValue) {
            $allSettings[$key] = $userSettings->has($key) 
                ? $userSettings[$key]->value 
                : $defaultValue;
        }

        // Agregar configuraciones personalizadas que no están en los defaults
        foreach ($userSettings as $key => $setting) {
            if (!isset(self::DEFAULT_SETTINGS[$key])) {
                $allSettings[$key] = $setting->value;
            }
        }

        return $allSettings;
    }

    /**
     * Establecer múltiples configuraciones para un usuario
     */
    public static function setUserSettings(int $userId, array $settings): void
    {
        foreach ($settings as $key => $value) {
            self::setUserSetting($userId, $key, $value);
        }
    }

    /**
     * Eliminar una configuración específica de un usuario
     */
    public static function removeUserSetting(int $userId, string $key): bool
    {
        return self::forUser($userId)->forKey($key)->delete() > 0;
    }

    /**
     * Resetear una configuración a su valor por defecto
     */
    public static function resetUserSetting(int $userId, string $key): ?self
    {
        if (!isset(self::DEFAULT_SETTINGS[$key])) {
            // Si no hay valor por defecto, eliminar la configuración
            self::removeUserSetting($userId, $key);
            return null;
        }

        return self::setUserSetting($userId, $key, self::DEFAULT_SETTINGS[$key]);
    }

    /**
     * Resetear todas las configuraciones de un usuario a los valores por defecto
     */
    public static function resetAllUserSettings(int $userId): void
    {
        // Eliminar todas las configuraciones existentes
        self::forUser($userId)->delete();

        // Establecer configuraciones por defecto
        foreach (self::DEFAULT_SETTINGS as $key => $value) {
            self::setUserSetting($userId, $key, $value);
        }
    }

    /**
     * Verificar si una configuración existe para un usuario
     */
    public static function hasUserSetting(int $userId, string $key): bool
    {
        return self::forUser($userId)->forKey($key)->exists();
    }

    /**
     * Obtener configuraciones de notificación de un usuario
     */
    public static function getNotificationSettings(int $userId): array
    {
        return self::getUserSetting($userId, 'notifications', self::DEFAULT_SETTINGS['notifications']);
    }

    /**
     * Verificar si un usuario tiene activado un tipo de notificación
     */
    public static function isNotificationEnabled(int $userId, string $channel, string $type): bool
    {
        $notifications = self::getNotificationSettings($userId);
        return $notifications[$channel][$type] ?? false;
    }

    /**
     * Obtener configuraciones de privacidad de un usuario
     */
    public static function getPrivacySettings(int $userId): array
    {
        return self::getUserSetting($userId, 'privacy', self::DEFAULT_SETTINGS['privacy']);
    }

    /**
     * Verificar si un usuario permite mostrar su información públicamente
     */
    public static function allowsPublicDisplay(int $userId, string $setting): bool
    {
        $privacy = self::getPrivacySettings($userId);
        return $privacy[$setting] ?? false;
    }

    /**
     * Obtener las configuraciones de widgets del dashboard
     */
    public static function getDashboardWidgets(int $userId): array
    {
        return self::getUserSetting($userId, 'dashboard_widgets', self::DEFAULT_SETTINGS['dashboard_widgets']);
    }

    /**
     * Verificar si un widget está habilitado para un usuario
     */
    public static function isWidgetEnabled(int $userId, string $widget): bool
    {
        $widgets = self::getDashboardWidgets($userId);
        return $widgets[$widget] ?? false;
    }

    /**
     * Obtener la configuración de tema de un usuario
     */
    public static function getTheme(int $userId): string
    {
        return self::getUserSetting($userId, 'theme', self::DEFAULT_SETTINGS['theme']);
    }

    /**
     * Obtener la configuración de idioma de un usuario
     */
    public static function getLanguage(int $userId): string
    {
        return self::getUserSetting($userId, 'language', self::DEFAULT_SETTINGS['language']);
    }

    /**
     * Obtener la configuración de zona horaria de un usuario
     */
    public static function getTimezone(int $userId): string
    {
        return self::getUserSetting($userId, 'timezone', self::DEFAULT_SETTINGS['timezone']);
    }
}
