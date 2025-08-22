<?php

namespace App\Http\Requests\Api\V1\MaintenanceTask;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'task_type' => ['sometimes', 'string', Rule::in([
                'inspection', 'repair', 'replacement', 'preventive', 'corrective', 'emergency', 'upgrade', 'calibration', 'cleaning', 'testing', 'other'
            ])],
            'status' => ['sometimes', 'string', Rule::in([
                'pending', 'in_progress', 'paused', 'completed', 'cancelled', 'on_hold'
            ])],
            'priority' => ['sometimes', 'string', Rule::in([
                'low', 'medium', 'high', 'urgent', 'critical'
            ])],
            'assigned_to' => ['sometimes', 'nullable', 'exists:users,id'],
            'assigned_by' => ['sometimes', 'nullable', 'exists:users,id'],
            'equipment_id' => ['sometimes', 'nullable', 'exists:equipment,id'],
            'location_id' => ['sometimes', 'nullable', 'exists:locations,id'],
            'schedule_id' => ['sometimes', 'nullable', 'exists:maintenance_schedules,id'],
            'organization_id' => ['sometimes', 'nullable', 'exists:organizations,id'],
            'due_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'estimated_hours' => ['sometimes', 'numeric', 'min:0.1', 'max:1000'],
            'estimated_cost' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'actual_start_time' => ['sometimes', 'nullable', 'date', 'before_or_equal:due_date'],
            'actual_end_time' => ['sometimes', 'nullable', 'date', 'after:actual_start_time'],
            'progress_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'completion_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'actual_hours' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:1000'],
            'actual_cost' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'quality_score' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'materials_used' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'tools_required' => ['sometimes', 'nullable', 'string', 'max:500'],
            'safety_requirements' => ['sometimes', 'nullable', 'string', 'max:500'],
            'technical_requirements' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'documentation_required' => ['sometimes', 'boolean'],
            'photos_required' => ['sometimes', 'boolean'],
            'signature_required' => ['sometimes', 'boolean'],
            'approval_required' => ['sometimes', 'boolean'],
            'is_recurring' => ['sometimes', 'boolean'],
            'recurrence_pattern' => ['sometimes', 'nullable', 'string', 'max:255'],
            'next_occurrence' => ['sometimes', 'nullable', 'date', 'after:due_date'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'attachments' => ['sometimes', 'nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,mp4,avi,mov', 'max:10240'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.string' => 'El título de la tarea debe ser una cadena de texto.',
            'title.max' => 'El título de la tarea no puede exceder 255 caracteres.',
            'task_type.string' => 'El tipo de tarea debe ser una cadena de texto.',
            'task_type.in' => 'El tipo de tarea seleccionado no es válido.',
            'status.string' => 'El estado de la tarea debe ser una cadena de texto.',
            'status.in' => 'El estado seleccionado no es válido.',
            'priority.string' => 'La prioridad de la tarea debe ser una cadena de texto.',
            'priority.in' => 'La prioridad seleccionada no es válida.',
            'assigned_to.exists' => 'El usuario asignado no existe.',
            'assigned_by.exists' => 'El usuario que asigna no existe.',
            'equipment_id.exists' => 'El equipo seleccionado no existe.',
            'location_id.exists' => 'La ubicación seleccionada no existe.',
            'schedule_id.exists' => 'El programa de mantenimiento seleccionado no existe.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'due_date.after_or_equal' => 'La fecha de vencimiento debe ser hoy o posterior.',
            'estimated_hours.numeric' => 'Las horas estimadas deben ser un número.',
            'estimated_hours.min' => 'Las horas estimadas deben ser al menos 0.1.',
            'estimated_hours.max' => 'Las horas estimadas no pueden exceder 1000.',
            'estimated_cost.numeric' => 'El costo estimado debe ser un número.',
            'estimated_cost.min' => 'El costo estimado no puede ser negativo.',
            'estimated_cost.max' => 'El costo estimado no puede exceder 999,999.99.',
            'actual_start_time.date' => 'La hora de inicio real debe ser una fecha válida.',
            'actual_start_time.before_or_equal' => 'La hora de inicio real debe ser anterior o igual a la fecha de vencimiento.',
            'actual_end_time.date' => 'La hora de finalización real debe ser una fecha válida.',
            'actual_end_time.after' => 'La hora de finalización real debe ser posterior a la hora de inicio.',
            'progress_percentage.numeric' => 'El porcentaje de progreso debe ser un número.',
            'progress_percentage.min' => 'El porcentaje de progreso no puede ser negativo.',
            'progress_percentage.max' => 'El porcentaje de progreso no puede exceder 100.',
            'completion_notes.max' => 'Las notas de finalización no pueden exceder 1000 caracteres.',
            'actual_hours.numeric' => 'Las horas reales deben ser un número.',
            'actual_hours.min' => 'Las horas reales no pueden ser negativas.',
            'actual_hours.max' => 'Las horas reales no pueden exceder 1000.',
            'actual_cost.numeric' => 'El costo real debe ser un número.',
            'actual_cost.min' => 'El costo real no puede ser negativo.',
            'actual_cost.max' => 'El costo real no puede exceder 999,999.99.',
            'quality_score.numeric' => 'La puntuación de calidad debe ser un número.',
            'quality_score.min' => 'La puntuación de calidad no puede ser negativa.',
            'quality_score.max' => 'La puntuación de calidad no puede exceder 100.',
            'materials_used.max' => 'Los materiales utilizados no pueden exceder 1000 caracteres.',
            'tools_required.max' => 'Las herramientas requeridas no pueden exceder 500 caracteres.',
            'safety_requirements.max' => 'Los requisitos de seguridad no pueden exceder 500 caracteres.',
            'technical_requirements.max' => 'Los requisitos técnicos no pueden exceder 1000 caracteres.',
            'recurrence_pattern.max' => 'El patrón de recurrencia no puede exceder 255 caracteres.',
            'next_occurrence.date' => 'La próxima ocurrencia debe ser una fecha válida.',
            'next_occurrence.after' => 'La próxima ocurrencia debe ser posterior a la fecha de vencimiento.',
            'tags.array' => 'Las etiquetas deben ser un array.',
            'tags.*.string' => 'Cada etiqueta debe ser una cadena de texto.',
            'tags.*.max' => 'Cada etiqueta no puede exceder 100 caracteres.',
            'attachments.array' => 'Los archivos adjuntos deben ser un array.',
            'attachments.*.file' => 'Cada archivo adjunto debe ser un archivo válido.',
            'attachments.*.mimes' => 'Los archivos adjuntos deben ser de tipo: pdf, doc, docx, jpg, jpeg, png, mp4, avi, mov.',
            'attachments.*.max' => 'Cada archivo adjunto no puede exceder 10MB.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'título de la tarea',
            'description' => 'descripción',
            'task_type' => 'tipo de tarea',
            'status' => 'estado',
            'priority' => 'prioridad',
            'assigned_to' => 'usuario asignado',
            'assigned_by' => 'usuario que asigna',
            'equipment_id' => 'equipo',
            'location_id' => 'ubicación',
            'schedule_id' => 'programa de mantenimiento',
            'organization_id' => 'organización',
            'due_date' => 'fecha de vencimiento',
            'estimated_hours' => 'horas estimadas',
            'estimated_cost' => 'costo estimado',
            'actual_start_time' => 'hora de inicio real',
            'actual_end_time' => 'hora de finalización real',
            'progress_percentage' => 'porcentaje de progreso',
            'completion_notes' => 'notas de finalización',
            'actual_hours' => 'horas reales',
            'actual_cost' => 'costo real',
            'quality_score' => 'puntuación de calidad',
            'materials_used' => 'materiales utilizados',
            'tools_required' => 'herramientas requeridas',
            'safety_requirements' => 'requisitos de seguridad',
            'technical_requirements' => 'requisitos técnicos',
            'documentation_required' => 'documentación requerida',
            'photos_required' => 'fotos requeridas',
            'signature_required' => 'firma requerida',
            'approval_required' => 'aprobación requerida',
            'is_recurring' => 'es recurrente',
            'recurrence_pattern' => 'patrón de recurrencia',
            'next_occurrence' => 'próxima ocurrencia',
            'tags' => 'etiquetas',
            'notes' => 'notas',
            'attachments' => 'archivos adjuntos',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'documentation_required' => $this->boolean('documentation_required'),
            'photos_required' => $this->boolean('photos_required'),
            'signature_required' => $this->boolean('signature_required'),
            'approval_required' => $this->boolean('approval_required'),
            'is_recurring' => $this->boolean('is_recurring'),
        ]);
    }
}
