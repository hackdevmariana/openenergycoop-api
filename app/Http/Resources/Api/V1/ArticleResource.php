<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ArticleResource',
    title: 'Article Resource',
    description: 'Resource de artículo',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Energía Solar en España'),
        new OA\Property(property: 'slug', type: 'string', example: 'energia-solar-espana'),
        new OA\Property(property: 'excerpt', type: 'string', nullable: true, example: 'Resumen del artículo sobre energía solar'),
        new OA\Property(property: 'content', type: 'string', example: 'Contenido completo del artículo...'),
        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], example: 'published'),
        new OA\Property(property: 'featured', type: 'boolean', example: false),
        new OA\Property(property: 'views_count', type: 'integer', example: 150),
        new OA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true, example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class ArticleResource extends JsonResource
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
