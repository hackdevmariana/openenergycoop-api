<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ImageResource",
 *     title="Image Resource",
 *     description="Recurso de imagen del sistema CMS",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Paisaje montañoso"),
 *     @OA\Property(property="slug", type="string", example="paisaje-montanoso"),
 *     @OA\Property(property="description", type="string", example="Hermoso paisaje de montañas al atardecer"),
 *     @OA\Property(property="alt_text", type="string", example="Montañas con cielo naranja al atardecer"),
 *     @OA\Property(property="url", type="string", example="https://example.com/storage/images/paisaje.jpg"),
 *     @OA\Property(property="thumbnail_url", type="string", example="https://example.com/storage/images/paisaje-thumb.jpg"),
 *     @OA\Property(property="mime_type", type="string", example="image/jpeg"),
 *     @OA\Property(property="file_size", type="integer", example=524288),
 *     @OA\Property(property="formatted_file_size", type="string", example="512.00 KB"),
 *     @OA\Property(property="dimensions", type="string", example="1920 × 1080 px"),
 *     @OA\Property(property="width", type="integer", example=1920),
 *     @OA\Property(property="height", type="integer", example=1080),
 *     @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"naturaleza", "montañas", "atardecer"}),
 *     @OA\Property(property="language", type="string", example="es"),
 *     @OA\Property(property="is_public", type="boolean", example=true),
 *     @OA\Property(property="is_featured", type="boolean", example=false),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="view_count", type="integer", example=150),
 *     @OA\Property(property="download_count", type="integer", example=25),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="published_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="category", ref="#/components/schemas/CategoryResource"),
 *     @OA\Property(property="uploaded_by", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Juan Pérez"),
 *         @OA\Property(property="email", type="string", example="juan@example.com")
 *     )
 * )
 */
class ImageResource extends JsonResource
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
            'description' => $this->description,
            'alt_text' => $this->alt_text,
            'url' => $this->getFullUrl(),
            'thumbnail_url' => $this->getThumbnailUrl(),
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'formatted_file_size' => $this->formatted_file_size,
            'dimensions' => $this->dimensions,
            'width' => $this->width,
            'height' => $this->height,
            'aspect_ratio' => $this->aspect_ratio,
            'is_landscape' => $this->is_landscape,
            'is_portrait' => $this->is_portrait,
            'is_square' => $this->is_square,
            'tags' => $this->tags ?? [],
            'language' => $this->language,
            'is_public' => $this->is_public,
            'is_featured' => $this->is_featured,
            'status' => $this->status,
            'view_count' => $this->view_count,
            'download_count' => $this->download_count,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'responsive_urls' => $this->responsive_urls,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'published_at' => $this->published_at,
            'last_used_at' => $this->last_used_at,

            // Relaciones
            'category' => new CategoryResource($this->whenLoaded('category')),
            'organization' => [
                'id' => $this->organization?->id,
                'name' => $this->organization?->name,
            ],
            'uploaded_by' => [
                'id' => $this->uploadedBy?->id,
                'name' => $this->uploadedBy?->name,
                'email' => $this->uploadedBy?->email,
            ],
        ];
    }
}