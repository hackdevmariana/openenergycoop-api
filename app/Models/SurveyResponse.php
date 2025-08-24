<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SurveyResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'survey_id',
        'user_id',
        'response_data',
    ];

    protected $casts = [
        'response_data' => 'array',
        'deleted_at' => 'datetime',
    ];

    // Constantes para tipos de respuesta
    const RESPONSE_TYPES = [
        'anonymous' => 'Anónima',
        'identified' => 'Identificada'
    ];

    /**
     * Relación con la encuesta
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Relación con el usuario (puede ser null para respuestas anónimas)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para respuestas anónimas
     */
    public function scopeAnonymous(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope para respuestas identificadas
     */
    public function scopeIdentified(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope para respuestas por encuesta
     */
    public function scopeBySurvey(Builder $query, int $surveyId): Builder
    {
        return $query->where('survey_id', $surveyId);
    }

    /**
     * Scope para respuestas por usuario
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para respuestas recientes
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope para respuestas por rango de fechas
     */
    public function scopeByDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope para respuestas de hoy
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope para respuestas de esta semana
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope para respuestas de este mes
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    /**
     * Scope para buscar en datos de respuesta
     */
    public function scopeSearchInResponse(Builder $query, string $search): Builder
    {
        return $query->where('response_data', 'like', "%{$search}%");
    }

    /**
     * Scope para respuestas con datos específicos
     */
    public function scopeWithResponseData(Builder $query, string $key, $value): Builder
    {
        return $query->where("response_data->{$key}", $value);
    }

    /**
     * Scope para respuestas con datos que contengan un valor
     */
    public function scopeWithResponseDataContains(Builder $query, string $key, string $value): Builder
    {
        return $query->where("response_data->{$key}", 'like', "%{$value}%");
    }

    /**
     * Verificar si la respuesta es anónima
     */
    public function isAnonymous(): bool
    {
        return is_null($this->user_id);
    }

    /**
     * Verificar si la respuesta es identificada
     */
    public function isIdentified(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Verificar si la respuesta pertenece a un usuario específico
     */
    public function belongsToUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->user_id === $user->id;
    }

    /**
     * Verificar si la respuesta puede ser vista por un usuario
     */
    public function canBeViewedBy(?User $user): bool
    {
        // Si la respuesta es anónima, solo administradores pueden verla
        if ($this->isAnonymous()) {
            return $user && $user->hasRole('admin');
        }

        // Si la respuesta es del usuario, puede verla
        if ($this->belongsToUser($user)) {
            return true;
        }

        // Administradores pueden ver todas las respuestas
        if ($user && $user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si la respuesta puede ser editada por un usuario
     */
    public function canBeEditedBy(?User $user): bool
    {
        // Solo el usuario que creó la respuesta puede editarla
        if ($this->belongsToUser($user)) {
            return true;
        }

        // Administradores pueden editar todas las respuestas
        if ($user && $user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si la respuesta puede ser eliminada por un usuario
     */
    public function canBeDeletedBy(?User $user): bool
    {
        // Solo el usuario que creó la respuesta puede eliminarla
        if ($this->belongsToUser($user)) {
            return true;
        }

        // Administradores pueden eliminar todas las respuestas
        if ($user && $user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Obtener el tipo de respuesta
     */
    public function getResponseTypeAttribute(): string
    {
        return $this->isAnonymous() ? 'anonymous' : 'identified';
    }

    /**
     * Obtener la etiqueta del tipo de respuesta
     */
    public function getResponseTypeLabelAttribute(): string
    {
        return self::RESPONSE_TYPES[$this->response_type] ?? 'Desconocido';
    }

    /**
     * Obtener la clase CSS para el badge del tipo de respuesta
     */
    public function getResponseTypeBadgeClassAttribute(): string
    {
        return match ($this->response_type) {
            'anonymous' => 'warning',
            'identified' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Obtener el tiempo transcurrido desde la respuesta
     */
    public function getTimeSinceResponseAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Obtener el tiempo transcurrido en formato legible
     */
    public function getTimeSinceResponseDetailedAttribute(): string
    {
        $diff = $this->created_at->diff(now());
        
        if ($diff->days > 0) {
            return "hace {$diff->days} días";
        }
        
        if ($diff->h > 0) {
            return "hace {$diff->h} horas";
        }
        
        if ($diff->i > 0) {
            return "hace {$diff->i} minutos";
        }
        
        return "hace unos segundos";
    }

    /**
     * Obtener el nombre del respondente
     */
    public function getRespondentNameAttribute(): string
    {
        if ($this->isAnonymous()) {
            return 'Usuario Anónimo';
        }

        return $this->user ? $this->user->name : 'Usuario Desconocido';
    }

    /**
     * Obtener el email del respondente
     */
    public function getRespondentEmailAttribute(): ?string
    {
        if ($this->isAnonymous()) {
            return null;
        }

        return $this->user ? $this->user->email : null;
    }

    /**
     * Obtener el avatar del respondente
     */
    public function getRespondentAvatarAttribute(): ?string
    {
        if ($this->isAnonymous()) {
            return null;
        }

        return $this->user ? $this->user->profile_photo_url : null;
    }

    /**
     * Obtener un valor específico de los datos de respuesta
     */
    public function getResponseValue(string $key, $default = null)
    {
        return data_get($this->response_data, $key, $default);
    }

    /**
     * Establecer un valor en los datos de respuesta
     */
    public function setResponseValue(string $key, $value): void
    {
        $data = $this->response_data ?? [];
        data_set($data, $key, $value);
        $this->response_data = $data;
    }

    /**
     * Verificar si existe una clave en los datos de respuesta
     */
    public function hasResponseKey(string $key): bool
    {
        return data_get($this->response_data, $key) !== null;
    }

    /**
     * Obtener todas las claves de los datos de respuesta
     */
    public function getResponseKeysAttribute(): array
    {
        if (!is_array($this->response_data)) {
            return [];
        }

        return array_keys($this->response_data);
    }

    /**
     * Obtener el número de campos en la respuesta
     */
    public function getResponseFieldCountAttribute(): int
    {
        if (!is_array($this->response_data)) {
            return 0;
        }

        return count($this->response_data);
    }

    /**
     * Obtener estadísticas de respuestas por encuesta
     */
    public static function getSurveyStats(int $surveyId): array
    {
        $totalResponses = self::bySurvey($surveyId)->count();
        $anonymousResponses = self::bySurvey($surveyId)->anonymous()->count();
        $identifiedResponses = self::bySurvey($surveyId)->identified()->count();
        $uniqueUsers = self::bySurvey($surveyId)->identified()->distinct('user_id')->count();
        $todayResponses = self::bySurvey($surveyId)->today()->count();
        $thisWeekResponses = self::bySurvey($surveyId)->thisWeek()->count();
        $thisMonthResponses = self::bySurvey($surveyId)->thisMonth()->count();

        return [
            'total_responses' => $totalResponses,
            'anonymous_responses' => $anonymousResponses,
            'identified_responses' => $identifiedResponses,
            'unique_users' => $uniqueUsers,
            'today_responses' => $todayResponses,
            'this_week_responses' => $thisWeekResponses,
            'this_month_responses' => $thisMonthResponses,
            'response_rate' => $totalResponses > 0 ? round(($totalResponses / max($uniqueUsers, 1)) * 100, 2) : 0
        ];
    }

    /**
     * Obtener estadísticas de respuestas por usuario
     */
    public static function getUserStats(int $userId): array
    {
        $totalResponses = self::byUser($userId)->count();
        $recentResponses = self::byUser($userId)->recent(30)->count();
        $thisMonthResponses = self::byUser($userId)->thisMonth()->count();
        $surveysResponded = self::byUser($userId)->distinct('survey_id')->count();

        return [
            'total_responses' => $totalResponses,
            'recent_responses' => $recentResponses,
            'this_month_responses' => $thisMonthResponses,
            'surveys_responded' => $surveysResponded,
            'average_responses_per_survey' => $surveysResponded > 0 ? round($totalResponses / $surveysResponded, 2) : 0
        ];
    }

    /**
     * Obtener estadísticas generales de respuestas
     */
    public static function getStats(array $filters = []): array
    {
        $query = self::query();

        // Aplicar filtros si se proporcionan
        if (isset($filters['survey_id'])) {
            $query->bySurvey($filters['survey_id']);
        }

        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (isset($filters['response_type'])) {
            if ($filters['response_type'] === 'anonymous') {
                $query->anonymous();
            } elseif ($filters['response_type'] === 'identified') {
                $query->identified();
            }
        }

        if (isset($filters['date_range'])) {
            $query->byDateRange($filters['date_range']['from'], $filters['date_range']['to']);
        }

        $total = $query->count();
        $anonymous = $query->getQuery()->newQuery()->anonymous()->count();
        $identified = $query->getQuery()->newQuery()->identified()->count();
        $today = $query->getQuery()->newQuery()->today()->count();
        $thisWeek = $query->getQuery()->newQuery()->thisWeek()->count();
        $thisMonth = $query->getQuery()->newQuery()->thisMonth()->count();

        return [
            'total' => $total,
            'anonymous' => $anonymous,
            'identified' => $identified,
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'anonymous_percentage' => $total > 0 ? round(($anonymous / $total) * 100, 2) : 0,
            'identified_percentage' => $total > 0 ? round(($identified / $total) * 100, 2) : 0
        ];
    }

    /**
     * Obtener respuestas populares (con más datos)
     */
    public static function getPopularResponses(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::withCount('survey')
                    ->orderBy('response_field_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtener respuestas recientes
     */
    public static function getRecentResponses(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return self::with(['survey', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtener respuestas de una encuesta específica
     */
    public static function getSurveyResponses(int $surveyId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return self::bySurvey($surveyId)
                    ->with(['user'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtener respuestas de un usuario específico
     */
    public static function getUserResponses(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return self::byUser($userId)
                    ->with(['survey'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Verificar si un usuario ha respondido una encuesta específica
     */
    public static function hasUserRespondedToSurvey(int $userId, int $surveyId): bool
    {
        return self::byUser($userId)->bySurvey($surveyId)->exists();
    }

    /**
     * Obtener la respuesta de un usuario a una encuesta específica
     */
    public static function getUserSurveyResponse(int $userId, int $surveyId): ?self
    {
        return self::byUser($userId)->bySurvey($surveyId)->first();
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Evento cuando se crea una respuesta
        static::creating(function ($response) {
            // Validar que los datos de respuesta sean un array válido
            if (!is_array($response->response_data)) {
                throw new \InvalidArgumentException('Los datos de respuesta deben ser un array válido');
            }

            // Validar que la encuesta exista y esté activa
            $survey = Survey::find($response->survey_id);
            if (!$survey) {
                throw new \InvalidArgumentException('La encuesta especificada no existe');
            }

            if (!$survey->isActive()) {
                throw new \InvalidArgumentException('La encuesta no está activa');
            }

            // Si la encuesta no permite respuestas anónimas, el usuario debe estar autenticado
            if (!$survey->allowsAnonymous() && !$response->user_id) {
                throw new \InvalidArgumentException('Esta encuesta no permite respuestas anónimas');
            }

            // Si el usuario está autenticado, verificar que no haya respondido ya
            if ($response->user_id && $survey->hasUserResponded(User::find($response->user_id))) {
                throw new \InvalidArgumentException('El usuario ya ha respondido esta encuesta');
            }
        });

        // Evento cuando se actualiza una respuesta
        static::updating(function ($response) {
            // Validar que los datos de respuesta sean un array válido
            if (!is_array($response->response_data)) {
                throw new \InvalidArgumentException('Los datos de respuesta deben ser un array válido');
            }
        });
    }
}
