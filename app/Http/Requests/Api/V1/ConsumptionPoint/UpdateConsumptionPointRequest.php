<?php

namespace App\Http\Requests\Api\V1\ConsumptionPoint;

use App\Models\ConsumptionPoint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConsumptionPointRequest extends FormRequest
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
        $consumptionPointId = $this->route('consumptionPoint')->id ?? $this->route('id');

        return [
            'point_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('consumption_points', 'point_number')->ignore($consumptionPointId)
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:65535',
            'point_type' => ['sometimes', 'string', Rule::in(array_keys(ConsumptionPoint::getPointTypes()))],
            'status' => ['sometimes', 'string', Rule::in(array_keys(ConsumptionPoint::getStatuses()))],
            'customer_id' => 'sometimes|exists:customers,id',
            'installation_id' => 'sometimes|nullable|exists:energy_installations,id',
            'location_address' => 'sometimes|nullable|string|max:500',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'peak_demand_kw' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'average_demand_kw' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'annual_consumption_kwh' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'connection_date' => 'sometimes|date|before_or_equal:today',
            'disconnection_date' => 'sometimes|nullable|date',
            'meter_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('consumption_points', 'meter_number')->ignore($consumptionPointId)
            ],
            'meter_type' => 'sometimes|nullable|string|max:100',
            'meter_installation_date' => 'sometimes|nullable|date|before_or_equal:today',
            'meter_next_calibration_date' => 'sometimes|nullable|date',
            'voltage_level' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'current_rating' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'phase_type' => 'sometimes|nullable|string|max:50',
            'connection_type' => 'sometimes|nullable|string|max:100',
            'service_type' => 'sometimes|nullable|string|max:100',
            'tariff_type' => 'sometimes|nullable|string|max:100',
            'billing_frequency' => 'sometimes|nullable|string|max:50',
            'is_connected' => 'sometimes|nullable|boolean',
            'is_primary' => 'sometimes|nullable|boolean',
            'notes' => 'sometimes|nullable|string|max:1000',
            'metadata' => 'sometimes|nullable|array',
            'managed_by' => 'sometimes|nullable|exists:users,id',
            'approved_by' => 'sometimes|nullable|exists:users,id',
            'approved_at' => 'sometimes|nullable|date',
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
            'point_number.unique' => 'El número de punto ya existe',
            'point_number.max' => 'El número de punto no puede tener más de 255 caracteres',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
            'description.max' => 'La descripción no puede tener más de 65535 caracteres',
            'point_type.in' => 'El tipo de punto seleccionado no es válido',
            'status.in' => 'El estado seleccionado no es válido',
            'customer_id.exists' => 'El cliente seleccionado no existe',
            'installation_id.exists' => 'La instalación seleccionada no existe',
            'location_address.max' => 'La dirección no puede tener más de 500 caracteres',
            'latitude.between' => 'La latitud debe estar entre -90 y 90',
            'longitude.between' => 'La longitud debe estar entre -180 y 180',
            'peak_demand_kw.numeric' => 'La demanda pico debe ser un número',
            'peak_demand_kw.min' => 'La demanda pico debe ser mayor o igual a 0',
            'peak_demand_kw.max' => 'La demanda pico no puede ser mayor a 999999.99',
            'average_demand_kw.numeric' => 'La demanda promedio debe ser un número',
            'average_demand_kw.min' => 'La demanda promedio debe ser mayor o igual a 0',
            'average_demand_kw.max' => 'La demanda promedio no puede ser mayor a 999999.99',
            'annual_consumption_kwh.numeric' => 'El consumo anual debe ser un número',
            'annual_consumption_kwh.min' => 'El consumo anual debe ser mayor o igual a 0',
            'annual_consumption_kwh.max' => 'El consumo anual no puede ser mayor a 999999999.99',
            'connection_date.before_or_equal' => 'La fecha de conexión no puede ser posterior a hoy',
            'meter_number.unique' => 'El número de medidor ya existe',
            'meter_number.max' => 'El número de medidor no puede tener más de 255 caracteres',
            'meter_type.max' => 'El tipo de medidor no puede tener más de 100 caracteres',
            'meter_installation_date.before_or_equal' => 'La fecha de instalación del medidor no puede ser posterior a hoy',
            'voltage_level.numeric' => 'El nivel de voltaje debe ser un número',
            'voltage_level.min' => 'El nivel de voltaje debe ser mayor o igual a 0',
            'voltage_level.max' => 'El nivel de voltaje no puede ser mayor a 999999.99',
            'current_rating.numeric' => 'La corriente nominal debe ser un número',
            'current_rating.min' => 'La corriente nominal debe ser mayor o igual a 0',
            'current_rating.max' => 'La corriente nominal no puede ser mayor a 999999.99',
            'phase_type.max' => 'El tipo de fase no puede tener más de 50 caracteres',
            'connection_type.max' => 'El tipo de conexión no puede tener más de 100 caracteres',
            'service_type.max' => 'El tipo de servicio no puede tener más de 100 caracteres',
            'tariff_type.max' => 'El tipo de tarifa no puede tener más de 100 caracteres',
            'billing_frequency.max' => 'La frecuencia de facturación no puede tener más de 50 caracteres',
            'notes.max' => 'Las notas no pueden tener más de 1000 caracteres',
            'managed_by.exists' => 'El usuario gestor seleccionado no existe',
            'approved_by.exists' => 'El usuario aprobador seleccionado no existe',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que la fecha de desconexión sea posterior a la fecha de conexión
            if ($this->filled('disconnection_date') && $this->filled('connection_date')) {
                if ($this->disconnection_date <= $this->connection_date) {
                    $validator->errors()->add('disconnection_date', 'La fecha de desconexión debe ser posterior a la fecha de conexión');
                }
            }

            // Validar que la fecha de próxima calibración sea posterior a la fecha de instalación del medidor
            if ($this->filled('meter_next_calibration_date') && $this->filled('meter_installation_date')) {
                if ($this->meter_next_calibration_date <= $this->meter_installation_date) {
                    $validator->errors()->add('meter_next_calibration_date', 'La fecha de próxima calibración debe ser posterior a la fecha de instalación del medidor');
                }
            }

            // Validar que si se proporciona una fecha de desconexión, el estado sea 'disconnected'
            if ($this->filled('disconnection_date') && $this->filled('status') && $this->status !== 'disconnected') {
                $validator->errors()->add('status', 'El estado debe ser "disconnected" cuando se proporciona una fecha de desconexión');
            }

            // Validar que si el estado es 'disconnected', se proporcione una fecha de desconexión
            if ($this->filled('status') && $this->status === 'disconnected' && !$this->filled('disconnection_date')) {
                $validator->errors()->add('disconnection_date', 'La fecha de desconexión es obligatoria cuando el estado es "disconnected"');
            }

            // Validar que si se proporciona una fecha de aprobación, se proporcione un usuario aprobador
            if ($this->filled('approved_at') && !$this->filled('approved_by')) {
                $validator->errors()->add('approved_by', 'El usuario aprobador es obligatorio cuando se proporciona una fecha de aprobación');
            }

            // Validar que si se proporciona un usuario aprobador, se proporcione una fecha de aprobación
            if ($this->filled('approved_by') && !$this->filled('approved_at')) {
                $validator->errors()->add('approved_at', 'La fecha de aprobación es obligatoria cuando se proporciona un usuario aprobador');
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir campos booleanos
        if ($this->has('is_connected')) {
            $this->merge(['is_connected' => $this->boolean('is_connected')]);
        }

        if ($this->has('is_primary')) {
            $this->merge(['is_primary' => $this->boolean('is_primary')]);
        }

        // Convertir campos numéricos
        if ($this->filled('peak_demand_kw')) {
            $this->merge(['peak_demand_kw' => (float) $this->peak_demand_kw]);
        }

        if ($this->filled('average_demand_kw')) {
            $this->merge(['average_demand_kw' => (float) $this->average_demand_kw]);
        }

        if ($this->filled('annual_consumption_kwh')) {
            $this->merge(['annual_consumption_kwh' => (float) $this->annual_consumption_kwh]);
        }

        if ($this->filled('voltage_level')) {
            $this->merge(['voltage_level' => (float) $this->voltage_level]);
        }

        if ($this->filled('current_rating')) {
            $this->merge(['current_rating' => (float) $this->current_rating]);
        }

        if ($this->filled('latitude')) {
            $this->merge(['latitude' => (float) $this->latitude]);
        }

        if ($this->filled('longitude')) {
            $this->merge(['longitude' => (float) $this->longitude]);
        }

        // Decodificar campos JSON si vienen como string
        if ($this->filled('metadata') && is_string($this->metadata)) {
            $this->merge(['metadata' => json_decode($this->metadata, true)]);
        }

        // Asignar fecha de aprobación si se proporciona un usuario aprobador y no se proporciona fecha
        if ($this->filled('approved_by') && !$this->filled('approved_at')) {
            $this->merge(['approved_at' => now()]);
        }
    }
}
