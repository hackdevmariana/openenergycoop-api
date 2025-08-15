<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FaqResource',
    title: 'FAQ Resource',
    description: 'Resource de pregunta frecuente',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'question', type: 'string', example: '¿Cómo me uno a la cooperativa?'),
        new OA\Property(property: 'answer', type: 'string', example: 'Puedes unirte completando el formulario de registro...'),
        new OA\Property(property: 'topic_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'language', type: 'string', example: 'es'),
        new OA\Property(property: 'is_draft', type: 'boolean', example: false),
        new OA\Property(property: 'is_featured', type: 'boolean', example: false),
        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
        new OA\Property(property: 'views_count', type: 'integer', example: 150),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class FaqResource extends JsonResource
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
