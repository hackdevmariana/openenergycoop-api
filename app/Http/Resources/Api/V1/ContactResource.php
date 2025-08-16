<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ContactResource',
    title: 'Contact Resource',
    description: 'Resource de información de contacto',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'type', type: 'string', enum: ['phone', 'email', 'address', 'office'], example: 'phone'),
        new OA\Property(property: 'label', type: 'string', example: 'Teléfono Principal'),
        new OA\Property(property: 'value', type: 'string', example: '+34 900 123 456'),
        new OA\Property(property: 'is_public', type: 'boolean', example: true),
        new OA\Property(property: 'is_primary', type: 'boolean', example: true),
        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class ContactResource extends JsonResource
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
            'address' => $this->address,
            'icon_address' => $this->icon_address,
            'phone' => $this->phone,
            'icon_phone' => $this->icon_phone,
            'email' => $this->email,
            'icon_email' => $this->icon_email,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'contact_type' => $this->contact_type,
            'business_hours' => $this->business_hours,
            'additional_info' => $this->additional_info,
            'organization_id' => $this->organization_id,
            'is_draft' => $this->is_draft,
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'organization' => $this->whenLoaded('organization'),
            'created_by' => $this->whenLoaded('createdBy'),
            
            // Computed Properties
            'is_published' => $this->isPublished(),
            'has_location' => $this->hasLocation(),
            'type_label' => $this->getTypeLabel(),
            'formatted_address' => $this->getFormattedAddress(),
            'formatted_phone' => $this->getFormattedPhone(),
            'is_business_hours' => $this->isBusinessHours(),
        ];
    }
}
