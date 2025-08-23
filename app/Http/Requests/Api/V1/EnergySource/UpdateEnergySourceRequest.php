<?php

namespace App\Http\Requests\Api\V1\EnergySource;

use App\Models\EnergySource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnergySourceRequest extends FormRequest
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
        $energySourceId = $this->route('energySource')->id ?? $this->route('id');
        
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('energy_sources', 'slug')->ignore($energySourceId)
            ],
            'description' => 'sometimes|nullable|string|max:65535',
            'category' => ['sometimes', 'string', Rule::in(array_keys(EnergySource::getCategories()))],
            'type' => ['sometimes', 'string', Rule::in(array_keys(EnergySource::getTypes()))],
            'status' => ['sometimes', 'string', Rule::in(array_keys(EnergySource::getStatuses()))],
            
            // Especificaciones técnicas
            'technical_specs' => 'sometimes|nullable|string',
            'efficiency_min' => 'sometimes|nullable|numeric|min:0|max:100',
            'efficiency_max' => 'sometimes|nullable|numeric|min:0|max:100',
            'efficiency_typical' => 'sometimes|nullable|numeric|min:0|max:100',
            'capacity_min' => 'sometimes|nullable|numeric|min:0',
            'capacity_max' => 'sometimes|nullable|numeric|min:0',
            'capacity_typical' => 'sometimes|nullable|numeric|min:0',
            'lifespan_years' => 'sometimes|nullable|integer|min:1',
            'degradation_rate' => 'sometimes|nullable|numeric|min:0|max:10',
            
            // Impacto ambiental
            'carbon_footprint_kg_kwh' => 'sometimes|nullable|numeric|min:0',
            'water_consumption_l_kwh' => 'sometimes|nullable|numeric|min:0',
            'land_use_m2_kw' => 'sometimes|nullable|numeric|min:0',
            'environmental_impact' => 'sometimes|nullable|string',
            'is_renewable' => 'sometimes|boolean',
            'is_clean' => 'sometimes|boolean',
            'renewable_certificate' => 'sometimes|nullable|string|max:255',
            'environmental_rating' => 'sometimes|nullable|numeric|min:0|max:100',
            
            // Aspectos financieros
            'installation_cost_per_kw' => 'sometimes|nullable|numeric|min:0',
            'maintenance_cost_annual' => 'sometimes|nullable|numeric|min:0',
            'operational_cost_per_kwh' => 'sometimes|nullable|numeric|min:0',
            'levelized_cost_kwh' => 'sometimes|nullable|numeric|min:0',
            'payback_period_years' => 'sometimes|nullable|numeric|min:0',
            'financial_notes' => 'sometimes|nullable|string',
            
            // Disponibilidad y dependencias
            'geographic_availability' => 'sometimes|nullable|string',
            'weather_dependencies' => 'sometimes|nullable|string',
            'seasonal_variations' => 'sometimes|nullable|string',
            'capacity_factor_min' => 'sometimes|nullable|numeric|min:0|max:100',
            'capacity_factor_max' => 'sometimes|nullable|numeric|min:0|max:100',
            
            // Tecnología y equipamiento
            'technology_description' => 'sometimes|nullable|string',
            'manufacturer' => 'sometimes|nullable|string|max:255',
            'model_series' => 'sometimes|nullable|string|max:255',
            'warranty_years' => 'sometimes|nullable|numeric|min:0',
            'certification_standards' => 'sometimes|nullable|string|max:255',
            'maintenance_requirements' => 'sometimes|nullable|string',
            
            // Configuración del sistema
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'is_public' => 'sometimes|boolean',
            'requires_approval' => 'sometimes|boolean',
            'icon' => 'sometimes|nullable|string|max:255',
            'color' => 'sometimes|nullable|string|max:7',
            'sort_order' => 'sometimes|nullable|integer|min:0',
            
            // Metadatos y etiquetas
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:100',
            'notes' => 'sometimes|nullable|string',
            
            // Documentos y archivos
            'images' => 'sometimes|nullable|array|max:15',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'documents' => 'sometimes|nullable|array|max:30',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,txt|max:10240',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
            'slug.unique' => 'El slug ya está en uso',
            'category.in' => 'La categoría seleccionada no es válida',
            'type.in' => 'El tipo seleccionado no es válido',
            'status.in' => 'El estado seleccionado no es válido',
            'efficiency_min.min' => 'La eficiencia mínima debe ser mayor o igual a 0',
            'efficiency_min.max' => 'La eficiencia mínima no puede ser mayor a 100',
            'efficiency_max.min' => 'La eficiencia máxima debe ser mayor o igual a 0',
            'efficiency_max.max' => 'La eficiencia máxima no puede ser mayor a 100',
            'efficiency_typical.min' => 'La eficiencia típica debe ser mayor o igual a 0',
            'efficiency_typical.max' => 'La eficiencia típica no puede ser mayor a 100',
            'capacity_min.min' => 'La capacidad mínima debe ser mayor o igual a 0',
            'capacity_max.min' => 'La capacidad máxima debe ser mayor o igual a 0',
            'capacity_typical.min' => 'La capacidad típica debe ser mayor o igual a 0',
            'lifespan_years.min' => 'La vida útil debe ser mayor a 0',
            'degradation_rate.min' => 'La tasa de degradación debe ser mayor o igual a 0',
            'degradation_rate.max' => 'La tasa de degradación no puede ser mayor a 10',
            'carbon_footprint_kg_kwh.min' => 'La huella de carbono debe ser mayor o igual a 0',
            'water_consumption_l_kwh.min' => 'El consumo de agua debe ser mayor o igual a 0',
            'land_use_m2_kw.min' => 'El uso de tierra debe ser mayor o igual a 0',
            'environmental_rating.min' => 'La calificación ambiental debe ser mayor o igual a 0',
            'environmental_rating.max' => 'La calificación ambiental no puede ser mayor a 100',
            'installation_cost_per_kw.min' => 'El costo de instalación debe ser mayor o igual a 0',
            'maintenance_cost_annual.min' => 'El costo de mantenimiento debe ser mayor o igual a 0',
            'operational_cost_per_kwh.min' => 'El costo operativo debe ser mayor o igual a 0',
            'levelized_cost_kwh.min' => 'El costo nivelado debe ser mayor o igual a 0',
            'payback_period_years.min' => 'El período de recuperación debe ser mayor o igual a 0',
            'capacity_factor_min.min' => 'El factor de capacidad mínimo debe ser mayor o igual a 0',
            'capacity_factor_min.max' => 'El factor de capacidad mínimo no puede ser mayor a 100',
            'capacity_factor_max.min' => 'El factor de capacidad máximo debe ser mayor o igual a 0',
            'capacity_factor_max.max' => 'El factor de capacidad máximo no puede ser mayor a 100',
            'warranty_years.min' => 'La garantía debe ser mayor o igual a 0',
            'sort_order.min' => 'El orden de clasificación debe ser mayor o igual a 0',
            'images.max' => 'No se pueden subir más de 15 imágenes',
            'images.*.image' => 'Los archivos deben ser imágenes',
            'images.*.mimes' => 'Las imágenes deben ser de tipo: jpeg, png, jpg, gif, webp',
            'images.*.max' => 'Cada imagen no puede superar los 5MB',
            'documents.max' => 'No se pueden subir más de 30 documentos',
            'documents.*.file' => 'Los archivos deben ser documentos válidos',
            'documents.*.mimes' => 'Los documentos deben ser de tipo: pdf, doc, docx, xls, xlsx, txt',
            'documents.*.max' => 'Cada documento no puede superar los 10MB',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Generar slug si se proporciona un nuevo nombre sin slug
        if ($this->filled('name') && !$this->filled('slug')) {
            $this->merge([
                'slug' => \Str::slug($this->name)
            ]);
        }

        // Convertir campos booleanos
        $booleanFields = [
            'is_renewable', 'is_clean', 'is_active', 'is_featured', 
            'is_public', 'requires_approval'
        ];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => $this->boolean($field)]);
            }
        }

        // Convertir campos numéricos
        $numericFields = [
            'efficiency_min', 'efficiency_max', 'efficiency_typical',
            'capacity_min', 'capacity_max', 'capacity_typical',
            'lifespan_years', 'degradation_rate', 'carbon_footprint_kg_kwh',
            'water_consumption_l_kwh', 'land_use_m2_kw', 'environmental_rating',
            'installation_cost_per_kw', 'maintenance_cost_annual',
            'operational_cost_per_kwh', 'levelized_cost_kwh',
            'payback_period_years', 'capacity_factor_min', 'capacity_factor_max',
            'warranty_years', 'sort_order'
        ];

        foreach ($numericFields as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => (float) $this->input($field)]);
            }
        }

        // Convertir campos de enteros
        $integerFields = ['lifespan_years', 'warranty_years', 'sort_order'];
        foreach ($integerFields as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => (int) $this->input($field)]);
            }
        }
    }
}
