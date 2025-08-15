<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PageResource',
    title: 'Page Resource',
    description: 'Resource de página',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Inicio'),
        new OA\Property(property: 'slug', type: 'string', example: 'inicio'),
        new OA\Property(property: 'route', type: 'string', nullable: true, example: '/home'),
        new OA\Property(property: 'language', type: 'string', example: 'es'),
        new OA\Property(property: 'is_draft', type: 'boolean', example: false),
        new OA\Property(property: 'template', type: 'string', nullable: true, example: 'default'),
        new OA\Property(property: 'meta_data', type: 'object', nullable: true, example: ['description' => 'Página de inicio']),
        new OA\Property(property: 'requires_auth', type: 'boolean', example: false),
        new OA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true, example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class PageResource extends JsonResource
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
