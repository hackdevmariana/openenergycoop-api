<?php

namespace App\Http\Requests\Api\V1\ConsumptionPoint;

use App\Models\ConsumptionPoint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsumptionPointRequest extends FormRequest
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
            'point_number' => 'required|string|max:255|unique:consumption_points,point_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'point_type' => ['required', 'string', Rule::in(array_keys(ConsumptionPoint::getPointTypes()))],
            'status' => ['required', 'string', Rule::in(array_keys(ConsumptionPoint::getStatuses()))],
            'customer_id' => 'required|exists:customers,id',
            'installation_id' => 'nullable|exists:energy_installations,id',
            'location_address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'peak_demand_kw' => 'nullable|numeric|min:0|max:999999.99',
            'average_demand_kw' => 'nullable|numeric|min:0|max:999999.99',
            'annual_consumption_kwh' => 'nullable|numeric|min:0|max:999999999.99',
            'connection_date' => 'required|date|before_or_equal:today',
            'disconnection_date' => 'nullable|date|after:connection_date',
            'meter_number' => 'nullable|string|max:255|unique:consumption_points,meter_number',
            'meter_type' => 'nullable|string|max:100',
            'meter_installation_date' => 'nullable|date|before_or_equal:today',
            'meter_next_calibration_date' => 'nullable|date|after:meter_installation_date',
            'voltage_level' => 'nullable|numeric|min:0|max:999999.99',
            'current_rating' => 'nullable|numeric|min:0|max:999999.99',
            'phase_type' => 'nullable|string|max:50',
            'connection_type' => 'nullable|string|max:100',
            'service_type' => 'nullable|string|max:100',
            'tariff_type' => 'nullable|string|max:100',
            'billing_frequency' => 'nullable|string|max:50',
            'is_connected' => 'nullable|boolean',
            'is_primary' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array',
            'managed_by' => 'nullable|exists:users,id',
            'created_by' => 'nullable|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'approved_at' => 'nullable|date',
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
            'point_number.required' => 'El campo número de punto es obligatorio',
            'point_number.unique' => 'El número de punto ya existe',
            'point_number.max' => 'El número de punto no puede tener más de 255 caracteres',
            'name.required' => 'El campo nombre es obligatorio',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
            'description.max' => 'La descripción no puede tener más de 65535 caracteres',
            'point_type.required' => 'El campo tipo de punto es obligatorio',
            'point_type.in' => 'El tipo de punto seleccionado no es válido',
            'status.required' => 'El campo estado es obligatorio',
            'status.in' => 'El estado seleccionado no es válido',
            'customer_id.required' => 'El campo cliente es obligatorio',
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
            'connection_date.required' => 'El campo fecha de conexión es obligatorio',
            'connection_date.before_or_equal' => 'La fecha de conexión no puede ser posterior a hoy',
            'disconnection_date.after' => 'La fecha de desconexión debe ser posterior a la fecha de conexión',
            'meter_number.unique' => 'El número de medidor ya existe',
            'meter_number.max' => 'El número de medidor no puede tener más de 255 caracteres',
            'meter_type.max' => 'El tipo de medidor no puede tener más de 100 caracteres',
            'meter_installation_date.before_or_equal' => 'La fecha de instalación del medidor no puede ser posterior a hoy',
            'meter_next_calibration_date.after' => 'La fecha de próxima calibración debe ser posterior a la fecha de instalación',
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
            'created_by.exists' => 'El usuario creador seleccionado no existe',
            'approved_by.exists' => 'El usuario aprobador seleccionado no existe',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir campos booleanos
        $this->merge([
            'is_connected' => $this->boolean('is_connected'),
            'is_primary' => $this->boolean('is_primary'),
        ]);

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

        // Asignar usuario creador si no se especifica
        if (!$this->filled('created_by')) {
            $this->merge(['created_by' => auth()->id()]);
        }

        // Asignar usuario gestor si no se especifica
        if (!$this->filled('managed_by')) {
            $this->merge(['managed_by' => auth()->id()]);
        }
    }
}
