<?php

namespace App\Http\Requests\Api\V1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category')?->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($categoryId)
            ],
            'description' => 'nullable|string|max:1000',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                Rule::notIn([$categoryId]) // No puede ser padre de sí misma
            ],
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
            'slug.required' => 'El slug es obligatorio.',
            'slug.unique' => 'Este slug ya está en uso.',
            'parent_id.exists' => 'La categoría padre seleccionada no existe.',
            'parent_id.not_in' => 'Una categoría no puede ser padre de sí misma.',
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
        // Auto-generar slug si se actualiza el nombre pero no el slug
        if ($this->has('name') && !$this->has('slug')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }

        // Convertir valores booleanos
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }

        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->route('category')?->id;

            // Validar jerarquía circular
            if ($this->parent_id && $categoryId) {
                $this->validateCircularHierarchy($validator, $categoryId, $this->parent_id);
            }
        });
    }

    /**
     * Validar que no se cree una jerarquía circular
     */
    private function validateCircularHierarchy($validator, $categoryId, $parentId): void
    {
        $visited = [];
        $current = $parentId;

        while ($current && !in_array($current, $visited)) {
            if ($current == $categoryId) {
                $validator->errors()->add('parent_id', 'No se puede crear una jerarquía circular.');
                return;
            }

            $visited[] = $current;
            $parent = \App\Models\Category::find($current);
            $current = $parent?->parent_id;
        }
    }
}