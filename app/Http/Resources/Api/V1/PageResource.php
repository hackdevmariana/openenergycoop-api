<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PageResource',
    title: 'Page Resource',
    description: 'Resource de página',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Inicio'),
        new OA\Property(property: 'slug', type: 'string', example: 'inicio'),
        new OA\Property(property: 'route', type: 'string', nullable: true, example: '/home'),
        new OA\Property(property: 'language', type: 'string', example: 'es'),
        new OA\Property(property: 'is_draft', type: 'boolean', example: false),
        new OA\Property(property: 'template', type: 'string', nullable: true, example: 'default'),
        new OA\Property(property: 'meta_data', type: 'object', nullable: true, example: ['description' => 'Página de inicio']),
        new OA\Property(property: 'requires_auth', type: 'boolean', example: false),
        new OA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true, example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class PageResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'route' => $this->route,
            'language' => $this->language,
            'organization_id' => $this->organization_id,
            'is_draft' => $this->is_draft,
            'template' => $this->template,
            'meta_data' => $this->meta_data,
            'cache_duration' => $this->cache_duration,
            'requires_auth' => $this->requires_auth,
            'allowed_roles' => $this->allowed_roles,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'published_at' => $this->published_at,
            'search_keywords' => $this->search_keywords,
            'internal_notes' => $this->internal_notes,
            'last_reviewed_at' => $this->last_reviewed_at,
            'accessibility_notes' => $this->accessibility_notes,
            'reading_level' => $this->reading_level,
            'created_by_user_id' => $this->created_by_user_id,
            'updated_by_user_id' => $this->updated_by_user_id,
            'approved_by_user_id' => $this->approved_by_user_id,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'parent' => $this->whenLoaded('parent'),
            'children' => $this->whenLoaded('children'),
            'organization' => $this->whenLoaded('organization'),
            'components' => $this->whenLoaded('components'),
            'published_components' => $this->whenLoaded('publishedComponents'),
            'seo_meta_data' => $this->whenLoaded('seoMetaData'),
            'created_by' => $this->whenLoaded('createdBy'),
            'updated_by' => $this->whenLoaded('updatedBy'),
            'approved_by' => $this->whenLoaded('approvedBy'),

            // Computed properties
            'full_slug' => $this->getFullSlug(),
            'breadcrumb' => $this->getBreadcrumb(),
            'url' => $this->getUrl(),
            'can_be_published' => $this->canBePublished(),
            'has_published_content' => $this->hasPublishedContent(),
            'template_label' => $this->getTemplateLabel(),
            'is_home_page' => $this->isHomePage(),
            'estimated_reading_time' => $this->getEstimatedReadingTime(),
            'is_published' => $this->isPublished(),
            'children_count' => $this->children_count ?? $this->children()->count(),
            'components_count' => $this->components_count ?? $this->components()->count(),

            // Media
            'featured_image_url' => $this->getFirstMediaUrl('featured_images'),
            'gallery_images' => $this->getMedia('gallery')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'name' => $media->name,
                    'alt' => $media->getCustomProperty('alt_text'),
                ];
            }),
        ];
    }
}
