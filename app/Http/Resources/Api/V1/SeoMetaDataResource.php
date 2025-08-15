<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SeoMetaDataResource',
    title: 'SEO Metadata Resource',
    description: 'Resource de metadatos SEO',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', nullable: true, example: 'Página de Inicio - OpenEnergyCoop'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Únete a la cooperativa energética más sostenible'),
        new OA\Property(property: 'keywords', type: 'string', nullable: true, example: 'energía, sostenible, cooperativa'),
        new OA\Property(property: 'canonical_url', type: 'string', nullable: true, example: 'https://openenergycoop.com/'),
        new OA\Property(property: 'robots', type: 'string', nullable: true, example: 'index,follow'),
        new OA\Property(property: 'og_title', type: 'string', nullable: true, example: 'OpenEnergyCoop'),
        new OA\Property(property: 'og_description', type: 'string', nullable: true, example: 'Tu cooperativa energética'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class SeoMetaDataResource extends JsonResource
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
