<?php

namespace App\Http\Requests\Api\V1\EnergyTransfer;

use App\Models\EnergyTransfer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnergyTransferRequest extends FormRequest
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
            'transfer_number' => [
                'sometimes',
                'string',
                'max:50',
                'unique:energy_transfers,transfer_number',
            ],
            'name' => 'required|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'transfer_type' => [
                'required',
                'string',
                Rule::in(array_keys(EnergyTransfer::getTransferTypes())),
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::in(array_keys(EnergyTransfer::getStatuses())),
            ],
            'priority' => [
                'sometimes',
                'string',
                Rule::in(array_keys(EnergyTransfer::getPriorities())),
            ],
            'source_id' => 'required|integer',
            'source_type' => 'required|string|max:255',
            'destination_id' => 'required|integer',
            'destination_type' => 'required|string|max:255',
            'source_meter_id' => 'sometimes|integer|exists:energy_meters,id',
            'destination_meter_id' => 'sometimes|integer|exists:energy_meters,id',
            'transfer_amount_kwh' => 'required|numeric|min:0.001',
            'transfer_amount_mwh' => 'sometimes|numeric|min:0.001',
            'transfer_rate_kw' => 'sometimes|numeric|min:0',
            'transfer_rate_mw' => 'sometimes|numeric|min:0',
            'transfer_unit' => 'sometimes|string|max:50',
            'scheduled_start_time' => 'required|date|after:now',
            'scheduled_end_time' => 'required|date|after:scheduled_start_time',
            'actual_start_time' => 'sometimes|date|after_or_equal:scheduled_start_time',
            'actual_end_time' => 'sometimes|date|after:actual_start_time',
            'completion_time' => 'sometimes|date|after:actual_start_time',
            'duration_hours' => 'sometimes|numeric|min:0.01',
            'efficiency_percentage' => 'sometimes|numeric|min:0|max:100',
            'loss_percentage' => 'sometimes|numeric|min:0|max:100',
            'loss_amount_kwh' => 'sometimes|numeric|min:0',
            'net_transfer_amount_kwh' => 'sometimes|numeric|min:0',
            'net_transfer_amount_mwh' => 'sometimes|numeric|min:0',
            'cost_per_kwh' => 'sometimes|numeric|min:0',
            'total_cost' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|max:10',
            'exchange_rate' => 'sometimes|numeric|min:0',
            'transfer_method' => 'sometimes|string|max:100',
            'transfer_medium' => 'sometimes|string|max:100',
            'transfer_protocol' => 'sometimes|string|max:100',
            'is_automated' => 'sometimes|boolean',
            'requires_approval' => 'sometimes|boolean',
            'is_approved' => 'sometimes|boolean',
            'is_verified' => 'sometimes|boolean',
            'transfer_conditions' => 'sometimes|array',
            'safety_requirements' => 'sometimes|array',
            'quality_standards' => 'sometimes|array',
            'transfer_parameters' => 'sometimes|array',
            'monitoring_data' => 'sometimes|array',
            'alarm_settings' => 'sometimes|array',
            'event_logs' => 'sometimes|array',
            'performance_metrics' => 'sometimes|array',
            'tags' => 'sometimes|array',
            'scheduled_by' => 'sometimes|integer|exists:users,id',
            'initiated_by' => 'sometimes|integer|exists:users,id',
            'approved_by' => 'sometimes|integer|exists:users,id',
            'verified_by' => 'sometimes|integer|exists:users,id',
            'completed_by' => 'sometimes|integer|exists:users,id',
            'notes' => 'sometimes|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'transfer_type.in' => 'El tipo de transferencia debe ser uno de los valores permitidos.',
            'status.in' => 'El estado debe ser uno de los valores permitidos.',
            'priority.in' => 'La prioridad debe ser uno de los valores permitidos.',
            'source_id.required' => 'El ID de origen es obligatorio.',
            'source_type.required' => 'El tipo de origen es obligatorio.',
            'destination_id.required' => 'El ID de destino es obligatorio.',
            'destination_type.required' => 'El tipo de destino es obligatorio.',
            'transfer_amount_kwh.required' => 'La cantidad de transferencia en kWh es obligatoria.',
            'transfer_amount_kwh.min' => 'La cantidad de transferencia debe ser al menos 0.001 kWh.',
            'transfer_amount_mwh.min' => 'La cantidad de transferencia debe ser al menos 0.001 MWh.',
            'transfer_rate_kw.min' => 'La tasa de transferencia debe ser al menos 0 kW.',
            'transfer_rate_mw.min' => 'La tasa de transferencia debe ser al menos 0 MW.',
            'scheduled_start_time.required' => 'La hora de inicio programada es obligatoria.',
            'scheduled_start_time.after' => 'La hora de inicio programada debe ser en el futuro.',
            'scheduled_end_time.required' => 'La hora de fin programada es obligatoria.',
            'scheduled_end_time.after' => 'La hora de fin programada debe ser posterior al inicio.',
            'actual_start_time.after_or_equal' => 'La hora de inicio real debe ser igual o posterior a la programada.',
            'actual_end_time.after' => 'La hora de fin real debe ser posterior al inicio real.',
            'completion_time.after' => 'La hora de finalización debe ser posterior al inicio real.',
            'duration_hours.min' => 'La duración debe ser al menos 0.01 horas.',
            'efficiency_percentage.min' => 'La eficiencia debe ser al menos 0%.',
            'efficiency_percentage.max' => 'La eficiencia no puede ser mayor a 100%.',
            'loss_percentage.min' => 'La pérdida debe ser al menos 0%.',
            'loss_percentage.max' => 'La pérdida no puede ser mayor a 100%.',
            'loss_amount_kwh.min' => 'La cantidad de pérdida debe ser al menos 0 kWh.',
            'net_transfer_amount_kwh.min' => 'La cantidad neta de transferencia debe ser al menos 0 kWh.',
            'net_transfer_amount_mwh.min' => 'La cantidad neta de transferencia debe ser al menos 0 MWh.',
            'cost_per_kwh.min' => 'El costo por kWh debe ser al menos 0.',
            'total_cost.min' => 'El costo total debe ser al menos 0.',
            'exchange_rate.min' => 'La tasa de cambio debe ser al menos 0.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 100 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Generar número de transferencia si no se proporciona
        if (!$this->has('transfer_number')) {
            $this->merge([
                'transfer_number' => 'ET' . date('Ymd') . str_pad(EnergyTransfer::count() + 1, 4, '0', STR_PAD_LEFT)
            ]);
        }

        // Establecer valores por defecto
        if (!$this->has('status')) {
            $this->merge(['status' => EnergyTransfer::STATUS_PENDING]);
        }

        if (!$this->has('priority')) {
            $this->merge(['priority' => EnergyTransfer::PRIORITY_NORMAL]);
        }

        if (!$this->has('currency')) {
            $this->merge(['currency' => 'EUR']);
        }

        if (!$this->has('exchange_rate')) {
            $this->merge(['exchange_rate' => 1.0]);
        }

        if (!$this->has('is_automated')) {
            $this->merge(['is_automated' => false]);
        }

        if (!$this->has('requires_approval')) {
            $this->merge(['requires_approval' => false]);
        }

        if (!$this->has('is_approved')) {
            $this->merge(['is_approved' => false]);
        }

        if (!$this->has('is_verified')) {
            $this->merge(['is_verified' => false]);
        }

        // Convertir campos numéricos
        $numericFields = [
            'transfer_amount_kwh', 'transfer_amount_mwh', 'transfer_rate_kw', 'transfer_rate_mw',
            'duration_hours', 'efficiency_percentage', 'loss_percentage', 'loss_amount_kwh',
            'net_transfer_amount_kwh', 'net_transfer_amount_mwh', 'cost_per_kwh', 'total_cost',
            'exchange_rate'
        ];

        foreach ($numericFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => (float) $this->input($field)]);
            }
        }

        // Convertir campos de array
        $arrayFields = [
            'transfer_conditions', 'safety_requirements', 'quality_standards',
            'transfer_parameters', 'monitoring_data', 'alarm_settings',
            'event_logs', 'performance_metrics', 'tags'
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => json_decode($this->input($field), true)]);
            }
        }

        // Convertir campos booleanos
        $booleanFields = ['is_automated', 'requires_approval', 'is_approved', 'is_verified'];
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => (bool) $this->input($field)]);
            }
        }

        // Calcular campos derivados si no se proporcionan
        if ($this->has('transfer_amount_kwh') && !$this->has('transfer_amount_mwh')) {
            $this->merge(['transfer_amount_mwh' => $this->transfer_amount_kwh / 1000]);
        }

        if ($this->has('transfer_amount_mwh') && !$this->has('transfer_amount_kwh')) {
            $this->merge(['transfer_amount_kwh' => $this->transfer_amount_mwh * 1000]);
        }

        if ($this->has('transfer_rate_kw') && !$this->has('transfer_rate_mw')) {
            $this->merge(['transfer_rate_mw' => $this->transfer_rate_kw / 1000]);
        }

        if ($this->has('transfer_rate_mw') && !$this->has('transfer_rate_kw')) {
            $this->merge(['transfer_rate_kw' => $this->transfer_rate_mw * 1000]);
        }

        // Calcular duración si se proporcionan fechas programadas
        if ($this->has('scheduled_start_time') && $this->has('scheduled_end_time') && !$this->has('duration_hours')) {
            $startTime = \Carbon\Carbon::parse($this->scheduled_start_time);
            $endTime = \Carbon\Carbon::parse($this->scheduled_end_time);
            $duration = $startTime->diffInHours($endTime);
            $this->merge(['duration_hours' => $duration]);
        }

        // Calcular cantidad neta si se proporciona la cantidad bruta y la pérdida
        if ($this->has('transfer_amount_kwh') && $this->has('loss_amount_kwh') && !$this->has('net_transfer_amount_kwh')) {
            $netAmount = $this->transfer_amount_kwh - $this->loss_amount_kwh;
            $this->merge(['net_transfer_amount_kwh' => max(0, $netAmount)]);
        }

        if ($this->has('net_transfer_amount_kwh') && !$this->has('net_transfer_amount_mwh')) {
            $this->merge(['net_transfer_amount_mwh' => $this->net_transfer_amount_kwh / 1000]);
        }

        // Calcular costo total si se proporciona el costo por kWh y la cantidad
        if ($this->has('cost_per_kwh') && $this->has('transfer_amount_kwh') && !$this->has('total_cost')) {
            $totalCost = $this->cost_per_kwh * $this->transfer_amount_kwh;
            $this->merge(['total_cost' => $totalCost]);
        }

        // Calcular porcentaje de pérdida si se proporciona la cantidad bruta y la pérdida
        if ($this->has('transfer_amount_kwh') && $this->has('loss_amount_kwh') && !$this->has('loss_percentage')) {
            if ($this->transfer_amount_kwh > 0) {
                $lossPercentage = ($this->loss_amount_kwh / $this->transfer_amount_kwh) * 100;
                $this->merge(['loss_percentage' => min(100, $lossPercentage)]);
            }
        }

        // Calcular porcentaje de eficiencia si se proporciona la pérdida
        if ($this->has('loss_percentage') && !$this->has('efficiency_percentage')) {
            $efficiency = 100 - $this->loss_percentage;
            $this->merge(['efficiency_percentage' => max(0, $efficiency)]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que la cantidad neta no sea mayor que la cantidad bruta
            if ($this->filled('transfer_amount_kwh') && $this->filled('net_transfer_amount_kwh')) {
                if ($this->net_transfer_amount_kwh > $this->transfer_amount_kwh) {
                    $validator->errors()->add('net_transfer_amount_kwh', 'La cantidad neta no puede ser mayor que la cantidad bruta.');
                }
            }

            // Validar que la cantidad de pérdida no sea mayor que la cantidad bruta
            if ($this->filled('transfer_amount_kwh') && $this->filled('loss_amount_kwh')) {
                if ($this->loss_amount_kwh > $this->transfer_amount_kwh) {
                    $validator->errors()->add('loss_amount_kwh', 'La cantidad de pérdida no puede ser mayor que la cantidad bruta.');
                }
            }

            // Validar que la suma de pérdida y cantidad neta no exceda la cantidad bruta
            if ($this->filled('transfer_amount_kwh') && $this->filled('loss_amount_kwh') && $this->filled('net_transfer_amount_kwh')) {
                $total = $this->loss_amount_kwh + $this->net_transfer_amount_kwh;
                $tolerance = 0.01; // Tolerancia de 0.01 kWh
                
                if (abs($total - $this->transfer_amount_kwh) > $tolerance) {
                    $validator->errors()->add('loss_amount_kwh', 'La suma de pérdida y cantidad neta debe ser igual a la cantidad bruta.');
                }
            }

            // Validar que el porcentaje de pérdida y eficiencia sumen 100%
            if ($this->filled('loss_percentage') && $this->filled('efficiency_percentage')) {
                $sum = $this->loss_percentage + $this->efficiency_percentage;
                $tolerance = 0.01; // Tolerancia de 0.01%
                
                if (abs($sum - 100) > $tolerance) {
                    $validator->errors()->add('efficiency_percentage', 'La suma de pérdida y eficiencia debe ser 100%.');
                }
            }

            // Validar que la duración programada sea razonable (máximo 30 días)
            if ($this->filled('scheduled_start_time') && $this->filled('scheduled_end_time')) {
                $startTime = \Carbon\Carbon::parse($this->scheduled_start_time);
                $endTime = \Carbon\Carbon::parse($this->scheduled_end_time);
                $duration = $startTime->diffInHours($endTime);
                
                if ($duration > 720) { // 30 días * 24 horas
                    $validator->errors()->add('scheduled_end_time', 'La duración de la transferencia no puede ser mayor a 30 días.');
                }
            }

            // Validar que la tasa de transferencia sea consistente con la cantidad y duración
            if ($this->filled('transfer_amount_kwh') && $this->filled('duration_hours') && $this->filled('transfer_rate_kw')) {
                $expectedRate = $this->transfer_amount_kwh / $this->duration_hours;
                $tolerance = 0.01; // Tolerancia de 0.01 kW
                
                if (abs($this->transfer_rate_kw - $expectedRate) > $tolerance) {
                    $validator->errors()->add('transfer_rate_kw', 'La tasa de transferencia debe ser consistente con la cantidad y duración.');
                }
            }

            // Validar que el costo total sea consistente con el costo por kWh
            if ($this->filled('cost_per_kwh') && $this->filled('transfer_amount_kwh') && $this->filled('total_cost')) {
                $expectedCost = $this->cost_per_kwh * $this->transfer_amount_kwh;
                $tolerance = 0.01; // Tolerancia de 0.01
                
                if (abs($this->total_cost - $expectedCost) > $tolerance) {
                    $validator->errors()->add('total_cost', 'El costo total debe ser consistente con el costo por kWh y la cantidad.');
                }
            }

            // Validar que el origen y destino no sean el mismo
            if ($this->filled('source_id') && $this->filled('destination_id') && 
                $this->filled('source_type') && $this->filled('destination_type')) {
                if ($this->source_id === $this->destination_id && $this->source_type === $this->destination_type) {
                    $validator->errors()->add('destination_id', 'El origen y destino no pueden ser el mismo.');
                }
            }

            // Validar que si se requiere aprobación, el estado inicial sea pendiente
            if ($this->filled('requires_approval') && $this->requires_approval && $this->filled('status')) {
                if ($this->status !== EnergyTransfer::STATUS_PENDING) {
                    $validator->errors()->add('status', 'Si se requiere aprobación, el estado inicial debe ser pendiente.');
                }
            }

            // Validar que si se requiere aprobación, no se pueda marcar como aprobado inicialmente
            if ($this->filled('requires_approval') && $this->requires_approval && $this->filled('is_approved')) {
                if ($this->is_approved) {
                    $validator->errors()->add('is_approved', 'No se puede marcar como aprobado si se requiere aprobación.');
                }
            }
        });
    }
}
