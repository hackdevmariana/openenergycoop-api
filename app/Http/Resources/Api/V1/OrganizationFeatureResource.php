<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrganizationFeatureResource',
    title: 'Organization Feature Resource',
    description: 'Resource de característica organizacional',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Gestión de Energía Solar'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Permite gestionar paneles solares'),
        new OA\Property(property: 'organization_id', type: 'integer', example: 1),
        new OA\Property(property: 'is_enabled', type: 'boolean', example: true),
        new OA\Property(property: 'config', type: 'object', nullable: true, example: ['max_panels' => 100]),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class OrganizationFeatureResource extends JsonResource
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
