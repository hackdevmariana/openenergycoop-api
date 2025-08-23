<?php

namespace App\Http\Resources\Api\V1\EnergyPool;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergyPoolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pool_number' => $this->pool_number,
            'name' => $this->name,
            'description' => $this->description,
            'pool_type' => $this->pool_type,
            'pool_type_label' => $this->getFormattedPoolType(),
            'status' => $this->status,
            'status_label' => $this->getFormattedStatus(),
            'energy_category' => $this->energy_category,
            'energy_category_label' => $this->getFormattedEnergyCategory(),
            'total_capacity_mw' => $this->total_capacity_mw,
            'available_capacity_mw' => $this->available_capacity_mw,
            'reserved_capacity_mw' => $this->reserved_capacity_mw,
            'utilized_capacity_mw' => $this->utilized_capacity_mw,
            'efficiency_rating' => $this->efficiency_rating,
            'availability_factor' => $this->availability_factor,
            'capacity_factor' => $this->capacity_factor,
            'annual_production_mwh' => $this->annual_production_mwh,
            'monthly_production_mwh' => $this->monthly_production_mwh,
            'daily_production_mwh' => $this->daily_production_mwh,
            'hourly_production_mwh' => $this->hourly_production_mwh,
            'location_address' => $this->location_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'region' => $this->region,
            'country' => $this->country,
            'commissioning_date' => $this->commissioning_date,
            'decommissioning_date' => $this->decommissioning_date,
            'expected_lifespan_years' => $this->expected_lifespan_years,
            'construction_cost' => $this->construction_cost,
            'operational_cost_per_mwh' => $this->operational_cost_per_mwh,
            'maintenance_cost_per_mwh' => $this->maintenance_cost_per_mwh,
            'technical_specifications' => $this->technical_specifications,
            'environmental_impact' => $this->environmental_impact,
            'regulatory_compliance' => $this->regulatory_compliance,
            'safety_features' => $this->safety_features,
            'pool_members' => $this->pool_members,
            'pool_operators' => $this->pool_operators,
            'pool_governance' => $this->pool_governance,
            'trading_rules' => $this->trading_rules,
            'settlement_procedures' => $this->settlement_procedures,
            'risk_management' => $this->risk_management,
            'performance_metrics' => $this->performance_metrics,
            'environmental_data' => $this->environmental_data,
            'regulatory_documents' => $this->regulatory_documents,
            'tags' => $this->tags,
            'managed_by' => $this->managed_by,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'notes' => $this->notes,
            
            // Campos calculados
            'utilization_percentage' => $this->getUtilizationPercentage(),
            'reservation_percentage' => $this->getReservationPercentage(),
            'available_percentage' => $this->getAvailablePercentage(),
            'age_in_years' => $this->getAgeInYears(),
            'remaining_lifespan' => $this->getRemainingLifespan(),
            'total_annual_cost' => $this->getTotalAnnualCost(),
            'cost_per_mwh' => $this->getCostPerMwh(),
            'is_active' => $this->isActive(),
            'is_approved' => $this->isApproved(),
            'is_commissioned' => $this->isCommissioned(),
            'has_available_capacity' => $this->hasAvailableCapacity(),
            'is_fully_utilized' => $this->isFullyUtilized(),
            'is_high_efficiency' => $this->isHighEfficiency(),
            'is_high_availability' => $this->isHighAvailability(),
            'is_high_capacity_factor' => $this->isHighCapacityFactor(),
            
            // Campos formateados
            'total_capacity_formatted' => $this->getFormattedTotalCapacity(),
            'available_capacity_formatted' => $this->getFormattedAvailableCapacity(),
            'reserved_capacity_formatted' => $this->getFormattedReservedCapacity(),
            'utilized_capacity_formatted' => $this->getFormattedUtilizedCapacity(),
            'efficiency_rating_formatted' => $this->getFormattedEfficiencyRating(),
            'availability_factor_formatted' => $this->getFormattedAvailabilityFactor(),
            'capacity_factor_formatted' => $this->getFormattedCapacityFactor(),
            'annual_production_formatted' => $this->getFormattedAnnualProduction(),
            'monthly_production_formatted' => $this->getFormattedMonthlyProduction(),
            'daily_production_formatted' => $this->getFormattedDailyProduction(),
            'hourly_production_formatted' => $this->getFormattedHourlyProduction(),
            'construction_cost_formatted' => $this->getFormattedConstructionCost(),
            'operational_cost_formatted' => $this->getFormattedOperationalCost(),
            'maintenance_cost_formatted' => $this->getFormattedMaintenanceCost(),
            'commissioning_date_formatted' => $this->getFormattedCommissioningDate(),
            'decommissioning_date_formatted' => $this->getFormattedDecommissioningDate(),
            'utilization_percentage_formatted' => $this->getFormattedUtilizationPercentage(),
            'reservation_percentage_formatted' => $this->getFormattedReservationPercentage(),
            'available_percentage_formatted' => $this->getFormattedAvailablePercentage(),
            'age_in_years_formatted' => $this->getFormattedAgeInYears(),
            'remaining_lifespan_formatted' => $this->getFormattedRemainingLifespan(),
            'total_annual_cost_formatted' => $this->getFormattedTotalAnnualCost(),
            'cost_per_mwh_formatted' => $this->getFormattedCostPerMwh(),
            
            // Banderas booleanas
            'has_location_data' => !is_null($this->latitude) && !is_null($this->longitude),
            'has_capacity_data' => !is_null($this->total_capacity_mw),
            'has_production_data' => !is_null($this->annual_production_mwh) || !is_null($this->monthly_production_mwh) || !is_null($this->daily_production_mwh) || !is_null($this->hourly_production_mwh),
            'has_cost_data' => !is_null($this->construction_cost) || !is_null($this->operational_cost_per_mwh) || !is_null($this->maintenance_cost_per_mwh),
            'has_technical_data' => !empty($this->technical_specifications),
            'has_environmental_data' => !empty($this->environmental_impact) || !empty($this->environmental_data),
            'has_regulatory_data' => !empty($this->regulatory_compliance) || !empty($this->regulatory_documents),
            'has_safety_data' => !empty($this->safety_features),
            'has_pool_data' => !empty($this->pool_members) || !empty($this->pool_operators) || !empty($this->pool_governance),
            'has_trading_data' => !empty($this->trading_rules) || !empty($this->settlement_procedures),
            'has_risk_data' => !empty($this->risk_management),
            'has_performance_data' => !empty($this->performance_metrics),
            'has_tags' => !empty($this->tags),
            
            // Clases de badge para UI
            'status_badge_class' => $this->getStatusBadgeClass(),
            'pool_type_badge_class' => $this->getPoolTypeBadgeClass(),
            'energy_category_badge_class' => $this->getEnergyCategoryBadgeClass(),
            'efficiency_badge_class' => $this->getEfficiencyBadgeClass(),
            'availability_badge_class' => $this->getAvailabilityBadgeClass(),
            'capacity_factor_badge_class' => $this->getCapacityFactorBadgeClass(),
            
            // Timestamps
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
            'updated_at_formatted' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relaciones (cargadas condicionalmente)
            'managed_by_user' => $this->whenLoaded('managedBy'),
            'created_by_user' => $this->whenLoaded('createdBy'),
            'approved_by_user' => $this->whenLoaded('approvedBy'),
            'forecasts' => $this->whenLoaded('forecasts'),
            'trading_orders' => $this->whenLoaded('tradingOrders'),
            'transfers' => $this->whenLoaded('transfers'),
            
            // Conteos de relaciones (cargados condicionalmente)
            'forecasts_count' => $this->when(isset($this->forecasts_count), $this->forecasts_count),
            'trading_orders_count' => $this->when(isset($this->trading_orders_count), $this->trading_orders_count),
            'transfers_count' => $this->when(isset($this->transfers_count), $this->transfers_count),
            
            // Permisos del usuario
            'can_edit' => auth()->user()?->can('update', $this->resource) ?? false,
            'can_delete' => auth()->user()?->can('delete', $this->resource) ?? false,
            'can_approve' => auth()->user()?->can('approve', $this->resource) ?? false,
            'can_duplicate' => auth()->user()?->can('create', $this->resource) ?? false,
        ];
    }
}
