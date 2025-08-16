<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TeamMembershipResource',
    title: 'Team Membership Resource',
    description: 'Resource de membresía de equipo',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'team_id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 2),
        new OA\Property(property: 'role', type: 'string', enum: ['member', 'admin', 'moderator'], example: 'member'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'joined_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'left_at', type: 'string', format: 'date-time', nullable: true, example: null),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class TeamMembershipResource extends JsonResource
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
