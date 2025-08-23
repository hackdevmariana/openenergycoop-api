<?php

namespace App\Http\Requests\Api\V1\ChecklistTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreChecklistTemplateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:checklist_templates,name',
            'description' => 'nullable|string|max:65535',
            'template_type' => ['required', 'string', Rule::in([
                'maintenance', 'inspection', 'safety', 'quality', 'compliance',
                'audit', 'training', 'operations', 'procedure', 'workflow'
            ])],
            'category' => 'required|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'checklist_items' => 'required|array|min:1|max:100',
            'checklist_items.*' => 'required|string|max:500',
            'required_items' => 'nullable|array|max:100',
            'required_items.*' => 'string|max:500',
            'optional_items' => 'nullable|array|max:100',
            'optional_items.*' => 'string|max:500',
            'conditional_items' => 'nullable|array|max:100',
            'conditional_items.*' => 'string|max:500',
            'item_order' => 'nullable|array|max:100',
            'item_order.*' => 'integer|min:1',
            'scoring_system' => 'nullable|array|max:50',
            'scoring_system.*' => 'string|max:200',
            'pass_threshold' => 'nullable|numeric|min:0|max:100',
            'fail_threshold' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'is_standard' => 'boolean',
            'version' => 'nullable|string|max:50|regex:/^\d+\.\d+$/',
            'tags' => 'nullable|array|max:20',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string|max:1000',
            'department' => 'nullable|string|max:100',
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high', 'urgent', 'critical'])],
            'risk_level' => ['nullable', 'string', Rule::in(['low', 'medium', 'high', 'extreme'])],
            'compliance_requirements' => 'nullable|array|max:20',
            'compliance_requirements.*' => 'string|max:200',
            'quality_standards' => 'nullable|array|max:20',
            'quality_standards.*' => 'string|max:200',
            'safety_requirements' => 'nullable|array|max:20',
            'safety_requirements.*' => 'string|max:200',
            'training_required' => 'boolean',
            'certification_required' => 'boolean',
            'documentation_required' => 'nullable|array|max:20',
            'documentation_required.*' => 'string|max:200',
            'environmental_considerations' => 'nullable|array|max:20',
            'environmental_considerations.*' => 'string|max:200',
            'budget_code' => 'nullable|string|max:50',
            'cost_center' => 'nullable|string|max:50',
            'project_code' => 'nullable|string|max:50',
            'estimated_completion_time' => 'nullable|numeric|min:0.1|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0|max:999999.99',
            'required_skills' => 'nullable|array|max:20',
            'required_skills.*' => 'string|max:100',
            'required_tools' => 'nullable|array|max:20',
            'required_tools.*' => 'string|max:100',
            'required_parts' => 'nullable|array|max:20',
            'required_parts.*' => 'string|max:100',
            'work_instructions' => 'nullable|array|max:50',
            'work_instructions.*' => 'string|max:500',
            'reference_documents' => 'nullable|array|max:20',
            'reference_documents.*' => 'string|max:200',
            'best_practices' => 'nullable|array|max:20',
            'best_practices.*' => 'string|max:200',
            'lessons_learned' => 'nullable|array|max:20',
            'lessons_learned.*' => 'string|max:200',
            'continuous_improvement' => 'nullable|array|max:20',
            'continuous_improvement.*' => 'string|max:200',
            'audit_frequency' => 'nullable|integer|min:1|max:365',
            'last_review_date' => 'nullable|date|before_or_equal:today',
            'next_review_date' => 'nullable|date|after:today',
            'approval_workflow' => 'nullable|array|max:20',
            'approval_workflow.*' => 'string|max:200',
            'escalation_procedures' => 'nullable|array|max:20',
            'escalation_procedures.*' => 'string|max:200',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la plantilla es obligatorio.',
            'name.unique' => 'Ya existe una plantilla con este nombre.',
            'template_type.required' => 'El tipo de plantilla es obligatorio.',
            'template_type.in' => 'El tipo de plantilla seleccionado no es válido.',
            'category.required' => 'La categoría es obligatoria.',
            'checklist_items.required' => 'Los elementos de la lista de verificación son obligatorios.',
            'checklist_items.min' => 'Debe haber al menos un elemento en la lista de verificación.',
            'checklist_items.max' => 'No puede haber más de 100 elementos en la lista de verificación.',
            'version.regex' => 'La versión debe tener el formato X.Y (ejemplo: 1.0).',
            'pass_threshold.min' => 'El umbral de aprobación debe ser mayor o igual a 0.',
            'pass_threshold.max' => 'El umbral de aprobación no puede ser mayor a 100.',
            'fail_threshold.min' => 'El umbral de fallo debe ser mayor o igual a 0.',
            'fail_threshold.max' => 'El umbral de fallo no puede ser mayor a 100.',
            'estimated_completion_time.min' => 'El tiempo estimado de finalización debe ser mayor a 0.',
            'estimated_completion_time.max' => 'El tiempo estimado de finalización no puede ser mayor a 1000 horas.',
            'estimated_cost.min' => 'El costo estimado debe ser mayor o igual a 0.',
            'estimated_cost.max' => 'El costo estimado no puede ser mayor a 999,999.99.',
            'audit_frequency.min' => 'La frecuencia de auditoría debe ser mayor a 0 días.',
            'audit_frequency.max' => 'La frecuencia de auditoría no puede ser mayor a 365 días.',
            'last_review_date.before_or_equal' => 'La fecha de última revisión no puede ser futura.',
            'next_review_date.after' => 'La fecha de próxima revisión debe ser futura.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Establecer valores por defecto para campos booleanos
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'is_standard' => $this->boolean('is_standard', false),
            'training_required' => $this->boolean('training_required', false),
            'certification_required' => $this->boolean('certification_required', false),
        ]);

        // Establecer valores por defecto para enums
        if (!$this->filled('priority')) {
            $this->merge(['priority' => 'medium']);
        }

        if (!$this->filled('risk_level')) {
            $this->merge(['risk_level' => 'low']);
        }

        if (!$this->filled('version')) {
            $this->merge(['version' => '1.0']);
        }

        // Decodificar campos JSON si vienen como strings
        $jsonFields = [
            'checklist_items', 'required_items', 'optional_items', 'conditional_items',
            'item_order', 'scoring_system', 'tags', 'compliance_requirements',
            'quality_standards', 'safety_requirements', 'documentation_required',
            'environmental_considerations', 'required_skills', 'required_tools',
            'required_parts', 'work_instructions', 'reference_documents',
            'best_practices', 'lessons_learned', 'continuous_improvement',
            'approval_workflow', 'escalation_procedures'
        ];

        foreach ($jsonFields as $field) {
            if ($this->filled($field) && is_string($this->input($field))) {
                $decoded = json_decode($this->input($field), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->merge([$field => $decoded]);
                }
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Validar que los umbrales sean coherentes
            if ($this->filled('pass_threshold') && $this->filled('fail_threshold')) {
                if ($this->pass_threshold <= $this->fail_threshold) {
                    $validator->errors()->add('pass_threshold', 'El umbral de aprobación debe ser mayor al umbral de fallo.');
                }
            }

            // Validar que la fecha de próxima revisión sea posterior a la última revisión
            if ($this->filled('last_review_date') && $this->filled('next_review_date')) {
                if ($this->next_review_date <= $this->last_review_date) {
                    $validator->errors()->add('next_review_date', 'La fecha de próxima revisión debe ser posterior a la fecha de última revisión.');
                }
            }

            // Validar que los elementos requeridos estén en la lista principal
            if ($this->filled('checklist_items') && $this->filled('required_items')) {
                $mainItems = $this->checklist_items;
                $requiredItems = $this->required_items;
                
                foreach ($requiredItems as $index => $requiredItem) {
                    if (!in_array($requiredItem, $mainItems)) {
                        $validator->errors()->add("required_items.{$index}", 'Los elementos requeridos deben estar incluidos en la lista principal de verificación.');
                    }
                }
            }

            // Validar que los elementos opcionales estén en la lista principal
            if ($this->filled('checklist_items') && $this->filled('optional_items')) {
                $mainItems = $this->checklist_items;
                $optionalItems = $this->optional_items;
                
                foreach ($optionalItems as $index => $optionalItem) {
                    if (!in_array($optionalItem, $mainItems)) {
                        $validator->errors()->add("optional_items.{$index}", 'Los elementos opcionales deben estar incluidos en la lista principal de verificación.');
                    }
                }
            }

            // Validar que los elementos condicionales estén en la lista principal
            if ($this->filled('checklist_items') && $this->filled('conditional_items')) {
                $mainItems = $this->checklist_items;
                $conditionalItems = $this->conditional_items;
                
                foreach ($conditionalItems as $index => $conditionalItem) {
                    if (!in_array($conditionalItem, $mainItems)) {
                        $validator->errors()->add("conditional_items.{$index}", 'Los elementos condicionales deben estar incluidos en la lista principal de verificación.');
                    }
                }
            }

            // Validar que no haya elementos duplicados en las listas
            if ($this->filled('checklist_items')) {
                $duplicates = array_diff_assoc($this->checklist_items, array_unique($this->checklist_items));
                if (!empty($duplicates)) {
                    $validator->errors()->add('checklist_items', 'No puede haber elementos duplicados en la lista de verificación.');
                }
            }

            // Validar que los elementos requeridos, opcionales y condicionales no se superpongan
            if ($this->filled('required_items') && $this->filled('optional_items')) {
                $overlap = array_intersect($this->required_items, $this->optional_items);
                if (!empty($overlap)) {
                    $validator->errors()->add('optional_items', 'Los elementos opcionales no pueden ser también requeridos.');
                }
            }

            if ($this->filled('required_items') && $this->filled('conditional_items')) {
                $overlap = array_intersect($this->required_items, $this->conditional_items);
                if (!empty($overlap)) {
                    $validator->errors()->add('conditional_items', 'Los elementos condicionales no pueden ser también requeridos.');
                }
            }

            if ($this->filled('optional_items') && $this->filled('conditional_items')) {
                $overlap = array_intersect($this->optional_items, $this->conditional_items);
                if (!empty($overlap)) {
                    $validator->errors()->add('conditional_items', 'Los elementos condicionales no pueden ser también opcionales.');
                }
            }

            // Validar que el orden de los elementos sea coherente
            if ($this->filled('item_order') && $this->filled('checklist_items')) {
                $itemOrder = $this->item_order;
                $checklistItems = $this->checklist_items;
                
                if (count($itemOrder) !== count($checklistItems)) {
                    $validator->errors()->add('item_order', 'El número de elementos en el orden debe coincidir con el número de elementos en la lista de verificación.');
                } else {
                    $sortedOrder = $itemOrder;
                    sort($sortedOrder);
                    if ($sortedOrder !== range(1, count($checklistItems))) {
                        $validator->errors()->add('item_order', 'El orden de los elementos debe ser secuencial del 1 al número total de elementos.');
                    }
                }
            }

            // Validar que el sistema de puntuación sea coherente
            if ($this->filled('scoring_system') && $this->filled('checklist_items')) {
                $scoringSystem = $this->scoring_system;
                $checklistItems = $this->checklist_items;
                
                if (count($scoringSystem) !== count($checklistItems)) {
                    $validator->errors()->add('scoring_system', 'El número de elementos en el sistema de puntuación debe coincidir con el número de elementos en la lista de verificación.');
                }
            }

            // Validar que los campos de costo y tiempo sean coherentes
            if ($this->filled('estimated_completion_time') && $this->estimated_completion_time > 0) {
                if ($this->estimated_completion_time < 0.1) {
                    $validator->errors()->add('estimated_completion_time', 'El tiempo estimado de finalización debe ser al menos 0.1 horas.');
                }
            }

            if ($this->filled('estimated_cost') && $this->estimated_cost > 0) {
                if ($this->estimated_cost < 0.01) {
                    $validator->errors()->add('estimated_cost', 'El costo estimado debe ser al menos 0.01.');
                }
            }

            // Validar que la frecuencia de auditoría sea coherente con las fechas de revisión
            if ($this->filled('audit_frequency') && $this->filled('next_review_date')) {
                $frequency = $this->audit_frequency;
                $nextReview = $this->next_review_date;
                $lastReview = $this->last_review_date ?? now();
                
                $expectedNextReview = $lastReview->addDays($frequency);
                $tolerance = 5; // 5 días de tolerancia
                
                if (abs($nextReview->diffInDays($expectedNextReview)) > $tolerance) {
                    $validator->errors()->add('next_review_date', 'La fecha de próxima revisión debe ser coherente con la frecuencia de auditoría establecida.');
                }
            }
        });
    }
}
