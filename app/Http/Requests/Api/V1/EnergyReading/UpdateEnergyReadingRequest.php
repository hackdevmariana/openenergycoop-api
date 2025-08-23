<?php

namespace App\Http\Requests\Api\V1\EnergyReading;

use App\Models\EnergyReading;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnergyReadingRequest extends FormRequest
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
        $energyReadingId = $this->route('energyReading')->id ?? $this->route('id');
        
        return [
            'reading_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('energy_readings', 'reading_number')->ignore($energyReadingId)
            ],
            'meter_id' => 'sometimes|exists:energy_meters,id',
            'installation_id' => 'sometimes|nullable|exists:energy_installations,id',
            'consumption_point_id' => 'sometimes|nullable|exists:consumption_points,id',
            'customer_id' => 'sometimes|exists:users,id',
            'reading_type' => ['sometimes', 'string', Rule::in(array_keys(EnergyReading::getReadingTypes()))],
            'reading_source' => ['sometimes', 'string', Rule::in(array_keys(EnergyReading::getReadingSources()))],
            'reading_status' => ['sometimes', 'string', Rule::in(array_keys(EnergyReading::getReadingStatuses()))],
            'reading_timestamp' => 'sometimes|date|before_or_equal:now',
            'reading_period' => 'sometimes|nullable|string|max:100',
            'reading_value' => 'sometimes|numeric|min:-999999.9999|max:999999.9999',
            'reading_unit' => 'sometimes|string|max:50',
            'previous_reading_value' => 'sometimes|nullable|numeric|min:-999999.9999|max:999999.9999',
            'consumption_value' => 'sometimes|nullable|numeric|min:0|max:999999.9999',
            'consumption_unit' => 'sometimes|nullable|string|max:50',
            'demand_value' => 'sometimes|nullable|numeric|min:0|max:999999.9999',
            'demand_unit' => 'sometimes|nullable|string|max:50',
            'power_factor' => 'sometimes|nullable|numeric|min:-1|max:1',
            'voltage_value' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'voltage_unit' => 'sometimes|nullable|string|max:50',
            'current_value' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'current_unit' => 'sometimes|nullable|string|max:50',
            'frequency_value' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'frequency_unit' => 'sometimes|nullable|string|max:50',
            'temperature' => 'sometimes|nullable|numeric|min:-273.15|max:999999.99',
            'temperature_unit' => 'sometimes|nullable|string|max:50',
            'humidity' => 'sometimes|nullable|numeric|min:0|max:100',
            'humidity_unit' => 'sometimes|nullable|string|max:50',
            'quality_score' => 'sometimes|nullable|numeric|min:0|max:100',
            'quality_notes' => 'sometimes|nullable|string|max:1000',
            'validation_notes' => 'sometimes|nullable|string|max:1000',
            'correction_notes' => 'sometimes|nullable|string|max:1000',
            'raw_data' => 'sometimes|nullable|array',
            'processed_data' => 'sometimes|nullable|array',
            'alarms' => 'sometimes|nullable|array',
            'events' => 'sometimes|nullable|array',
            'tags' => 'sometimes|nullable|array',
            'read_by' => 'sometimes|nullable|exists:users,id',
            'validated_by' => 'sometimes|nullable|exists:users,id',
            'validated_at' => 'sometimes|nullable|date|before_or_equal:now',
            'corrected_by' => 'sometimes|nullable|exists:users,id',
            'corrected_at' => 'sometimes|nullable|date|before_or_equal:now',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'reading_number.unique' => 'El número de lectura ya existe.',
            'meter_id.exists' => 'El medidor seleccionado no existe.',
            'installation_id.exists' => 'La instalación seleccionada no existe.',
            'consumption_point_id.exists' => 'El punto de consumo seleccionado no existe.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'reading_type.in' => 'El tipo de lectura seleccionado no es válido.',
            'reading_source.in' => 'La fuente de lectura seleccionada no es válida.',
            'reading_status.in' => 'El estado de lectura seleccionado no es válido.',
            'reading_timestamp.date' => 'El timestamp de lectura debe ser una fecha válida.',
            'reading_timestamp.before_or_equal' => 'El timestamp de lectura no puede ser futuro.',
            'reading_value.numeric' => 'El valor de lectura debe ser numérico.',
            'reading_value.min' => 'El valor de lectura debe ser mayor a -999,999.9999.',
            'reading_value.max' => 'El valor de lectura no puede exceder 999,999.9999.',
            'previous_reading_value.numeric' => 'El valor de lectura anterior debe ser numérico.',
            'consumption_value.numeric' => 'El valor de consumo debe ser numérico.',
            'consumption_value.min' => 'El valor de consumo debe ser mayor o igual a 0.',
            'demand_value.numeric' => 'El valor de demanda debe ser numérico.',
            'demand_value.min' => 'El valor de demanda debe ser mayor o igual a 0.',
            'power_factor.numeric' => 'El factor de potencia debe ser numérico.',
            'power_factor.min' => 'El factor de potencia debe ser mayor o igual a -1.',
            'power_factor.max' => 'El factor de potencia debe ser menor o igual a 1.',
            'voltage_value.numeric' => 'El valor de voltaje debe ser numérico.',
            'voltage_value.min' => 'El valor de voltaje debe ser mayor o igual a 0.',
            'current_value.numeric' => 'El valor de corriente debe ser numérico.',
            'current_value.min' => 'El valor de corriente debe ser mayor o igual a 0.',
            'frequency_value.numeric' => 'El valor de frecuencia debe ser numérico.',
            'frequency_value.min' => 'El valor de frecuencia debe ser mayor o igual a 0.',
            'temperature.numeric' => 'La temperatura debe ser numérica.',
            'temperature.min' => 'La temperatura debe ser mayor a -273.15°C.',
            'humidity.numeric' => 'La humedad debe ser numérica.',
            'humidity.min' => 'La humedad debe ser mayor o igual a 0%.',
            'humidity.max' => 'La humedad debe ser menor o igual a 100%.',
            'quality_score.numeric' => 'La puntuación de calidad debe ser numérica.',
            'quality_score.min' => 'La puntuación de calidad debe ser mayor o igual a 0.',
            'quality_score.max' => 'La puntuación de calidad debe ser menor o igual a 100.',
            'validated_at.date' => 'La fecha de validación debe ser una fecha válida.',
            'validated_at.before_or_equal' => 'La fecha de validación no puede ser futura.',
            'corrected_at.date' => 'La fecha de corrección debe ser una fecha válida.',
            'corrected_at.before_or_equal' => 'La fecha de corrección no puede ser futura.',
            'read_by.exists' => 'El usuario lector seleccionado no existe.',
            'validated_by.exists' => 'El usuario validador seleccionado no existe.',
            'corrected_by.exists' => 'El usuario corrector seleccionado no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir campos numéricos
        if ($this->filled('reading_value')) {
            $this->merge(['reading_value' => (float) $this->reading_value]);
        }

        if ($this->filled('previous_reading_value')) {
            $this->merge(['previous_reading_value' => (float) $this->previous_reading_value]);
        }

        if ($this->filled('consumption_value')) {
            $this->merge(['consumption_value' => (float) $this->consumption_value]);
        }

        if ($this->filled('demand_value')) {
            $this->merge(['demand_value' => (float) $this->demand_value]);
        }

        if ($this->filled('power_factor')) {
            $this->merge(['power_factor' => (float) $this->power_factor]);
        }

        if ($this->filled('voltage_value')) {
            $this->merge(['voltage_value' => (float) $this->voltage_value]);
        }

        if ($this->filled('current_value')) {
            $this->merge(['current_value' => (float) $this->current_value]);
        }

        if ($this->filled('frequency_value')) {
            $this->merge(['frequency_value' => (float) $this->frequency_value]);
        }

        if ($this->filled('temperature')) {
            $this->merge(['temperature' => (float) $this->temperature]);
        }

        if ($this->filled('humidity')) {
            $this->merge(['humidity' => (float) $this->humidity]);
        }

        if ($this->filled('quality_score')) {
            $this->merge(['quality_score' => (float) $this->quality_score]);
        }

        // Decodificar campos JSON si son string
        if ($this->filled('raw_data') && is_string($this->raw_data)) {
            $this->merge(['raw_data' => json_decode($this->raw_data, true)]);
        }

        if ($this->filled('processed_data') && is_string($this->processed_data)) {
            $this->merge(['processed_data' => json_decode($this->processed_data, true)]);
        }

        if ($this->filled('alarms') && is_string($this->alarms)) {
            $this->merge(['alarms' => json_decode($this->alarms, true)]);
        }

        if ($this->filled('events') && is_string($this->events)) {
            $this->merge(['events' => json_decode($this->events, true)]);
        }

        if ($this->filled('tags') && is_string($this->tags)) {
            $this->merge(['tags' => json_decode($this->tags, true)]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que la fecha de validación esté presente si se proporciona el validador
            if ($this->filled('validated_by') && !$this->filled('validated_at')) {
                $validator->errors()->add('validated_at', 'La fecha de validación es obligatoria cuando se especifica un validador.');
            }

            // Validar que el validador esté presente si se proporciona la fecha de validación
            if ($this->filled('validated_at') && !$this->filled('validated_by')) {
                $validator->errors()->add('validated_by', 'El validador es obligatorio cuando se especifica una fecha de validación.');
            }

            // Validar que la fecha de corrección esté presente si se proporciona el corrector
            if ($this->filled('corrected_by') && !$this->filled('corrected_at')) {
                $validator->errors()->add('corrected_at', 'La fecha de corrección es obligatoria cuando se especifica un corrector.');
            }

            // Validar que el corrector esté presente si se proporciona la fecha de corrección
            if ($this->filled('corrected_at') && !$this->filled('corrected_by')) {
                $validator->errors()->add('corrected_by', 'El corrector es obligatorio cuando se especifica una fecha de corrección.');
            }

            // Validar que el valor de lectura anterior sea menor que el actual para lecturas acumulativas
            if ($this->filled('reading_type') && $this->reading_type === 'cumulative') {
                if ($this->filled('previous_reading_value') && $this->filled('reading_value')) {
                    if ($this->previous_reading_value >= $this->reading_value) {
                        $validator->errors()->add('previous_reading_value', 'Para lecturas acumulativas, el valor anterior debe ser menor que el valor actual.');
                    }
                }
            }

            // Validar que el factor de potencia esté en el rango correcto
            if ($this->filled('power_factor')) {
                if ($this->power_factor < -1 || $this->power_factor > 1) {
                    $validator->errors()->add('power_factor', 'El factor de potencia debe estar entre -1 y 1.');
                }
            }

            // Validar que la humedad esté en el rango correcto
            if ($this->filled('humidity')) {
                if ($this->humidity < 0 || $this->humidity > 100) {
                    $validator->errors()->add('humidity', 'La humedad debe estar entre 0% y 100%.');
                }
            }

            // Validar que la puntuación de calidad esté en el rango correcto
            if ($this->filled('quality_score')) {
                if ($this->quality_score < 0 || $this->quality_score > 100) {
                    $validator->errors()->add('quality_score', 'La puntuación de calidad debe estar entre 0 y 100.');
                }
            }
        });
    }
}
