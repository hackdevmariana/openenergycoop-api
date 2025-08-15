<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SocialLinkResource',
    title: 'Social Link Resource',
    description: 'Resource de enlace social',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'platform', type: 'string', enum: ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube'], example: 'facebook'),
        new OA\Property(property: 'url', type: 'string', example: 'https://facebook.com/openenergycoop'),
        new OA\Property(property: 'handle', type: 'string', nullable: true, example: '@openenergycoop'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'followers_count', type: 'integer', nullable: true, example: 1250),
        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class SocialLinkResource extends JsonResource
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
