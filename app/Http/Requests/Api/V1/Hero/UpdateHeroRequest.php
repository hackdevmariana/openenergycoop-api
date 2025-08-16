<?php

namespace App\Http\Requests\Api\V1\Hero;

use App\Models\Hero;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroRequest extends FormRequest
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
            'image' => 'sometimes|nullable|string|max:500',
            'mobile_image' => 'sometimes|nullable|string|max:500',
            'text' => 'sometimes|nullable|string|max:1000',
            'subtext' => 'sometimes|nullable|string|max:500',
            'text_button' => 'sometimes|nullable|string|max:100',
            'internal_link' => 'sometimes|nullable|string|max:500',
            'cta_link_external' => 'sometimes|nullable|url|max:500',
            'position' => 'sometimes|nullable|integer|min:0|max:999',
            'exhibition_beginning' => 'sometimes|nullable|date',
            'exhibition_end' => 'sometimes|nullable|date|after:exhibition_beginning',
            'active' => 'sometimes|boolean',
            'video_url' => 'sometimes|nullable|url|max:500',
            'video_background' => 'sometimes|nullable|string|max:500',
            'text_align' => 'sometimes|nullable|string|in:left,center,right',
            'overlay_opacity' => 'sometimes|nullable|integer|min:0|max:100',
            'animation_type' => 'sometimes|nullable|string|in:fade,slide_left,slide_right,slide_up,slide_down,zoom,bounce,none',
            'cta_style' => 'sometimes|nullable|string|in:primary,secondary,outline,ghost,link',
            'priority' => 'sometimes|nullable|integer|min:0|max:999',
            'language' => 'sometimes|nullable|string|size:2',
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
            'text.max' => 'El texto principal no puede exceder 1000 caracteres.',
            'subtext.max' => 'El subtexto no puede exceder 500 caracteres.',
            'text_button.max' => 'El texto del botón no puede exceder 100 caracteres.',
            'cta_link_external.url' => 'El enlace externo debe ser una URL válida.',
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
        // Convert boolean values
        if ($this->has('active')) {
            $this->merge(['active' => $this->boolean('active')]);
        }

        if ($this->has('is_draft')) {
            $this->merge(['is_draft' => $this->boolean('is_draft')]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hero = $this->route('hero');
            
            // If updating content fields, validate that at least one content field remains
            if ($this->hasAny(['text', 'image', 'video_url'])) {
                $newText = $this->has('text') ? $this->text : $hero->text;
                $newImage = $this->has('image') ? $this->image : $hero->image;
                $newVideoUrl = $this->has('video_url') ? $this->video_url : $hero->video_url;
                
                if (empty($newText) && empty($newImage) && empty($newVideoUrl)) {
                    $validator->errors()->add('content', 'Debe mantener al menos texto, imagen o video para el hero.');
                }
            }

            // Validate video URLs for known platforms
            if ($this->has('video_url') && $this->video_url) {
                $this->validateVideoUrl($validator, $this->video_url, 'video_url');
            }
            
            if ($this->has('video_background') && $this->video_background) {
                $this->validateVideoUrl($validator, $this->video_background, 'video_background');
            }

            // Validate CTA consistency
            if ($this->has('text_button') || $this->has('internal_link') || $this->has('cta_link_external')) {
                $newTextButton = $this->has('text_button') ? $this->text_button : $hero->text_button;
                $newInternalLink = $this->has('internal_link') ? $this->internal_link : $hero->internal_link;
                $newExternalLink = $this->has('cta_link_external') ? $this->cta_link_external : $hero->cta_link_external;
                
                if ($newTextButton && !$newInternalLink && !$newExternalLink) {
                    $validator->errors()->add('text_button', 'Si especifica texto del botón, debe proporcionar un enlace (interno o externo).');
                }
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
