<?php

namespace App\Http\Requests\Api\V1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCategoryRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'type' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:100',
            'language' => 'nullable|string|in:es,en,ca,eu,gl',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
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
            'name' => 'nombre',
            'slug' => 'slug',
            'description' => 'descripción',
            'parent_id' => 'categoría padre',
            'type' => 'tipo',
            'color' => 'color',
            'icon' => 'icono',
            'language' => 'idioma',
            'is_active' => 'activo',
            'is_featured' => 'destacado',
            'sort_order' => 'orden',
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
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'slug.unique' => 'Este slug ya está en uso.',
            'parent_id.exists' => 'La categoría padre seleccionada no existe.',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #FF0000).',
            'language.in' => 'El idioma debe ser uno de: es, en, ca, eu, gl.',
            'sort_order.min' => 'El orden debe ser un número positivo.',
            'seo_title.max' => 'El título SEO no puede tener más de 60 caracteres.',
            'seo_description.max' => 'La descripción SEO no puede tener más de 160 caracteres.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generar slug si no se proporciona
        if (!$this->has('slug') || empty($this->slug)) {
            $this->merge([
                'slug' => Str::slug($this->name ?? ''),
            ]);
        }

        // Valores por defecto
        $this->merge([
            'language' => $this->language ?? 'es',
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'is_featured' => $this->has('is_featured') ? $this->boolean('is_featured') : false,
            'sort_order' => $this->sort_order ?? 0,
            'type' => $this->type ?? 'general',
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que no se cree una categoría padre de sí misma
            if ($this->parent_id && $this->parent_id == $this->id) {
                $validator->errors()->add('parent_id', 'Una categoría no puede ser padre de sí misma.');
            }
        });
    }
}