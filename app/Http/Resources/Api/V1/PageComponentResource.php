<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PageComponentResource',
    title: 'Page Component Resource',
    description: 'Resource de componente de página',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'page_id', type: 'integer', example: 1),
        new OA\Property(property: 'componentable_type', type: 'string', example: 'App\\Models\\Hero'),
        new OA\Property(property: 'componentable_id', type: 'integer', example: 1),
        new OA\Property(property: 'position', type: 'integer', example: 1),
        new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'language', type: 'string', example: 'es'),
        new OA\Property(property: 'is_draft', type: 'boolean', example: false),
        new OA\Property(property: 'settings', type: 'object', nullable: true, example: ['margin' => '20px']),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class PageComponentResource extends JsonResource
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
