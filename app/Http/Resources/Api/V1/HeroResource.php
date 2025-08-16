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
        return [
            'id' => $this->id,
            'image' => $this->image,
            'mobile_image' => $this->mobile_image,
            'text' => $this->text,
            'subtext' => $this->subtext,
            'text_button' => $this->text_button,
            'internal_link' => $this->internal_link,
            'cta_link_external' => $this->cta_link_external,
            'position' => $this->position,
            'exhibition_beginning' => $this->exhibition_beginning,
            'exhibition_end' => $this->exhibition_end,
            'active' => $this->active,
            'video_url' => $this->video_url,
            'video_background' => $this->video_background,
            'text_align' => $this->text_align,
            'overlay_opacity' => $this->overlay_opacity,
            'animation_type' => $this->animation_type,
            'cta_style' => $this->cta_style,
            'priority' => $this->priority,
            'language' => $this->language,
            'organization_id' => $this->organization_id,
            'is_draft' => $this->is_draft,
            'published_at' => $this->published_at,
            'created_by_user_id' => $this->created_by_user_id,
            'updated_by_user_id' => $this->updated_by_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'organization' => $this->whenLoaded('organization'),
            'created_by' => $this->whenLoaded('createdBy'),
            'updated_by' => $this->whenLoaded('updatedBy'),

            // Computed properties
            'is_published' => $this->isPublished(),
            'is_in_exhibition_period' => $this->isInExhibitionPeriod(),
            'display_text' => $this->getDisplayText(),
            'display_subtext' => $this->getDisplaySubtext(),
            'cta_url' => $this->getCtaUrl(),
            'has_video' => $this->hasVideo(),
            'video_url_computed' => $this->getVideoUrl(),
            'image_url' => $this->getImageUrl(),
            'mobile_image_url' => $this->getMobileImageUrl(),
            'image_url_optimized' => $this->getImageUrl('optimized'),
            'mobile_image_url_optimized' => $this->getMobileImageUrl('mobile_optimized'),
            'image_url_thumb' => $this->getImageUrl('thumb'),
            'text_alignment_class' => $this->getTextAlignmentClass(),
            'cta_style_class' => $this->getCtaStyleClass(),
            'animation_class' => $this->getAnimationClass(),
            'overlay_style' => $this->getOverlayStyle(),
            'word_count' => $this->getWordCount(),
        ];
    }
}
