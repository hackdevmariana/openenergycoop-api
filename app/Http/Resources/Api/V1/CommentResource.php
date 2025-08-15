<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CommentResource',
    title: 'Comment Resource',
    description: 'Resource de comentario',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'content', type: 'string', example: 'Este es un comentario muy útil'),
        new OA\Property(property: 'author_name', type: 'string', nullable: true, example: 'Juan Pérez'),
        new OA\Property(property: 'author_email', type: 'string', nullable: true, example: 'juan@email.com'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected', 'spam'], example: 'approved'),
        new OA\Property(property: 'is_pinned', type: 'boolean', example: false),
        new OA\Property(property: 'likes_count', type: 'integer', example: 5),
        new OA\Property(property: 'dislikes_count', type: 'integer', example: 0),
        new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class CommentResource extends JsonResource
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
