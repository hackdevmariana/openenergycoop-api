<?php

namespace App\Http\Requests\Api\V1\Milestone;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'milestone_type' => ['required', Rule::in(\App\Models\Milestone::getMilestoneTypes())],
            'status' => ['required', Rule::in(\App\Models\Milestone::getStatuses())],
            'priority' => ['required', Rule::in(\App\Models\Milestone::getPriorities())],
            'target_date' => 'required|date|after:today',
            'start_date' => 'nullable|date|before_or_equal:target_date',
            'completion_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:target_date',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'budget' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'parent_milestone_id' => 'nullable|exists:milestones,id',
            'assigned_to' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:milestones,id',
            'risk_level' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio',
            'title.max' => 'El título no puede tener más de 255 caracteres',
            'description.max' => 'La descripción no puede tener más de 1000 caracteres',
            'milestone_type.required' => 'El tipo de hito es obligatorio',
            'milestone_type.in' => 'El tipo de hito seleccionado no es válido',
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado seleccionado no es válido',
            'priority.required' => 'La prioridad es obligatoria',
            'priority.in' => 'La prioridad seleccionada no es válida',
            'target_date.required' => 'La fecha objetivo es obligatoria',
            'target_date.after' => 'La fecha objetivo debe ser posterior a hoy',
            'start_date.before_or_equal' => 'La fecha de inicio debe ser anterior o igual a la fecha objetivo',
            'completion_date.after_or_equal' => 'La fecha de finalización debe ser posterior o igual a la fecha de inicio',
            'completion_date.before_or_equal' => 'La fecha de finalización debe ser anterior o igual a la fecha objetivo',
            'progress_percentage.min' => 'El porcentaje de progreso no puede ser menor a 0',
            'progress_percentage.max' => 'El porcentaje de progreso no puede ser mayor a 100',
            'budget.min' => 'El presupuesto no puede ser negativo',
            'actual_cost.min' => 'El costo real no puede ser negativo',
            'parent_milestone_id.exists' => 'El hito padre seleccionado no existe',
            'assigned_to.exists' => 'El usuario asignado no existe',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 50 caracteres',
            'dependencies.*.exists' => 'Uno de los hitos de dependencia no existe',
            'notes.max' => 'Las notas no pueden tener más de 2000 caracteres',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'progress_percentage' => $this->progress_percentage ?? 0,
            'budget' => $this->budget ? (float) $this->budget : null,
            'actual_cost' => $this->actual_cost ? (float) $this->actual_cost : null,
            'tags' => $this->tags ? array_filter($this->tags) : [],
            'dependencies' => $this->dependencies ? array_filter($this->dependencies) : [],
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que si es un hito hijo, el padre no sea también un hito hijo
            if ($this->parent_milestone_id) {
                $parentMilestone = \App\Models\Milestone::find($this->parent_milestone_id);
                if ($parentMilestone && $parentMilestone->parent_milestone_id) {
                    $validator->errors()->add('parent_milestone_id', 'No se puede asignar un hito hijo a otro hito hijo');
                }
            }

            // Validar que no haya dependencias circulares
            if ($this->dependencies && $this->parent_milestone_id) {
                $parentId = $this->parent_milestone_id;
                foreach ($this->dependencies as $dependencyId) {
                    if ($dependencyId == $parentId) {
                        $validator->errors()->add('dependencies', 'No se puede crear una dependencia circular con el hito padre');
                        break;
                    }
                }
            }

            // Validar que el progreso sea coherente con el estado
            if ($this->status === \App\Models\Milestone::STATUS_COMPLETED && $this->progress_percentage < 100) {
                $validator->errors()->add('progress_percentage', 'Un hito completado debe tener 100% de progreso');
            }

            if ($this->status === \App\Models\Milestone::STATUS_NOT_STARTED && $this->progress_percentage > 0) {
                $validator->errors()->add('progress_percentage', 'Un hito no iniciado debe tener 0% de progreso');
            }

            // Validar fechas coherentes
            if ($this->start_date && $this->completion_date && $this->start_date > $this->completion_date) {
                $validator->errors()->add('completion_date', 'La fecha de finalización debe ser posterior a la fecha de inicio');
            }

            // Validar que el costo real no exceda el presupuesto
            if ($this->budget && $this->actual_cost && $this->actual_cost > $this->budget) {
                $validator->errors()->add('actual_cost', 'El costo real no puede exceder el presupuesto');
            }
        });
    }
}
