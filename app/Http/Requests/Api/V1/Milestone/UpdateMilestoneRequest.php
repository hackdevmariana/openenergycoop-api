<?php

namespace App\Http\Requests\Api\V1\Milestone;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'milestone_type' => ['sometimes', Rule::in(\App\Models\Milestone::getMilestoneTypes())],
            'status' => ['sometimes', Rule::in(\App\Models\Milestone::getStatuses())],
            'priority' => ['sometimes', Rule::in(\App\Models\Milestone::getPriorities())],
            'target_date' => 'sometimes|date|after:today',
            'start_date' => 'sometimes|nullable|date|before_or_equal:target_date',
            'completion_date' => 'sometimes|nullable|date|after_or_equal:start_date|before_or_equal:target_date',
            'progress_percentage' => 'sometimes|nullable|numeric|min:0|max:100',
            'budget' => 'sometimes|nullable|numeric|min:0',
            'actual_cost' => 'sometimes|nullable|numeric|min:0',
            'parent_milestone_id' => 'sometimes|nullable|exists:milestones,id',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:50',
            'dependencies' => 'sometimes|nullable|array',
            'dependencies.*' => 'exists:milestones,id',
            'risk_level' => 'sometimes|nullable|string|max:50',
            'notes' => 'sometimes|nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'El título no puede tener más de 255 caracteres',
            'description.max' => 'La descripción no puede tener más de 1000 caracteres',
            'milestone_type.in' => 'El tipo de hito seleccionado no es válido',
            'status.in' => 'El estado seleccionado no es válido',
            'priority.in' => 'La prioridad seleccionada no es válida',
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
            'budget' => $this->budget ? (float) $this->budget : null,
            'actual_cost' => $this->actual_cost ? (float) $this->actual_cost : null,
            'tags' => $this->tags ? array_filter($this->tags) : [],
            'dependencies' => $this->dependencies ? array_filter($this->dependencies) : [],
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $milestone = $this->route('milestone');

            // Validar que si es un hito hijo, el padre no sea también un hito hijo
            if ($this->has('parent_milestone_id') && $this->parent_milestone_id) {
                $parentMilestone = \App\Models\Milestone::find($this->parent_milestone_id);
                if ($parentMilestone && $parentMilestone->parent_milestone_id) {
                    $validator->errors()->add('parent_milestone_id', 'No se puede asignar un hito hijo a otro hito hijo');
                }

                // Evitar referencias circulares
                if ($this->parent_milestone_id == $milestone->id) {
                    $validator->errors()->add('parent_milestone_id', 'Un hito no puede ser su propio padre');
                }
            }

            // Validar que no haya dependencias circulares
            if ($this->has('dependencies') && $this->dependencies) {
                foreach ($this->dependencies as $dependencyId) {
                    if ($dependencyId == $milestone->id) {
                        $validator->errors()->add('dependencies', 'Un hito no puede depender de sí mismo');
                        break;
                    }

                    // Verificar dependencias circulares en la jerarquía
                    if ($this->has('parent_milestone_id') && $this->parent_milestone_id) {
                        if ($dependencyId == $this->parent_milestone_id) {
                            $validator->errors()->add('dependencies', 'No se puede crear una dependencia circular con el hito padre');
                            break;
                        }
                    }
                }
            }

            // Validar que el progreso sea coherente con el estado
            if ($this->has('status') && $this->has('progress_percentage')) {
                if ($this->status === \App\Models\Milestone::STATUS_COMPLETED && $this->progress_percentage < 100) {
                    $validator->errors()->add('progress_percentage', 'Un hito completado debe tener 100% de progreso');
                }

                if ($this->status === \App\Models\Milestone::STATUS_NOT_STARTED && $this->progress_percentage > 0) {
                    $validator->errors()->add('progress_percentage', 'Un hito no iniciado debe tener 0% de progreso');
                }
            }

            // Validar fechas coherentes
            if ($this->has('start_date') && $this->has('completion_date') && $this->start_date && $this->completion_date) {
                if ($this->start_date > $this->completion_date) {
                    $validator->errors()->add('completion_date', 'La fecha de finalización debe ser posterior a la fecha de inicio');
                }
            }

            // Validar que el costo real no exceda el presupuesto
            if ($this->has('budget') && $this->has('actual_cost') && $this->budget && $this->actual_cost) {
                if ($this->actual_cost > $this->budget) {
                    $validator->errors()->add('actual_cost', 'El costo real no puede exceder el presupuesto');
                }
            }

            // Proteger campos críticos si el hito ya está en progreso o completado
            if ($milestone->status === \App\Models\Milestone::STATUS_IN_PROGRESS || $milestone->status === \App\Models\Milestone::STATUS_COMPLETED) {
                if ($this->has('milestone_type') && $this->milestone_type !== $milestone->milestone_type) {
                    $validator->errors()->add('milestone_type', 'No se puede cambiar el tipo de hito una vez iniciado');
                }

                if ($this->has('parent_milestone_id') && $this->parent_milestone_id !== $milestone->parent_milestone_id) {
                    $validator->errors()->add('parent_milestone_id', 'No se puede cambiar el hito padre una vez iniciado');
                }
            }

            // Proteger contra cambios de estado que no sean válidos
            if ($this->has('status') && $this->status !== $milestone->status) {
                if ($milestone->status === \App\Models\Milestone::STATUS_COMPLETED && $this->status !== \App\Models\Milestone::STATUS_COMPLETED) {
                    $validator->errors()->add('status', 'No se puede cambiar el estado de un hito completado');
                }

                if ($milestone->status === \App\Models\Milestone::STATUS_CANCELLED && $this->status !== \App\Models\Milestone::STATUS_CANCELLED) {
                    $validator->errors()->add('status', 'No se puede cambiar el estado de un hito cancelado');
                }
            }
        });
    }
}
