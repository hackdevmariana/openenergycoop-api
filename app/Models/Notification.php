<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'read_at',
        'type',
        'delivered_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Constantes para tipos de notificación
    const TYPE_INFO = 'info';
    const TYPE_ALERT = 'alert';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';

    /**
     * Obtener todos los tipos de notificación disponibles
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_INFO,
            self::TYPE_ALERT,
            self::TYPE_SUCCESS,
            self::TYPE_WARNING,
            self::TYPE_ERROR,
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
     * Scope para notificaciones no leídas
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope para notificaciones leídas
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope para notificaciones por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para notificaciones por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para notificaciones recientes
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope para notificaciones entregadas
     */
    public function scopeDelivered($query)
    {
        return $query->whereNotNull('delivered_at');
    }

    /**
     * Scope para notificaciones no entregadas
     */
    public function scopeNotDelivered($query)
    {
        return $query->whereNull('delivered_at');
    }

    /**
     * Marcar como leída
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Marcar como entregada
     */
    public function markAsDelivered(): bool
    {
        return $this->update(['delivered_at' => now()]);
    }

    /**
     * Verificar si está leída
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Verificar si está entregada
     */
    public function isDelivered(): bool
    {
        return !is_null($this->delivered_at);
    }

    /**
     * Verificar si es reciente (últimas 24 horas)
     */
    public function isRecent(): bool
    {
        return $this->created_at->isAfter(now()->subDay());
    }

    /**
     * Obtener el tiempo transcurrido desde la creación
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Obtener el tiempo transcurrido desde que se leyó
     */
    public function getReadTimeAgoAttribute(): ?string
    {
        return $this->read_at ? $this->read_at->diffForHumans() : null;
    }

    /**
     * Obtener el tiempo transcurrido desde que se entregó
     */
    public function getDeliveredTimeAgoAttribute(): ?string
    {
        return $this->delivered_at ? $this->delivered_at->diffForHumans() : null;
    }

    /**
     * Obtener la clase CSS para el tipo de notificación
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SUCCESS => 'badge-success',
            self::TYPE_WARNING => 'badge-warning',
            self::TYPE_ERROR => 'badge-error',
            self::TYPE_ALERT => 'badge-alert',
            default => 'badge-info',
        };
    }

    /**
     * Obtener el icono para el tipo de notificación
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SUCCESS => 'check-circle',
            self::TYPE_WARNING => 'exclamation-triangle',
            self::TYPE_ERROR => 'x-circle',
            self::TYPE_ALERT => 'bell',
            default => 'information-circle',
        };
    }

    /**
     * Obtener el color para el tipo de notificación
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SUCCESS => 'green',
            self::TYPE_WARNING => 'yellow',
            self::TYPE_ERROR => 'red',
            self::TYPE_ALERT => 'orange',
            default => 'blue',
        };
    }

    /**
     * Verificar si es de tipo informativo
     */
    public function isInfo(): bool
    {
        return $this->type === self::TYPE_INFO;
    }

    /**
     * Verificar si es de tipo alerta
     */
    public function isAlert(): bool
    {
        return $this->type === self::TYPE_ALERT;
    }

    /**
     * Verificar si es de tipo éxito
     */
    public function isSuccess(): bool
    {
        return $this->type === self::TYPE_SUCCESS;
    }

    /**
     * Verificar si es de tipo advertencia
     */
    public function isWarning(): bool
    {
        return $this->type === self::TYPE_WARNING;
    }

    /**
     * Verificar si es de tipo error
     */
    public function isError(): bool
    {
        return $this->type === self::TYPE_ERROR;
    }

    /**
     * Obtener el resumen del mensaje (primeros 100 caracteres)
     */
    public function getMessageSummaryAttribute(): string
    {
        return Str::limit($this->message, 100);
    }

    /**
     * Obtener el título formateado
     */
    public function getTitleFormattedAttribute(): string
    {
        return ucfirst($this->title);
    }

    /**
     * Verificar si es urgente (tipo error o alerta)
     */
    public function isUrgent(): bool
    {
        return in_array($this->type, [self::TYPE_ERROR, self::TYPE_ALERT]);
    }

    /**
     * Verificar si es de baja prioridad (tipo info)
     */
    public function isLowPriority(): bool
    {
        return $this->type === self::TYPE_INFO;
    }

    /**
     * Obtener estadísticas de notificaciones para un usuario
     */
    public static function getUserStats(int $userId): array
    {
        return [
            'total' => self::byUser($userId)->count(),
            'unread' => self::byUser($userId)->unread()->count(),
            'read' => self::byUser($userId)->read()->count(),
            'by_type' => self::byUser($userId)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'recent' => self::byUser($userId)->recent(7)->count(),
            'delivered' => self::byUser($userId)->delivered()->count(),
            'not_delivered' => self::byUser($userId)->notDelivered()->count(),
        ];
    }

    /**
     * Limpiar notificaciones antiguas (más de 30 días)
     */
    public static function cleanupOld(int $days = 30): int
    {
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Marcar múltiples notificaciones como leídas
     */
    public static function markMultipleAsRead(array $ids): int
    {
        return self::whereIn('id', $ids)->update(['read_at' => now()]);
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public static function markAllAsRead(int $userId): int
    {
        return self::byUser($userId)->unread()->update(['read_at' => now()]);
    }
}
