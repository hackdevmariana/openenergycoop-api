<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'HeroResource',
    title: 'Hero Resource',
    description: 'Resource de banner principal',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'text', type: 'string', nullable: true, example: 'Bienvenido a OpenEnergyCoop'),
        new OA\Property(property: 'subtext', type: 'string', nullable: true, example: 'Tu cooperativa energética sostenible'),
        new OA\Property(property: 'text_button', type: 'string', nullable: true, example: 'Únete ahora'),
        new OA\Property(property: 'cta_link_external', type: 'string', nullable: true, example: 'https://example.com'),
        new OA\Property(property: 'position', type: 'integer', example: 1),
        new OA\Property(property: 'active', type: 'boolean', example: true),
        new OA\Property(property: 'exhibition_beginning', type: 'string', format: 'date-time', nullable: true, example: '2024-01-15T00:00:00Z'),
        new OA\Property(property: 'exhibition_end', type: 'string', format: 'date-time', nullable: true, example: '2024-12-31T23:59:59Z'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class HeroResource extends JsonResource
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
