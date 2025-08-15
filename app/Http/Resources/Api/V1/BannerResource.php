<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BannerResource',
    title: 'Banner Resource',
    description: 'Resource de banner promocional',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Promoción Especial'),
        new OA\Property(property: 'content', type: 'string', nullable: true, example: 'Únete a nuestra cooperativa energética'),
        new OA\Property(property: 'type', type: 'string', enum: ['info', 'warning', 'success', 'error'], example: 'info'),
        new OA\Property(property: 'position', type: 'string', example: 'top'),
        new OA\Property(property: 'priority', type: 'integer', example: 1),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class BannerResource extends JsonResource
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
