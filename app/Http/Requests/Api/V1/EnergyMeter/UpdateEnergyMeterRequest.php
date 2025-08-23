<?php

namespace App\Http\Requests\Api\V1\EnergyMeter;

use App\Models\EnergyMeter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnergyMeterRequest extends FormRequest
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
        $energyMeterId = $this->route('energyMeter')->id ?? $this->route('id');

        return [
            'meter_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('energy_meters', 'meter_number')->ignore($energyMeterId)
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:65535',
            'meter_type' => ['sometimes', 'string', Rule::in(array_keys(EnergyMeter::getMeterTypes()))],
            'status' => ['sometimes', 'string', Rule::in(array_keys(EnergyMeter::getStatuses()))],
            'meter_category' => ['sometimes', 'string', Rule::in(array_keys(EnergyMeter::getMeterCategories()))],
            'manufacturer' => 'sometimes|nullable|string|max:255',
            'model' => 'sometimes|nullable|string|max:255',
            'serial_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('energy_meters', 'serial_number')->ignore($energyMeterId)
            ],
            'installation_id' => 'sometimes|nullable|exists:energy_installations,id',
            'consumption_point_id' => 'sometimes|nullable|exists:consumption_points,id',
            'customer_id' => 'sometimes|exists:customers,id',
            'installation_date' => 'sometimes|date|before_or_equal:today',
            'commissioning_date' => 'sometimes|nullable|date|after_or_equal:installation_date',
            'next_calibration_date' => 'sometimes|nullable|date|after:installation_date',
            'voltage_rating' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'current_rating' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'accuracy_class' => 'sometimes|nullable|numeric|min:0.1|max:10.0',
            'measurement_range_min' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'measurement_range_max' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'is_smart_meter' => 'sometimes|nullable|boolean',
            'has_remote_reading' => 'sometimes|nullable|boolean',
            'has_two_way_communication' => 'sometimes|nullable|boolean',
            'communication_protocol' => 'sometimes|nullable|string|max:100',
            'firmware_version' => 'sometimes|nullable|string|max:100',
            'hardware_version' => 'sometimes|nullable|string|max:100',
            'warranty_expiry_date' => 'sometimes|nullable|date|after:installation_date',
            'last_maintenance_date' => 'sometimes|nullable|date|before_or_equal:today',
            'next_maintenance_date' => 'sometimes|nullable|date|after:last_maintenance_date',
            'notes' => 'sometimes|nullable|string|max:1000',
            'metadata' => 'sometimes|nullable|array',
            'managed_by' => 'sometimes|nullable|exists:users,id',
            'approved_by' => 'sometimes|nullable|exists:users,id',
            'approved_at' => 'sometimes|nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'meter_number.unique' => 'El número de medidor ya existe.',
            'meter_type.in' => 'El tipo de medidor seleccionado no es válido.',
            'status.in' => 'El estado seleccionado no es válido.',
            'meter_category.in' => 'La categoría seleccionada no es válida.',
            'serial_number.unique' => 'El número de serie ya existe.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'installation_id.exists' => 'La instalación seleccionada no existe.',
            'consumption_point_id.exists' => 'El punto de consumo seleccionado no existe.',
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
            'approved_by.exists' => 'El usuario aprobador seleccionado no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir campos booleanos
        if ($this->filled('is_smart_meter')) {
            $this->merge(['is_smart_meter' => $this->boolean('is_smart_meter')]);
        }

        if ($this->filled('has_remote_reading')) {
            $this->merge(['has_remote_reading' => $this->boolean('has_remote_reading')]);
        }

        if ($this->filled('has_two_way_communication')) {
            $this->merge(['has_two_way_communication' => $this->boolean('has_two_way_communication')]);
        }

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
            // Obtener el medidor actual para validaciones cruzadas
            $energyMeter = $this->route('energyMeter');
            
            if (!$energyMeter) {
                return;
            }

            // Validar que el rango máximo sea mayor que el mínimo
            if ($this->filled('measurement_range_min') && $this->filled('measurement_range_max')) {
                if ($this->measurement_range_max <= $this->measurement_range_min) {
                    $validator->errors()->add('measurement_range_max', 'El rango máximo de medición debe ser mayor al rango mínimo.');
                }
            }

            // Validar fechas de instalación y puesta en servicio
            $installationDate = $this->filled('installation_date') ? $this->installation_date : $energyMeter->installation_date;
            
            if ($this->filled('commissioning_date') && $installationDate) {
                if ($this->commissioning_date < $installationDate) {
                    $validator->errors()->add('commissioning_date', 'La fecha de puesta en servicio debe ser posterior o igual a la fecha de instalación.');
                }
            }

            if ($this->filled('next_calibration_date') && $installationDate) {
                if ($this->next_calibration_date <= $installationDate) {
                    $validator->errors()->add('next_calibration_date', 'La fecha de próxima calibración debe ser posterior a la fecha de instalación.');
                }
            }

            if ($this->filled('warranty_expiry_date') && $installationDate) {
                if ($this->warranty_expiry_date <= $installationDate) {
                    $validator->errors()->add('warranty_expiry_date', 'La fecha de vencimiento de garantía debe ser posterior a la fecha de instalación.');
                }
            }

            // Validar fechas de mantenimiento
            $lastMaintenanceDate = $this->filled('last_maintenance_date') ? $this->last_maintenance_date : $energyMeter->last_maintenance_date;
            
            if ($this->filled('next_maintenance_date') && $lastMaintenanceDate) {
                if ($this->next_maintenance_date <= $lastMaintenanceDate) {
                    $validator->errors()->add('next_maintenance_date', 'La fecha de próximo mantenimiento debe ser posterior a la fecha de último mantenimiento.');
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

            // Validar consistencia del estado
            if ($this->filled('status')) {
                $newStatus = $this->status;
                $oldStatus = $energyMeter->status;

                // Si se cambia a estado inactivo, verificar que no tenga fechas futuras
                if ($newStatus === 'inactive' && $oldStatus !== 'inactive') {
                    if ($this->filled('next_calibration_date') && $this->next_calibration_date > now()) {
                        $validator->errors()->add('status', 'No se puede cambiar a estado inactivo un medidor con fechas de calibración futuras.');
                    }

                    if ($this->filled('next_maintenance_date') && $this->next_maintenance_date > now()) {
                        $validator->errors()->add('status', 'No se puede cambiar a estado inactivo un medidor con fechas de mantenimiento futuras.');
                    }
                }

                // Si se cambia a estado de mantenimiento, verificar que tenga fecha de próximo mantenimiento
                if ($newStatus === 'maintenance' && $oldStatus !== 'maintenance') {
                    if (!$this->filled('next_maintenance_date') && !$energyMeter->next_maintenance_date) {
                        $validator->errors()->add('status', 'Un medidor en mantenimiento debe tener una fecha de próximo mantenimiento.');
                    }
                }
            }
        });
    }
}
