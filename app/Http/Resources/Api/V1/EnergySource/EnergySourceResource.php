<?php

namespace App\Http\Resources\Api\V1\EnergySource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergySourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'category_label' => $this->getFormattedEnergyCategory(),
            'type' => $this->type,
            'type_label' => $this->getFormattedSourceType(),
            'status' => $this->status,
            'status_label' => $this->getFormattedStatus(),
            
            // Especificaciones técnicas
            'technical_specs' => $this->technical_specs,
            'efficiency_min' => $this->efficiency_min,
            'efficiency_max' => $this->efficiency_max,
            'efficiency_typical' => $this->efficiency_typical,
            'capacity_min' => $this->capacity_min,
            'capacity_max' => $this->capacity_max,
            'capacity_typical' => $this->capacity_typical,
            'lifespan_years' => $this->lifespan_years,
            'degradation_rate' => $this->degradation_rate,
            
            // Impacto ambiental
            'carbon_footprint_kg_kwh' => $this->carbon_footprint_kg_kwh,
            'water_consumption_l_kwh' => $this->water_consumption_l_kwh,
            'land_use_m2_kw' => $this->land_use_m2_kw,
            'environmental_impact' => $this->environmental_impact,
            'is_renewable' => $this->is_renewable,
            'is_clean' => $this->is_clean,
            'renewable_certificate' => $this->renewable_certificate,
            'environmental_rating' => $this->environmental_rating,
            
            // Aspectos financieros
            'installation_cost_per_kw' => $this->installation_cost_per_kw,
            'maintenance_cost_annual' => $this->maintenance_cost_annual,
            'operational_cost_per_kwh' => $this->operational_cost_per_kwh,
            'levelized_cost_kwh' => $this->levelized_cost_kwh,
            'payback_period_years' => $this->payback_period_years,
            'financial_notes' => $this->financial_notes,
            
            // Disponibilidad y dependencias
            'geographic_availability' => $this->geographic_availability,
            'weather_dependencies' => $this->weather_dependencies,
            'seasonal_variations' => $this->seasonal_variations,
            'capacity_factor_min' => $this->capacity_factor_min,
            'capacity_factor_max' => $this->capacity_factor_max,
            
            // Tecnología y equipamiento
            'technology_description' => $this->technology_description,
            'manufacturer' => $this->manufacturer,
            'model_series' => $this->model_series,
            'warranty_years' => $this->warranty_years,
            'certification_standards' => $this->certification_standards,
            'maintenance_requirements' => $this->maintenance_requirements,
            
            // Configuración del sistema
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'is_public' => $this->is_public,
            'requires_approval' => $this->requires_approval,
            'icon' => $this->icon,
            'color' => $this->color,
            'sort_order' => $this->sort_order,
            
            // Metadatos y etiquetas
            'tags' => $this->tags,
            'notes' => $this->notes,
            
            // Campos calculados
            'efficiency_range' => $this->when($this->efficiency_min && $this->efficiency_max, [
                'min' => $this->efficiency_min,
                'max' => $this->efficiency_max,
                'average' => round(($this->efficiency_min + $this->efficiency_max) / 2, 1)
            ]),
            'capacity_range' => $this->when($this->capacity_min && $this->capacity_max, [
                'min' => $this->capacity_min,
                'max' => $this->capacity_max,
                'average' => round(($this->capacity_min + $this->capacity_max) / 2, 1)
            ]),
            'capacity_factor_range' => $this->when($this->capacity_factor_min && $this->capacity_factor_max, [
                'min' => $this->capacity_factor_min,
                'max' => $this->capacity_factor_max,
                'average' => round(($this->capacity_factor_min + $this->capacity_factor_max) / 2, 1)
            ]),
            
            // Campos formateados
            'formatted_efficiency_typical' => $this->when($this->efficiency_typical, $this->efficiency_typical . '%'),
            'formatted_capacity_typical' => $this->when($this->capacity_typical, $this->capacity_typical . ' kW'),
            'formatted_lifespan_years' => $this->when($this->lifespan_years, $this->lifespan_years . ' años'),
            'formatted_degradation_rate' => $this->when($this->degradation_rate, $this->degradation_rate . '%/año'),
            'formatted_carbon_footprint' => $this->when($this->carbon_footprint_kg_kwh, $this->carbon_footprint_kg_kwh . ' kg CO2/kWh'),
            'formatted_water_consumption' => $this->when($this->water_consumption_l_kwh, $this->water_consumption_l_kwh . ' L/kWh'),
            'formatted_land_use' => $this->when($this->land_use_m2_kw, $this->land_use_m2_kw . ' m²/kW'),
            'formatted_environmental_rating' => $this->when($this->environmental_rating, $this->environmental_rating . '/100'),
            'formatted_installation_cost' => $this->when($this->installation_cost_per_kw, '$' . number_format($this->installation_cost_per_kw, 2) . '/kW'),
            'formatted_maintenance_cost' => $this->when($this->maintenance_cost_annual, '$' . number_format($this->maintenance_cost_annual, 2) . '/kW/año'),
            'formatted_operational_cost' => $this->when($this->operational_cost_per_kwh, '$' . number_format($this->operational_cost_per_kwh, 4) . '/kWh'),
            'formatted_levelized_cost' => $this->when($this->levelized_cost_kwh, '$' . number_format($this->levelized_cost_kwh, 4) . '/kWh'),
            'formatted_payback_period' => $this->when($this->payback_period_years, $this->payback_period_years . ' años'),
            'formatted_warranty_years' => $this->when($this->warranty_years, $this->warranty_years . ' años'),
            
            // Estados y flags
            'is_operational' => $this->isActive(),
            'is_under_maintenance' => $this->isMaintenance(),
            'is_planned' => $this->isPlanned(),
            'is_under_construction' => $this->isUnderConstruction(),
            'is_decommissioned' => $this->isDecommissioned(),
            
            // Clases de badges para UI
            'status_badge_class' => $this->getStatusBadgeClass(),
            'source_type_badge_class' => $this->getSourceTypeBadgeClass(),
            'energy_category_badge_class' => $this->getEnergyCategoryBadgeClass(),
            'efficiency_badge_class' => $this->getEfficiencyBadgeClass(),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Relaciones (cargadas cuando sea necesario)
            'installations_count' => $this->whenLoaded('installations', function () {
                return $this->installations->count();
            }),
            'meters_count' => $this->whenLoaded('meters', function () {
                return $this->meters->count();
            }),
            'readings_count' => $this->whenLoaded('readings', function () {
                return $this->readings->count();
            }),
            'forecasts_count' => $this->whenLoaded('forecasts', function () {
                return $this->forecasts->count();
            }),
            'production_projects_count' => $this->whenLoaded('productionProjects', function () {
                return $this->productionProjects->count();
            }),
            
            // Relaciones completas (cuando se soliciten)
            'installations' => $this->whenLoaded('installations'),
            'meters' => $this->whenLoaded('meters'),
            'readings' => $this->whenLoaded('readings'),
            'forecasts' => $this->whenLoaded('forecasts'),
            'production_projects' => $this->whenLoaded('productionProjects'),
        ];
    }
}
