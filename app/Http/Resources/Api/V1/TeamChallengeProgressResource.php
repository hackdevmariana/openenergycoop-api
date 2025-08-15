<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TeamChallengeProgressResource',
    title: 'Team Challenge Progress Resource',
    description: 'Resource de progreso de equipo en desafío',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'team_id', type: 'integer', example: 1),
        new OA\Property(property: 'challenge_id', type: 'integer', example: 1),
        new OA\Property(property: 'current_value', type: 'number', format: 'float', nullable: true, example: 75.5),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'completed', 'failed', 'paused'], example: 'active'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Progreso actualizado manualmente'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class TeamChallengeProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
