<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EventAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'registered_at',
        'checked_in_at',
        'cancellation_reason',
        'notes',
        'checkin_token',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Constantes para estados de asistencia
    const STATUS_REGISTERED = 'registered';
    const STATUS_ATTENDED = 'attended';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    // Estados disponibles
    const STATUSES = [
        self::STATUS_REGISTERED => 'Registrado',
        self::STATUS_ATTENDED => 'Asistió',
        self::STATUS_CANCELLED => 'Cancelado',
        self::STATUS_NO_SHOW => 'No asistió',
    ];

    /**
     * Obtener el evento
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Obtener el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para asistencias por estado
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para asistencias registradas
     */
    public function scopeRegistered($query)
    {
        return $query->where('status', self::STATUS_REGISTERED);
    }

    /**
     * Scope para asistencias que asistieron
     */
    public function scopeAttended($query)
    {
        return $query->where('status', self::STATUS_ATTENDED);
    }

    /**
     * Scope para asistencias canceladas
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope para asistencias que no asistieron
     */
    public function scopeNoShow($query)
    {
        return $query->where('status', self::STATUS_NO_SHOW);
    }

    /**
     * Scope para asistencias por evento
     */
    public function scopeByEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope para asistencias por usuario
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para asistencias por fecha de registro
     */
    public function scopeByRegistrationDate($query, $date)
    {
        return $query->whereDate('registered_at', $date);
    }

    /**
     * Scope para asistencias por fecha de check-in
     */
    public function scopeByCheckinDate($query, $date)
    {
        return $query->whereDate('checked_in_at', $date);
    }

    /**
     * Scope para asistencias recientes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('registered_at', '>=', now()->subDays($days));
    }

    /**
     * Scope para asistencias por organización
     */
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->whereHas('event', function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        });
    }

    /**
     * Scope para búsqueda en notas y razones
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('notes', 'like', "%{$search}%")
              ->orWhere('cancellation_reason', 'like', "%{$search}%");
        });
    }

    /**
     * Verificar si la asistencia está registrada
     */
    public function isRegistered(): bool
    {
        return $this->status === self::STATUS_REGISTERED;
    }

    /**
     * Verificar si la asistencia asistió
     */
    public function isAttended(): bool
    {
        return $this->status === self::STATUS_ATTENDED;
    }

    /**
     * Verificar si la asistencia está cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Verificar si la asistencia no asistió
     */
    public function isNoShow(): bool
    {
        return $this->status === self::STATUS_NO_SHOW;
    }

    /**
     * Verificar si se puede hacer check-in
     */
    public function canCheckIn(): bool
    {
        return $this->isRegistered() && !$this->checked_in_at;
    }

    /**
     * Verificar si se puede cancelar
     */
    public function canCancel(): bool
    {
        return $this->isRegistered() && !$this->event->isPast();
    }

    /**
     * Hacer check-in del usuario
     */
    public function checkIn(): bool
    {
        if (!$this->canCheckIn()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ATTENDED,
            'checked_in_at' => now(),
        ]);

        return true;
    }

    /**
     * Cancelar asistencia
     */
    public function cancel(string $reason = null): bool
    {
        if (!$this->canCancel()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELED,
            'cancellation_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Marcar como no asistió
     */
    public function markAsNoShow(): bool
    {
        if (!$this->isRegistered()) {
            return false;
        }

        $this->update(['status' => self::STATUS_NO_SHOW]);
        return true;
    }

    /**
     * Re-registrar usuario cancelado
     */
    public function reRegister(): bool
    {
        if (!$this->isCancelled()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REGISTERED,
            'cancellation_reason' => null,
            'checked_in_at' => null,
        ]);

        return true;
    }

    /**
     * Obtener la etiqueta del estado
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Obtener la clase del badge del estado
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_REGISTERED => 'info',
            self::STATUS_ATTENDED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_NO_SHOW => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Obtener el icono del estado
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            self::STATUS_REGISTERED => 'heroicon-o-clock',
            self::STATUS_ATTENDED => 'heroicon-o-check-circle',
            self::STATUS_CANCELLED => 'heroicon-o-x-circle',
            self::STATUS_NO_SHOW => 'heroicon-o-exclamation-triangle',
            default => 'heroicon-o-question-mark-circle'
        };
    }

    /**
     * Obtener el color del estado
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_REGISTERED => 'blue',
            self::STATUS_ATTENDED => 'green',
            self::STATUS_CANCELLED => 'red',
            self::STATUS_NO_SHOW => 'yellow',
            default => 'gray'
        };
    }

    /**
     * Obtener el tiempo transcurrido desde el registro
     */
    public function getTimeSinceRegistrationAttribute(): string
    {
        return $this->registered_at->diffForHumans();
    }

    /**
     * Obtener el tiempo transcurrido desde el check-in
     */
    public function getTimeSinceCheckinAttribute(): string
    {
        if (!$this->checked_in_at) {
            return 'No ha hecho check-in';
        }
        
        return $this->checked_in_at->diffForHumans();
    }

    /**
     * Obtener el tiempo hasta el evento
     */
    public function getTimeUntilEventAttribute(): string
    {
        if ($this->event->isPast()) {
            return 'Evento finalizado';
        }
        
        return $this->event->date->diffForHumans();
    }

    /**
     * Verificar si el check-in está atrasado
     */
    public function isCheckinOverdue(): bool
    {
        if ($this->isAttended() || $this->isCancelled()) {
            return false;
        }
        
        return $this->event->isPast() && !$this->checked_in_at;
    }

    /**
     * Obtener estadísticas de asistencia por evento
     */
    public static function getEventStats(int $eventId): array
    {
        $query = self::where('event_id', $eventId);
        
        return [
            'total_registered' => $query->where('status', self::STATUS_REGISTERED)->count(),
            'total_attended' => $query->where('status', self::STATUS_ATTENDED)->count(),
            'total_cancelled' => $query->where('status', self::STATUS_CANCELLED)->count(),
            'total_no_show' => $query->where('status', self::STATUS_NO_SHOW)->count(),
            'attendance_rate' => $query->count() > 0 ? 
                round(($query->where('status', self::STATUS_ATTENDED)->count() / $query->count()) * 100, 2) : 0,
            'cancellation_rate' => $query->count() > 0 ? 
                round(($query->where('status', self::STATUS_CANCELLED)->count() / $query->count()) * 100, 2) : 0,
            'no_show_rate' => $query->count() > 0 ? 
                round(($query->where('status', self::STATUS_NO_SHOW)->count() / $query->count()) * 100, 2) : 0,
        ];
    }

    /**
     * Obtener estadísticas de asistencia por usuario
     */
    public static function getUserStats(int $userId): array
    {
        $query = self::where('user_id', $userId);
        
        return [
            'total_events' => $query->count(),
            'attended_events' => $query->where('status', self::STATUS_ATTENDED)->count(),
            'registered_events' => $query->where('status', self::STATUS_REGISTERED)->count(),
            'cancelled_events' => $query->where('status', self::STATUS_CANCELLED)->count(),
            'no_show_events' => $query->where('status', self::STATUS_NO_SHOW)->count(),
            'attendance_rate' => $query->count() > 0 ? 
                round(($query->where('status', self::STATUS_ATTENDED)->count() / $query->count()) * 100, 2) : 0,
        ];
    }

    /**
     * Obtener estadísticas generales
     */
    public static function getStats(array $filters = []): array
    {
        $query = self::query();
        
        if (isset($filters['event_id'])) {
            $query->byEvent($filters['event_id']);
        }
        
        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }
        
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }
        
        if (isset($filters['organization_id'])) {
            $query->byOrganization($filters['organization_id']);
        }
        
        return [
            'total' => $query->count(),
            'registered' => $query->where('status', self::STATUS_REGISTERED)->count(),
            'attended' => $query->where('status', self::STATUS_ATTENDED)->count(),
            'cancelled' => $query->where('status', self::STATUS_CANCELLED)->count(),
            'no_show' => $query->where('status', self::STATUS_NO_SHOW)->count(),
            'by_status' => $query->selectRaw('status, count(*) as count')
                                 ->groupBy('status')
                                 ->pluck('count', 'status')
                                 ->toArray(),
        ];
    }

    /**
     * Verificar si un usuario ya está registrado en un evento
     */
    public static function isUserRegistered(int $eventId, int $userId): bool
    {
        return self::where('event_id', $eventId)
                  ->where('user_id', $userId)
                  ->exists();
    }

    /**
     * Obtener asistencia por token de check-in
     */
    public static function findByCheckinToken(string $token): ?self
    {
        return self::where('checkin_token', $token)->first();
    }

    /**
     * Generar nuevo token de check-in
     */
    public function generateNewCheckinToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (self::where('checkin_token', $token)->exists());
        
        $this->update(['checkin_token' => $token]);
        
        return $token;
    }
}
