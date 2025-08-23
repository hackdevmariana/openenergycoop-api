<?php

namespace App\Http\Resources\Api\V1\EnergyMeter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergyMeterResource extends JsonResource
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
            'meter_number' => $this->meter_number,
            'name' => $this->name,
            'description' => $this->description,
            'meter_type' => $this->meter_type,
            'meter_type_label' => $this->getFormattedMeterType(),
            'status' => $this->status,
            'status_label' => $this->getFormattedStatus(),
            'meter_category' => $this->meter_category,
            'meter_category_label' => $this->getFormattedMeterCategory(),
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'installation_id' => $this->installation_id,
            'consumption_point_id' => $this->consumption_point_id,
            'customer_id' => $this->customer_id,
            'installation_date' => $this->installation_date,
            'installation_date_formatted' => $this->getFormattedInstallationDate(),
            'commissioning_date' => $this->commissioning_date,
            'commissioning_date_formatted' => $this->getFormattedCommissioningDate(),
            'next_calibration_date' => $this->next_calibration_date,
            'next_calibration_date_formatted' => $this->getFormattedNextCalibrationDate(),
            'voltage_rating' => $this->voltage_rating,
            'voltage_rating_formatted' => $this->getFormattedVoltageRating(),
            'current_rating' => $this->current_rating,
            'current_rating_formatted' => $this->getFormattedCurrentRating(),
            'accuracy_class' => $this->accuracy_class,
            'measurement_range_min' => $this->measurement_range_min,
            'measurement_range_max' => $this->measurement_range_max,
            'is_smart_meter' => $this->is_smart_meter,
            'has_remote_reading' => $this->has_remote_reading,
            'has_two_way_communication' => $this->has_two_way_communication,
            'communication_protocol' => $this->communication_protocol,
            'firmware_version' => $this->firmware_version,
            'hardware_version' => $this->hardware_version,
            'warranty_expiry_date' => $this->warranty_expiry_date,
            'warranty_expiry_date_formatted' => $this->getFormattedWarrantyExpiryDate(),
            'last_maintenance_date' => $this->last_maintenance_date,
            'last_maintenance_date_formatted' => $this->getFormattedLastMaintenanceDate(),
            'next_maintenance_date' => $this->next_maintenance_date,
            'next_maintenance_date_formatted' => $this->getFormattedNextMaintenanceDate(),
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'managed_by' => $this->managed_by,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'approved_at_formatted' => $this->approved_at ? $this->approved_at->format('d/m/Y H:i') : null,

            // Campos calculados
            'age_in_years' => $this->getAgeInYears(),
            'days_until_calibration' => $this->getDaysUntilCalibration(),
            'days_until_warranty_expiry' => $this->getDaysUntilWarrantyExpiry(),
            'days_until_maintenance' => $this->getDaysUntilMaintenance(),
            'is_under_warranty' => $this->isUnderWarranty(),
            'needs_calibration' => $this->needsCalibration(),
            'needs_maintenance' => $this->needsMaintenance(),
            'is_commissioned' => $this->isCommissioned(),
            'measurement_range' => $this->getMeasurementRange(),

            // Campos de estado
            'is_active' => $this->isActive(),
            'is_operational' => $this->isOperational(),
            'is_smart_meter_badge' => $this->getSmartMeterBadgeClass(),
            'status_badge' => $this->getStatusBadgeClass(),
            'meter_type_badge' => $this->getMeterTypeBadgeClass(),
            'meter_category_badge' => $this->getMeterCategoryBadgeClass(),
            'accuracy_badge' => $this->getAccuracyBadgeClass(),
            'remote_reading_badge' => $this->getRemoteReadingBadgeClass(),
            'two_way_communication_badge' => $this->getTwoWayCommunicationBadgeClass(),

            // Timestamps
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
            'updated_at' => $this->updated_at,
            'updated_at_formatted' => $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : null,

            // Relaciones condicionales
            'installation' => $this->whenLoaded('installation', function () {
                return [
                    'id' => $this->installation->id,
                    'name' => $this->installation->name,
                    'installation_number' => $this->installation->installation_number,
                ];
            }),

            'consumption_point' => $this->whenLoaded('consumptionPoint', function () {
                return [
                    'id' => $this->consumptionPoint->id,
                    'name' => $this->consumptionPoint->name,
                    'point_number' => $this->consumptionPoint->point_number,
                ];
            }),

            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'customer_number' => $this->customer->customer_number,
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

            // Conteos condicionales
            'readings_count' => $this->when(isset($this->readings_count), $this->readings_count),
            'forecasts_count' => $this->when(isset($this->forecasts_count), $this->forecasts_count),

            // Relaciones completas (solo cuando se solicitan explícitamente)
            'readings' => $this->when($request->has('include') && in_array('readings', explode(',', $request->include)), $this->readings),
            'forecasts' => $this->when($request->has('include') && in_array('forecasts', explode(',', $request->include)), $this->forecasts),

            // Permisos del usuario
            'can_edit' => auth()->user()?->can('update', $this->resource) ?? false,
            'can_delete' => auth()->user()?->can('delete', $this->resource) ?? false,
            'can_duplicate' => auth()->user()?->can('create', EnergyMeter::class) ?? false,
        ];
    }
}
