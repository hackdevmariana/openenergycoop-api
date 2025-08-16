<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrganizationResource',
    title: 'Organization Resource',
    description: 'Resource de organización',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'OpenEnergyCoop Madrid'),
        new OA\Property(property: 'slug', type: 'string', example: 'openenergycoop-madrid'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Cooperativa energética de la Comunidad de Madrid'),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'madrid@openenergycoop.com'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+34 900 123 456'),
        new OA\Property(property: 'website', type: 'string', nullable: true, example: 'https://madrid.openenergycoop.com'),
        new OA\Property(property: 'address', type: 'string', nullable: true, example: 'Calle Mayor 123, 28001 Madrid'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'members_count', type: 'integer', example: 250),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class OrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'domain' => $this->domain,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,

            'css_files' => $this->css_files,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relaciones
            'features' => $this->whenLoaded('features'),
        ];
    }
}
