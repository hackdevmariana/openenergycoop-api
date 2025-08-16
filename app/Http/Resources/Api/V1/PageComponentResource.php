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
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'componentable_type' => $this->componentable_type,
            'componentable_id' => $this->componentable_id,
            'position' => $this->position,
            'parent_id' => $this->parent_id,
            'language' => $this->language,
            'organization_id' => $this->organization_id,
            'is_draft' => $this->is_draft,
            'version' => $this->version,
            'published_at' => $this->published_at,
            'preview_token' => $this->preview_token,
            'settings' => $this->settings,
            'cache_enabled' => $this->cache_enabled,
            'visibility_rules' => $this->visibility_rules,
            'ab_test_group' => $this->ab_test_group,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'page' => $this->whenLoaded('page'),
            'componentable' => $this->whenLoaded('componentable'),
            'organization' => $this->whenLoaded('organization'),
            'parent' => $this->whenLoaded('parent'),
            'children' => $this->whenLoaded('children'),

            // Computed properties
            'component_type_name' => $this->getComponentTypeName(),
            'is_visible' => $this->isVisible(),
            'can_be_published' => $this->canBePublished(),
            'is_published' => $this->isPublished(),
            'next_position' => $this->getNextPosition(),
            'children_count' => $this->children_count ?? $this->children()->count(),
            
            // Preview & settings helpers
            'preview_url' => $this->preview_token ? $this->generatePreviewUrl() : null,
            'has_visibility_rules' => !empty($this->visibility_rules),
            'component_class' => class_basename($this->componentable_type),
        ];
    }
}
