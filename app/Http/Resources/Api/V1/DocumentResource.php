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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'visible' => $this->visible,
            'download_count' => $this->download_count,
            'number_of_views' => $this->number_of_views,
            'version' => $this->version,
            'expires_at' => $this->expires_at,
            'requires_auth' => $this->requires_auth,
            'allowed_roles' => $this->allowed_roles,
            'thumbnail_path' => $this->thumbnail_path,
            'language' => $this->language,
            'is_draft' => $this->is_draft,
            'published_at' => $this->published_at,
            'search_keywords' => $this->search_keywords,
            'uploaded_at' => $this->uploaded_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relaciones
            'category' => $this->whenLoaded('category'),
            'organization' => $this->whenLoaded('organization'),
            'uploaded_by' => $this->whenLoaded('uploadedBy'),
            'created_by' => $this->whenLoaded('createdBy'),
            'updated_by' => $this->whenLoaded('updatedBy'),

            // Computed fields
            'is_published' => $this->isPublished(),
            'is_expired' => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'download_url' => $this->getDownloadUrl(),
            'secure_download_url' => $this->getSecureDownloadUrl(),
            'thumbnail_url' => $this->getThumbnailUrl(),
            'file_type_label' => $this->getFileTypeLabel(),
            'file_type_icon' => $this->getFileTypeIcon(),
            'formatted_file_size' => $this->getFormattedFileSize(),
            'can_be_downloaded' => $this->canBeDownloadedBy(auth()->user()),
        ];
    }
}
