<?php

namespace App\Http\Requests\Api\V1\EnergyInstallation;

use App\Models\EnergyInstallation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnergyInstallationRequest extends FormRequest
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
            'installation_number' => 'required|string|max:255|unique:energy_installations,installation_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'installation_type' => ['required', 'string', Rule::in(array_keys(EnergyInstallation::getInstallationTypes()))],
            'status' => ['required', 'string', Rule::in(array_keys(EnergyInstallation::getStatuses()))],
            'priority' => ['required', 'string', Rule::in(array_keys(EnergyInstallation::getPriorities()))],
            'energy_source_id' => 'required|exists:energy_sources,id',
            'customer_id' => 'nullable|exists:customers,id',
            'project_id' => 'nullable|exists:production_projects,id',
            'installed_capacity_kw' => 'required|numeric|min:0|max:999999.99',
            'operational_capacity_kw' => 'nullable|numeric|min:0|max:999999.99',
            'efficiency_rating' => 'nullable|numeric|min:0|max:100',
            'installation_date' => 'required|date|before_or_equal:today',
            'commissioning_date' => 'nullable|date|after_or_equal:installation_date',
            'location_address' => 'nullable|string|max:500',
            'location_coordinates' => 'nullable|string|max:100',
            'installation_cost' => 'nullable|numeric|min:0|max:999999999.99',
            'maintenance_cost_per_year' => 'nullable|numeric|min:0|max:999999999.99',
            'warranty_expiry_date' => 'nullable|date|after:installation_date',
            'next_maintenance_date' => 'nullable|date|after:installation_date',
            'notes' => 'nullable|string|max:65535',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'installed_by_id' => 'nullable|exists:users,id',
            'managed_by_id' => 'nullable|exists:users,id',
            'approved_by_id' => 'nullable|exists:users,id',
            'approval_date' => 'nullable|date|after_or_equal:installation_date',
            'technical_specifications' => 'nullable|json',
            'safety_certifications' => 'nullable|json',
            'environmental_impact_data' => 'nullable|json',
            'performance_metrics' => 'nullable|json',
            'maintenance_schedule' => 'nullable|json',
            'emergency_contacts' => 'nullable|json',
            'documentation_files' => 'nullable|json',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'custom_fields' => 'nullable|json',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'installation_number.required' => 'El campo número de instalación es obligatorio',
            'installation_number.unique' => 'El número de instalación ya existe',
            'name.required' => 'El campo nombre es obligatorio',
            'installation_type.required' => 'El campo tipo de instalación es obligatorio',
            'installation_type.in' => 'El tipo de instalación seleccionado no es válido',
            'status.required' => 'El campo estado es obligatorio',
            'status.in' => 'El estado seleccionado no es válido',
            'priority.required' => 'El campo prioridad es obligatorio',
            'priority.in' => 'La prioridad seleccionada no es válida',
            'energy_source_id.required' => 'El campo fuente de energía es obligatorio',
            'energy_source_id.exists' => 'La fuente de energía seleccionada no existe',
            'customer_id.exists' => 'El cliente seleccionado no existe',
            'project_id.exists' => 'El proyecto seleccionado no existe',
            'installed_capacity_kw.required' => 'El campo capacidad instalada es obligatorio',
            'installed_capacity_kw.numeric' => 'La capacidad instalada debe ser un número',
            'installed_capacity_kw.min' => 'La capacidad instalada debe ser mayor o igual a 0',
            'operational_capacity_kw.numeric' => 'La capacidad operativa debe ser un número',
            'operational_capacity_kw.min' => 'La capacidad operativa debe ser mayor o igual a 0',
            'efficiency_rating.numeric' => 'La eficiencia debe ser un número',
            'efficiency_rating.min' => 'La eficiencia debe ser mayor o igual a 0',
            'efficiency_rating.max' => 'La eficiencia no puede ser mayor a 100',
            'installation_date.required' => 'El campo fecha de instalación es obligatorio',
            'installation_date.date' => 'La fecha de instalación debe ser una fecha válida',
            'installation_date.before_or_equal' => 'La fecha de instalación no puede ser futura',
            'commissioning_date.date' => 'La fecha de puesta en marcha debe ser una fecha válida',
            'commissioning_date.after_or_equal' => 'La fecha de puesta en marcha debe ser posterior o igual a la fecha de instalación',
            'installation_cost.numeric' => 'El costo de instalación debe ser un número',
            'installation_cost.min' => 'El costo de instalación debe ser mayor o igual a 0',
            'maintenance_cost_per_year.numeric' => 'El costo de mantenimiento anual debe ser un número',
            'maintenance_cost_per_year.min' => 'El costo de mantenimiento anual debe ser mayor o igual a 0',
            'warranty_expiry_date.date' => 'La fecha de vencimiento de garantía debe ser una fecha válida',
            'warranty_expiry_date.after' => 'La fecha de vencimiento de garantía debe ser posterior a la fecha de instalación',
            'next_maintenance_date.date' => 'La próxima fecha de mantenimiento debe ser una fecha válida',
            'next_maintenance_date.after' => 'La próxima fecha de mantenimiento debe ser posterior a la fecha de instalación',
            'installed_by_id.exists' => 'El usuario instalador seleccionado no existe',
            'managed_by_id.exists' => 'El usuario gestor seleccionado no existe',
            'approved_by_id.exists' => 'El usuario aprobador seleccionado no existe',
            'approval_date.date' => 'La fecha de aprobación debe ser una fecha válida',
            'approval_date.after_or_equal' => 'La fecha de aprobación debe ser posterior o igual a la fecha de instalación',
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
        // Generar número de instalación si no se proporciona
        if (!$this->installation_number) {
            $this->merge([
                'installation_number' => 'INST-' . str_pad(EnergyInstallation::count() + 1, 3, '0', STR_PAD_LEFT)
            ]);
        }

        // Convertir campos booleanos
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'is_public' => $this->boolean('is_public', false),
        ]);

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
    }
}
