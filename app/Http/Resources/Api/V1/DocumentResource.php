<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DocumentResource',
    title: 'Document Resource',
    description: 'Resource de documento',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Manual de Usuario'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Guía completa para usuarios'),
        new OA\Property(property: 'file_path', type: 'string', example: '/documents/manual-usuario.pdf'),
        new OA\Property(property: 'file_size', type: 'integer', example: 1024000),
        new OA\Property(property: 'mime_type', type: 'string', example: 'application/pdf'),
        new OA\Property(property: 'is_public', type: 'boolean', example: true),
        new OA\Property(property: 'download_count', type: 'integer', example: 150),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00Z')
    ]
)]
class DocumentResource extends JsonResource
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
