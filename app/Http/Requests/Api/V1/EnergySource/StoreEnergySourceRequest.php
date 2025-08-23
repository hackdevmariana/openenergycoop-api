<?php

namespace App\Http\Requests\Api\V1\EnergySource;

use App\Models\EnergySource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnergySourceRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:energy_sources,slug',
            'description' => 'nullable|string|max:65535',
            'category' => ['required', 'string', Rule::in(array_keys(EnergySource::getCategories()))],
            'type' => ['required', 'string', Rule::in(array_keys(EnergySource::getTypes()))],
            'status' => ['required', 'string', Rule::in(array_keys(EnergySource::getStatuses()))],
            
            // Especificaciones técnicas
            'technical_specs' => 'nullable|string',
            'efficiency_min' => 'nullable|numeric|min:0|max:100',
            'efficiency_max' => 'nullable|numeric|min:0|max:100',
            'efficiency_typical' => 'nullable|numeric|min:0|max:100',
            'capacity_min' => 'nullable|numeric|min:0',
            'capacity_max' => 'nullable|numeric|min:0',
            'capacity_typical' => 'nullable|numeric|min:0',
            'lifespan_years' => 'nullable|integer|min:1',
            'degradation_rate' => 'nullable|numeric|min:0|max:10',
            
            // Impacto ambiental
            'carbon_footprint_kg_kwh' => 'nullable|numeric|min:0',
            'water_consumption_l_kwh' => 'nullable|numeric|min:0',
            'land_use_m2_kw' => 'nullable|numeric|min:0',
            'environmental_impact' => 'nullable|string',
            'is_renewable' => 'boolean',
            'is_clean' => 'boolean',
            'renewable_certificate' => 'nullable|string|max:255',
            'environmental_rating' => 'nullable|numeric|min:0|max:100',
            
            // Aspectos financieros
            'installation_cost_per_kw' => 'nullable|numeric|min:0',
            'maintenance_cost_annual' => 'nullable|numeric|min:0',
            'operational_cost_per_kwh' => 'nullable|numeric|min:0',
            'levelized_cost_kwh' => 'nullable|numeric|min:0',
            'payback_period_years' => 'nullable|numeric|min:0',
            'financial_notes' => 'nullable|string',
            
            // Disponibilidad y dependencias
            'geographic_availability' => 'nullable|string',
            'weather_dependencies' => 'nullable|string',
            'seasonal_variations' => 'nullable|string',
            'capacity_factor_min' => 'nullable|numeric|min:0|max:100',
            'capacity_factor_max' => 'nullable|numeric|min:0|max:100',
            
            // Tecnología y equipamiento
            'technology_description' => 'nullable|string',
            'manufacturer' => 'nullable|string|max:255',
            'model_series' => 'nullable|string|max:255',
            'warranty_years' => 'nullable|numeric|min:0',
            'certification_standards' => 'nullable|string|max:255',
            'maintenance_requirements' => 'nullable|string',
            
            // Configuración del sistema
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
            'requires_approval' => 'boolean',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            
            // Metadatos y etiquetas
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'notes' => 'nullable|string',
            
            // Documentos y archivos
            'images' => 'nullable|array|max:15',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'documents' => 'nullable|array|max:30',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,txt|max:10240',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El campo nombre es obligatorio',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
            'slug.unique' => 'El slug ya está en uso',
            'category.required' => 'El campo categoría es obligatorio',
            'category.in' => 'La categoría seleccionada no es válida',
            'type.required' => 'El campo tipo es obligatorio',
            'type.in' => 'El tipo seleccionado no es válido',
            'status.required' => 'El campo estado es obligatorio',
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
        // Generar slug si no se proporciona
        if (!$this->filled('slug') && $this->filled('name')) {
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

        // Establecer valores por defecto
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'is_featured' => $this->boolean('is_featured', false),
            'is_public' => $this->boolean('is_public', true),
            'requires_approval' => $this->boolean('requires_approval', false),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
