<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OrganizationRole",
 *     title="OrganizationRole",
 *     description="Modelo de rol de organización",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="organization_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Gestor de Proyectos"),
 *     @OA\Property(property="slug", type="string", example="gestor-proyectos"),
 *     @OA\Property(property="description", type="string", example="Responsable de gestionar proyectos"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"project.view", "project.create"}),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(
 *         property="organization",
 *         ref="#/components/schemas/Organization",
 *         description="Organización a la que pertenece el rol"
 *     ),
 *     @OA\Property(
 *         property="users_count",
 *         type="integer",
 *         description="Número de usuarios con este rol",
 *         example=5
 *     )
 * )
 */
class OrganizationRoleResource extends JsonResource
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
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'permissions' => $this->permissions,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relaciones cargadas si están disponibles
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                ];
            }),
            'users_count' => $this->whenCounted('users'),
        ];
    }
}
