<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Enums\AppEnums;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_name',
        'device_type',
        'platform',
        'last_seen_at',
        'push_token',
        'user_agent',
        'ip_address',
        'is_current',
        'revoked_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_current' => 'boolean',
    ];

    /**
     * Tipos de dispositivo disponibles
     */
    public const DEVICE_TYPES = AppEnums::USER_DEVICE_TYPES;

    /**
     * Plataformas comunes
     */
    public const PLATFORMS = AppEnums::USER_DEVICE_PLATFORMS;

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para obtener dispositivos activos (no revocados)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Scope para obtener dispositivos revocados
     */
    public function scopeRevoked(Builder $query): Builder
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Scope para obtener el dispositivo actual
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope para filtrar por tipo de dispositivo
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('device_type', $type);
    }

    /**
     * Scope para obtener dispositivos por usuario
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para dispositivos con push token
     */
    public function scopeWithPushToken(Builder $query): Builder
    {
        return $query->whereNotNull('push_token');
    }

    /**
     * Scope para dispositivos vistos recientemente
     */
    public function scopeRecentlyActive(Builder $query, int $days = 30): Builder
    {
        return $query->where('last_seen_at', '>=', now()->subDays($days));
    }

    /**
     * Verificar si el dispositivo está activo
     */
    public function isActive(): bool
    {
        return is_null($this->revoked_at);
    }

    /**
     * Verificar si el dispositivo ha sido revocado
     */
    public function isRevoked(): bool
    {
        return !is_null($this->revoked_at);
    }

    /**
     * Verificar si es el dispositivo actual
     */
    public function isCurrent(): bool
    {
        return $this->is_current;
    }

    /**
     * Revocar el dispositivo
     */
    public function revoke(): bool
    {
        return $this->update([
            'revoked_at' => now(),
            'is_current' => false,
        ]);
    }

    /**
     * Marcar como dispositivo actual
     */
    public function setCurrent(): bool
    {
        // Desmarcar otros dispositivos del mismo usuario como actuales
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        // Marcar este dispositivo como actual
        return $this->update(['is_current' => true]);
    }

    /**
     * Actualizar la última vez visto
     */
    public function updateLastSeen(?string $ipAddress = null): bool
    {
        $data = ['last_seen_at' => now()];
        
        if ($ipAddress) {
            $data['ip_address'] = $ipAddress;
        }

        return $this->update($data);
    }

    /**
     * Obtener el nombre legible del tipo de dispositivo
     */
    public function getDeviceTypeNameAttribute(): string
    {
        return self::DEVICE_TYPES[$this->device_type] ?? $this->device_type;
    }

    /**
     * Obtener información del dispositivo formateada
     */
    public function getDeviceInfoAttribute(): string
    {
        $info = $this->device_name ?: $this->device_type_name;
        
        if ($this->platform) {
            $info .= " ({$this->platform})";
        }
        
        return $info;
    }

    /**
     * Verificar si el dispositivo está activo recientemente
     */
    public function isRecentlyActive(int $days = 30): bool
    {
        if (!$this->last_seen_at) {
            return false;
        }

        return $this->last_seen_at->isAfter(now()->subDays($days));
    }

    /**
     * Registrar un nuevo dispositivo o actualizar uno existente
     */
    public static function registerDevice(
        int $userId,
        string $deviceType = 'web',
        ?string $deviceName = null,
        ?string $platform = null,
        ?string $userAgent = null,
        ?string $ipAddress = null,
        ?string $pushToken = null,
        bool $setCurrent = true
    ): self {
        // Buscar dispositivo existente por user_agent y usuario
        $device = null;
        if ($userAgent) {
            $device = self::where('user_id', $userId)
                ->where('user_agent', $userAgent)
                ->active()
                ->first();
        }

        if ($device) {
            // Actualizar dispositivo existente
            $device->update([
                'device_name' => $deviceName ?: $device->device_name,
                'device_type' => $deviceType,
                'platform' => $platform ?: $device->platform,
                'last_seen_at' => now(),
                'push_token' => $pushToken ?: $device->push_token,
                'ip_address' => $ipAddress ?: $device->ip_address,
            ]);
        } else {
            // Crear nuevo dispositivo
            $device = self::create([
                'user_id' => $userId,
                'device_name' => $deviceName,
                'device_type' => $deviceType,
                'platform' => $platform,
                'last_seen_at' => now(),
                'push_token' => $pushToken,
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
                'is_current' => false,
            ]);
        }

        if ($setCurrent) {
            $device->setCurrent();
        }

        return $device;
    }

    /**
     * Obtener dispositivos activos de un usuario
     */
    public static function getActiveDevicesForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::forUser($userId)
            ->active()
            ->orderBy('last_seen_at', 'desc')
            ->get();
    }

    /**
     * Limpiar dispositivos antiguos inactivos
     */
    public static function cleanupOldDevices(int $daysOld = 90): int
    {
        return self::where('last_seen_at', '<', now()->subDays($daysOld))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }
}
