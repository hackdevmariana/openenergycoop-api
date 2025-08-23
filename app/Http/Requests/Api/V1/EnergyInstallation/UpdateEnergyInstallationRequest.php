<?php

namespace App\Http\Requests\Api\V1\EnergyInstallation;

use App\Models\EnergyInstallation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnergyInstallationRequest extends FormRequest
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
        $energyInstallationId = $this->route('energyInstallation')->id ?? $this->route('id');

        return [
            'installation_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('energy_installations', 'installation_number')->ignore($energyInstallationId)
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:65535',
            'installation_type' => ['sometimes', 'string', Rule::in(array_keys(EnergyInstallation::getInstallationTypes()))],
            'status' => ['sometimes', 'string', Rule::in(array_keys(EnergyInstallation::getStatuses()))],
            'priority' => ['sometimes', 'string', Rule::in(array_keys(EnergyInstallation::getPriorities()))],
            'energy_source_id' => 'sometimes|exists:energy_sources,id',
            'customer_id' => 'sometimes|nullable|exists:customers,id',
            'project_id' => 'sometimes|nullable|exists:production_projects,id',
            'installed_capacity_kw' => 'sometimes|numeric|min:0|max:999999.99',
            'operational_capacity_kw' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'efficiency_rating' => 'sometimes|nullable|numeric|min:0|max:100',
            'installation_date' => 'sometimes|date|before_or_equal:today',
            'commissioning_date' => 'sometimes|nullable|date',
            'location_address' => 'sometimes|nullable|string|max:500',
            'location_coordinates' => 'sometimes|nullable|string|max:100',
            'installation_cost' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'maintenance_cost_per_year' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'warranty_expiry_date' => 'sometimes|nullable|date',
            'next_maintenance_date' => 'sometimes|nullable|date',
            'notes' => 'sometimes|nullable|string|max:65535',
            'is_active' => 'sometimes|boolean',
            'is_public' => 'sometimes|boolean',
            'installed_by_id' => 'sometimes|nullable|exists:users,id',
            'managed_by_id' => 'sometimes|nullable|exists:users,id',
            'approved_by_id' => 'sometimes|nullable|exists:users,id',
            'approval_date' => 'sometimes|nullable|date',
            'technical_specifications' => 'sometimes|nullable|json',
            'safety_certifications' => 'sometimes|nullable|json',
            'environmental_impact_data' => 'sometimes|nullable|json',
            'performance_metrics' => 'sometimes|nullable|json',
            'maintenance_schedule' => 'sometimes|nullable|json',
            'emergency_contacts' => 'sometimes|nullable|json',
            'documentation_files' => 'sometimes|nullable|json',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:100',
            'custom_fields' => 'sometimes|nullable|json',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'installation_number.unique' => 'El número de instalación ya existe',
            'installation_type.in' => 'El tipo de instalación seleccionado no es válido',
            'status.in' => 'El estado seleccionado no es válido',
            'priority.in' => 'La prioridad seleccionada no es válida',
            'energy_source_id.exists' => 'La fuente de energía seleccionada no existe',
            'customer_id.exists' => 'El cliente seleccionado no existe',
            'project_id.exists' => 'El proyecto seleccionado no existe',
            'installed_capacity_kw.numeric' => 'La capacidad instalada debe ser un número',
            'installed_capacity_kw.min' => 'La capacidad instalada debe ser mayor o igual a 0',
            'operational_capacity_kw.numeric' => 'La capacidad operativa debe ser un número',
            'operational_capacity_kw.min' => 'La capacidad operativa debe ser mayor o igual a 0',
            'efficiency_rating.numeric' => 'La eficiencia debe ser un número',
            'efficiency_rating.min' => 'La eficiencia debe ser mayor o igual a 0',
            'efficiency_rating.max' => 'La eficiencia no puede ser mayor a 100',
            'installation_date.date' => 'La fecha de instalación debe ser una fecha válida',
            'installation_date.before_or_equal' => 'La fecha de instalación no puede ser futura',
            'commissioning_date.date' => 'La fecha de puesta en marcha debe ser una fecha válida',
            'installation_cost.numeric' => 'El costo de instalación debe ser un número',
            'installation_cost.min' => 'El costo de instalación debe ser mayor o igual a 0',
            'maintenance_cost_per_year.numeric' => 'El costo de mantenimiento anual debe ser un número',
            'maintenance_cost_per_year.min' => 'El costo de mantenimiento anual debe ser mayor o igual a 0',
            'warranty_expiry_date.date' => 'La fecha de vencimiento de garantía debe ser una fecha válida',
            'next_maintenance_date.date' => 'La próxima fecha de mantenimiento debe ser una fecha válida',
            'installed_by_id.exists' => 'El usuario instalador seleccionado no existe',
            'managed_by_id.exists' => 'El usuario gestor seleccionado no existe',
            'approved_by_id.exists' => 'El usuario aprobador seleccionado no existe',
            'tags.array' => 'Las etiquetas deben ser un array',
            'tags.*.string' => 'Cada etiqueta debe ser una cadena de texto',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 100 caracteres',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir campos booleanos
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }

        if ($this->has('is_public')) {
            $this->merge(['is_public' => $this->boolean('is_public')]);
        }

        // Convertir campos numéricos
        if ($this->has('installed_capacity_kw')) {
            $this->merge(['installed_capacity_kw' => (float) $this->installed_capacity_kw]);
        }

        if ($this->has('operational_capacity_kw')) {
            $this->merge(['operational_capacity_kw' => (float) $this->operational_capacity_kw]);
        }

        if ($this->has('efficiency_rating')) {
            $this->merge(['efficiency_rating' => (float) $this->efficiency_rating]);
        }

        if ($this->has('installation_cost')) {
            $this->merge(['installation_cost' => (float) $this->installation_cost]);
        }

        if ($this->has('maintenance_cost_per_year')) {
            $this->merge(['maintenance_cost_per_year' => (float) $this->maintenance_cost_per_year]);
        }

        // Convertir campos JSON
        $jsonFields = [
            'technical_specifications',
            'safety_certifications',
            'environmental_impact_data',
            'performance_metrics',
            'maintenance_schedule',
            'emergency_contacts',
            'documentation_files',
            'custom_fields'
        ];

        foreach ($jsonFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => json_decode($this->input($field), true)]);
            }
        }

        // Validar fechas relacionadas si se proporcionan
        if ($this->has('commissioning_date') && $this->has('installation_date')) {
            $this->merge([
                'commissioning_date' => $this->commissioning_date,
                'installation_date' => $this->installation_date
            ]);
        }

        if ($this->has('warranty_expiry_date') && $this->has('installation_date')) {
            $this->merge([
                'warranty_expiry_date' => $this->warranty_expiry_date,
                'installation_date' => $this->installation_date
            ]);
        }

        if ($this->has('next_maintenance_date') && $this->has('installation_date')) {
            $this->merge([
                'next_maintenance_date' => $this->next_maintenance_date,
                'installation_date' => $this->installation_date
            ]);
        }

        if ($this->has('approval_date') && $this->has('installation_date')) {
            $this->merge([
                'approval_date' => $this->approval_date,
                'installation_date' => $this->installation_date
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que la fecha de puesta en marcha sea posterior a la fecha de instalación
            if ($this->has('commissioning_date') && $this->has('installation_date')) {
                if ($this->commissioning_date <= $this->installation_date) {
                    $validator->errors()->add('commissioning_date', 'La fecha de puesta en marcha debe ser posterior a la fecha de instalación');
                }
            }

            // Validar que la fecha de vencimiento de garantía sea posterior a la fecha de instalación
            if ($this->has('warranty_expiry_date') && $this->has('installation_date')) {
                if ($this->warranty_expiry_date <= $this->installation_date) {
                    $validator->errors()->add('warranty_expiry_date', 'La fecha de vencimiento de garantía debe ser posterior a la fecha de instalación');
                }
            }

            // Validar que la próxima fecha de mantenimiento sea posterior a la fecha de instalación
            if ($this->has('next_maintenance_date') && $this->has('installation_date')) {
                if ($this->next_maintenance_date <= $this->installation_date) {
                    $validator->errors()->add('next_maintenance_date', 'La próxima fecha de mantenimiento debe ser posterior a la fecha de instalación');
                }
            }

            // Validar que la fecha de aprobación sea posterior o igual a la fecha de instalación
            if ($this->has('approval_date') && $this->has('installation_date')) {
                if ($this->approval_date < $this->installation_date) {
                    $validator->errors()->add('approval_date', 'La fecha de aprobación debe ser posterior o igual a la fecha de instalación');
                }
            }
        });
    }
}
