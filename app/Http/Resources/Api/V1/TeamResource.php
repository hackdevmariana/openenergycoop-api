<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Team',
    title: 'Team',
    description: 'Modelo de equipo',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Green Warriors'),
        new OA\Property(property: 'slug', type: 'string', example: 'green-warriors'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Un equipo comprometido con la energía renovable'),
        new OA\Property(property: 'is_open', type: 'boolean', example: true),
        new OA\Property(property: 'max_members', type: 'integer', nullable: true, example: 25),
        new OA\Property(property: 'logo_path', type: 'string', nullable: true, example: '/storage/teams/logo.jpg'),
        new OA\Property(property: 'members_count', type: 'integer', example: 12),
        new OA\Property(property: 'available_slots', type: 'integer', nullable: true, example: 13),
        new OA\Property(property: 'is_full', type: 'boolean', example: false),
        new OA\Property(property: 'can_join', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
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
            property: 'created_by',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
                new OA\Property(property: 'email', type: 'string', example: 'juan@example.com')
            ]
        ),
        new OA\Property(
            property: 'members',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'María García'),
                    new OA\Property(property: 'role', type: 'string', example: 'member'),
                    new OA\Property(property: 'joined_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
                ]
            )
        ),
        new OA\Property(
            property: 'current_challenges',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Desafío Solar Mensual'),
                    new OA\Property(property: 'progress_kwh', type: 'number', format: 'float', example: 1250.50),
                    new OA\Property(property: 'target_kwh', type: 'number', format: 'float', example: 2000.00),
                    new OA\Property(property: 'progress_percentage', type: 'number', format: 'float', example: 62.53)
                ]
            )
        )
    ]
)]
class TeamResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'is_open' => $this->is_open,
            'max_members' => $this->max_members,
            'logo_path' => $this->logo_path,
            'members_count' => $this->when(
                $this->relationLoaded('activeMemberships') || isset($this->active_memberships_count),
                fn() => $this->active_memberships_count ?? $this->activeMemberships->count()
            ),
            'available_slots' => $this->when(
                $this->max_members !== null,
                fn() => $this->available_slots
            ),
            'is_full' => $this->isFull(),
            'can_join' => $this->when(
                auth()->check(),
                fn() => $this->canJoin(auth()->user())
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relaciones
            'organization' => $this->when(
                $this->relationLoaded('organization'),
                fn() => $this->organization ? [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                ] : null
            ),

            'created_by' => $this->when(
                $this->relationLoaded('createdBy'),
                fn() => [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ]
            ),

            'members' => $this->when(
                $this->relationLoaded('activeMemberships'),
                fn() => $this->activeMemberships->map(function ($membership) {
                    return [
                        'id' => $membership->user->id,
                        'name' => $membership->user->name,
                        'email' => $membership->user->email,
                        'role' => $membership->role,
                        'joined_at' => $membership->joined_at,
                        'is_admin' => $membership->isAdmin(),
                        'is_moderator' => $membership->isModerator(),
                        'has_admin_privileges' => $membership->hasAdminPrivileges(),
                    ];
                })
            ),

            'current_challenges' => $this->when(
                $this->relationLoaded('challengeProgress'),
                fn() => $this->challengeProgress->filter(function ($progress) {
                    return $progress->challenge && $progress->challenge->isCurrentlyActive();
                })->map(function ($progress) {
                    return [
                        'id' => $progress->challenge->id,
                        'name' => $progress->challenge->name,
                        'type' => $progress->challenge->type,
                        'progress_kwh' => $progress->progress_kwh,
                        'target_kwh' => $progress->challenge->target_kwh,
                        'progress_percentage' => $progress->progress_percentage,
                        'remaining_kwh' => $progress->remaining_kwh,
                        'is_completed' => $progress->isCompleted(),
                        'completed_at' => $progress->completed_at,
                        'team_rank' => $progress->team_rank,
                        'start_date' => $progress->challenge->start_date,
                        'end_date' => $progress->challenge->end_date,
                        'days_remaining' => $progress->challenge->days_remaining,
                    ];
                })->values()
            ),

            // Estadísticas adicionales cuando se solicita detalle completo
            'statistics' => $this->when(
                $request->routeIs('*.show') && $this->relationLoaded('activeMemberships'),
                fn() => [
                    'total_members' => $this->activeMemberships->count(),
                    'admins_count' => $this->activeMemberships->where('role', 'admin')->count(),
                    'moderators_count' => $this->activeMemberships->where('role', 'moderator')->count(),
                    'members_count' => $this->activeMemberships->where('role', 'member')->count(),
                    'recent_members' => $this->activeMemberships
                        ->sortByDesc('joined_at')
                        ->take(5)
                        ->map(function ($membership) {
                            return [
                                'name' => $membership->user->name,
                                'joined_at' => $membership->joined_at,
                                'role' => $membership->role,
                            ];
                        })->values(),
                ]
            ),

            // Meta información
            'meta' => [
                'can_edit' => $this->when(
                    auth()->check(),
                    fn() => auth()->user()->can('update', $this->resource)
                ),
                'can_delete' => $this->when(
                    auth()->check(),
                    fn() => auth()->user()->can('delete', $this->resource)
                ),
                'is_member' => $this->when(
                    auth()->check(),
                    fn() => $this->hasMember(auth()->user())
                ),
                'is_admin' => $this->when(
                    auth()->check(),
                    fn() => $this->isAdmin(auth()->user())
                ),
                'logo_url' => $this->logo_path 
                    ? asset('storage/' . $this->logo_path)
                    : "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&color=7F9CF5&background=EBF4FF",
            ],
        ];
    }
}
