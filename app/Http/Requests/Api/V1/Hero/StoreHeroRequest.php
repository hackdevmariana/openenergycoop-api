<?php

namespace App\Http\Requests\Api\V1\Hero;

use App\Models\Hero;
use Illuminate\Foundation\Http\FormRequest;

class StoreHeroRequest extends FormRequest
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
            'image' => 'nullable|string|max:500',
            'mobile_image' => 'nullable|string|max:500',
            'text' => 'nullable|string|max:1000',
            'subtext' => 'nullable|string|max:500',
            'text_button' => 'nullable|string|max:100',
            'internal_link' => 'nullable|string|max:500',
            'cta_link_external' => 'nullable|url|max:500',
            'position' => 'nullable|integer|min:0|max:999',
            'exhibition_beginning' => 'nullable|date|after_or_equal:today',
            'exhibition_end' => 'nullable|date|after:exhibition_beginning',
            'active' => 'nullable|boolean',
            'video_url' => 'nullable|url|max:500',
            'video_background' => 'nullable|string|max:500',
            'text_align' => 'nullable|string|in:left,center,right',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'animation_type' => 'nullable|string|in:fade,slide_left,slide_right,slide_up,slide_down,zoom,bounce,none',
            'cta_style' => 'nullable|string|in:primary,secondary,outline,ghost,link',
            'priority' => 'nullable|integer|min:0|max:999',
            'language' => 'nullable|string|size:2',
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
            'text.max' => 'El texto principal no puede exceder 1000 caracteres.',
            'subtext.max' => 'El subtexto no puede exceder 500 caracteres.',
            'text_button.max' => 'El texto del botón no puede exceder 100 caracteres.',
            'cta_link_external.url' => 'El enlace externo debe ser una URL válida.',
            'exhibition_beginning.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
            'exhibition_end.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'overlay_opacity.between' => 'La opacidad debe estar entre 0 y 100.',
            'text_align.in' => 'La alineación de texto debe ser: left, center o right.',
            'cta_style.in' => 'El estilo del botón debe ser: primary, secondary, outline, ghost o link.',
            'animation_type.in' => 'El tipo de animación debe ser válido.',
            'language.size' => 'El idioma debe tener exactamente 2 caracteres.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'active' => $this->has('active') ? $this->boolean('active') : true,
            'is_draft' => $this->has('is_draft') ? $this->boolean('is_draft') : true,
            'text_align' => $this->text_align ?? 'center',
            'cta_style' => $this->cta_style ?? 'primary',
            'overlay_opacity' => $this->overlay_opacity ?? 50,
            'priority' => $this->priority ?? 0,
            'language' => $this->language ?? 'es',
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that at least one content field is provided
            if (empty($this->text) && empty($this->image) && empty($this->video_url)) {
                $validator->errors()->add('content', 'Debe proporcionar al menos texto, imagen o video para el hero.');
            }

            // Validate video URLs for known platforms
            if ($this->video_url) {
                $this->validateVideoUrl($validator, $this->video_url, 'video_url');
            }
            
            if ($this->video_background) {
                $this->validateVideoUrl($validator, $this->video_background, 'video_background');
            }

            // Validate CTA consistency
            if ($this->text_button && !$this->internal_link && !$this->cta_link_external) {
                $validator->errors()->add('text_button', 'Si especifica texto del botón, debe proporcionar un enlace (interno o externo).');
            }
        });
    }

    /**
     * Validate video URL for known platforms.
     */
    private function validateVideoUrl($validator, $url, $field): void
    {
        $supportedPlatforms = [
            'youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com',
            'twitch.tv', 'facebook.com', 'instagram.com'
        ];

        $isSupported = false;
        foreach ($supportedPlatforms as $platform) {
            if (strpos($url, $platform) !== false) {
                $isSupported = true;
                break;
            }
        }

        if (!$isSupported) {
            $validator->errors()->add($field, 'La URL del video debe ser de una plataforma soportada (YouTube, Vimeo, etc.).');
        }
    }
}
