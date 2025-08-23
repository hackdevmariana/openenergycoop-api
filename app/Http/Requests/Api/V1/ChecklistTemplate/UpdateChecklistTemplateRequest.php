<?php

namespace App\Http\Requests\Api\V1\ChecklistTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateChecklistTemplateRequest extends FormRequest
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
        $checklistTemplateId = $this->route('checklist_template')->id ?? $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('checklist_templates', 'name')->ignore($checklistTemplateId)],
            'description' => 'sometimes|nullable|string|max:65535',
            'template_type' => ['sometimes', 'string', Rule::in([
                'maintenance', 'inspection', 'safety', 'quality', 'compliance',
                'audit', 'training', 'operations', 'procedure', 'workflow'
            ])],
            'category' => 'sometimes|string|max:100',
            'subcategory' => 'sometimes|nullable|string|max:100',
            'checklist_items' => 'sometimes|array|min:1|max:100',
            'checklist_items.*' => 'required|string|max:500',
            'required_items' => 'sometimes|nullable|array|max:100',
            'required_items.*' => 'string|max:500',
            'optional_items' => 'sometimes|nullable|array|max:100',
            'optional_items.*' => 'string|max:500',
            'conditional_items' => 'sometimes|nullable|array|max:100',
            'conditional_items.*' => 'string|max:500',
            'item_order' => 'sometimes|nullable|array|max:100',
            'item_order.*' => 'integer|min:1',
            'scoring_system' => 'sometimes|nullable|array|max:50',
            'scoring_system.*' => 'string|max:200',
            'pass_threshold' => 'sometimes|nullable|numeric|min:0|max:100',
            'fail_threshold' => 'sometimes|nullable|numeric|min:0|max:100',
            'is_active' => 'sometimes|boolean',
            'is_standard' => 'sometimes|boolean',
            'version' => 'sometimes|nullable|string|max:50|regex:/^\d+\.\d+$/',
            'tags' => 'sometimes|nullable|array|max:20',
            'tags.*' => 'string|max:50',
            'notes' => 'sometimes|nullable|string|max:1000',
            'department' => 'sometimes|nullable|string|max:100',
            'priority' => ['sometimes', 'nullable', 'string', Rule::in(['low', 'medium', 'high', 'urgent', 'critical'])],
            'risk_level' => ['sometimes', 'nullable', 'string', Rule::in(['low', 'medium', 'high', 'extreme'])],
            'compliance_requirements' => 'sometimes|nullable|array|max:20',
            'compliance_requirements.*' => 'string|max:200',
            'quality_standards' => 'sometimes|nullable|array|max:20',
            'quality_standards.*' => 'string|max:200',
            'safety_requirements' => 'sometimes|nullable|array|max:20',
            'safety_requirements.*' => 'string|max:200',
            'training_required' => 'sometimes|boolean',
            'certification_required' => 'sometimes|boolean',
            'documentation_required' => 'sometimes|nullable|array|max:20',
            'documentation_required.*' => 'string|max:200',
            'environmental_considerations' => 'sometimes|nullable|array|max:20',
            'environmental_considerations.*' => 'string|max:200',
            'budget_code' => 'sometimes|nullable|string|max:50',
            'cost_center' => 'sometimes|nullable|string|max:50',
            'project_code' => 'sometimes|nullable|string|max:50',
            'estimated_completion_time' => 'sometimes|nullable|numeric|min:0.1|max:1000',
            'estimated_cost' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'required_skills' => 'sometimes|nullable|array|max:20',
            'required_skills.*' => 'string|max:100',
            'required_tools' => 'sometimes|nullable|array|max:20',
            'required_tools.*' => 'string|max:100',
            'required_parts' => 'sometimes|nullable|array|max:20',
            'required_parts.*' => 'string|max:100',
            'work_instructions' => 'sometimes|nullable|array|max:50',
            'work_instructions.*' => 'string|max:500',
            'reference_documents' => 'sometimes|nullable|array|max:20',
            'reference_documents.*' => 'string|max:200',
            'best_practices' => 'sometimes|nullable|array|max:20',
            'best_practices.*' => 'string|max:200',
            'lessons_learned' => 'sometimes|nullable|array|max:20',
            'lessons_learned.*' => 'string|max:200',
            'continuous_improvement' => 'sometimes|nullable|array|max:20',
            'continuous_improvement.*' => 'string|max:200',
            'audit_frequency' => 'sometimes|nullable|integer|min:1|max:365',
            'last_review_date' => 'sometimes|nullable|date|before_or_equal:today',
            'next_review_date' => 'sometimes|nullable|date|after:today',
            'approval_workflow' => 'sometimes|nullable|array|max:20',
            'approval_workflow.*' => 'string|max:200',
            'escalation_procedures' => 'sometimes|nullable|array|max:20',
            'escalation_procedures.*' => 'string|max:200',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe una plantilla con este nombre.',
            'template_type.in' => 'El tipo de plantilla seleccionado no es válido.',
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

            // Protección de campos críticos para plantillas estándar aprobadas
            $checklistTemplate = $this->route('checklist_template');
            if ($checklistTemplate && $checklistTemplate->is_standard && $checklistTemplate->isApproved()) {
                $criticalFields = ['template_type', 'category', 'checklist_items', 'required_items', 'pass_threshold', 'fail_threshold'];
                
                foreach ($criticalFields as $field) {
                    if ($this->filled($field)) {
                        $validator->errors()->add($field, 'No se pueden modificar campos críticos en plantillas estándar aprobadas sin una nueva aprobación.');
                    }
                }
            }

            // Protección de campos de aprobación
            if ($this->filled('approved_at') || $this->filled('approved_by')) {
                $validator->errors()->add('approved_at', 'Los campos de aprobación no pueden ser modificados directamente.');
            }

            // Validación de cambios de estado para plantillas con tareas activas
            if ($checklistTemplate && $this->filled('is_active')) {
                $hasActiveTasks = $checklistTemplate->maintenanceTasks()->where('status', 'in_progress')->exists();
                
                if ($hasActiveTasks && !$this->boolean('is_active')) {
                    $validator->errors()->add('is_active', 'No se puede desactivar una plantilla que tiene tareas en progreso.');
                }
            }

            // Validación de cambios de versión
            if ($this->filled('version') && $checklistTemplate) {
                $currentVersion = $checklistTemplate->version;
                $newVersion = $this->version;
                
                if (version_compare($newVersion, $currentVersion, '<=')) {
                    $validator->errors()->add('version', 'La nueva versión debe ser mayor a la versión actual.');
                }
            }

            // Validación de cambios de prioridad para plantillas críticas
            if ($this->filled('priority') && $checklistTemplate) {
                $currentPriority = $checklistTemplate->priority;
                $newPriority = $this->priority;
                
                if (in_array($currentPriority, ['urgent', 'critical']) && in_array($newPriority, ['low', 'medium'])) {
                    $validator->errors()->add('priority', 'No se puede reducir significativamente la prioridad de plantillas críticas sin justificación.');
                }
            }

            // Validación de cambios de nivel de riesgo para plantillas de alto riesgo
            if ($this->filled('risk_level') && $checklistTemplate) {
                $currentRiskLevel = $checklistTemplate->risk_level;
                $newRiskLevel = $this->risk_level;
                
                if (in_array($currentRiskLevel, ['high', 'extreme']) && in_array($newRiskLevel, ['low', 'medium'])) {
                    $validator->errors()->add('risk_level', 'No se puede reducir significativamente el nivel de riesgo de plantillas de alto riesgo sin justificación.');
                }
            }

            // Validación de cambios en elementos de seguridad
            if ($this->filled('safety_requirements') && $checklistTemplate) {
                $currentSafety = $checklistTemplate->safety_requirements ?? [];
                $newSafety = $this->safety_requirements;
                
                if (count($newSafety) < count($currentSafety)) {
                    $validator->errors()->add('safety_requirements', 'No se pueden reducir los requisitos de seguridad sin una evaluación de riesgo.');
                }
            }

            // Validación de cambios en requisitos de cumplimiento
            if ($this->filled('compliance_requirements') && $checklistTemplate) {
                $currentCompliance = $checklistTemplate->compliance_requirements ?? [];
                $newCompliance = $this->compliance_requirements;
                
                if (count($newCompliance) < count($currentCompliance)) {
                    $validator->errors()->add('compliance_requirements', 'No se pueden reducir los requisitos de cumplimiento sin una evaluación de riesgo.');
                }
            }
        });
    }
}
