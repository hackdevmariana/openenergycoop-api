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
        return [
            'id' => $this->id,
            'image' => $this->image,
            'mobile_image' => $this->mobile_image,
            'internal_link' => $this->internal_link,
            'url' => $this->url,
            'position' => $this->position,
            'active' => $this->active,
            'alt_text' => $this->alt_text,
            'title' => $this->title,
            'description' => $this->description,
            'exhibition_beginning' => $this->exhibition_beginning,
            'exhibition_end' => $this->exhibition_end,
            'banner_type' => $this->banner_type,
            'display_rules' => $this->display_rules,
            'click_count' => $this->click_count,
            'impression_count' => $this->impression_count,
            'is_draft' => $this->is_draft,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relaciones
            'organization' => $this->whenLoaded('organization'),
            'created_by' => $this->whenLoaded('createdBy'),
            'updated_by' => $this->whenLoaded('updatedBy'),

            // Computed fields
            'is_published' => $this->isPublished(),
            'is_currently_displaying' => $this->isCurrentlyDisplaying(),
            'click_through_rate' => $this->getClickThroughRate(),
            'type_label' => $this->getTypeLabel(),
        ];
    }
}
