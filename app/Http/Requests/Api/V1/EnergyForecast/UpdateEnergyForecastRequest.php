<?php

namespace App\Http\Requests\Api\V1\EnergyForecast;

use App\Models\EnergyForecast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnergyForecastRequest extends FormRequest
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
            'forecast_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('energy_forecasts', 'forecast_number')->ignore($this->forecast->id),
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'forecast_type' => [
                'sometimes',
                'string',
                Rule::in(array_keys(EnergyForecast::getForecastTypes())),
            ],
            'forecast_horizon' => [
                'sometimes',
                'string',
                Rule::in(array_keys(EnergyForecast::getForecastHorizons())),
            ],
            'forecast_method' => [
                'sometimes',
                'string',
                Rule::in(array_keys(EnergyForecast::getForecastMethods())),
            ],
            'forecast_status' => [
                'sometimes',
                'string',
                Rule::in(array_keys(EnergyForecast::getForecastStatuses())),
            ],
            'accuracy_level' => [
                'sometimes',
                'string',
                Rule::in(array_keys(EnergyForecast::getAccuracyLevels())),
            ],
            'accuracy_score' => 'sometimes|numeric|min:0|max:100',
            'confidence_interval_lower' => 'sometimes|numeric',
            'confidence_interval_upper' => 'sometimes|numeric',
            'confidence_level' => 'sometimes|numeric|min:0|max:100',
            'source_id' => 'sometimes|integer|exists:energy_sources,id',
            'source_type' => 'sometimes|string|max:255',
            'target_id' => 'sometimes|integer',
            'target_type' => 'sometimes|string|max:255',
            'forecast_start_time' => 'sometimes|date',
            'forecast_end_time' => 'sometimes|date',
            'generation_time' => 'sometimes|date',
            'valid_from' => 'sometimes|date',
            'valid_until' => 'sometimes|date',
            'expiry_time' => 'sometimes|date',
            'time_zone' => 'sometimes|string|max:50',
            'time_resolution' => 'sometimes|string|max:50',
            'forecast_periods' => 'sometimes|integer|min:1',
            'total_forecasted_value' => 'sometimes|numeric|min:0',
            'forecast_unit' => 'sometimes|string|max:50',
            'baseline_value' => 'sometimes|numeric',
            'trend_value' => 'sometimes|numeric',
            'seasonal_value' => 'sometimes|numeric',
            'cyclical_value' => 'sometimes|numeric',
            'irregular_value' => 'sometimes|numeric',
            'forecast_data' => 'sometimes|array',
            'baseline_data' => 'sometimes|array',
            'trend_data' => 'sometimes|array',
            'seasonal_data' => 'sometimes|array',
            'cyclical_data' => 'sometimes|array',
            'irregular_data' => 'sometimes|array',
            'weather_data' => 'sometimes|array',
            'input_variables' => 'sometimes|array',
            'model_parameters' => 'sometimes|array',
            'validation_metrics' => 'sometimes|array',
            'performance_history' => 'sometimes|array',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:100',
            'notes' => 'sometimes|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'forecast_type.in' => 'El tipo de pronóstico debe ser uno de los valores permitidos.',
            'forecast_horizon.in' => 'El horizonte del pronóstico debe ser uno de los valores permitidos.',
            'forecast_method.in' => 'El método de pronóstico debe ser uno de los valores permitidos.',
            'forecast_status.in' => 'El estado del pronóstico debe ser uno de los valores permitidos.',
            'accuracy_level.in' => 'El nivel de precisión debe ser uno de los valores permitidos.',
            'accuracy_score.min' => 'El puntaje de precisión debe ser al menos 0.',
            'accuracy_score.max' => 'El puntaje de precisión no puede ser mayor a 100.',
            'confidence_level.min' => 'El nivel de confianza debe ser al menos 0.',
            'confidence_level.max' => 'El nivel de confianza no puede ser mayor a 100.',
            'forecast_periods.min' => 'El número de períodos debe ser al menos 1.',
            'total_forecasted_value.min' => 'El valor total pronosticado debe ser al menos 0.',
            'source_id.exists' => 'La fuente de energía seleccionada no existe.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 100 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir campos numéricos
        $numericFields = [
            'accuracy_score', 'confidence_interval_lower', 'confidence_interval_upper',
            'confidence_level', 'forecast_periods', 'total_forecasted_value',
            'baseline_value', 'trend_value', 'seasonal_value', 'cyclical_value', 'irregular_value'
        ];

        foreach ($numericFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => (float) $this->input($field)]);
            }
        }

        // Convertir campos de array
        $arrayFields = [
            'forecast_data', 'baseline_data', 'trend_data', 'seasonal_data',
            'cyclical_data', 'irregular_data', 'weather_data', 'input_variables',
            'model_parameters', 'validation_metrics', 'performance_history', 'tags'
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => json_decode($this->input($field), true)]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que confidence_interval_upper sea mayor que confidence_interval_lower
            if ($this->filled('confidence_interval_upper') && $this->filled('confidence_interval_lower')) {
                if ($this->confidence_interval_upper <= $this->confidence_interval_lower) {
                    $validator->errors()->add('confidence_interval_upper', 'El límite superior del intervalo de confianza debe ser mayor que el límite inferior.');
                }
            }

            // Validar que el rango de fechas del pronóstico sea razonable
            if ($this->filled('forecast_start_time') && $this->filled('forecast_end_time')) {
                $startTime = \Carbon\Carbon::parse($this->forecast_start_time);
                $endTime = \Carbon\Carbon::parse($this->forecast_end_time);
                
                if ($startTime >= $endTime) {
                    $validator->errors()->add('forecast_end_time', 'La fecha de fin del pronóstico debe ser posterior a la fecha de inicio.');
                }
                
                $duration = $startTime->diffInHours($endTime);
                if ($duration > 8760) { // Más de 1 año
                    $validator->errors()->add('forecast_end_time', 'El período del pronóstico no puede ser mayor a 1 año.');
                }
            }

            // Validar que la fecha de expiración sea posterior al período del pronóstico
            if ($this->filled('expiry_time') && $this->filled('forecast_end_time')) {
                $expiryTime = \Carbon\Carbon::parse($this->expiry_time);
                $forecastEndTime = \Carbon\Carbon::parse($this->forecast_end_time);

                if ($expiryTime <= $forecastEndTime) {
                    $validator->errors()->add('expiry_time', 'La fecha de expiración debe ser posterior al final del período del pronóstico.');
                }
            }

            // Validar que valid_until sea posterior a valid_from
            if ($this->filled('valid_until') && $this->filled('valid_from')) {
                $validUntil = \Carbon\Carbon::parse($this->valid_until);
                $validFrom = \Carbon\Carbon::parse($this->valid_from);

                if ($validUntil <= $validFrom) {
                    $validator->errors()->add('valid_until', 'La fecha de validez hasta debe ser posterior a la fecha de validez desde.');
                }
            }

            // Validar que el puntaje de precisión y nivel de confianza sean consistentes
            if ($this->filled('accuracy_score') && $this->filled('accuracy_level')) {
                $score = (float) $this->accuracy_score;
                $level = $this->accuracy_level;

                if (($level === 'very_high' && $score < 90) ||
                    ($level === 'high' && ($score < 80 || $score >= 90)) ||
                    ($level === 'medium' && ($score < 70 || $score >= 80)) ||
                    ($level === 'low' && $score >= 70)) {
                    $validator->errors()->add('accuracy_score', 'El puntaje de precisión no es consistente con el nivel de precisión seleccionado.');
                }
            }

            // Validar que no se modifiquen campos críticos si el pronóstico ya está validado
            if ($this->forecast->isValidatedStatus()) {
                $criticalFields = ['forecast_type', 'forecast_horizon', 'forecast_method', 'forecast_start_time', 'forecast_end_time'];
                foreach ($criticalFields as $field) {
                    if ($this->has($field)) {
                        $validator->errors()->add($field, 'No se puede modificar este campo en un pronóstico ya validado.');
                    }
                }
            }

            // Validar que no se modifiquen campos si el pronóstico está expirado
            if ($this->forecast->isExpired()) {
                $validator->errors()->add('forecast_status', 'No se puede modificar un pronóstico expirado.');
            }
        });
    }
}
