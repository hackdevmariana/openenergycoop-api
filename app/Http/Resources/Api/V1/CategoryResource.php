<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CategoryResource",
 *     title="Category Resource",
 *     description="Recurso de categoría del sistema CMS",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Tecnología"),
 *     @OA\Property(property="slug", type="string", example="tecnologia"),
 *     @OA\Property(property="description", type="string", example="Artículos sobre tecnología"),
 *     @OA\Property(property="type", type="string", example="article"),
 *     @OA\Property(property="color", type="string", example="#3B82F6"),
 *     @OA\Property(property="icon", type="string", example="heroicon-o-computer-desktop"),
 *     @OA\Property(property="language", type="string", example="es"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="is_featured", type="boolean", example=false),
 *     @OA\Property(property="sort_order", type="integer", example=1),
 *     @OA\Property(property="content_count", type="integer", example=25),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="parent", ref="#/components/schemas/CategoryResource"),
 *     @OA\Property(property="children", type="array", @OA\Items(ref="#/components/schemas/CategoryResource"))
 * )
 */
class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'color' => $this->color,
            'icon' => $this->icon,
            'language' => $this->language,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
            'level' => $this->level,
            'path' => $this->path,
            'content_count' => ($this->articles?->count() ?? 0) + ($this->images?->count() ?? 0),
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'published_at' => $this->published_at,

            // Relaciones
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'organization' => [
                'id' => $this->organization?->id,
                'name' => $this->organization?->name,
            ],

            // Estadísticas de contenido (solo si están cargadas)
            'articles_count' => $this->when(
                $this->relationLoaded('articles'),
                fn() => $this->articles->count()
            ),
            'images_count' => $this->when(
                $this->relationLoaded('images'),
                fn() => $this->images->count()
            ),
        ];
    }
}