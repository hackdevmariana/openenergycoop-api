<?php

namespace App\Http\Requests\Api\V1\AutomationRule;

use App\Models\AutomationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAutomationRuleRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'rule_type' => [
                'required',
                'string',
                Rule::in(array_keys(AutomationRule::getRuleTypes())),
            ],
            'trigger_type' => [
                'required',
                'string',
                Rule::in(array_keys(AutomationRule::getTriggerTypes())),
            ],
            'trigger_conditions' => 'nullable|array',
            'action_type' => [
                'required',
                'string',
                Rule::in(array_keys(AutomationRule::getActionTypes())),
            ],
            'action_parameters' => 'nullable|array',
            'target_entity_id' => 'nullable|integer',
            'target_entity_type' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'priority' => 'integer|min:1|max:10',
            'execution_frequency' => [
                'required',
                'string',
                Rule::in(array_keys(AutomationRule::getExecutionFrequencies())),
            ],
            'last_executed_at' => 'nullable|date',
            'next_execution_at' => 'nullable|date|after:now',
            'execution_count' => 'integer|min:0',
            'max_executions' => 'nullable|integer|min:1',
            'success_count' => 'integer|min:0',
            'failure_count' => 'integer|min:0',
            'last_error_message' => 'nullable|string|max:1000',
            'schedule_cron' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'retry_on_failure' => 'boolean',
            'max_retries' => 'nullable|integer|min:0|max:10',
            'retry_delay_minutes' => 'nullable|integer|min:1|max:1440',
            'notification_emails' => 'nullable|array',
            'notification_emails.*' => 'email|max:255',
            'webhook_url' => 'nullable|url|max:500',
            'webhook_headers' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'notes' => 'nullable|string|max:1000',
            'created_by' => 'nullable|integer|exists:users,id',
            'approved_by' => 'nullable|integer|exists:users,id',
            'approved_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la regla es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'rule_type.required' => 'El tipo de regla es obligatorio.',
            'rule_type.in' => 'El tipo de regla debe ser uno de los valores permitidos.',
            'trigger_type.required' => 'El tipo de disparador es obligatorio.',
            'trigger_type.in' => 'El tipo de disparador debe ser uno de los valores permitidos.',
            'action_type.required' => 'El tipo de acción es obligatorio.',
            'action_type.in' => 'El tipo de acción debe ser uno de los valores permitidos.',
            'execution_frequency.required' => 'La frecuencia de ejecución es obligatoria.',
            'execution_frequency.in' => 'La frecuencia de ejecución debe ser uno de los valores permitidos.',
            'priority.min' => 'La prioridad debe ser al menos 1.',
            'priority.max' => 'La prioridad no puede ser mayor a 10.',
            'execution_count.min' => 'El contador de ejecuciones debe ser al menos 0.',
            'max_executions.min' => 'El máximo de ejecuciones debe ser al menos 1.',
            'success_count.min' => 'El contador de éxitos debe ser al menos 0.',
            'failure_count.min' => 'El contador de fallos debe ser al menos 0.',
            'max_retries.min' => 'El máximo de reintentos debe ser al menos 0.',
            'max_retries.max' => 'El máximo de reintentos no puede ser mayor a 10.',
            'retry_delay_minutes.min' => 'El retraso entre reintentos debe ser al menos 1 minuto.',
            'retry_delay_minutes.max' => 'El retraso entre reintentos no puede ser mayor a 1440 minutos (24 horas).',
            'next_execution_at.after' => 'La próxima ejecución debe ser posterior a la fecha actual.',
            'notification_emails.*.email' => 'Cada email de notificación debe ser válido.',
            'webhook_url.url' => 'La URL del webhook debe ser válida.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 100 caracteres.',
            'created_by.exists' => 'El usuario creador debe existir en el sistema.',
            'approved_by.exists' => 'El usuario aprobador debe existir en el sistema.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Asignar usuario creador si no se proporciona
        if (!$this->has('created_by')) {
            $this->merge(['created_by' => auth()->id()]);
        }

        // Valores por defecto
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'priority' => $this->integer('priority', 5),
            'execution_count' => $this->integer('execution_count', 0),
            'success_count' => $this->integer('success_count', 0),
            'failure_count' => $this->integer('failure_count', 0),
            'retry_on_failure' => $this->boolean('retry_on_failure', false),
            'max_retries' => $this->integer('max_retries', 3),
            'retry_delay_minutes' => $this->integer('retry_delay_minutes', 5),
        ]);

        // Convertir campos de array
        $arrayFields = [
            'trigger_conditions', 'action_parameters', 'notification_emails',
            'webhook_headers', 'tags'
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => json_decode($this->input($field), true)]);
            }
        }

        // Convertir campos booleanos
        $booleanFields = ['is_active', 'retry_on_failure'];
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => (bool) $this->input($field)]);
            }
        }

        // Convertir campos numéricos
        $numericFields = [
            'priority', 'execution_count', 'max_executions', 'success_count',
            'failure_count', 'max_retries', 'retry_delay_minutes'
        ];

        foreach ($numericFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => (int) $this->input($field)]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que el contador de éxitos no sea mayor que el contador de ejecuciones
            if ($this->filled('success_count') && $this->filled('execution_count')) {
                if ($this->success_count > $this->execution_count) {
                    $validator->errors()->add('success_count', 'El contador de éxitos no puede ser mayor que el contador de ejecuciones.');
                }
            }

            // Validar que el contador de fallos no sea mayor que el contador de ejecuciones
            if ($this->filled('failure_count') && $this->filled('execution_count')) {
                if ($this->failure_count > $this->execution_count) {
                    $validator->errors()->add('failure_count', 'El contador de fallos no puede ser mayor que el contador de ejecuciones.');
                }
            }

            // Validar que la suma de éxitos y fallos no exceda el total de ejecuciones
            if ($this->filled('success_count') && $this->filled('failure_count') && $this->filled('execution_count')) {
                $total = $this->success_count + $this->failure_count;
                if ($total > $this->execution_count) {
                    $validator->errors()->add('execution_count', 'La suma de éxitos y fallos no puede exceder el total de ejecuciones.');
                }
            }

            // Validar que el máximo de reintentos sea mayor que 0 si se activa el reintento
            if ($this->boolean('retry_on_failure') && $this->filled('max_retries')) {
                if ($this->max_retries <= 0) {
                    $validator->errors()->add('max_retries', 'El máximo de reintentos debe ser mayor que 0 si se activa el reintento.');
                }
            }

            // Validar que el retraso entre reintentos sea mayor que 0 si se activa el reintento
            if ($this->boolean('retry_on_failure') && $this->filled('retry_delay_minutes')) {
                if ($this->retry_delay_minutes <= 0) {
                    $validator->errors()->add('retry_delay_minutes', 'El retraso entre reintentos debe ser mayor que 0 si se activa el reintento.');
                }
            }

            // Validar que la próxima ejecución sea posterior a la última ejecución
            if ($this->filled('next_execution_at') && $this->filled('last_executed_at')) {
                $nextExecution = \Carbon\Carbon::parse($this->next_execution_at);
                $lastExecution = \Carbon\Carbon::parse($this->last_executed_at);
                
                if ($nextExecution <= $lastExecution) {
                    $validator->errors()->add('next_execution_at', 'La próxima ejecución debe ser posterior a la última ejecución.');
                }
            }

            // Validar que el cronograma sea válido si se proporciona
            if ($this->filled('schedule_cron')) {
                if (!$this->isValidCronExpression($this->schedule_cron)) {
                    $validator->errors()->add('schedule_cron', 'La expresión cron no es válida.');
                }
            }

            // Validar que la zona horaria sea válida si se proporciona
            if ($this->filled('timezone')) {
                if (!in_array($this->timezone, timezone_identifiers_list())) {
                    $validator->errors()->add('timezone', 'La zona horaria no es válida.');
                }
            }
        });
    }

    /**
     * Validar expresión cron.
     */
    private function isValidCronExpression(string $cron): bool
    {
        $parts = explode(' ', trim($cron));
        
        if (count($parts) !== 5) {
            return false;
        }

        $patterns = [
            'minute' => '/^(\*|[0-5]?[0-9](-[0-5]?[0-9])?(,\d+)*|\*\/\d+)$/',
            'hour' => '/^(\*|1?[0-9]|2[0-3](-1?[0-9]|2[0-3])?(,\d+)*|\*\/\d+)$/',
            'day' => '/^(\*|[1-9]|[12][0-9]|3[01](-[1-9]|[12][0-9]|3[01])?(,\d+)*|\*\/\d+)$/',
            'month' => '/^(\*|[1-9]|1[0-2](-[1-9]|1[0-2])?(,\d+)*|\*\/\d+)$/',
            'weekday' => '/^(\*|[0-6](-[0-6])?(,\d+)*|\*\/\d+)$/',
        ];

        foreach ($parts as $index => $part) {
            $keys = array_keys($patterns);
            if (!preg_match($patterns[$keys[$index]], $part)) {
                return false;
            }
        }

        return true;
    }
}
