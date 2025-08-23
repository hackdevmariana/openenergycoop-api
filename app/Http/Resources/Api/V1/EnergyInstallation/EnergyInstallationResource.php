<?php

namespace App\Http\Resources\Api\V1\EnergyInstallation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergyInstallationResource extends JsonResource
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
            'installation_number' => $this->installation_number,
            'name' => $this->name,
            'description' => $this->description,
            'installation_type' => $this->installation_type,
            'installation_type_label' => $this->getFormattedInstallationType(),
            'status' => $this->status,
            'status_label' => $this->getFormattedStatus(),
            'priority' => $this->priority,
            'priority_label' => $this->getFormattedPriority(),
            'energy_source_id' => $this->energy_source_id,
            'customer_id' => $this->customer_id,
            'project_id' => $this->project_id,
            'installed_capacity_kw' => $this->installed_capacity_kw,
            'operational_capacity_kw' => $this->operational_capacity_kw,
            'efficiency_rating' => $this->efficiency_rating,
            'installation_date' => $this->installation_date?->toDateString(),
            'commissioning_date' => $this->commissioning_date?->toDateString(),
            'location_address' => $this->location_address,
            'location_coordinates' => $this->location_coordinates,
            'installation_cost' => $this->installation_cost,
            'maintenance_cost_per_year' => $this->maintenance_cost_per_year,
            'warranty_expiry_date' => $this->warranty_expiry_date?->toDateString(),
            'next_maintenance_date' => $this->next_maintenance_date?->toDateString(),
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'is_public' => $this->is_public,
            'installed_by_id' => $this->installed_by_id,
            'managed_by_id' => $this->managed_by_id,
            'approved_by_id' => $this->approved_by_id,
            'approval_date' => $this->approval_date?->toDateString(),
            'technical_specifications' => $this->technical_specifications,
            'safety_certifications' => $this->safety_certifications,
            'environmental_impact_data' => $this->environmental_impact_data,
            'performance_metrics' => $this->performance_metrics,
            'maintenance_schedule' => $this->maintenance_schedule,
            'emergency_contacts' => $this->emergency_contacts,
            'documentation_files' => $this->documentation_files,
            'tags' => $this->tags,
            'custom_fields' => $this->custom_fields,

            // Campos calculados
            'capacity_utilization_percentage' => $this->getCapacityUtilizationPercentage(),
            'days_since_installation' => $this->getDaysSinceInstallation(),
            'days_until_maintenance' => $this->getDaysUntilMaintenance(),
            'days_until_warranty_expiry' => $this->getDaysUntilWarrantyExpiry(),
            'is_under_warranty' => $this->isUnderWarranty(),
            'is_maintenance_due' => $this->isMaintenanceDue(),
            'is_overdue_maintenance' => $this->isOverdueMaintenance(),

            // Campos formateados
            'formatted_installation_cost' => $this->getFormattedInstallationCost(),
            'formatted_maintenance_cost' => $this->getFormattedMaintenanceCost(),
            'formatted_installed_capacity' => $this->getFormattedInstalledCapacity(),
            'formatted_operational_capacity' => $this->getFormattedOperationalCapacity(),
            'formatted_efficiency' => $this->getFormattedEfficiency(),

            // Campos de estado
            'status_badge_class' => $this->getStatusBadgeClass(),
            'priority_badge_class' => $this->getPriorityBadgeClass(),
            'type_badge_class' => $this->getTypeBadgeClass(),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relaciones cargadas condicionalmente
            'energy_source' => $this->whenLoaded('energySource', function () {
                return [
                    'id' => $this->energySource->id,
                    'name' => $this->energySource->name,
                    'type' => $this->energySource->type,
                    'category' => $this->energySource->category,
                ];
            }),

            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                ];
            }),

            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'status' => $this->project->status,
                ];
            }),

            'installed_by' => $this->whenLoaded('installedBy', function () {
                return [
                    'id' => $this->installedBy->id,
                    'name' => $this->installedBy->name,
                    'email' => $this->installedBy->email,
                ];
            }),

            'managed_by' => $this->whenLoaded('managedBy', function () {
                return [
                    'id' => $this->managedBy->id,
                    'name' => $this->managedBy->name,
                    'email' => $this->managedBy->email,
                ];
            }),

            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),

            'approved_by' => $this->whenLoaded('approvedBy', function () {
                return [
                    'id' => $this->approvedBy->id,
                    'name' => $this->approvedBy->name,
                    'email' => $this->approvedBy->email,
                ];
            }),

            // Conteos relacionados (solo si se solicitan)
            'maintenance_tasks_count' => $this->when($request->has('with_counts'), function () {
                return $this->maintenanceTasks()->count();
            }),

            'energy_productions_count' => $this->when($request->has('with_counts'), function () {
                return $this->energyProductions()->count();
            }),

            'documents_count' => $this->when($request->has('with_counts'), function () {
                return $this->documents()->count();
            }),

            // Relaciones completas (solo si se solicitan)
            'maintenance_tasks' => $this->when($request->has('with_maintenance_tasks'), function () {
                return $this->maintenanceTasks;
            }),

            'energy_productions' => $this->when($request->has('with_energy_productions'), function () {
                return $this->energyProductions;
            }),

            'documents' => $this->when($request->has('with_documents'), function () {
                return $this->documents;
            }),

            // Campos adicionales para UI
            'can_edit' => $this->when(auth()->check(), function () {
                return auth()->user()->can('update', $this->resource);
            }),

            'can_delete' => $this->when(auth()->check(), function () {
                return auth()->user()->can('delete', $this->resource);
            }),

            'can_duplicate' => $this->when(auth()->check(), function () {
                return auth()->user()->can('create', EnergyInstallation::class);
            }),
        ];
    }
}
