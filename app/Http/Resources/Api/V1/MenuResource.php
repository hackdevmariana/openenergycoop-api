<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MenuResource',
    title: 'Menu Resource',
    description: 'Resource de menú',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Inicio'),
        new OA\Property(property: 'url', type: 'string', nullable: true, example: '/home'),
        new OA\Property(property: 'external_url', type: 'string', nullable: true, example: 'https://external.com'),
        new OA\Property(property: 'target', type: 'string', enum: ['_self', '_blank'], example: '_self'),
        new OA\Property(property: 'icon', type: 'string', nullable: true, example: 'home'),
        new OA\Property(property: 'css_class', type: 'string', nullable: true, example: 'menu-item-home'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
        new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class MenuResource extends JsonResource
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
