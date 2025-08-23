<?php

namespace App\Http\Resources\Api\V1\ConsumptionPoint;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsumptionPointResource extends JsonResource
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
            'point_number' => $this->point_number,
            'name' => $this->name,
            'description' => $this->description,
            'point_type' => $this->point_type,
            'point_type_label' => $this->getFormattedPointType(),
            'status' => $this->status,
            'status_label' => $this->getFormattedStatus(),
            'customer_id' => $this->customer_id,
            'installation_id' => $this->installation_id,
            'location_address' => $this->location_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'peak_demand_kw' => $this->peak_demand_kw,
            'average_demand_kw' => $this->average_demand_kw,
            'annual_consumption_kwh' => $this->annual_consumption_kwh,
            'connection_date' => $this->connection_date?->toDateString(),
            'disconnection_date' => $this->disconnection_date?->toDateString(),
            'meter_number' => $this->meter_number,
            'meter_type' => $this->meter_type,
            'meter_installation_date' => $this->meter_installation_date?->toDateString(),
            'meter_next_calibration_date' => $this->meter_next_calibration_date?->toDateString(),
            'voltage_level' => $this->voltage_level,
            'current_rating' => $this->current_rating,
            'phase_type' => $this->phase_type,
            'connection_type' => $this->connection_type,
            'service_type' => $this->service_type,
            'tariff_type' => $this->tariff_type,
            'billing_frequency' => $this->billing_frequency,
            'is_connected' => $this->is_connected,
            'is_primary' => $this->is_primary,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'managed_by' => $this->managed_by,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            
            // Campos calculados
            'capacity_utilization' => $this->getCapacityUtilization(),
            'days_since_connection' => $this->getDaysSinceConnection(),
            'days_since_disconnection' => $this->getDaysSinceDisconnection(),
            'days_until_calibration' => $this->getDaysUntilCalibration(),
            'demand_factor' => $this->getDemandFactor(),
            'is_operational' => $this->isOperational(),
            'needs_maintenance' => $this->needsMaintenance(),
            'needs_meter_calibration' => $this->needsMeterCalibration(),
            'is_under_warranty' => $this->isUnderWarranty(),
            
            // Campos formateados
            'formatted_peak_demand' => $this->getFormattedPeakDemand(),
            'formatted_average_demand' => $this->getFormattedAverageDemand(),
            'formatted_annual_consumption' => $this->getFormattedAnnualConsumption(),
            'formatted_voltage_level' => $this->getFormattedVoltageLevel(),
            'formatted_current_rating' => $this->getFormattedCurrentRating(),
            'formatted_connection_date' => $this->getFormattedConnectionDate(),
            'formatted_disconnection_date' => $this->getFormattedDisconnectionDate(),
            
            // Campos booleanos para UI
            'is_active' => $this->status === 'active',
            'is_maintenance' => $this->status === 'maintenance',
            'is_disconnected' => $this->status === 'disconnected',
            'is_planned' => $this->status === 'planned',
            'is_decommissioned' => $this->status === 'decommissioned',
            
            // Clases de badges para UI
            'status_badge_class' => $this->getStatusBadgeClass(),
            'point_type_badge_class' => $this->getPointTypeBadgeClass(),
            
            // Relaciones cargadas condicionalmente
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ];
            }),
            'installation' => $this->whenLoaded('installation', function () {
                return [
                    'id' => $this->installation->id,
                    'name' => $this->installation->name,
                    'installation_number' => $this->installation->installation_number,
                    'installation_type' => $this->installation->installation_type,
                ];
            }),
            'managed_by_user' => $this->whenLoaded('managedBy', function () {
                return [
                    'id' => $this->managedBy->id,
                    'name' => $this->managedBy->name,
                    'email' => $this->managedBy->email,
                ];
            }),
            'created_by_user' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
            'approved_by_user' => $this->whenLoaded('approvedBy', function () {
                return [
                    'id' => $this->approvedBy->id,
                    'name' => $this->approvedBy->name,
                    'email' => $this->approvedBy->email,
                ];
            }),
            
            // Contadores de relaciones
            'meters_count' => $this->when(isset($this->meters_count), $this->meters_count),
            'readings_count' => $this->when(isset($this->readings_count), $this->readings_count),
            'forecasts_count' => $this->when(isset($this->forecasts_count), $this->forecasts_count),
            
            // Relaciones completas cuando se solicitan
            'meters' => $this->whenLoaded('meters', function () {
                return $this->meters->map(function ($meter) {
                    return [
                        'id' => $meter->id,
                        'meter_number' => $meter->meter_number,
                        'meter_type' => $meter->meter_type,
                        'status' => $meter->status,
                    ];
                });
            }),
            'readings' => $this->whenLoaded('readings', function () {
                return $this->readings->map(function ($reading) {
                    return [
                        'id' => $reading->id,
                        'reading_date' => $reading->reading_date?->toDateString(),
                        'consumption_kwh' => $reading->consumption_kwh,
                        'demand_kw' => $reading->demand_kw,
                    ];
                });
            }),
            'forecasts' => $this->whenLoaded('forecasts', function () {
                return $this->forecasts->map(function ($forecast) {
                    return [
                        'id' => $forecast->id,
                        'forecast_date' => $forecast->forecast_date?->toDateString(),
                        'predicted_consumption_kwh' => $forecast->predicted_consumption_kwh,
                        'confidence_level' => $forecast->confidence_level,
                    ];
                });
            }),
            
            // Permisos del usuario autenticado
            'can_edit' => auth()->user()?->can('update', $this->resource) ?? false,
            'can_delete' => auth()->user()?->can('delete', $this->resource) ?? false,
            'can_duplicate' => auth()->user()?->can('create', ConsumptionPoint::class) ?? false,
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
