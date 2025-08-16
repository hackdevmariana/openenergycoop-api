<?php

namespace App\Http\Requests\Api\V1\Banner;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
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
            'image' => 'required|string|max:255',
            'mobile_image' => 'nullable|string|max:255',
            'internal_link' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'position' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'exhibition_beginning' => 'nullable|date|after_or_equal:today',
            'exhibition_end' => 'nullable|date|after:exhibition_beginning',
            'banner_type' => 'nullable|string|in:header,sidebar,footer,popup,inline',
            'display_rules' => 'nullable|array',
            'organization_id' => 'nullable|exists:organizations,id',
            'is_draft' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'image.required' => 'La imagen es obligatoria.',
            'url.url' => 'El URL debe ser una dirección válida.',
            'exhibition_beginning.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
            'exhibition_end.after' => 'La fecha de fin debe ser posterior al inicio.',
            'banner_type.in' => 'El tipo de banner debe ser: header, sidebar, footer, popup o inline.',
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
            'active' => $this->has('active') ? $this->boolean('active') : true,
            'is_draft' => $this->has('is_draft') ? $this->boolean('is_draft') : true,
            'position' => $this->position ?? 0,
            'banner_type' => $this->banner_type ?? 'header',
        ]);
    }
}
