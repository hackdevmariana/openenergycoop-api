<?php

namespace App\Http\Resources\Api\V1\ProductionProject;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionProjectResource extends JsonResource
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
            
            // Tipos y estados
            'project_type' => $this->project_type,
            'project_type_label' => $this->getFormattedProjectType(),
            'technology_type' => $this->technology_type,
            'technology_type_label' => $this->getTechnologyTypeLabel(),
            'status' => $this->status,
            'status_label' => $this->getFormattedStatus(),
            'priority' => $this->priority,
            'priority_label' => $this->getFormattedPriority(),
            
            // Relaciones principales
            'organization_id' => $this->organization_id,
            'owner_user_id' => $this->owner_user_id,
            'energy_source_id' => $this->energy_source_id,
            'created_by' => $this->created_by,
            
            // Capacidad y rendimiento
            'capacity_kw' => $this->capacity_kw,
            'estimated_annual_production' => $this->estimated_annual_production,
            'efficiency_rating' => $this->efficiency_rating,
            'peak_power_kw' => $this->peak_power_kw,
            'capacity_factor' => $this->capacity_factor,
            
            // Especificaciones técnicas
            'technical_specifications' => $this->technical_specifications,
            'equipment_details' => $this->equipment_details,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            
            // Ubicación
            'location_address' => $this->location_address,
            'location_city' => $this->location_city,
            'location_region' => $this->location_region,
            'location_country' => $this->location_country,
            'location_postal_code' => $this->location_postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_metadata' => $this->location_metadata,
            
            // Cronograma
            'planning_start_date' => $this->planning_start_date?->toISOString(),
            'construction_start_date' => $this->construction_start_date?->toISOString(),
            'construction_end_date' => $this->construction_end_date?->toISOString(),
            'operational_start_date' => $this->operational_start_date?->toISOString(),
            'expected_end_date' => $this->expected_end_date?->toISOString(),
            'completion_percentage' => $this->completion_percentage,
            'estimated_duration_months' => $this->estimated_duration_months,
            
            // Aspectos financieros
            'total_investment' => $this->total_investment,
            'cost_per_kw' => $this->cost_per_kw,
            'estimated_roi_percentage' => $this->estimated_roi_percentage,
            'payback_period_years' => $this->payback_period_years,
            'annual_operating_cost' => $this->annual_operating_cost,
            
            // Crowdfunding e inversión
            'accepts_crowdfunding' => $this->accepts_crowdfunding,
            'is_investment_ready' => $this->is_investment_ready,
            'crowdfunding_target' => $this->crowdfunding_target,
            'crowdfunding_raised' => $this->crowdfunding_raised,
            'min_investment' => $this->min_investment,
            'max_investment' => $this->max_investment,
            'investment_terms' => $this->investment_terms,
            
            // Impacto ambiental
            'co2_avoided_tons_year' => $this->co2_avoided_tons_year,
            'renewable_percentage' => $this->renewable_percentage,
            'environmental_score' => $this->environmental_score,
            'environmental_certifications' => $this->environmental_certifications,
            'sustainability_metrics' => $this->sustainability_metrics,
            
            // Permisos y regulaciones
            'regulatory_approved' => $this->regulatory_approved,
            'permits_complete' => $this->permits_complete,
            'regulatory_approval_date' => $this->regulatory_approval_date?->toISOString(),
            'regulatory_authority' => $this->regulatory_authority,
            'permits_required' => $this->permits_required,
            'permits_obtained' => $this->permits_obtained,
            
            // Mantenimiento
            'annual_maintenance_cost' => $this->annual_maintenance_cost,
            'maintenance_interval_months' => $this->maintenance_interval_months,
            'maintenance_provider' => $this->maintenance_provider,
            'last_maintenance_date' => $this->last_maintenance_date?->toISOString(),
            'next_maintenance_date' => $this->next_maintenance_date?->toISOString(),
            'maintenance_requirements' => $this->maintenance_requirements,
            
            // Configuración del sistema
            'is_public' => $this->is_public,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'requires_approval' => $this->requires_approval,
            
            // Metadatos y etiquetas
            'tags' => $this->tags,
            'notes' => $this->notes,
            
            // Documentos y archivos
            'images' => $this->images,
            'documents' => $this->documents,
            
            // Campos calculados
            'days_until_completion' => $this->getDaysUntilCompletion(),
            'days_overdue' => $this->getDaysOverdue(),
            'project_duration' => $this->getProjectDuration(),
            'progress_percentage' => $this->getProgressPercentage(),
            'capacity_progress_percentage' => $this->getCapacityProgressPercentage(),
            'is_overdue' => $this->isOverdue(),
            'is_high_priority' => $this->isHighPriority(),
            'is_approved' => $this->isApproved(),
            
            // Producción de energía
            'total_generated_kwh' => $this->getTotalGeneratedKwh(),
            'daily_production' => $this->getDailyProduction(),
            'monthly_production' => $this->getMonthlyProduction(),
            'yearly_production' => $this->getYearlyProduction(),
            
            // Formateo de campos
            'formatted_capacity' => $this->getFormattedCapacity(),
            'formatted_total_investment' => $this->getFormattedTotalInvestment(),
            'formatted_annual_operating_cost' => $this->getFormattedAnnualOperatingCost(),
            'formatted_crowdfunding_target' => $this->getFormattedCrowdfundingTarget(),
            'formatted_crowdfunding_raised' => $this->getFormattedCrowdfundingRaised(),
            'formatted_min_investment' => $this->getFormattedMinInvestment(),
            'formatted_max_investment' => $this->getFormattedMaxInvestment(),
            'formatted_planning_start_date' => $this->getFormattedPlanningStartDate(),
            'formatted_construction_start_date' => $this->getFormattedConstructionStartDate(),
            'formatted_expected_end_date' => $this->getFormattedExpectedEndDate(),
            'formatted_completion_percentage' => $this->getFormattedCompletionPercentage(),
            'formatted_days_until_completion' => $this->getFormattedDaysUntilCompletion(),
            
            // Relaciones cargadas
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                    'slug' => $this->organization->slug,
                    'type' => $this->organization->type ?? null,
                ];
            }),
            
            'owner_user' => $this->whenLoaded('ownerUser', function () {
                return [
                    'id' => $this->ownerUser->id,
                    'name' => $this->ownerUser->name,
                    'email' => $this->ownerUser->email,
                ];
            }),
            
            'energy_source' => $this->whenLoaded('energySource', function () {
                return [
                    'id' => $this->energySource->id,
                    'name' => $this->energySource->name,
                    'type' => $this->energySource->type ?? null,
                ];
            }),
            
            'created_by_user' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
            
            'installations' => $this->whenLoaded('installations', function () {
                return $this->installations->map(function ($installation) {
                    return [
                        'id' => $installation->id,
                        'name' => $installation->name,
                        'type' => $installation->type ?? null,
                        'capacity_kw' => $installation->capacity_kw,
                    ];
                });
            }),
            
            'meters' => $this->whenLoaded('meters', function () {
                return $this->meters->map(function ($meter) {
                    return [
                        'id' => $meter->id,
                        'serial_number' => $meter->serial_number,
                        'type' => $meter->type ?? null,
                        'is_active' => $meter->is_active,
                    ];
                });
            }),
            
            'readings' => $this->whenLoaded('readings', function () {
                return $this->readings->map(function ($reading) {
                    return [
                        'id' => $reading->id,
                        'timestamp' => $reading->timestamp?->toISOString(),
                        'type' => $reading->type ?? null,
                        'delta_kwh' => $reading->delta_kwh,
                        'total_kwh' => $reading->total_kwh,
                    ];
                });
            }),
            
            'milestones' => $this->whenLoaded('milestones', function () {
                return $this->milestones->map(function ($milestone) {
                    return [
                        'id' => $milestone->id,
                        'title' => $milestone->title,
                        'description' => $milestone->description,
                        'due_date' => $milestone->due_date?->toISOString(),
                        'status' => $milestone->status ?? null,
                        'is_completed' => $milestone->is_completed,
                    ];
                });
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
