<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserOrganizationRole",
 *     title="UserOrganizationRole",
 *     description="Modelo de asignación de rol de usuario en una organización",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="organization_id", type="integer", example=1),
 *     @OA\Property(property="organization_role_id", type="integer", example=1),
 *     @OA\Property(property="assigned_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User",
 *         description="Usuario asignado"
 *     ),
 *     @OA\Property(
 *         property="organization",
 *         ref="#/components/schemas/Organization",
 *         description="Organización donde se asigna el rol"
 *     ),
 *     @OA\Property(
 *         property="organizationRole",
 *         ref="#/components/schemas/OrganizationRole",
 *         description="Rol asignado en la organización"
 *     )
 * )
 */
class UserOrganizationRoleResource extends JsonResource
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
            'user_id' => $this->user_id,
            'organization_id' => $this->organization_id,
            'organization_role_id' => $this->organization_role_id,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relaciones cargadas si están disponibles
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                ];
            }),
            'organizationRole' => $this->whenLoaded('organizationRole', function () {
                return [
                    'id' => $this->organizationRole->id,
                    'name' => $this->organizationRole->name,
                    'slug' => $this->organizationRole->slug,
                    'description' => $this->organizationRole->description,
                ];
            }),
        ];
    }
}
