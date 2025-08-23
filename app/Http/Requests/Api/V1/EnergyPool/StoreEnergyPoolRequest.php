<?php

namespace App\Http\Requests\Api\V1\EnergyPool;

use App\Models\EnergyPool;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnergyPoolRequest extends FormRequest
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
            'pool_number' => 'required|string|max:255|unique:energy_pools,pool_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'pool_type' => ['required', 'string', Rule::in(array_keys(EnergyPool::getPoolTypes()))],
            'status' => ['required', 'string', Rule::in(array_keys(EnergyPool::getStatuses()))],
            'energy_category' => ['required', 'string', Rule::in(array_keys(EnergyPool::getEnergyCategories()))],
            'total_capacity_mw' => 'nullable|numeric|min:0|max:999999.99',
            'available_capacity_mw' => 'nullable|numeric|min:0|max:999999.99',
            'reserved_capacity_mw' => 'nullable|numeric|min:0|max:999999.99',
            'utilized_capacity_mw' => 'nullable|numeric|min:0|max:999999.99',
            'efficiency_rating' => 'nullable|numeric|min:0|max:100',
            'availability_factor' => 'nullable|numeric|min:0|max:100',
            'capacity_factor' => 'nullable|numeric|min:0|max:100',
            'annual_production_mwh' => 'nullable|numeric|min:0|max:999999.99',
            'monthly_production_mwh' => 'nullable|numeric|min:0|max:999999.99',
            'daily_production_mwh' => 'nullable|numeric|min:0|max:999999.99',
            'hourly_production_mwh' => 'nullable|numeric|min:0|max:999999.99',
            'location_address' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|min:-90|max:90',
            'longitude' => 'nullable|numeric|min:-180|max:180',
            'region' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'commissioning_date' => 'nullable|date|before_or_equal:today',
            'decommissioning_date' => 'nullable|date|after:commissioning_date',
            'expected_lifespan_years' => 'nullable|integer|min:1|max:100',
            'construction_cost' => 'nullable|numeric|min:0|max:999999999.99',
            'operational_cost_per_mwh' => 'nullable|numeric|min:0|max:999999.99',
            'maintenance_cost_per_mwh' => 'nullable|numeric|min:0|max:999999.99',
            'technical_specifications' => 'nullable|array',
            'environmental_impact' => 'nullable|array',
            'regulatory_compliance' => 'nullable|array',
            'safety_features' => 'nullable|array',
            'pool_members' => 'nullable|array',
            'pool_operators' => 'nullable|array',
            'pool_governance' => 'nullable|array',
            'trading_rules' => 'nullable|array',
            'settlement_procedures' => 'nullable|array',
            'risk_management' => 'nullable|array',
            'performance_metrics' => 'nullable|array',
            'environmental_data' => 'nullable|array',
            'regulatory_documents' => 'nullable|array',
            'tags' => 'nullable|array',
            'managed_by' => 'nullable|exists:users,id',
            'created_by' => 'nullable|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'approved_at' => 'nullable|date|before_or_equal:now',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pool_number.required' => 'El número de pool es obligatorio.',
            'pool_number.unique' => 'El número de pool ya existe.',
            'name.required' => 'El nombre del pool es obligatorio.',
            'pool_type.required' => 'El tipo de pool es obligatorio.',
            'pool_type.in' => 'El tipo de pool seleccionado no es válido.',
            'status.required' => 'El estado del pool es obligatorio.',
            'status.in' => 'El estado del pool seleccionado no es válido.',
            'energy_category.required' => 'La categoría de energía es obligatoria.',
            'energy_category.in' => 'La categoría de energía seleccionada no es válida.',
            'total_capacity_mw.numeric' => 'La capacidad total debe ser numérica.',
            'total_capacity_mw.min' => 'La capacidad total debe ser mayor o igual a 0.',
            'available_capacity_mw.numeric' => 'La capacidad disponible debe ser numérica.',
            'available_capacity_mw.min' => 'La capacidad disponible debe ser mayor o igual a 0.',
            'reserved_capacity_mw.numeric' => 'La capacidad reservada debe ser numérica.',
            'reserved_capacity_mw.min' => 'La capacidad reservada debe ser mayor o igual a 0.',
            'utilized_capacity_mw.numeric' => 'La capacidad utilizada debe ser numérica.',
            'utilized_capacity_mw.min' => 'La capacidad utilizada debe ser mayor o igual a 0.',
            'efficiency_rating.numeric' => 'La calificación de eficiencia debe ser numérica.',
            'efficiency_rating.min' => 'La calificación de eficiencia debe ser mayor o igual a 0.',
            'efficiency_rating.max' => 'La calificación de eficiencia debe ser menor o igual a 100.',
            'availability_factor.numeric' => 'El factor de disponibilidad debe ser numérico.',
            'availability_factor.min' => 'El factor de disponibilidad debe ser mayor o igual a 0.',
            'availability_factor.max' => 'El factor de disponibilidad debe ser menor o igual a 100.',
            'capacity_factor.numeric' => 'El factor de capacidad debe ser numérico.',
            'capacity_factor.min' => 'El factor de capacidad debe ser mayor o igual a 0.',
            'capacity_factor.max' => 'El factor de capacidad debe ser menor o igual a 100.',
            'annual_production_mwh.numeric' => 'La producción anual debe ser numérica.',
            'annual_production_mwh.min' => 'La producción anual debe ser mayor o igual a 0.',
            'monthly_production_mwh.numeric' => 'La producción mensual debe ser numérica.',
            'monthly_production_mwh.min' => 'La producción mensual debe ser mayor o igual a 0.',
            'daily_production_mwh.numeric' => 'La producción diaria debe ser numérica.',
            'daily_production_mwh.min' => 'La producción diaria debe ser mayor o igual a 0.',
            'hourly_production_mwh.numeric' => 'La producción horaria debe ser numérica.',
            'hourly_production_mwh.min' => 'La producción horaria debe ser mayor o igual a 0.',
            'latitude.numeric' => 'La latitud debe ser numérica.',
            'latitude.min' => 'La latitud debe estar entre -90 y 90.',
            'latitude.max' => 'La latitud debe estar entre -90 y 90.',
            'longitude.numeric' => 'La longitud debe ser numérica.',
            'longitude.min' => 'La longitud debe estar entre -180 y 180.',
            'longitude.max' => 'La longitud debe estar entre -180 y 180.',
            'commissioning_date.date' => 'La fecha de puesta en servicio debe ser una fecha válida.',
            'commissioning_date.before_or_equal' => 'La fecha de puesta en servicio no puede ser futura.',
            'decommissioning_date.date' => 'La fecha de desmantelamiento debe ser una fecha válida.',
            'decommissioning_date.after' => 'La fecha de desmantelamiento debe ser posterior a la fecha de puesta en servicio.',
            'expected_lifespan_years.integer' => 'La vida útil esperada debe ser un número entero.',
            'expected_lifespan_years.min' => 'La vida útil esperada debe ser mayor o igual a 1 año.',
            'expected_lifespan_years.max' => 'La vida útil esperada no puede exceder 100 años.',
            'construction_cost.numeric' => 'El costo de construcción debe ser numérico.',
            'construction_cost.min' => 'El costo de construcción debe ser mayor o igual a 0.',
            'operational_cost_per_mwh.numeric' => 'El costo operativo por MWh debe ser numérico.',
            'operational_cost_per_mwh.min' => 'El costo operativo por MWh debe ser mayor o igual a 0.',
            'maintenance_cost_per_mwh.numeric' => 'El costo de mantenimiento por MWh debe ser numérico.',
            'maintenance_cost_per_mwh.min' => 'El costo de mantenimiento por MWh debe ser mayor o igual a 0.',
            'approved_at.date' => 'La fecha de aprobación debe ser una fecha válida.',
            'approved_at.before_or_equal' => 'La fecha de aprobación no puede ser futura.',
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

        // Convertir campos numéricos
        if ($this->filled('total_capacity_mw')) {
            $this->merge(['total_capacity_mw' => (float) $this->total_capacity_mw]);
        }

        if ($this->filled('available_capacity_mw')) {
            $this->merge(['available_capacity_mw' => (float) $this->available_capacity_mw]);
        }

        if ($this->filled('reserved_capacity_mw')) {
            $this->merge(['reserved_capacity_mw' => (float) $this->reserved_capacity_mw]);
        }

        if ($this->filled('utilized_capacity_mw')) {
            $this->merge(['utilized_capacity_mw' => (float) $this->utilized_capacity_mw]);
        }

        if ($this->filled('efficiency_rating')) {
            $this->merge(['efficiency_rating' => (float) $this->efficiency_rating]);
        }

        if ($this->filled('availability_factor')) {
            $this->merge(['availability_factor' => (float) $this->availability_factor]);
        }

        if ($this->filled('capacity_factor')) {
            $this->merge(['capacity_factor' => (float) $this->capacity_factor]);
        }

        if ($this->filled('annual_production_mwh')) {
            $this->merge(['annual_production_mwh' => (float) $this->annual_production_mwh]);
        }

        if ($this->filled('monthly_production_mwh')) {
            $this->merge(['monthly_production_mwh' => (float) $this->monthly_production_mwh]);
        }

        if ($this->filled('daily_production_mwh')) {
            $this->merge(['daily_production_mwh' => (float) $this->daily_production_mwh]);
        }

        if ($this->filled('hourly_production_mwh')) {
            $this->merge(['hourly_production_mwh' => (float) $this->hourly_production_mwh]);
        }

        if ($this->filled('latitude')) {
            $this->merge(['latitude' => (float) $this->latitude]);
        }

        if ($this->filled('longitude')) {
            $this->merge(['longitude' => (float) $this->longitude]);
        }

        if ($this->filled('construction_cost')) {
            $this->merge(['construction_cost' => (float) $this->construction_cost]);
        }

        if ($this->filled('operational_cost_per_mwh')) {
            $this->merge(['operational_cost_per_mwh' => (float) $this->operational_cost_per_mwh]);
        }

        if ($this->filled('maintenance_cost_per_mwh')) {
            $this->merge(['maintenance_cost_per_mwh' => (float) $this->maintenance_cost_per_mwh]);
        }

        // Decodificar campos JSON si son string
        if ($this->filled('technical_specifications') && is_string($this->technical_specifications)) {
            $this->merge(['technical_specifications' => json_decode($this->technical_specifications, true)]);
        }

        if ($this->filled('environmental_impact') && is_string($this->environmental_impact)) {
            $this->merge(['environmental_impact' => json_decode($this->environmental_impact, true)]);
        }

        if ($this->filled('regulatory_compliance') && is_string($this->regulatory_compliance)) {
            $this->merge(['regulatory_compliance' => json_decode($this->regulatory_compliance, true)]);
        }

        if ($this->filled('safety_features') && is_string($this->safety_features)) {
            $this->merge(['safety_features' => json_decode($this->safety_features, true)]);
        }

        if ($this->filled('pool_members') && is_string($this->pool_members)) {
            $this->merge(['pool_members' => json_decode($this->pool_members, true)]);
        }

        if ($this->filled('pool_operators') && is_string($this->pool_operators)) {
            $this->merge(['pool_operators' => json_decode($this->pool_operators, true)]);
        }

        if ($this->filled('pool_governance') && is_string($this->pool_governance)) {
            $this->merge(['pool_governance' => json_decode($this->pool_governance, true)]);
        }

        if ($this->filled('trading_rules') && is_string($this->trading_rules)) {
            $this->merge(['trading_rules' => json_decode($this->trading_rules, true)]);
        }

        if ($this->filled('settlement_procedures') && is_string($this->settlement_procedures)) {
            $this->merge(['settlement_procedures' => json_decode($this->settlement_procedures, true)]);
        }

        if ($this->filled('risk_management') && is_string($this->risk_management)) {
            $this->merge(['risk_management' => json_decode($this->risk_management, true)]);
        }

        if ($this->filled('performance_metrics') && is_string($this->performance_metrics)) {
            $this->merge(['performance_metrics' => json_decode($this->performance_metrics, true)]);
        }

        if ($this->filled('environmental_data') && is_string($this->environmental_data)) {
            $this->merge(['environmental_data' => json_decode($this->environmental_data, true)]);
        }

        if ($this->filled('regulatory_documents') && is_string($this->regulatory_documents)) {
            $this->merge(['regulatory_documents' => json_decode($this->regulatory_documents, true)]);
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
            // Validar que la fecha de aprobación esté presente si se proporciona el aprobador
            if ($this->filled('approved_by') && !$this->filled('approved_at')) {
                $validator->errors()->add('approved_at', 'La fecha de aprobación es obligatoria cuando se especifica un aprobador.');
            }

            // Validar que el aprobador esté presente si se proporciona la fecha de aprobación
            if ($this->filled('approved_at') && !$this->filled('approved_by')) {
                $validator->errors()->add('approved_by', 'El aprobador es obligatorio cuando se especifica una fecha de aprobación.');
            }

            // Validar que la capacidad disponible no exceda la capacidad total
            if ($this->filled('total_capacity_mw') && $this->filled('available_capacity_mw')) {
                if ($this->available_capacity_mw > $this->total_capacity_mw) {
                    $validator->errors()->add('available_capacity_mw', 'La capacidad disponible no puede exceder la capacidad total.');
                }
            }

            // Validar que la capacidad reservada no exceda la capacidad total
            if ($this->filled('total_capacity_mw') && $this->filled('reserved_capacity_mw')) {
                if ($this->reserved_capacity_mw > $this->total_capacity_mw) {
                    $validator->errors()->add('reserved_capacity_mw', 'La capacidad reservada no puede exceder la capacidad total.');
                }
            }

            // Validar que la capacidad utilizada no exceda la capacidad total
            if ($this->filled('total_capacity_mw') && $this->filled('utilized_capacity_mw')) {
                if ($this->utilized_capacity_mw > $this->total_capacity_mw) {
                    $validator->errors()->add('utilized_capacity_mw', 'La capacidad utilizada no puede exceder la capacidad total.');
                }
            }

            // Validar que la suma de capacidades no exceda la capacidad total
            if ($this->filled('total_capacity_mw')) {
                $sumCapacities = 0;
                if ($this->filled('available_capacity_mw')) $sumCapacities += $this->available_capacity_mw;
                if ($this->filled('reserved_capacity_mw')) $sumCapacities += $this->reserved_capacity_mw;
                if ($this->filled('utilized_capacity_mw')) $sumCapacities += $this->utilized_capacity_mw;

                if ($sumCapacities > $this->total_capacity_mw) {
                    $validator->errors()->add('total_capacity_mw', 'La suma de capacidades (disponible, reservada y utilizada) no puede exceder la capacidad total.');
                }
            }

            // Validar que la fecha de desmantelamiento esté presente si se proporciona la fecha de puesta en servicio
            if ($this->filled('commissioning_date') && $this->filled('decommissioning_date')) {
                if ($this->decommissioning_date <= $this->commissioning_date) {
                    $validator->errors()->add('decommissioning_date', 'La fecha de desmantelamiento debe ser posterior a la fecha de puesta en servicio.');
                }
            }

            // Validar que la vida útil esperada sea coherente con las fechas
            if ($this->filled('commissioning_date') && $this->filled('expected_lifespan_years')) {
                $expectedEndDate = $this->commissioning_date->addYears($this->expected_lifespan_years);
                if ($expectedEndDate < now()) {
                    $validator->errors()->add('expected_lifespan_years', 'La vida útil esperada parece ser muy corta para un pool que ya está en servicio.');
                }
            }
        });
    }
}
