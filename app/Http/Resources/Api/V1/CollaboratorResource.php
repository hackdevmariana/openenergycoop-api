<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CollaboratorResource',
    title: 'Collaborator Resource',
    description: 'Resource de colaborador',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Ana García'),
        new OA\Property(property: 'position', type: 'string', nullable: true, example: 'Directora de Sostenibilidad'),
        new OA\Property(property: 'department', type: 'string', nullable: true, example: 'Desarrollo'),
        new OA\Property(property: 'bio', type: 'string', nullable: true, example: 'Especialista en energías renovables con 10 años de experiencia'),
        new OA\Property(property: 'email', type: 'string', nullable: true, example: 'ana@openenergycoop.com'),
        new OA\Property(property: 'linkedin_url', type: 'string', nullable: true, example: 'https://linkedin.com/in/anagarcia'),
        new OA\Property(property: 'is_featured', type: 'boolean', example: true),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class CollaboratorResource extends JsonResource
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
