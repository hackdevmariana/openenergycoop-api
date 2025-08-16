<?php

namespace App\Http\Requests\Api\V1\SocialLink;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'platform' => 'required|string|in:facebook,twitter,instagram,linkedin,youtube,tiktok,telegram,whatsapp,github,discord,other',
            'url' => 'required|url|max:500',
            'icon' => 'nullable|string|max:100',
            'css_class' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'followers_count' => 'nullable|integer|min:0|max:999999999',
            'organization_id' => 'nullable|exists:organizations,id',
            'is_draft' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'platform.required' => 'La plataforma es obligatoria.',
            'platform.in' => 'La plataforma debe ser una de las siguientes: facebook, twitter, instagram, linkedin, youtube, tiktok, telegram, whatsapp, github, discord, other.',
            'url.required' => 'La URL es obligatoria.',
            'url.url' => 'La URL debe tener un formato válido.',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #FF0000).',
            'followers_count.integer' => 'El número de seguidores debe ser un número entero.',
            'followers_count.min' => 'El número de seguidores no puede ser negativo.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Valores por defecto
        $this->merge([
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'is_draft' => $this->has('is_draft') ? $this->boolean('is_draft') : true,
            'order' => $this->order ?? 0,
        ]);

        // Validar URL específica de la plataforma
        if ($this->has('platform') && $this->has('url')) {
            $this->validatePlatformUrl();
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que la URL corresponda a la plataforma seleccionada
            if ($this->platform && $this->url) {
                $urlPatterns = [
                    'facebook' => ['facebook.com', 'fb.com'],
                    'twitter' => ['twitter.com', 'x.com'],
                    'instagram' => ['instagram.com'],
                    'linkedin' => ['linkedin.com'],
                    'youtube' => ['youtube.com', 'youtu.be'],
                    'tiktok' => ['tiktok.com'],
                    'telegram' => ['t.me', 'telegram.me'],
                    'whatsapp' => ['wa.me', 'whatsapp.com'],
                    'github' => ['github.com'],
                    'discord' => ['discord.gg', 'discord.com'],
                ];

                if (isset($urlPatterns[$this->platform])) {
                    $isValidUrl = false;
                    foreach ($urlPatterns[$this->platform] as $pattern) {
                        if (strpos($this->url, $pattern) !== false) {
                            $isValidUrl = true;
                            break;
                        }
                    }

                    if (!$isValidUrl) {
                        $validator->errors()->add('url', "La URL no corresponde a la plataforma {$this->platform} seleccionada.");
                    }
                }
            }
        });
    }

    /**
     * Validate platform-specific URL patterns.
     */
    private function validatePlatformUrl(): void
    {
        // Clean up the URL
        $url = trim($this->url);
        
        // Ensure URL has protocol
        if (!preg_match('/^https?:\/\//', $url)) {
            $this->merge(['url' => 'https://' . $url]);
        }
    }
}
