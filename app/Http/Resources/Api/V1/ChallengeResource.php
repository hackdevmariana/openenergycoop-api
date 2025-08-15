<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Challenge',
    title: 'Challenge',
    description: 'Modelo de desafío energético',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Desafío Solar Mensual'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Genera la máxima energía solar durante este mes'),
        new OA\Property(property: 'type', type: 'string', enum: ['individual', 'team', 'organization'], example: 'team'),
        new OA\Property(property: 'target_kwh', type: 'number', format: 'float', example: 2000.00),
        new OA\Property(property: 'points_reward', type: 'integer', example: 500),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2024-01-01'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2024-01-31'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'criteria', type: 'object', nullable: true),
        new OA\Property(property: 'icon', type: 'string', nullable: true, example: 'solar-panel'),
        new OA\Property(property: 'is_currently_active', type: 'boolean', example: true),
        new OA\Property(property: 'has_started', type: 'boolean', example: true),
        new OA\Property(property: 'has_ended', type: 'boolean', example: false),
        new OA\Property(property: 'days_remaining', type: 'integer', example: 15),
        new OA\Property(property: 'duration_in_days', type: 'integer', example: 31),
        new OA\Property(property: 'participating_teams_count', type: 'integer', example: 8),
        new OA\Property(property: 'completed_teams_count', type: 'integer', example: 3),
        new OA\Property(property: 'completion_rate', type: 'number', format: 'float', example: 37.50),
        new OA\Property(property: 'average_progress', type: 'number', format: 'float', example: 1250.75),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
        new OA\Property(
            property: 'organization',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Cooperativa Verde')
            ]
        ),
        new OA\Property(
            property: 'leader_team',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Green Warriors'),
                new OA\Property(property: 'progress_kwh', type: 'number', format: 'float', example: 1800.50)
            ]
        ),
        new OA\Property(
            property: 'top_teams',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'rank', type: 'integer', example: 1),
                    new OA\Property(property: 'team_id', type: 'integer', example: 1),
                    new OA\Property(property: 'team_name', type: 'string', example: 'Green Warriors'),
                    new OA\Property(property: 'progress_kwh', type: 'number', format: 'float', example: 1800.50),
                    new OA\Property(property: 'progress_percentage', type: 'number', format: 'float', example: 90.03)
                ]
            )
        )
    ]
)]
class ChallengeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'target_kwh' => $this->target_kwh,
            'points_reward' => $this->points_reward,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'criteria' => $this->criteria,
            'icon' => $this->icon,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Estados calculados
            'is_currently_active' => $this->isCurrentlyActive(),
            'has_started' => $this->hasStarted(),
            'has_ended' => $this->hasEnded(),
            'days_remaining' => $this->days_remaining,
            'duration_in_days' => $this->duration_in_days,

            // Estadísticas de participación
            'participating_teams_count' => $this->when(
                isset($this->team_progress_count),
                $this->team_progress_count
            ),
            'completed_teams_count' => $this->when(
                isset($this->completed_teams_count),
                $this->completed_teams_count
            ),
            'completion_rate' => $this->when(
                isset($this->team_progress_count) && isset($this->completed_teams_count),
                fn() => $this->team_progress_count > 0 
                    ? round(($this->completed_teams_count / $this->team_progress_count) * 100, 2)
                    : 0
            ),
            'average_progress' => $this->when(
                $this->relationLoaded('teamProgress'),
                fn() => round($this->teamProgress->avg('progress_kwh') ?? 0, 2)
            ),

            // Relaciones
            'organization' => $this->when(
                $this->relationLoaded('organization'),
                fn() => $this->organization ? [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                ] : null
            ),

            'leader_team' => $this->when(
                $this->relationLoaded('teamProgress'),
                fn() => $this->getLeaderTeamData()
            ),

            'top_teams' => $this->when(
                $this->relationLoaded('teamProgress') && $request->routeIs('*.show'),
                fn() => $this->getTopTeamsData(5)
            ),

            // Información detallada para vista individual
            'detailed_stats' => $this->when(
                $request->routeIs('*.show') && $this->relationLoaded('teamProgress'),
                fn() => [
                    'total_progress_kwh' => round($this->teamProgress->sum('progress_kwh'), 2),
                    'highest_progress' => round($this->teamProgress->max('progress_kwh') ?? 0, 2),
                    'lowest_progress' => round($this->teamProgress->min('progress_kwh') ?? 0, 2),
                    'teams_near_completion' => $this->teamProgress->filter(function ($progress) {
                        return $progress->progress_percentage >= 80 && !$progress->isCompleted();
                    })->count(),
                    'recently_completed' => $this->teamProgress->filter(function ($progress) {
                        return $progress->isCompleted() && 
                               $progress->completed_at >= now()->subDays(7);
                    })->count(),
                ]
            ),

            // Meta información
            'meta' => [
                'can_participate' => $this->when(
                    auth()->check() && $this->type === 'team',
                    fn() => $this->canUserParticipate()
                ),
                'user_team_progress' => $this->when(
                    auth()->check() && $this->type === 'team',
                    fn() => $this->getUserTeamProgress()
                ),
                'difficulty_level' => $this->getDifficultyLevel(),
                'reward_tier' => $this->getRewardTier(),
                'icon_url' => $this->icon 
                    ? asset("images/challenges/icons/{$this->icon}.svg")
                    : null,
            ],
        ];
    }

    /**
     * Obtener datos del equipo líder
     */
    private function getLeaderTeaderData(): ?array
    {
        if (!$this->relationLoaded('teamProgress')) {
            return null;
        }

        $leader = $this->teamProgress
            ->sortByDesc('progress_kwh')
            ->first();

        if (!$leader || !$leader->relationLoaded('team')) {
            return null;
        }

        return [
            'id' => $leader->team->id,
            'name' => $leader->team->name,
            'slug' => $leader->team->slug,
            'logo_path' => $leader->team->logo_path,
            'progress_kwh' => $leader->progress_kwh,
            'progress_percentage' => $leader->progress_percentage,
            'is_completed' => $leader->isCompleted(),
            'completed_at' => $leader->completed_at,
        ];
    }

    /**
     * Obtener datos de los equipos top
     */
    private function getTopTeamsData(int $limit = 5): array
    {
        if (!$this->relationLoaded('teamProgress')) {
            return [];
        }

        return $this->teamProgress
            ->sortByDesc('progress_kwh')
            ->take($limit)
            ->map(function ($progress, $index) {
                return [
                    'rank' => $index + 1,
                    'team_id' => $progress->team->id,
                    'team_name' => $progress->team->name,
                    'team_slug' => $progress->team->slug,
                    'team_logo' => $progress->team->logo_path,
                    'progress_kwh' => $progress->progress_kwh,
                    'progress_percentage' => $progress->progress_percentage,
                    'is_completed' => $progress->isCompleted(),
                    'completed_at' => $progress->completed_at,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Verificar si el usuario puede participar
     */
    private function canUserParticipate(): bool
    {
        $user = auth()->user();
        
        // Si no está activo, no se puede participar
        if (!$this->isCurrentlyActive()) {
            return false;
        }

        // Para desafíos de equipo, verificar si el usuario tiene equipos elegibles
        if ($this->type === 'team') {
            $userTeams = \App\Models\Team::whereHas('activeMemberships', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();

            foreach ($userTeams as $team) {
                if ($this->resource->canTeamParticipate($team)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Obtener progreso del equipo del usuario
     */
    private function getUserTeamProgress(): ?array
    {
        $user = auth()->user();
        
        if ($this->type !== 'team') {
            return null;
        }

        $userTeams = \App\Models\Team::whereHas('activeMemberships', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        foreach ($userTeams as $team) {
            $progress = $this->teamProgress->where('team_id', $team->id)->first();
            if ($progress) {
                return [
                    'team_id' => $team->id,
                    'team_name' => $team->name,
                    'progress_kwh' => $progress->progress_kwh,
                    'progress_percentage' => $progress->progress_percentage,
                    'is_completed' => $progress->isCompleted(),
                    'rank' => $progress->team_rank,
                ];
            }
        }

        return null;
    }

    /**
     * Calcular nivel de dificultad basado en target_kwh
     */
    private function getDifficultyLevel(): string
    {
        $target = $this->target_kwh;
        
        if ($target <= 500) return 'easy';
        if ($target <= 2000) return 'medium';
        if ($target <= 10000) return 'hard';
        return 'expert';
    }

    /**
     * Calcular tier de recompensa basado en points_reward
     */
    private function getRewardTier(): string
    {
        $points = $this->points_reward;
        
        if ($points <= 100) return 'bronze';
        if ($points <= 500) return 'silver';
        if ($points <= 1000) return 'gold';
        return 'platinum';
    }

    /**
     * Corregir el nombre del método
     */
    private function getLeaderTeamData(): ?array
    {
        return $this->getLeaderTeaderData();
    }
}
