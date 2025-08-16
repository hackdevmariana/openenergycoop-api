<?php

namespace App\Http\Requests\Api\V1\PageComponent;

use App\Models\PageComponent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageComponentRequest extends FormRequest
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
            'page_id' => 'required|exists:pages,id',
            'componentable_type' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Accept both short names and full namespaces
                    $shortName = class_basename($value);
                    if (!array_key_exists($shortName, PageComponent::COMPONENT_TYPES)) {
                        $fail('El tipo de componente seleccionado no es válido.');
                    }
                },
            ],
            'componentable_id' => 'required|integer|min:1',
            'position' => 'nullable|integer|min:1',
            'parent_id' => 'nullable|exists:page_components,id',
            'language' => 'required|string|in:es,en,ca,eu,gl',
            'organization_id' => 'nullable|exists:organizations,id',
            'is_draft' => 'boolean',
            'version' => 'nullable|string|max:20',
            'settings' => 'nullable|array',
            'cache_enabled' => 'boolean',
            'visibility_rules' => 'nullable|array',
            'visibility_rules.*.type' => 'required_with:visibility_rules|string|in:auth_required,role_required,date_range,device_type',
            'visibility_rules.*.value' => 'nullable|string',
            'visibility_rules.*.start' => 'nullable|date',
            'visibility_rules.*.end' => 'nullable|date|after:visibility_rules.*.start',
            'ab_test_group' => 'nullable|string|max:10',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'page_id.required' => 'La página es obligatoria.',
            'page_id.exists' => 'La página seleccionada no existe.',
            'componentable_type.required' => 'El tipo de componente es obligatorio.',
            'componentable_type.in' => 'El tipo de componente seleccionado no es válido.',
            'componentable_id.required' => 'El ID del componente es obligatorio.',
            'componentable_id.min' => 'El ID del componente debe ser mayor a 0.',
            'parent_id.exists' => 'El componente padre seleccionado no existe.',
            'language.required' => 'El idioma es obligatorio.',
            'language.in' => 'El idioma debe ser uno de: es, en, ca, eu, gl.',
            'position.min' => 'La posición debe ser mayor a 0.',
            'version.max' => 'La versión no puede exceder 20 caracteres.',
            'visibility_rules.*.type.required_with' => 'El tipo de regla de visibilidad es obligatorio.',
            'visibility_rules.*.type.in' => 'El tipo de regla de visibilidad no es válido.',
            'visibility_rules.*.end.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'ab_test_group.max' => 'El grupo de test A/B no puede exceder 10 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'page_id' => 'página',
            'componentable_type' => 'tipo de componente',
            'componentable_id' => 'ID del componente',
            'parent_id' => 'componente padre',
            'language' => 'idioma',
            'organization_id' => 'organización',
            'is_draft' => 'borrador',
            'version' => 'versión',
            'settings' => 'configuraciones',
            'cache_enabled' => 'caché habilitado',
            'visibility_rules' => 'reglas de visibilidad',
            'ab_test_group' => 'grupo de test A/B',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'language' => $this->language ?? 'es',
            'is_draft' => $this->is_draft ?? true,
            'cache_enabled' => $this->cache_enabled ?? true,
            'version' => $this->version ?? '1.0',
        ]);

        // Convert componentable_type to full class name if needed
        if ($this->componentable_type && !str_starts_with($this->componentable_type, 'App\\Models\\')) {
            $this->merge(['componentable_type' => 'App\\Models\\' . $this->componentable_type]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate parent-child relationship
            if ($this->parent_id) {
                $this->validateParentRelationship($validator);
            }

            // Validate componentable exists
            if ($this->componentable_type && $this->componentable_id) {
                $this->validateComponentableExists($validator);
            }

            // Validate page and language consistency
            if ($this->page_id && $this->language) {
                $this->validatePageLanguageConsistency($validator);
            }
        });
    }

    /**
     * Validate parent-child relationship.
     */
    private function validateParentRelationship($validator): void
    {
        $parent = PageComponent::find($this->parent_id);
        
        if ($parent) {
            // Check if parent is on the same page
            if ($parent->page_id !== $this->page_id) {
                $validator->errors()->add('parent_id', 'El componente padre debe estar en la misma página.');
            }

            // Check if parent is in the same language
            if ($parent->language !== $this->language) {
                $validator->errors()->add('parent_id', 'El componente padre debe estar en el mismo idioma.');
            }

            // Check if parent is in the same organization
            if ($parent->organization_id !== $this->organization_id) {
                $validator->errors()->add('parent_id', 'El componente padre debe pertenecer a la misma organización.');
            }
        }
    }

    /**
     * Validate that the componentable exists.
     */
    private function validateComponentableExists($validator): void
    {
        if (!class_exists($this->componentable_type)) {
            $validator->errors()->add('componentable_type', 'El tipo de componente no es válido.');
            return;
        }

        $model = $this->componentable_type;
        if (!$model::find($this->componentable_id)) {
            $validator->errors()->add('componentable_id', 'El componente referenciado no existe.');
        }
    }

    /**
     * Validate page and language consistency.
     */
    private function validatePageLanguageConsistency($validator): void
    {
        $page = \App\Models\Page::find($this->page_id);
        
        if ($page && $page->language !== $this->language) {
            $validator->errors()->add('language', 'El idioma del componente debe coincidir con el idioma de la página.');
        }

        // Also set organization_id from page if not provided
        if ($page && !$this->organization_id) {
            $this->merge(['organization_id' => $page->organization_id]);
        }
    }
}
