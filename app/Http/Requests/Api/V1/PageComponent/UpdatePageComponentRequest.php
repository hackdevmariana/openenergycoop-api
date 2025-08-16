<?php

namespace App\Http\Requests\Api\V1\PageComponent;

use App\Models\PageComponent;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePageComponentRequest extends FormRequest
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
            'position' => 'sometimes|integer|min:1',
            'parent_id' => 'sometimes|nullable|exists:page_components,id',
            'is_draft' => 'sometimes|boolean',
            'version' => 'sometimes|nullable|string|max:20',
            'settings' => 'sometimes|nullable|array',
            'cache_enabled' => 'sometimes|boolean',
            'visibility_rules' => 'sometimes|nullable|array',
            'visibility_rules.*.type' => 'required_with:visibility_rules|string|in:auth_required,role_required,date_range,device_type',
            'visibility_rules.*.value' => 'nullable|string',
            'visibility_rules.*.start' => 'nullable|date',
            'visibility_rules.*.end' => 'nullable|date|after:visibility_rules.*.start',
            'ab_test_group' => 'sometimes|nullable|string|max:10',
            'published_at' => 'sometimes|nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'position.min' => 'La posición debe ser mayor a 0.',
            'parent_id.exists' => 'El componente padre seleccionado no existe.',
            'version.max' => 'La versión no puede exceder 20 caracteres.',
            'visibility_rules.*.type.required_with' => 'El tipo de regla de visibilidad es obligatorio.',
            'visibility_rules.*.type.in' => 'El tipo de regla de visibilidad no es válido.',
            'visibility_rules.*.end.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'ab_test_group.max' => 'El grupo de test A/B no puede exceder 10 caracteres.',
            'published_at.date' => 'La fecha de publicación debe ser una fecha válida.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'position' => 'posición',
            'parent_id' => 'componente padre',
            'is_draft' => 'borrador',
            'version' => 'versión',
            'settings' => 'configuraciones',
            'cache_enabled' => 'caché habilitado',
            'visibility_rules' => 'reglas de visibilidad',
            'ab_test_group' => 'grupo de test A/B',
            'published_at' => 'fecha de publicación',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $component = $this->route('pageComponent') ?? $this->route('page_component');



            // Validate parent-child relationship
            if ($this->has('parent_id')) {
                $this->validateParentRelationship($validator, $component);
            }

            // Validate publishing requirements
            if ($this->has('is_draft') && $this->is_draft === false) {
                $this->validatePublishingRequirements($validator, $component);
            }

            // Validate position conflicts
            if ($this->has('position')) {
                $this->validatePositionConflicts($validator, $component);
            }
        });
    }

    /**
     * Validate parent-child relationship.
     */
    private function validateParentRelationship($validator, $component): void
    {


        if (!$component) {
            return;
        }
        
        // If parent_id is null, it's removing the parent, which is valid
        if ($this->parent_id === null) {
            return;
        }
        
        // Prevent setting self as parent
        if ((int)$this->parent_id === (int)$component->id) {
            $validator->errors()->add('parent_id', 'Un componente no puede ser su propio padre.');
            return;
        }
        
        $parent = PageComponent::find($this->parent_id);
        
        if ($parent) {

            // Check if parent is on the same page
            if ($parent->page_id !== $component->page_id) {
                $validator->errors()->add('parent_id', 'El componente padre debe estar en la misma página.');
            }

            // Check if parent is in the same language
            if ($parent->language !== $component->language) {
                $validator->errors()->add('parent_id', 'El componente padre debe estar en el mismo idioma.');
            }

            // Check for circular reference
            $this->checkCircularReference($validator, $parent, $component);
        }
    }

    /**
     * Check for circular references in parent-child relationship.
     */
    private function checkCircularReference($validator, $parent, $currentComponent): void
    {
        $visited = [$currentComponent->id];
        $current = $parent;

        while ($current && $current->parent_id) {
            if (in_array($current->parent_id, $visited)) {
                $validator->errors()->add('parent_id', 'Esta relación crearía una referencia circular.');
                break;
            }
            
            $visited[] = $current->id;
            $current = $current->parent;
        }
    }

    /**
     * Validate publishing requirements.
     */
    private function validatePublishingRequirements($validator, $component): void
    {
        if (!$component) {
            return;
        }
        
        // Check if the componentable can be published
        if ($component->componentable && method_exists($component->componentable, 'canBePublished')) {
            if (!$component->componentable->canBePublished()) {
                $validator->errors()->add('is_draft', 'El componente no puede ser publicado debido a restricciones del contenido.');
            }
        }

        // Check if the parent page is published (if component is being published)
        if ($component->page && $component->page->is_draft) {
            $validator->errors()->add('is_draft', 'No se puede publicar un componente en una página que está en borrador.');
        }
    }

    /**
     * Validate position conflicts.
     */
    private function validatePositionConflicts($validator, $component): void
    {
        if (!$component) {
            return;
        }

        // Check if another component already has this position on the same page
        $existingComponent = PageComponent::where('page_id', $component->page_id)
            ->where('position', $this->position)
            ->where('id', '!=', $component->id)
            ->where('parent_id', $component->parent_id) // Same parent level
            ->first();

        if ($existingComponent) {
            $validator->errors()->add('position', 'Ya existe otro componente en esta posición.');
        }
    }
}
