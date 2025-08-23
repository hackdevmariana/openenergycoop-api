<?php

namespace App\Http\Resources\Api\V1\EnergyReading;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergyReadingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reading_number' => $this->reading_number,
            'meter_id' => $this->meter_id,
            'installation_id' => $this->installation_id,
            'consumption_point_id' => $this->consumption_point_id,
            'customer_id' => $this->customer_id,
            'reading_type' => $this->reading_type,
            'reading_type_label' => $this->getFormattedReadingType(),
            'reading_source' => $this->reading_source,
            'reading_source_label' => $this->getFormattedReadingSource(),
            'reading_status' => $this->reading_status,
            'reading_status_label' => $this->getFormattedReadingStatus(),
            'reading_timestamp' => $this->reading_timestamp,
            'reading_timestamp_formatted' => $this->reading_timestamp?->format('Y-m-d H:i:s'),
            'reading_period' => $this->reading_period,
            'reading_value' => $this->reading_value,
            'reading_unit' => $this->reading_unit,
            'previous_reading_value' => $this->previous_reading_value,
            'consumption_value' => $this->consumption_value,
            'consumption_unit' => $this->consumption_unit,
            'demand_value' => $this->demand_value,
            'demand_unit' => $this->demand_unit,
            'power_factor' => $this->power_factor,
            'voltage_value' => $this->voltage_value,
            'voltage_unit' => $this->voltage_unit,
            'current_value' => $this->current_value,
            'current_unit' => $this->current_unit,
            'frequency_value' => $this->frequency_value,
            'frequency_unit' => $this->frequency_unit,
            'temperature' => $this->temperature,
            'temperature_unit' => $this->temperature_unit,
            'humidity' => $this->humidity,
            'humidity_unit' => $this->humidity_unit,
            'quality_score' => $this->quality_score,
            'quality_notes' => $this->quality_notes,
            'validation_notes' => $this->validation_notes,
            'correction_notes' => $this->correction_notes,
            'raw_data' => $this->raw_data,
            'processed_data' => $this->processed_data,
            'alarms' => $this->alarms,
            'events' => $this->events,
            'tags' => $this->tags,
            'read_by' => $this->read_by,
            'validated_by' => $this->validated_by,
            'validated_at' => $this->validated_at,
            'validated_at_formatted' => $this->validated_at?->format('Y-m-d H:i:s'),
            'corrected_by' => $this->corrected_by,
            'corrected_at' => $this->corrected_at,
            'corrected_at_formatted' => $this->corrected_at?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            
            // Campos calculados
            'hour_of_day' => $this->getHourOfDay(),
            'day_of_week' => $this->getDayOfWeek(),
            'season' => $this->getSeason(),
            'consumption_delta' => $this->getConsumptionDelta(),
            'is_valid' => $this->isValid(),
            'is_corrected' => $this->isCorrected(),
            'is_high_quality' => $this->isHighQuality(),
            'age_in_hours' => $this->created_at?->diffInHours(now()),
            'age_in_days' => $this->created_at?->diffInDays(now()),
            
            // Campos formateados
            'reading_value_formatted' => $this->getFormattedReadingValue(),
            'consumption_value_formatted' => $this->getFormattedConsumptionValue(),
            'demand_value_formatted' => $this->getFormattedDemandValue(),
            'power_factor_formatted' => $this->getFormattedPowerFactor(),
            'quality_score_formatted' => $this->getFormattedQualityScore(),
            'temperature_formatted' => $this->getFormattedTemperature(),
            'humidity_formatted' => $this->getFormattedHumidity(),
            
            // Banderas booleanas
            'has_previous_reading' => !is_null($this->previous_reading_value),
            'has_consumption_data' => !is_null($this->consumption_value),
            'has_demand_data' => !is_null($this->demand_value),
            'has_electrical_data' => !is_null($this->power_factor) || !is_null($this->voltage_value) || !is_null($this->current_value) || !is_null($this->frequency_value),
            'has_environmental_data' => !is_null($this->temperature) || !is_null($this->humidity),
            'has_quality_data' => !is_null($this->quality_score),
            'has_validation_data' => !is_null($this->validated_by),
            'has_correction_data' => !is_null($this->corrected_by),
            'has_raw_data' => !empty($this->raw_data),
            'has_processed_data' => !empty($this->processed_data),
            'has_alarms' => !empty($this->alarms),
            'has_events' => !empty($this->events),
            'has_tags' => !empty($this->tags),
            
            // Clases de badge para UI
            'status_badge_class' => $this->getStatusBadgeClass(),
            'type_badge_class' => $this->getTypeBadgeClass(),
            'source_badge_class' => $this->getSourceBadgeClass(),
            'quality_badge_class' => $this->getQualityBadgeClass(),
            
            // Timestamps
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,
            'updated_at_formatted' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relaciones (cargadas condicionalmente)
            'meter' => $this->whenLoaded('meter'),
            'installation' => $this->whenLoaded('installation'),
            'consumption_point' => $this->whenLoaded('consumptionPoint'),
            'customer' => $this->whenLoaded('customer'),
            'read_by_user' => $this->whenLoaded('readBy'),
            'validated_by_user' => $this->whenLoaded('validatedBy'),
            'corrected_by_user' => $this->whenLoaded('correctedBy'),
            'created_by_user' => $this->whenLoaded('createdBy'),
            
            // Conteos de relaciones (cargados condicionalmente)
            'meter_readings_count' => $this->when(isset($this->meter_readings_count), $this->meter_readings_count),
            'installation_readings_count' => $this->when(isset($this->installation_readings_count), $this->installation_readings_count),
            'consumption_point_readings_count' => $this->when(isset($this->consumption_point_readings_count), $this->consumption_point_readings_count),
            'customer_readings_count' => $this->when(isset($this->customer_readings_count), $this->customer_readings_count),
            
            // Permisos del usuario
            'can_edit' => auth()->user()?->can('update', $this->resource) ?? false,
            'can_delete' => auth()->user()?->can('delete', $this->resource) ?? false,
            'can_validate' => auth()->user()?->can('validate', $this->resource) ?? false,
            'can_correct' => auth()->user()?->can('correct', $this->resource) ?? false,
        ];
    }
}
