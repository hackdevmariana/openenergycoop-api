<?php

namespace App\Http\Resources\Api\V1\ProductionProject;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductionProjectCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'types' => [
                    'solar_farm' => $this->collection->where('project_type', 'solar_farm')->count(),
                    'wind_farm' => $this->collection->where('project_type', 'wind_farm')->count(),
                    'hydroelectric' => $this->collection->where('project_type', 'hydroelectric')->count(),
                    'biomass' => $this->collection->where('project_type', 'biomass')->count(),
                    'geothermal' => $this->collection->where('project_type', 'geothermal')->count(),
                    'hybrid' => $this->collection->where('project_type', 'hybrid')->count(),
                    'storage' => $this->collection->where('project_type', 'storage')->count(),
                    'grid_upgrade' => $this->collection->where('project_type', 'grid_upgrade')->count(),
                    'other' => $this->collection->where('project_type', 'other')->count(),
                ],
                'statuses' => [
                    'planning' => $this->collection->where('status', 'planning')->count(),
                    'approved' => $this->collection->where('status', 'approved')->count(),
                    'in_progress' => $this->collection->where('status', 'in_progress')->count(),
                    'on_hold' => $this->collection->where('status', 'on_hold')->count(),
                    'completed' => $this->collection->where('status', 'completed')->count(),
                    'cancelled' => $this->collection->where('status', 'cancelled')->count(),
                    'maintenance' => $this->collection->where('status', 'maintenance')->count(),
                ],
                'active_count' => $this->collection->where('is_active', true)->count(),
                'public_count' => $this->collection->where('is_public', true)->count(),
                'crowdfunding_count' => $this->collection->where('accepts_crowdfunding', true)->count(),
                'regulatory_approved_count' => $this->collection->where('regulatory_approved', true)->count(),
                'total_capacity_kw' => $this->collection->sum('capacity_kw'),
                'total_investment' => $this->collection->sum('total_investment'),
                'average_completion' => round($this->collection->avg('completion_percentage'), 1),
                'average_efficiency' => round($this->collection->avg('efficiency_rating'), 1),
            ],
        ];
    }
}
