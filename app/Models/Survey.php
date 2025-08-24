<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'anonymous_allowed',
        'visible_results',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'anonymous_allowed' => 'boolean',
        'visible_results' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Constantes para estados de la encuesta
    const STATUS = [
        'draft' => 'Borrador',
        'active' => 'Activa',
        'completed' => 'Completada',
        'expired' => 'Expirada',
        'cancelled' => 'Cancelada'
    ];

    // Constantes para tipos de visibilidad de resultados
    const VISIBILITY_TYPES = [
        'immediate' => 'Inmediata',
        'after_completion' => 'Después de completar',
        'after_expiry' => 'Después de expirar',
        'never' => 'Nunca'
    ];

    /**
     * Relación con las respuestas de la encuesta
     */
    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Relación con respuestas anónimas
     */
    public function anonymousResponses()
    {
        return $this->hasMany(SurveyResponse::class)->whereNull('user_id');
    }

    /**
     * Relación con respuestas de usuarios identificados
     */
    public function userResponses()
    {
        return $this->hasMany(SurveyResponse::class)->whereNotNull('user_id');
    }

    /**
     * Relación con usuarios que han respondido
     */
    public function respondents()
    {
        return $this->belongsToMany(User::class, 'survey_responses')
                    ->whereNotNull('user_id')
                    ->withTimestamps();
    }

    /**
     * Scope para encuestas activas (entre fechas de inicio y fin)
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = now();
        return $query->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now);
    }

    /**
     * Scope para encuestas futuras
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>', now());
    }

    /**
     * Scope para encuestas pasadas
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('ends_at', '<', now());
    }

    /**
     * Scope para encuestas que permiten respuestas anónimas
     */
    public function scopeAnonymousAllowed(Builder $query): Builder
    {
        return $query->where('anonymous_allowed', true);
    }

    /**
     * Scope para encuestas que no permiten respuestas anónimas
     */
    public function scopeAnonymousNotAllowed(Builder $query): Builder
    {
        return $query->where('anonymous_allowed', false);
    }

    /**
     * Scope para encuestas con resultados visibles
     */
    public function scopeResultsVisible(Builder $query): Builder
    {
        return $query->where('visible_results', true);
    }

    /**
     * Scope para encuestas con resultados ocultos
     */
    public function scopeResultsHidden(Builder $query): Builder
    {
        return $query->where('visible_results', false);
    }

    /**
     * Scope para encuestas que han comenzado
     */
    public function scopeStarted(Builder $query): Builder
    {
        return $query->where('starts_at', '<=', now());
    }

    /**
     * Scope para encuestas que han terminado
     */
    public function scopeEnded(Builder $query): Builder
    {
        return $query->where('ends_at', '<=', now());
    }

    /**
     * Scope para encuestas que están por comenzar
     */
    public function scopeNotStarted(Builder $query): Builder
    {
        return $query->where('starts_at', '>', now());
    }

    /**
     * Scope para encuestas que no han terminado
     */
    public function scopeNotEnded(Builder $query): Builder
    {
        return $query->where('ends_at', '>', now());
    }

    /**
     * Scope para buscar en título y descripción
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para encuestas por rango de fechas
     */
    public function scopeByDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('starts_at', [$from, $to])
                    ->orWhereBetween('ends_at', [$from, $to]);
    }

    /**
     * Scope para encuestas que están activas hoy
     */
    public function scopeActiveToday(Builder $query): Builder
    {
        $today = now()->startOfDay();
        return $query->where('starts_at', '<=', $today->copy()->endOfDay())
                    ->where('ends_at', '>=', $today);
    }

    /**
     * Scope para encuestas que expiran pronto (próximos 7 días)
     */
    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        $deadline = now()->addDays($days);
        return $query->where('ends_at', '<=', $deadline)
                    ->where('ends_at', '>', now());
    }

    /**
     * Verificar si la encuesta está activa
     */
    public function isActive(): bool
    {
        $now = now();
        return $this->starts_at <= $now && $this->ends_at >= $now;
    }

    /**
     * Verificar si la encuesta ha comenzado
     */
    public function hasStarted(): bool
    {
        return $this->starts_at <= now();
    }

    /**
     * Verificar si la encuesta ha terminado
     */
    public function hasEnded(): bool
    {
        return $this->ends_at <= now();
    }

    /**
     * Verificar si la encuesta está por comenzar
     */
    public function isUpcoming(): bool
    {
        return $this->starts_at > now();
    }

    /**
     * Verificar si la encuesta está expirada
     */
    public function isExpired(): bool
    {
        return $this->ends_at < now();
    }

    /**
     * Verificar si la encuesta permite respuestas anónimas
     */
    public function allowsAnonymous(): bool
    {
        return $this->anonymous_allowed;
    }

    /**
     * Verificar si los resultados son visibles
     */
    public function hasVisibleResults(): bool
    {
        return $this->visible_results;
    }

    /**
     * Verificar si un usuario puede ver los resultados
     */
    public function canUserSeeResults(?User $user = null): bool
    {
        if (!$this->hasVisibleResults()) {
            return false;
        }

        // Si la encuesta no ha terminado, solo mostrar resultados a usuarios que hayan respondido
        if (!$this->hasEnded()) {
            if (!$user) {
                return false;
            }
            return $this->hasUserResponded($user);
        }

        return true;
    }

    /**
     * Verificar si un usuario ha respondido la encuesta
     */
    public function hasUserResponded(?User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        return $this->responses()->where('user_id', $user->id)->exists();
    }

    /**
     * Verificar si un usuario puede responder la encuesta
     */
    public function canUserRespond(?User $user = null): bool
    {
        // Verificar si la encuesta está activa
        if (!$this->isActive()) {
            return false;
        }

        // Si no es anónima, el usuario debe estar autenticado
        if (!$this->allowsAnonymous() && !$user) {
            return false;
        }

        // Si el usuario está autenticado, verificar que no haya respondido ya
        if ($user && $this->hasUserResponded($user)) {
            return false;
        }

        return true;
    }

    /**
     * Obtener el estado actual de la encuesta
     */
    public function getStatusAttribute(): string
    {
        if ($this->isExpired()) {
            return 'expired';
        }

        if ($this->isActive()) {
            return 'active';
        }

        if ($this->isUpcoming()) {
            return 'draft';
        }

        return 'completed';
    }

    /**
     * Obtener la etiqueta del estado
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? 'Desconocido';
    }

    /**
     * Obtener la clase CSS para el badge del estado
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'draft' => 'info',
            'completed' => 'primary',
            'expired' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Obtener el tiempo restante hasta que comience la encuesta
     */
    public function getTimeUntilStartAttribute(): string
    {
        if ($this->hasStarted()) {
            return 'Ya comenzó';
        }

        $diff = now()->diffForHumans($this->starts_at, ['parts' => 2]);
        return "Comienza {$diff}";
    }

    /**
     * Obtener el tiempo restante hasta que termine la encuesta
     */
    public function getTimeUntilEndAttribute(): string
    {
        if ($this->hasEnded()) {
            return 'Ya terminó';
        }

        $diff = now()->diffForHumans($this->ends_at, ['parts' => 2]);
        return "Termina {$diff}";
    }

    /**
     * Obtener la duración total de la encuesta
     */
    public function getDurationAttribute(): string
    {
        $duration = $this->starts_at->diffInDays($this->ends_at);
        
        if ($duration == 0) {
            $hours = $this->starts_at->diffInHours($this->ends_at);
            return "{$hours} horas";
        }

        return "{$duration} días";
    }

    /**
     * Obtener estadísticas de respuestas
     */
    public function getResponseStatsAttribute(): array
    {
        $totalResponses = $this->responses()->count();
        $anonymousResponses = $this->anonymousResponses()->count();
        $userResponses = $this->userResponses()->count();
        $uniqueUsers = $this->userResponses()->distinct('user_id')->count();

        return [
            'total_responses' => $totalResponses,
            'anonymous_responses' => $anonymousResponses,
            'user_responses' => $userResponses,
            'unique_users' => $uniqueUsers,
            'response_rate' => $totalResponses > 0 ? round(($totalResponses / max($uniqueUsers, 1)) * 100, 2) : 0
        ];
    }

    /**
     * Obtener estadísticas generales de encuestas
     */
    public static function getStats(array $filters = []): array
    {
        $query = self::query();

        // Aplicar filtros si se proporcionan
        if (isset($filters['status'])) {
            switch ($filters['status']) {
                case 'active':
                    $query->active();
                    break;
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'past':
                    $query->past();
                    break;
            }
        }

        if (isset($filters['anonymous_allowed'])) {
            if ($filters['anonymous_allowed']) {
                $query->anonymousAllowed();
            } else {
                $query->anonymousNotAllowed();
            }
        }

        if (isset($filters['visible_results'])) {
            if ($filters['visible_results']) {
                $query->resultsVisible();
            } else {
                $query->resultsHidden();
            }
        }

        $total = $query->count();
        $active = $query->getQuery()->newQuery()->active()->count();
        $upcoming = $query->getQuery()->newQuery()->upcoming()->count();
        $past = $query->getQuery()->newQuery()->past()->count();
        $anonymousAllowed = $query->getQuery()->newQuery()->anonymousAllowed()->count();
        $resultsVisible = $query->getQuery()->newQuery()->resultsVisible()->count();

        return [
            'total' => $total,
            'active' => $active,
            'upcoming' => $upcoming,
            'past' => $past,
            'anonymous_allowed' => $anonymousAllowed,
            'anonymous_not_allowed' => $total - $anonymousAllowed,
            'results_visible' => $resultsVisible,
            'results_hidden' => $total - $resultsVisible,
        ];
    }

    /**
     * Obtener encuestas recomendadas para un usuario
     */
    public static function getRecommendedForUser(User $user, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()
                    ->anonymousNotAllowed()
                    ->whereNotIn('id', function ($query) use ($user) {
                        $query->select('survey_id')
                              ->from('survey_responses')
                              ->where('user_id', $user->id);
                    })
                    ->orderBy('starts_at')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtener encuestas populares (con más respuestas)
     */
    public static function getPopularSurveys(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::withCount('responses')
                    ->orderBy('responses_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtener encuestas que expiran pronto
     */
    public static function getExpiringSoon(int $days = 7, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::expiringSoon($days)
                    ->orderBy('ends_at')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtener encuestas activas hoy
     */
    public static function getActiveToday(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::activeToday()
                    ->orderBy('starts_at')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Evento cuando se crea una encuesta
        static::creating(function ($survey) {
            // Validar que la fecha de fin sea posterior a la de inicio
            if ($survey->ends_at <= $survey->starts_at) {
                throw new \InvalidArgumentException('La fecha de fin debe ser posterior a la fecha de inicio');
            }
        });

        // Evento cuando se actualiza una encuesta
        static::updating(function ($survey) {
            // Validar que la fecha de fin sea posterior a la de inicio
            if ($survey->ends_at <= $survey->starts_at) {
                throw new \InvalidArgumentException('La fecha de fin debe ser posterior a la fecha de inicio');
            }
        });
    }
}
