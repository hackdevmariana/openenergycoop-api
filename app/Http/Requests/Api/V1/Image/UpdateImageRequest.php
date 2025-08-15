<?php

namespace App\Http\Requests\Api\V1\Image;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $imageId = $this->route('image')?->id;

        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('images', 'slug')->ignore($imageId)
            ],
            'description' => 'nullable|string|max:1000',
            'alt_text' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'language' => 'nullable|string|in:es,en,ca,eu,gl',
            'is_public' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:60',
            'seo_description' => 'nullable|string|max:160',
            'organization_id' => 'nullable|exists:organizations,id',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'slug' => 'slug',
            'description' => 'descripción',
            'alt_text' => 'texto alternativo',
            'category_id' => 'categoría',
            'tags' => 'etiquetas',
            'language' => 'idioma',
            'is_public' => 'público',
            'is_featured' => 'destacado',
            'seo_title' => 'título SEO',
            'seo_description' => 'descripción SEO',
            'organization_id' => 'organización',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'slug.required' => 'El slug es obligatorio.',
            'slug.unique' => 'Este slug ya está en uso.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'language.in' => 'El idioma debe ser uno de: es, en, ca, eu, gl.',
            'seo_title.max' => 'El título SEO no puede tener más de 60 caracteres.',
            'seo_description.max' => 'La descripción SEO no puede tener más de 160 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generar slug si se actualiza el título pero no el slug
        if ($this->has('title') && !$this->has('slug')) {
            $this->merge([
                'slug' => Str::slug($this->title),
            ]);
        }

        // Convertir valores booleanos
        if ($this->has('is_public')) {
            $this->merge(['is_public' => $this->boolean('is_public')]);
        }

        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        }
    }
}