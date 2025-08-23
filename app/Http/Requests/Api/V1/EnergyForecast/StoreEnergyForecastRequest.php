<?php

namespace App\Http\Requests\Api\V1\EnergyForecast;

use App\Models\EnergyForecast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnergyForecastRequest extends FormRequest
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
                'nullable',
                'string',
                'max:50',
                Rule::unique('energy_forecasts', 'forecast_number'),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'forecast_type' => [
                'required',
                'string',
                Rule::in(array_keys(EnergyForecast::getForecastTypes())),
            ],
            'forecast_horizon' => [
                'required',
                'string',
                Rule::in(array_keys(EnergyForecast::getForecastHorizons())),
            ],
            'forecast_method' => [
                'required',
                'string',
                Rule::in(array_keys(EnergyForecast::getForecastMethods())),
            ],
            'forecast_status' => [
                'nullable',
                'string',
                Rule::in(array_keys(EnergyForecast::getForecastStatuses())),
            ],
            'accuracy_level' => [
                'nullable',
                'string',
                Rule::in(array_keys(EnergyForecast::getAccuracyLevels())),
            ],
            'accuracy_score' => 'nullable|numeric|min:0|max:100',
            'confidence_interval_lower' => 'nullable|numeric',
            'confidence_interval_upper' => 'nullable|numeric',
            'confidence_level' => 'nullable|numeric|min:0|max:100',
            'source_id' => 'nullable|integer|exists:energy_sources,id',
            'source_type' => 'nullable|string|max:255',
            'target_id' => 'nullable|integer',
            'target_type' => 'nullable|string|max:255',
            'forecast_start_time' => 'required|date',
            'forecast_end_time' => 'required|date|after:forecast_start_time',
            'generation_time' => 'nullable|date',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'expiry_time' => 'nullable|date|after:forecast_end_time',
            'time_zone' => 'nullable|string|max:50',
            'time_resolution' => 'nullable|string|max:50',
            'forecast_periods' => 'nullable|integer|min:1',
            'total_forecasted_value' => 'nullable|numeric|min:0',
            'forecast_unit' => 'nullable|string|max:50',
            'baseline_value' => 'nullable|numeric',
            'trend_value' => 'nullable|numeric',
            'seasonal_value' => 'nullable|numeric',
            'cyclical_value' => 'nullable|numeric',
            'irregular_value' => 'nullable|numeric',
            'forecast_data' => 'nullable|array',
            'baseline_data' => 'nullable|array',
            'trend_data' => 'nullable|array',
            'seasonal_data' => 'nullable|array',
            'cyclical_data' => 'nullable|array',
            'irregular_data' => 'nullable|array',
            'weather_data' => 'nullable|array',
            'input_variables' => 'nullable|array',
            'model_parameters' => 'nullable|array',
            'validation_metrics' => 'nullable|array',
            'performance_history' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'notes' => 'nullable|string|max:1000',
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
            'forecast_end_time.after' => 'La fecha de fin del pronóstico debe ser posterior a la fecha de inicio.',
            'valid_until.after' => 'La fecha de validez hasta debe ser posterior a la fecha de validez desde.',
            'expiry_time.after' => 'La fecha de expiración debe ser posterior a la fecha de fin del pronóstico.',
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
        // Asignar created_by si no está presente
        if (!$this->has('created_by')) {
            $this->merge(['created_by' => auth()->id()]);
        }

        // Generar forecast_number si no está presente
        if (!$this->has('forecast_number')) {
            $this->merge(['forecast_number' => 'FC-' . uniqid()]);
        }

        // Establecer forecast_status por defecto
        if (!$this->has('forecast_status')) {
            $this->merge(['forecast_status' => 'draft']);
        }

        // Establecer generation_time por defecto
        if (!$this->has('generation_time')) {
            $this->merge(['generation_time' => now()]);
        }

        // Establecer valid_from por defecto
        if (!$this->has('valid_from')) {
            $this->merge(['valid_from' => now()]);
        }

        // Establecer time_zone por defecto
        if (!$this->has('time_zone')) {
            $this->merge(['time_zone' => 'UTC']);
        }

        // Establecer forecast_unit por defecto
        if (!$this->has('forecast_unit')) {
            $this->merge(['forecast_unit' => 'kWh']);
        }

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
        });
    }
}
