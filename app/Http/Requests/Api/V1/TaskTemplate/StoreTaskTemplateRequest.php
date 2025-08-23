<?php

namespace App\Http\Requests\Api\V1\TaskTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'description' => 'nullable|string',
            'template_type' => ['required', Rule::in(array_keys(\App\Models\TaskTemplate::getTemplateTypes()))],
            'category' => 'required|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'priority' => ['required', Rule::in(array_keys(\App\Models\TaskTemplate::getPriorities()))],
            'risk_level' => ['required', Rule::in(array_keys(\App\Models\TaskTemplate::getRiskLevels()))],
            'department' => 'required|string|max:100',
            'estimated_duration_hours' => 'nullable|numeric|min:0.1|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0|max:1000000',
            'required_skills' => 'nullable|array',
            'required_skills.*' => 'string|max:100',
            'required_tools' => 'nullable|array',
            'required_tools.*' => 'string|max:100',
            'required_materials' => 'nullable|array',
            'required_materials.*' => 'string|max:100',
            'safety_requirements' => 'nullable|string',
            'quality_standards' => 'nullable|string',
            'compliance_requirements' => 'nullable|string',
            'documentation_requirements' => 'nullable|string',
            'approval_workflow' => 'nullable|string',
            'version' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'is_standard' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la plantilla es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'template_type.required' => 'El tipo de plantilla es obligatorio.',
            'template_type.in' => 'El tipo de plantilla seleccionado no es válido.',
            'category.required' => 'La categoría es obligatoria.',
            'category.max' => 'La categoría no puede tener más de 100 caracteres.',
            'subcategory.max' => 'La subcategoría no puede tener más de 100 caracteres.',
            'priority.required' => 'La prioridad es obligatoria.',
            'priority.in' => 'La prioridad seleccionada no es válida.',
            'risk_level.required' => 'El nivel de riesgo es obligatorio.',
            'risk_level.in' => 'El nivel de riesgo seleccionado no es válido.',
            'department.required' => 'El departamento es obligatorio.',
            'department.max' => 'El departamento no puede tener más de 100 caracteres.',
            'estimated_duration_hours.numeric' => 'La duración estimada debe ser un número.',
            'estimated_duration_hours.min' => 'La duración estimada debe ser al menos 0.1 horas.',
            'estimated_duration_hours.max' => 'La duración estimada no puede exceder 1000 horas.',
            'estimated_cost.numeric' => 'El costo estimado debe ser un número.',
            'estimated_cost.min' => 'El costo estimado debe ser al menos 0.',
            'estimated_cost.max' => 'El costo estimado no puede exceder 1,000,000.',
            'required_skills.array' => 'Las habilidades requeridas deben ser una lista.',
            'required_skills.*.string' => 'Cada habilidad requerida debe ser texto.',
            'required_skills.*.max' => 'Cada habilidad requerida no puede tener más de 100 caracteres.',
            'required_tools.array' => 'Las herramientas requeridas deben ser una lista.',
            'required_tools.*.string' => 'Cada herramienta requerida debe ser texto.',
            'required_tools.*.max' => 'Cada herramienta requerida no puede tener más de 100 caracteres.',
            'required_materials.array' => 'Los materiales requeridos deben ser una lista.',
            'required_materials.*.string' => 'Cada material requerido debe ser texto.',
            'required_materials.*.max' => 'Cada material requerido no puede tener más de 100 caracteres.',
            'tags.array' => 'Las etiquetas deben ser una lista.',
            'tags.*.string' => 'Cada etiqueta debe ser texto.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 50 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'is_standard' => $this->boolean('is_standard', false),
            'version' => $this->version ?? '1.0',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que si es plantilla estándar, debe estar activa
            if ($this->boolean('is_standard') && !$this->boolean('is_active')) {
                $validator->errors()->add('is_standard', 'Una plantilla estándar debe estar activa.');
            }

            // Validar que si tiene costo estimado, debe tener duración estimada
            if ($this->filled('estimated_cost') && !$this->filled('estimated_duration_hours')) {
                $validator->errors()->add('estimated_duration_hours', 'Si se especifica un costo estimado, también debe especificarse la duración estimada.');
            }

            // Validar que si es plantilla estándar, debe tener categoría y subcategoría
            if ($this->boolean('is_standard') && !$this->filled('subcategory')) {
                $validator->errors()->add('subcategory', 'Una plantilla estándar debe tener subcategoría.');
            }

            // Validar que si es de alta prioridad o alto riesgo, debe tener requisitos de seguridad
            if (in_array($this->priority, ['high', 'urgent', 'critical']) && !$this->filled('safety_requirements')) {
                $validator->errors()->add('safety_requirements', 'Las plantillas de alta prioridad deben incluir requisitos de seguridad.');
            }

            if (in_array($this->risk_level, ['high', 'extreme']) && !$this->filled('safety_requirements')) {
                $validator->errors()->add('safety_requirements', 'Las plantillas de alto riesgo deben incluir requisitos de seguridad.');
            }
        });
    }
}
