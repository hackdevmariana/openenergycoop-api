<?php

namespace App\Http\Requests\Api\V1\ProductionProject;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductionProject;
use Illuminate\Validation\Rule;

class UpdateProductionProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el controlador
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->route('productionProject')->id ?? $this->route('id');
        
        return [
            // Información básica
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('production_projects', 'slug')->ignore($projectId)
            ],
            'description' => 'sometimes|nullable|string|max:65535',
            'project_type' => 'sometimes|string|in:' . implode(',', array_keys(ProductionProject::getProjectTypes())),
            'technology_type' => 'sometimes|string|in:' . implode(',', array_keys(ProductionProject::getTechnologyTypes())),
            'status' => 'sometimes|string|in:' . implode(',', array_keys(ProductionProject::getStatuses())),
            'priority' => 'sometimes|nullable|string|in:' . implode(',', array_keys(ProductionProject::getPriorities())),
            
            // Relaciones y asignaciones
            'organization_id' => 'sometimes|exists:organizations,id',
            'owner_user_id' => 'sometimes|exists:users,id',
            'energy_source_id' => 'sometimes|exists:energy_sources,id',
            'created_by' => 'sometimes|exists:users,id',
            
            // Capacidad y rendimiento
            'capacity_kw' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'estimated_annual_production' => 'sometimes|nullable|numeric|min:0|max:999999999',
            'efficiency_rating' => 'sometimes|nullable|numeric|min:0|max:100',
            'peak_power_kw' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'capacity_factor' => 'sometimes|nullable|numeric|min:0|max:100',
            
            // Especificaciones técnicas
            'technical_specifications' => 'sometimes|nullable|string',
            'equipment_details' => 'sometimes|nullable|string',
            'manufacturer' => 'sometimes|nullable|string|max:255',
            'model' => 'sometimes|nullable|string|max:255',
            
            // Ubicación
            'location_address' => 'sometimes|nullable|string|max:255',
            'location_city' => 'sometimes|nullable|string|max:255',
            'location_region' => 'sometimes|nullable|string|max:255',
            'location_country' => 'sometimes|nullable|string|size:2',
            'location_postal_code' => 'sometimes|nullable|string|max:20',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'location_metadata' => 'sometimes|nullable|string',
            
            // Cronograma del proyecto
            'planning_start_date' => 'sometimes|nullable|date',
            'construction_start_date' => 'sometimes|nullable|date|after_or_equal:planning_start_date',
            'construction_end_date' => 'sometimes|nullable|date|after_or_equal:construction_start_date',
            'operational_start_date' => 'sometimes|nullable|date|after_or_equal:construction_end_date',
            'expected_end_date' => 'sometimes|nullable|date|after:construction_start_date',
            'completion_percentage' => 'sometimes|nullable|numeric|min:0|max:100',
            'estimated_duration_months' => 'sometimes|nullable|integer|min:1|max:1200',
            
            // Aspectos financieros
            'total_investment' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'cost_per_kw' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'estimated_roi_percentage' => 'sometimes|nullable|numeric|min:0|max:1000',
            'payback_period_years' => 'sometimes|nullable|numeric|min:0|max:100',
            'annual_operating_cost' => 'sometimes|nullable|numeric|min:0|max:99999999.99',
            
            // Crowdfunding e inversión
            'accepts_crowdfunding' => 'sometimes|nullable|boolean',
            'is_investment_ready' => 'sometimes|nullable|boolean',
            'crowdfunding_target' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'crowdfunding_raised' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'min_investment' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'max_investment' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'investment_terms' => 'sometimes|nullable|string|max:255',
            
            // Impacto ambiental
            'co2_avoided_tons_year' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'renewable_percentage' => 'sometimes|nullable|numeric|min:0|max:100',
            'environmental_score' => 'sometimes|nullable|numeric|min:0|max:100',
            'environmental_certifications' => 'sometimes|nullable|string',
            'sustainability_metrics' => 'sometimes|nullable|string',
            
            // Permisos y regulaciones
            'regulatory_approved' => 'sometimes|nullable|boolean',
            'permits_complete' => 'sometimes|nullable|boolean',
            'regulatory_approval_date' => 'sometimes|nullable|date',
            'regulatory_authority' => 'sometimes|nullable|string|max:255',
            'permits_required' => 'sometimes|nullable|string',
            'permits_obtained' => 'sometimes|nullable|string',
            
            // Mantenimiento
            'annual_maintenance_cost' => 'sometimes|nullable|numeric|min:0|max:99999999.99',
            'maintenance_interval_months' => 'sometimes|nullable|integer|min:1|max:120',
            'maintenance_provider' => 'sometimes|nullable|string|max:255',
            'last_maintenance_date' => 'sometimes|nullable|date',
            'next_maintenance_date' => 'sometimes|nullable|date|after:last_maintenance_date',
            'maintenance_requirements' => 'sometimes|nullable|string',
            
            // Configuración del sistema
            'is_public' => 'sometimes|nullable|boolean',
            'is_active' => 'sometimes|nullable|boolean',
            'is_featured' => 'sometimes|nullable|boolean',
            'requires_approval' => 'sometimes|nullable|boolean',
            
            // Metadatos y etiquetas
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:100',
            'notes' => 'sometimes|nullable|string',
            
            // Documentos y archivos
            'images' => 'sometimes|nullable|array',
            'images.*' => 'string|max:255',
            'documents' => 'sometimes|nullable|array',
            'documents.*' => 'string|max:255',
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
            'name.max' => 'El nombre del proyecto no puede tener más de 255 caracteres',
            'slug.unique' => 'El slug del proyecto ya existe',
            'project_type.in' => 'El tipo de proyecto seleccionado no es válido',
            'technology_type.in' => 'El tipo de tecnología seleccionado no es válido',
            'status.in' => 'El estado del proyecto seleccionado no es válido',
            'organization_id.exists' => 'La organización seleccionada no existe',
            'owner_user_id.exists' => 'El usuario propietario no existe',
            'energy_source_id.exists' => 'La fuente de energía seleccionada no existe',
            'created_by.exists' => 'El usuario creador no existe',
            'capacity_kw.numeric' => 'La capacidad debe ser un número',
            'capacity_kw.min' => 'La capacidad no puede ser negativa',
            'estimated_annual_production.numeric' => 'La producción anual estimada debe ser un número',
            'estimated_annual_production.min' => 'La producción anual estimada no puede ser negativa',
            'efficiency_rating.numeric' => 'La eficiencia debe ser un número',
            'efficiency_rating.min' => 'La eficiencia no puede ser menor a 0%',
            'efficiency_rating.max' => 'La eficiencia no puede ser mayor a 100%',
            'completion_percentage.numeric' => 'El porcentaje de completado debe ser un número',
            'completion_percentage.min' => 'El porcentaje de completado no puede ser menor a 0%',
            'completion_percentage.max' => 'El porcentaje de completado no puede ser mayor a 100%',
            'total_investment.numeric' => 'La inversión total debe ser un número',
            'total_investment.min' => 'La inversión total no puede ser negativa',
            'latitude.numeric' => 'La latitud debe ser un número',
            'latitude.between' => 'La latitud debe estar entre -90 y 90',
            'longitude.numeric' => 'La longitud debe ser un número',
            'longitude.between' => 'La longitud debe estar entre -180 y 180',
            'construction_start_date.after_or_equal' => 'La fecha de inicio de construcción debe ser posterior o igual a la fecha de inicio de planificación',
            'construction_end_date.after_or_equal' => 'La fecha de fin de construcción debe ser posterior o igual a la fecha de inicio de construcción',
            'operational_start_date.after_or_equal' => 'La fecha de inicio operacional debe ser posterior o igual a la fecha de fin de construcción',
            'expected_end_date.after' => 'La fecha de finalización esperada debe ser posterior a la fecha de inicio de construcción',
            'next_maintenance_date.after' => 'La próxima fecha de mantenimiento debe ser posterior a la última fecha de mantenimiento',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del proyecto',
            'slug' => 'slug del proyecto',
            'description' => 'descripción del proyecto',
            'project_type' => 'tipo de proyecto',
            'technology_type' => 'tipo de tecnología',
            'status' => 'estado del proyecto',
            'priority' => 'prioridad del proyecto',
            'organization_id' => 'organización',
            'owner_user_id' => 'propietario del proyecto',
            'energy_source_id' => 'fuente de energía',
            'created_by' => 'usuario creador',
            'capacity_kw' => 'capacidad en kW',
            'estimated_annual_production' => 'producción anual estimada',
            'efficiency_rating' => 'eficiencia',
            'completion_percentage' => 'porcentaje de completado',
            'total_investment' => 'inversión total',
            'latitude' => 'latitud',
            'longitude' => 'longitud',
            'construction_start_date' => 'fecha de inicio de construcción',
            'construction_end_date' => 'fecha de fin de construcción',
            'operational_start_date' => 'fecha de inicio operacional',
            'expected_end_date' => 'fecha de finalización esperada',
            'next_maintenance_date' => 'próxima fecha de mantenimiento',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Asegurar que los valores booleanos sean correctos
        $this->merge([
            'accepts_crowdfunding' => $this->boolean('accepts_crowdfunding'),
            'is_investment_ready' => $this->boolean('is_investment_ready'),
            'regulatory_approved' => $this->boolean('regulatory_approved'),
            'permits_complete' => $this->boolean('permits_complete'),
            'is_public' => $this->boolean('is_public'),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'requires_approval' => $this->boolean('requires_approval'),
        ]);

        // Generar slug automáticamente si se proporciona un nuevo nombre
        if ($this->filled('name') && !$this->filled('slug')) {
            $this->merge([
                'slug' => \Str::slug($this->name)
            ]);
        }
    }
}
