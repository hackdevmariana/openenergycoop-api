<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'date',
        'location',
        'public',
        'language',
        'organization_id',
        'is_draft',
    ];

    protected $casts = [
        'date' => 'datetime',
        'public' => 'boolean',
        'is_draft' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Constantes para idiomas
    const LANGUAGES = [
        'es' => 'Español',
        'en' => 'English',
        'ca' => 'Català',
        'eu' => 'Euskara',
        'gl' => 'Galego',
    ];

    // Constantes para estados del evento
    const STATUS_UPCOMING = 'upcoming';
    const STATUS_ONGOING = 'ongoing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Obtener la organización del evento
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener las asistencias al evento
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(EventAttendance::class);
    }

    /**
     * Obtener los usuarios registrados al evento
     */
    public function registeredUsers(): HasMany
    {
        return $this->hasMany(EventAttendance::class)->where('status', 'registered');
    }

    /**
     * Obtener los usuarios que asistieron al evento
     */
    public function attendedUsers(): HasMany
    {
        return $this->hasMany(EventAttendance::class)->where('status', 'attended');
    }

    /**
     * Obtener los usuarios que cancelaron
     */
    public function cancelledUsers(): HasMany
    {
        return $this->hasMany(EventAttendance::class)->where('status', 'cancelled');
    }

    /**
     * Obtener los usuarios que no asistieron
     */
    public function noShowUsers(): HasMany
    {
        return $this->hasMany(EventAttendance::class)->where('status', 'no_show');
    }

    /**
     * Scope para eventos públicos
     */
    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    /**
     * Scope para eventos privados
     */
    public function scopePrivate($query)
    {
        return $query->where('public', false);
    }

    /**
     * Scope para eventos por idioma
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope para eventos por organización
     */
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope para eventos por fecha
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope para eventos futuros
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>', now());
    }

    /**
     * Scope para eventos pasados
     */
    public function scopePast($query)
    {
        return $query->where('date', '<', now());
    }

    /**
     * Scope para eventos de hoy
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope para eventos de esta semana
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope para eventos de este mes
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
    }

    /**
     * Scope para eventos publicados (no borradores)
     */
    public function scopePublished($query)
    {
        return $query->where('is_draft', false);
    }

    /**
     * Scope para eventos borradores
     */
    public function scopeDrafts($query)
    {
        return $query->where('is_draft', true);
    }

    /**
     * Scope para eventos por ubicación
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    /**
     * Scope para búsqueda en título y descripción
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    }

    /**
     * Verificar si el evento es público
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * Verificar si el evento es privado
     */
    public function isPrivate(): bool
    {
        return !$this->public;
    }

    /**
     * Verificar si el evento es un borrador
     */
    public function isDraft(): bool
    {
        return $this->is_draft;
    }

    /**
     * Verificar si el evento está publicado
     */
    public function isPublished(): bool
    {
        return !$this->is_draft;
    }

    /**
     * Verificar si el evento es futuro
     */
    public function isUpcoming(): bool
    {
        return $this->date->isFuture();
    }

    /**
     * Verificar si el evento es pasado
     */
    public function isPast(): bool
    {
        return $this->date->isPast();
    }

    /**
     * Verificar si el evento es hoy
     */
    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Verificar si el evento está en curso
     */
    public function isOngoing(): bool
    {
        $now = now();
        $startTime = $this->date;
        $endTime = $this->date->addHours(2); // Asumimos 2 horas de duración por defecto
        
        return $now->between($startTime, $endTime);
    }

    /**
     * Obtener el estado del evento
     */
    public function getStatusAttribute(): string
    {
        if ($this->isCancelled()) {
            return self::STATUS_CANCELLED;
        }
        
        if ($this->isOngoing()) {
            return self::STATUS_ONGOING;
        }
        
        if ($this->isUpcoming()) {
            return self::STATUS_UPCOMING;
        }
        
        return self::STATUS_COMPLETED;
    }

    /**
     * Verificar si el evento está cancelado
     */
    public function isCancelled(): bool
    {
        return $this->deleted_at !== null;
    }

    /**
     * Obtener el tiempo restante hasta el evento
     */
    public function getTimeUntilAttribute(): string
    {
        if ($this->isPast()) {
            return 'Evento finalizado';
        }
        
        return $this->date->diffForHumans();
    }

    /**
     * Obtener el tiempo transcurrido desde el evento
     */
    public function getTimeAgoAttribute(): string
    {
        if ($this->isUpcoming()) {
            return 'Evento próximo';
        }
        
        return $this->date->diffForHumans();
    }

    /**
     * Obtener la etiqueta del idioma
     */
    public function getLanguageLabelAttribute(): string
    {
        return self::LANGUAGES[$this->language] ?? $this->language;
    }

    /**
     * Obtener la clase del badge del estado
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_UPCOMING => 'info',
            self::STATUS_ONGOING => 'success',
            self::STATUS_COMPLETED => 'secondary',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Obtener el icono del estado
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            self::STATUS_UPCOMING => 'heroicon-o-calendar',
            self::STATUS_ONGOING => 'heroicon-o-play-circle',
            self::STATUS_COMPLETED => 'heroicon-o-check-circle',
            self::STATUS_CANCELLED => 'heroicon-o-x-circle',
            default => 'heroicon-o-calendar'
        };
    }

    /**
     * Obtener el color del estado
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_UPCOMING => 'blue',
            self::STATUS_ONGOING => 'green',
            self::STATUS_COMPLETED => 'gray',
            self::STATUS_CANCELLED => 'red',
            default => 'gray'
        };
    }

    /**
     * Obtener estadísticas de asistencia
     */
    public function getAttendanceStatsAttribute(): array
    {
        return [
            'total_registered' => $this->attendances()->where('status', 'registered')->count(),
            'total_attended' => $this->attendances()->where('status', 'attended')->count(),
            'total_cancelled' => $this->attendances()->where('status', 'cancelled')->count(),
            'total_no_show' => $this->attendances()->where('status', 'no_show')->count(),
            'attendance_rate' => $this->attendances()->count() > 0 ? 
                round(($this->attendances()->where('status', 'attended')->count() / $this->attendances()->count()) * 100, 2) : 0,
        ];
    }

    /**
     * Verificar si un usuario está registrado
     */
    public function isUserRegistered(int $userId): bool
    {
        return $this->attendances()->where('user_id', $userId)->exists();
    }

    /**
     * Verificar si un usuario asistió
     */
    public function didUserAttend(int $userId): bool
    {
        return $this->attendances()->where('user_id', $userId)->where('status', 'attended')->exists();
    }

    /**
     * Obtener la asistencia de un usuario
     */
    public function getUserAttendance(int $userId): ?EventAttendance
    {
        return $this->attendances()->where('user_id', $userId)->first();
    }

    /**
     * Registrar un usuario al evento
     */
    public function registerUser(int $userId): EventAttendance
    {
        return $this->attendances()->create([
            'user_id' => $userId,
            'status' => 'registered',
            'registered_at' => now(),
            'checkin_token' => $this->generateCheckinToken(),
        ]);
    }

    /**
     * Generar token de check-in único
     */
    private function generateCheckinToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (EventAttendance::where('checkin_token', $token)->exists());
        
        return $token;
    }

    /**
     * Obtener estadísticas generales de eventos
     */
    public static function getStats(array $filters = []): array
    {
        $query = self::query();
        
        if (isset($filters['organization_id'])) {
            $query->byOrganization($filters['organization_id']);
        }
        
        if (isset($filters['public'])) {
            $query->where('public', $filters['public']);
        }
        
        if (isset($filters['language'])) {
            $query->byLanguage($filters['language']);
        }
        
        return [
            'total' => $query->count(),
            'public' => $query->where('public', true)->count(),
            'private' => $query->where('public', false)->count(),
            'upcoming' => $query->upcoming()->count(),
            'past' => $query->past()->count(),
            'today' => $query->today()->count(),
            'this_week' => $query->thisWeek()->count(),
            'this_month' => $query->thisMonth()->count(),
            'published' => $query->published()->count(),
            'drafts' => $query->drafts()->count(),
            'by_language' => $query->selectRaw('language, count(*) as count')
                                   ->groupBy('language')
                                   ->pluck('count', 'language')
                                   ->toArray(),
        ];
    }

    /**
     * Obtener eventos recomendados para un usuario
     */
    public static function getRecommendedForUser(int $userId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $user = User::find($userId);
        if (!$user) {
            return collect();
        }
        
        // Obtener eventos de organizaciones a las que pertenece el usuario
        $organizationIds = $user->organizations()->pluck('organizations.id');
        
        return self::public()
                  ->published()
                  ->upcoming()
                  ->whereIn('organization_id', $organizationIds)
                  ->orderBy('date')
                  ->limit($limit)
                  ->get();
    }
}
