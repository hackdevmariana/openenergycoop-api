<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'target_kwh',
        'points_reward',
        'start_date',
        'end_date',
        'is_active',
        'criteria',
        'icon',
        'organization_id',
    ];

    protected $casts = [
        'target_kwh' => 'decimal:2',
        'points_reward' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'criteria' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener la organización del desafío
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener el progreso de equipos en este desafío
     */
    public function teamProgress(): HasMany
    {
        return $this->hasMany(TeamChallengeProgress::class);
    }

    /**
     * Obtener los equipos participando en este desafío
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_challenge_progress')
                    ->withPivot(['progress_kwh', 'completed_at'])
                    ->withTimestamps();
    }

    /**
     * Scope para desafíos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para desafíos inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope para desafíos por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para desafíos de equipo
     */
    public function scopeTeamChallenges($query)
    {
        return $query->where('type', 'team');
    }

    /**
     * Scope para desafíos individuales
     */
    public function scopeIndividualChallenges($query)
    {
        return $query->where('type', 'individual');
    }

    /**
     * Scope para desafíos de organización
     */
    public function scopeOrganizationChallenges($query)
    {
        return $query->where('type', 'organization');
    }

    /**
     * Scope para desafíos por organización
     */
    public function scopeByOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope para desafíos actuales (en curso)
     */
    public function scopeCurrent($query)
    {
        $now = now()->toDateString();
        return $query->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

    /**
     * Scope para desafíos futuros
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now()->toDateString());
    }

    /**
     * Scope para desafíos pasados
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', now()->toDateString());
    }

    /**
     * Verificar si el desafío está activo
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now()->toDateString();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    /**
     * Verificar si el desafío ha comenzado
     */
    public function hasStarted(): bool
    {
        return $this->start_date <= now()->toDateString();
    }

    /**
     * Verificar si el desafío ha terminado
     */
    public function hasEnded(): bool
    {
        return $this->end_date < now()->toDateString();
    }

    /**
     * Obtener días restantes del desafío
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->hasEnded()) {
            return 0;
        }

        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Obtener duración del desafío en días
     */
    public function getDurationInDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Obtener progreso promedio de los equipos
     */
    public function getAverageProgressAttribute(): float
    {
        $progress = $this->teamProgress()
                         ->avg('progress_kwh');
        
        return round($progress ?? 0, 2);
    }

    /**
     * Obtener número de equipos que han completado el desafío
     */
    public function getCompletedTeamsCountAttribute(): int
    {
        return $this->teamProgress()
                    ->whereNotNull('completed_at')
                    ->count();
    }

    /**
     * Obtener número total de equipos participando
     */
    public function getParticipatingTeamsCountAttribute(): int
    {
        return $this->teamProgress()->count();
    }

    /**
     * Obtener tasa de finalización
     */
    public function getCompletionRateAttribute(): float
    {
        $total = $this->participating_teams_count;
        if ($total === 0) {
            return 0;
        }

        return round(($this->completed_teams_count / $total) * 100, 2);
    }

    /**
     * Obtener el equipo líder en el desafío
     */
    public function getLeaderTeamAttribute(): ?Team
    {
        $progress = $this->teamProgress()
                         ->with('team')
                         ->orderBy('progress_kwh', 'desc')
                         ->first();

        return $progress?->team;
    }

    /**
     * Obtener el ranking de equipos en el desafío
     */
    public function getTeamRanking(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->teamProgress()
                    ->with('team')
                    ->orderBy('progress_kwh', 'desc')
                    ->orderBy('completed_at', 'asc') // Los que completaron primero van arriba
                    ->limit($limit)
                    ->get();
    }

    /**
     * Verificar si un equipo puede participar
     */
    public function canTeamParticipate(Team $team): bool
    {
        // El desafío debe estar activo
        if (!$this->isCurrentlyActive()) {
            return false;
        }

        // Si es específico de organización, el equipo debe pertenecer a ella
        if ($this->organization_id && $team->organization_id !== $this->organization_id) {
            return false;
        }

        // El equipo no debe estar ya participando
        return !$this->teams()->where('teams.id', $team->id)->exists();
    }

    /**
     * Agregar un equipo al desafío
     */
    public function addTeam(Team $team): TeamChallengeProgress
    {
        return TeamChallengeProgress::create([
            'team_id' => $team->id,
            'challenge_id' => $this->id,
            'progress_kwh' => 0,
        ]);
    }

    /**
     * Obtener desafíos recomendados para un equipo
     */
    public static function getRecommendedForTeam(Team $team, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
                    ->current()
                    ->teamChallenges()
                    ->where(function ($query) use ($team) {
                        $query->whereNull('organization_id')
                              ->orWhere('organization_id', $team->organization_id);
                    })
                    ->whereDoesntHave('teams', function ($query) use ($team) {
                        $query->where('teams.id', $team->id);
                    })
                    ->orderBy('points_reward', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Validar fechas
        static::saving(function ($challenge) {
            if ($challenge->start_date > $challenge->end_date) {
                throw new \Exception('Start date cannot be after end date');
            }
        });
    }
}
