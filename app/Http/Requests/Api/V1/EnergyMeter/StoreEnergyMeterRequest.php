<?php

namespace App\Http\Requests\Api\V1\EnergyMeter;

use App\Models\EnergyMeter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnergyMeterRequest extends FormRequest
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
            'meter_number' => 'required|string|max:255|unique:energy_meters,meter_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'meter_type' => ['required', 'string', Rule::in(array_keys(EnergyMeter::getMeterTypes()))],
            'status' => ['required', 'string', Rule::in(array_keys(EnergyMeter::getStatuses()))],
            'meter_category' => ['required', 'string', Rule::in(array_keys(EnergyMeter::getMeterCategories()))],
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'required|string|max:255|unique:energy_meters,serial_number',
            'installation_id' => 'nullable|exists:energy_installations,id',
            'consumption_point_id' => 'nullable|exists:consumption_points,id',
            'customer_id' => 'required|exists:customers,id',
            'installation_date' => 'required|date|before_or_equal:today',
            'commissioning_date' => 'nullable|date|after_or_equal:installation_date',
            'next_calibration_date' => 'nullable|date|after:installation_date',
            'voltage_rating' => 'nullable|numeric|min:0|max:999999.99',
            'current_rating' => 'nullable|numeric|min:0|max:999999.99',
            'accuracy_class' => 'nullable|numeric|min:0.1|max:10.0',
            'measurement_range_min' => 'nullable|numeric|min:0|max:999999.99',
            'measurement_range_max' => 'nullable|numeric|min:0|max:999999.99',
            'is_smart_meter' => 'nullable|boolean',
            'has_remote_reading' => 'nullable|boolean',
            'has_two_way_communication' => 'nullable|boolean',
            'communication_protocol' => 'nullable|string|max:100',
            'firmware_version' => 'nullable|string|max:100',
            'hardware_version' => 'nullable|string|max:100',
            'warranty_expiry_date' => 'nullable|date|after:installation_date',
            'last_maintenance_date' => 'nullable|date|before_or_equal:today',
            'next_maintenance_date' => 'nullable|date|after:last_maintenance_date',
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
     */
    public function messages(): array
    {
        return [
            'meter_number.required' => 'El número de medidor es obligatorio.',
            'meter_number.unique' => 'El número de medidor ya existe.',
            'name.required' => 'El nombre del medidor es obligatorio.',
            'meter_type.required' => 'El tipo de medidor es obligatorio.',
            'meter_type.in' => 'El tipo de medidor seleccionado no es válido.',
            'status.required' => 'El estado del medidor es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            'meter_category.required' => 'La categoría del medidor es obligatoria.',
            'meter_category.in' => 'La categoría seleccionada no es válida.',
            'serial_number.required' => 'El número de serie es obligatorio.',
            'serial_number.unique' => 'El número de serie ya existe.',
            'customer_id.required' => 'El cliente es obligatorio.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'installation_id.exists' => 'La instalación seleccionada no existe.',
            'consumption_point_id.exists' => 'El punto de consumo seleccionado no existe.',
            'installation_date.required' => 'La fecha de instalación es obligatoria.',
            'installation_date.before_or_equal' => 'La fecha de instalación no puede ser futura.',
            'commissioning_date.after_or_equal' => 'La fecha de puesta en servicio debe ser posterior o igual a la fecha de instalación.',
            'next_calibration_date.after' => 'La fecha de próxima calibración debe ser posterior a la fecha de instalación.',
            'voltage_rating.min' => 'La tensión nominal debe ser mayor a 0.',
            'voltage_rating.max' => 'La tensión nominal no puede exceder 999,999.99.',
            'current_rating.min' => 'La corriente nominal debe ser mayor a 0.',
            'current_rating.max' => 'La corriente nominal no puede exceder 999,999.99.',
            'accuracy_class.min' => 'La clase de precisión debe ser mayor a 0.1.',
            'accuracy_class.max' => 'La clase de precisión no puede exceder 10.0.',
            'measurement_range_min.min' => 'El rango mínimo de medición debe ser mayor a 0.',
            'measurement_range_max.min' => 'El rango máximo de medición debe ser mayor a 0.',
            'warranty_expiry_date.after' => 'La fecha de vencimiento de garantía debe ser posterior a la fecha de instalación.',
            'last_maintenance_date.before_or_equal' => 'La fecha de último mantenimiento no puede ser futura.',
            'next_maintenance_date.after' => 'La fecha de próximo mantenimiento debe ser posterior a la fecha de último mantenimiento.',
            'managed_by.exists' => 'El usuario gestor seleccionado no existe.',
            'created_by.exists' => 'El usuario creador seleccionado no existe.',
            'approved_by.exists' => 'El usuario aprobador seleccionado no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Asignar usuario creador si no se proporciona
        if (!$this->filled('created_by')) {
            $this->merge(['created_by' => auth()->id()]);
        }

        // Asignar usuario gestor si no se proporciona
        if (!$this->filled('managed_by')) {
            $this->merge(['managed_by' => auth()->id()]);
        }

        // Convertir campos booleanos
        $this->merge([
            'is_smart_meter' => $this->boolean('is_smart_meter'),
            'has_remote_reading' => $this->boolean('has_remote_reading'),
            'has_two_way_communication' => $this->boolean('has_two_way_communication'),
        ]);

        // Convertir campos numéricos
        if ($this->filled('voltage_rating')) {
            $this->merge(['voltage_rating' => (float) $this->voltage_rating]);
        }

        if ($this->filled('current_rating')) {
            $this->merge(['current_rating' => (float) $this->current_rating]);
        }

        if ($this->filled('accuracy_class')) {
            $this->merge(['accuracy_class' => (float) $this->accuracy_class]);
        }

        if ($this->filled('measurement_range_min')) {
            $this->merge(['measurement_range_min' => (float) $this->measurement_range_min]);
        }

        if ($this->filled('measurement_range_max')) {
            $this->merge(['measurement_range_max' => (float) $this->measurement_range_max]);
        }

        // Decodificar campo metadata si es string
        if ($this->filled('metadata') && is_string($this->metadata)) {
            $this->merge(['metadata' => json_decode($this->metadata, true)]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que el rango máximo sea mayor que el mínimo
            if ($this->filled('measurement_range_min') && $this->filled('measurement_range_max')) {
                if ($this->measurement_range_max <= $this->measurement_range_min) {
                    $validator->errors()->add('measurement_range_max', 'El rango máximo de medición debe ser mayor al rango mínimo.');
                }
            }

            // Validar que la fecha de aprobación esté presente si se proporciona el aprobador
            if ($this->filled('approved_by') && !$this->filled('approved_at')) {
                $validator->errors()->add('approved_at', 'La fecha de aprobación es obligatoria cuando se especifica un aprobador.');
            }

            // Validar que el aprobador esté presente si se proporciona la fecha de aprobación
            if ($this->filled('approved_at') && !$this->filled('approved_by')) {
                $validator->errors()->add('approved_by', 'El aprobador es obligatorio cuando se especifica una fecha de aprobación.');
            }
        });
    }
}
