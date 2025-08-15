<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamChallengeProgress extends Model
{
    use HasFactory;

    protected $table = 'team_challenge_progress';

    protected $fillable = [
        'team_id',
        'challenge_id',
        'progress_kwh',
        'completed_at',
    ];

    protected $casts = [
        'progress_kwh' => 'decimal:2',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el equipo del progreso
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Obtener el desafío del progreso
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Scope para progreso completado
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope para progreso en curso
     */
    public function scopeInProgress($query)
    {
        return $query->whereNull('completed_at');
    }

    /**
     * Scope para progreso por equipo
     */
    public function scopeByTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope para progreso por desafío
     */
    public function scopeByChallenge($query, int $challengeId)
    {
        return $query->where('challenge_id', $challengeId);
    }

    /**
     * Verificar si el desafío está completado
     */
    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    /**
     * Obtener el porcentaje de progreso
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->challenge || $this->challenge->target_kwh == 0) {
            return 0;
        }

        $percentage = ($this->progress_kwh / $this->challenge->target_kwh) * 100;
        return min(100, round($percentage, 2));
    }

    /**
     * Obtener kWh restantes para completar
     */
    public function getRemainingKwhAttribute(): float
    {
        if (!$this->challenge) {
            return 0;
        }

        $remaining = $this->challenge->target_kwh - $this->progress_kwh;
        return max(0, round($remaining, 2));
    }

    /**
     * Verificar si el equipo está cerca de completar (>80%)
     */
    public function isNearCompletion(): bool
    {
        return $this->progress_percentage >= 80;
    }

    /**
     * Actualizar progreso
     */
    public function updateProgress(float $kwhToAdd): void
    {
        $newProgress = $this->progress_kwh + $kwhToAdd;
        $this->update(['progress_kwh' => $newProgress]);

        // Verificar si se completó el desafío
        $this->checkCompletion();
    }

    /**
     * Establecer progreso absoluto
     */
    public function setProgress(float $totalKwh): void
    {
        $this->update(['progress_kwh' => $totalKwh]);
        $this->checkCompletion();
    }

    /**
     * Verificar y marcar como completado si se alcanzó el objetivo
     */
    public function checkCompletion(): void
    {
        if (!$this->isCompleted() && 
            $this->challenge && 
            $this->progress_kwh >= $this->challenge->target_kwh) {
            
            $this->update(['completed_at' => now()]);
            
            // Aquí podrías disparar eventos o notificaciones
            $this->onChallengeCompleted();
        }
    }

    /**
     * Marcar manualmente como completado
     */
    public function markAsCompleted(): void
    {
        if (!$this->isCompleted()) {
            $this->update(['completed_at' => now()]);
            $this->onChallengeCompleted();
        }
    }

    /**
     * Resetear progreso
     */
    public function resetProgress(): void
    {
        $this->update([
            'progress_kwh' => 0,
            'completed_at' => null,
        ]);
    }

    /**
     * Obtener el ranking del equipo en el desafío
     */
    public function getTeamRankAttribute(): int
    {
        return static::where('challenge_id', $this->challenge_id)
                    ->where('progress_kwh', '>', $this->progress_kwh)
                    ->count() + 1;
    }

    /**
     * Obtener días desde que se completó
     */
    public function getDaysSinceCompletionAttribute(): ?int
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInDays(now());
    }

    /**
     * Obtener estadísticas de progreso por equipo
     */
    public static function getTeamStats(int $teamId): array
    {
        $progress = static::byTeam($teamId);
        
        return [
            'total_challenges' => $progress->count(),
            'completed_challenges' => $progress->completed()->count(),
            'in_progress_challenges' => $progress->inProgress()->count(),
            'total_kwh_progress' => $progress->sum('progress_kwh'),
            'average_completion_rate' => static::getAverageCompletionRate($teamId),
        ];
    }

    /**
     * Obtener tasa de finalización promedio de un equipo
     */
    public static function getAverageCompletionRate(int $teamId): float
    {
        $progressRecords = static::byTeam($teamId)->with('challenge')->get();
        
        if ($progressRecords->isEmpty()) {
            return 0;
        }

        $totalPercentage = $progressRecords->sum(function ($progress) {
            return $progress->progress_percentage;
        });

        return round($totalPercentage / $progressRecords->count(), 2);
    }

    /**
     * Obtener el progreso más reciente de un equipo
     */
    public static function getRecentProgressByTeam(int $teamId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::byTeam($teamId)
                    ->with(['challenge'])
                    ->orderBy('updated_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtener equipos líderes en un desafío
     */
    public static function getLeaderboard(int $challengeId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::byChallenge($challengeId)
                    ->with(['team'])
                    ->orderBy('progress_kwh', 'desc')
                    ->orderBy('completed_at', 'asc') // Los que completaron primero
                    ->limit($limit)
                    ->get();
    }

    /**
     * Acción a ejecutar cuando se completa un desafío
     */
    protected function onChallengeCompleted(): void
    {
        // Aquí puedes agregar lógica para:
        // - Otorgar puntos al equipo
        // - Enviar notificaciones
        // - Crear achievements
        // - Registrar en logs de actividad
        
        // Ejemplo: Agregar puntos a los miembros del equipo
        if ($this->challenge && $this->challenge->points_reward > 0) {
            $pointsPerMember = intval($this->challenge->points_reward / $this->team->members_count);
            
            foreach ($this->team->members as $member) {
                $profile = $member->userProfile;
                if ($profile) {
                    $profile->addPoints($pointsPerMember);
                }
            }
        }
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Verificar finalización al actualizar progreso
        static::updated(function ($progress) {
            if ($progress->wasChanged('progress_kwh')) {
                $progress->checkCompletion();
            }
        });

        // Validar que no haya duplicados
        static::creating(function ($progress) {
            $existing = static::where('team_id', $progress->team_id)
                             ->where('challenge_id', $progress->challenge_id)
                             ->exists();
            
            if ($existing) {
                throw new \Exception('Team is already participating in this challenge');
            }
        });
    }
}
