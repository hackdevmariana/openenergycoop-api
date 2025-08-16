<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppSettingResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="AppSetting",
     *     type="object",
     *     title="AppSetting",
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="name", type="string"),
     *     @OA\Property(property="slogan", type="string"),
     *     @OA\Property(property="primary_color", type="string"),
     *     @OA\Property(property="secondary_color", type="string"),
     *     @OA\Property(property="locale", type="string"),
     *     @OA\Property(property="custom_js", type="string"),
     *     @OA\Property(property="logo_url", type="string", format="url"),
     *     @OA\Property(property="favicon_url", type="string", format="url"),
     *     @OA\Property(
     *         property="organization",
     *         type="object",
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="name", type="string")
     *     )
     * )
     */

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slogan' => $this->slogan,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'locale' => $this->locale,
            'custom_js' => $this->custom_js,
            'favicon_path' => $this->favicon_path,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'organization' => $this->whenLoaded('organization', function () {
                return $this->organization ? [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                ] : null;
            }),
            'logo_url' => $this->getFirstMediaUrl('logo'),
            'favicon_url' => $this->getFirstMediaUrl('favicon'),
            
            // Computed properties
            'has_logo' => !empty($this->getFirstMediaUrl('logo')),
            'has_favicon' => !empty($this->getFirstMediaUrl('favicon')) || !empty($this->favicon_path),
            'effective_favicon' => $this->getFirstMediaUrl('favicon') ?: $this->favicon_path,
            'theme_colors' => [
                'primary' => $this->primary_color,
                'secondary' => $this->secondary_color,
            ],
        ];
    }
}
