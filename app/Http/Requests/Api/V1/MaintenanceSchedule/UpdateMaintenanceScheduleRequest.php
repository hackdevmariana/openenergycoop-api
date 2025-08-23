<?php

namespace App\Http\Requests\Api\V1\MaintenanceSchedule;

use App\Models\MaintenanceSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceScheduleRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:65535',
            'schedule_type' => [
                'sometimes',
                'string',
                Rule::in(array_keys(MaintenanceSchedule::getScheduleTypes())),
            ],
            'frequency_type' => [
                'sometimes',
                'string',
                Rule::in(array_keys(MaintenanceSchedule::getFrequencyTypes())),
            ],
            'frequency_value' => 'sometimes|integer|min:1|max:365',
            'priority' => [
                'sometimes',
                'string',
                Rule::in(array_keys(MaintenanceSchedule::getPriorities())),
            ],
            'department' => 'sometimes|string|max:100',
            'category' => 'sometimes|string|max:100',
            'equipment_id' => 'sometimes|integer|exists:equipment,id',
            'location_id' => 'sometimes|integer|exists:locations,id',
            'vendor_id' => 'sometimes|integer|exists:vendors,id',
            'task_template_id' => 'sometimes|integer|exists:task_templates,id',
            'checklist_template_id' => 'sometimes|integer|exists:checklist_templates,id',
            'estimated_duration_hours' => 'sometimes|numeric|min:0.1|max:168',
            'estimated_cost' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'auto_generate_tasks' => 'sometimes|boolean',
            'send_notifications' => 'sometimes|boolean',
            'notification_emails' => 'sometimes|array',
            'notification_emails.*' => 'email|max:255',
            'start_date' => 'sometimes|date|after_or_equal:today',
            'end_date' => 'sometimes|date|after:start_date',
            'next_maintenance_date' => 'sometimes|date|after_or_equal:start_date',
            'last_maintenance_date' => 'sometimes|date|before_or_equal:today',
            'maintenance_window_start' => 'sometimes|date_format:H:i',
            'maintenance_window_end' => 'sometimes|date_format:H:i|after:maintenance_window_start',
            'weather_dependent' => 'sometimes|boolean',
            'weather_conditions' => 'sometimes|array',
            'weather_conditions.*' => 'string|max:100',
            'required_skills' => 'sometimes|array',
            'required_skills.*' => 'string|max:100',
            'required_tools' => 'sometimes|array',
            'required_tools.*' => 'string|max:100',
            'required_materials' => 'sometimes|array',
            'required_materials.*' => 'string|max:100',
            'safety_requirements' => 'sometimes|array',
            'safety_requirements.*' => 'string|max:255',
            'quality_standards' => 'sometimes|array',
            'quality_standards.*' => 'string|max:255',
            'compliance_requirements' => 'sometimes|array',
            'compliance_requirements.*' => 'string|max:255',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:100',
            'notes' => 'sometimes|string|max:1000',
            'approved_by' => 'sometimes|integer|exists:users,id',
            'approved_at' => 'sometimes|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'schedule_type.in' => 'El tipo de programa debe ser uno de los valores permitidos.',
            'frequency_type.in' => 'El tipo de frecuencia debe ser uno de los valores permitidos.',
            'frequency_value.min' => 'El valor de frecuencia debe ser al menos 1.',
            'frequency_value.max' => 'El valor de frecuencia no puede ser mayor a 365.',
            'priority.in' => 'La prioridad debe ser uno de los valores permitidos.',
            'department.max' => 'El departamento no puede tener más de 100 caracteres.',
            'category.max' => 'La categoría no puede tener más de 100 caracteres.',
            'equipment_id.exists' => 'El equipo seleccionado no existe.',
            'location_id.exists' => 'La ubicación seleccionada no existe.',
            'vendor_id.exists' => 'El proveedor seleccionado no existe.',
            'task_template_id.exists' => 'La plantilla de tareas seleccionada no existe.',
            'checklist_template_id.exists' => 'La plantilla de checklist seleccionada no existe.',
            'estimated_duration_hours.min' => 'La duración estimada debe ser al menos 0.1 horas.',
            'estimated_duration_hours.max' => 'La duración estimada no puede ser mayor a 168 horas (1 semana).',
            'estimated_cost.min' => 'El costo estimado debe ser al menos 0.',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'next_maintenance_date.after_or_equal' => 'La próxima fecha de mantenimiento debe ser posterior o igual a la fecha de inicio.',
            'last_maintenance_date.before_or_equal' => 'La última fecha de mantenimiento debe ser hoy o anterior.',
            'maintenance_window_end.after' => 'La hora de fin de la ventana de mantenimiento debe ser posterior a la hora de inicio.',
            'notification_emails.*.email' => 'Cada email de notificación debe ser válido.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 100 caracteres.',
            'approved_by.exists' => 'El usuario aprobador debe existir en el sistema.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir campos de array
        $arrayFields = [
            'notification_emails', 'weather_conditions', 'required_skills',
            'required_tools', 'required_materials', 'safety_requirements',
            'quality_standards', 'compliance_requirements', 'tags'
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => json_decode($this->input($field), true)]);
            }
        }

        // Convertir campos booleanos
        $booleanFields = ['is_active', 'auto_generate_tasks', 'send_notifications', 'weather_dependent'];
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => (bool) $this->input($field)]);
            }
        }

        // Convertir campos numéricos
        $numericFields = ['frequency_value', 'estimated_duration_hours', 'estimated_cost'];
        foreach ($numericFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => (float) $this->input($field)]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que la frecuencia sea consistente con el tipo
            if ($this->filled('frequency_type') && $this->filled('frequency_value')) {
                $maxValues = [
                    'daily' => 365,
                    'weekly' => 52,
                    'monthly' => 12,
                    'quarterly' => 4,
                    'biannual' => 2,
                    'annual' => 1,
                    'custom' => 365,
                ];

                $maxValue = $maxValues[$this->frequency_type] ?? 365;
                if ($this->frequency_value > $maxValue) {
                    $validator->errors()->add('frequency_value', "El valor de frecuencia para '{$this->frequency_type}' no puede ser mayor a {$maxValue}.");
                }
            }

            // Validar que la ventana de mantenimiento sea razonable (máximo 24 horas)
            if ($this->filled('maintenance_window_start') && $this->filled('maintenance_window_end')) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $this->maintenance_window_start);
                $end = \Carbon\Carbon::createFromFormat('H:i', $this->maintenance_window_end);
                
                if ($end->lt($start)) {
                    $end->addDay();
                }
                
                $duration = $start->diffInHours($end);
                if ($duration > 24) {
                    $validator->errors()->add('maintenance_window_end', 'La ventana de mantenimiento no puede ser mayor a 24 horas.');
                }
            }

            // Validar que la duración estimada sea consistente con la frecuencia
            if ($this->filled('estimated_duration_hours') && $this->filled('frequency_type')) {
                $maxDurations = [
                    'daily' => 8,      // Máximo 8 horas para mantenimiento diario
                    'weekly' => 40,    // Máximo 40 horas para mantenimiento semanal
                    'monthly' => 80,   // Máximo 80 horas para mantenimiento mensual
                    'quarterly' => 160, // Máximo 160 horas para mantenimiento trimestral
                    'biannual' => 320, // Máximo 320 horas para mantenimiento semestral
                    'annual' => 640,   // Máximo 640 horas para mantenimiento anual
                    'custom' => 168,   // Máximo 1 semana para mantenimiento personalizado
                ];

                $maxDuration = $maxDurations[$this->frequency_type] ?? 168;
                if ($this->estimated_duration_hours > $maxDuration) {
                    $validator->errors()->add('estimated_duration_hours', "La duración estimada para frecuencia '{$this->frequency_type}' no puede ser mayor a {$maxDuration} horas.");
                }
            }

            // Validar que el costo estimado sea razonable
            if ($this->filled('estimated_cost') && $this->estimated_cost > 1000000) {
                $validator->errors()->add('estimated_cost', 'El costo estimado no puede ser mayor a 1,000,000.');
            }

            // Validar que las fechas sean consistentes
            if ($this->filled('start_date') && $this->filled('end_date')) {
                $startDate = \Carbon\Carbon::parse($this->start_date);
                $endDate = \Carbon\Carbon::parse($this->end_date);
                
                $duration = $startDate->diffInDays($endDate);
                if ($duration > 3650) { // Máximo 10 años
                    $validator->errors()->add('end_date', 'El programa no puede tener una duración mayor a 10 años.');
                }
            }

            // Validar que la próxima fecha de mantenimiento sea consistente con la frecuencia
            if ($this->filled('next_maintenance_date') && $this->filled('start_date') && $this->filled('frequency_type')) {
                $startDate = \Carbon\Carbon::parse($this->start_date);
                $nextDate = \Carbon\Carbon::parse($this->next_maintenance_date);
                
                $minIntervals = [
                    'daily' => 1,
                    'weekly' => 7,
                    'monthly' => 30,
                    'quarterly' => 90,
                    'biannual' => 180,
                    'annual' => 365,
                    'custom' => 1,
                ];

                $minInterval = $minIntervals[$this->frequency_type] ?? 1;
                $actualInterval = $startDate->diffInDays($nextDate);
                
                if ($actualInterval < $minInterval) {
                    $validator->errors()->add('next_maintenance_date', "La próxima fecha de mantenimiento debe ser al menos {$minInterval} día(s) después de la fecha de inicio para frecuencia '{$this->frequency_type}'.");
                }
            }

            // Validar que se proporcionen emails de notificación si se activan las notificaciones
            if ($this->boolean('send_notifications') && empty($this->notification_emails)) {
                $validator->errors()->add('notification_emails', 'Debe proporcionar al menos un email de notificación si se activan las notificaciones.');
            }

            // Validar que se proporcionen condiciones climáticas si depende del clima
            if ($this->boolean('weather_dependent') && empty($this->weather_conditions)) {
                $validator->errors()->add('weather_conditions', 'Debe proporcionar condiciones climáticas si el mantenimiento depende del clima.');
            }

            // Validar que se proporcione al menos una habilidad requerida
            if (empty($this->required_skills)) {
                $validator->errors()->add('required_skills', 'Debe proporcionar al menos una habilidad requerida.');
            }

            // Validar que se proporcione al menos una herramienta requerida
            if (empty($this->required_tools)) {
                $validator->errors()->add('required_tools', 'Debe proporcionar al menos una herramienta requerida.');
            }

            // Validar que se proporcione al menos un material requerido
            if (empty($this->required_materials)) {
                $validator->errors()->add('required_materials', 'Debe proporcionar al menos un material requerido.');
            }

            // Validar que no se modifiquen campos críticos si el programa ya está aprobado
            if ($this->maintenanceSchedule->isApproved()) {
                $criticalFields = ['schedule_type', 'frequency_type', 'frequency_value', 'start_date', 'end_date'];
                foreach ($criticalFields as $field) {
                    if ($this->has($field)) {
                        $validator->errors()->add($field, 'No se puede modificar este campo en un programa ya aprobado.');
                    }
                }
            }

            // Validar que no se modifiquen campos si el programa está en ejecución
            if ($this->maintenanceSchedule->isActive() && $this->maintenanceSchedule->next_maintenance_date) {
                $executionFields = ['schedule_type', 'frequency_type', 'frequency_value'];
                foreach ($executionFields as $field) {
                    if ($this->has($field)) {
                        $validator->errors()->add($field, 'No se puede modificar este campo en un programa que ya está en ejecución.');
                    }
                }
            }

            // Validar que la prioridad no se reduzca si el programa es de alta prioridad
            if ($this->filled('priority') && $this->maintenanceSchedule->isHighPriority()) {
                if ($this->priority < $this->maintenanceSchedule->priority) {
                    $validator->errors()->add('priority', 'No se puede reducir la prioridad de un programa de alta prioridad.');
                }
            }

            // Validar que la fecha de fin no sea anterior a la próxima fecha de mantenimiento
            if ($this->filled('end_date') && $this->maintenanceSchedule->next_maintenance_date) {
                $endDate = \Carbon\Carbon::parse($this->end_date);
                $nextMaintenance = \Carbon\Carbon::parse($this->maintenanceSchedule->next_maintenance_date);
                
                if ($endDate < $nextMaintenance) {
                    $validator->errors()->add('end_date', 'La fecha de fin no puede ser anterior a la próxima fecha de mantenimiento programada.');
                }
            }
        });
    }
}
