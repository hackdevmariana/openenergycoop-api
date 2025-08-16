<?php

namespace App\Http\Requests\Api\V1\SocialLink;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware and policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => 'sometimes|required|url|max:500',
            'icon' => 'sometimes|nullable|string|max:100',
            'css_class' => 'sometimes|nullable|string|max:100',
            'color' => 'sometimes|nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'order' => 'sometimes|nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'followers_count' => 'sometimes|nullable|integer|min:0|max:999999999',
            'organization_id' => 'sometimes|nullable|exists:organizations,id',
            'is_draft' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
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
        // Convertir valores booleanos
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }

        if ($this->has('is_draft')) {
            $this->merge(['is_draft' => $this->boolean('is_draft')]);
        }

        // Clean up URL if provided
        if ($this->has('url')) {
            $url = trim($this->url);
            
            // Ensure URL has protocol
            if (!preg_match('/^https?:\/\//', $url)) {
                $this->merge(['url' => 'https://' . $url]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // If URL is being updated, validate it against the existing platform
            if ($this->has('url')) {
                $socialLink = $this->route('socialLink');
                if ($socialLink && $socialLink->platform) {
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

                    if (isset($urlPatterns[$socialLink->platform])) {
                        $isValidUrl = false;
                        foreach ($urlPatterns[$socialLink->platform] as $pattern) {
                            if (strpos($this->url, $pattern) !== false) {
                                $isValidUrl = true;
                                break;
                            }
                        }

                        if (!$isValidUrl) {
                            $validator->errors()->add('url', "La URL no corresponde a la plataforma {$socialLink->platform}.");
                        }
                    }
                }
            }
        });
    }
}
