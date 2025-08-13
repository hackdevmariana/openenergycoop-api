<?php

namespace App\Traits;

use App\Models\Organization;

trait HasOrganization
{
    /**
     * Get the organization that owns the model.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope a query to only include records for a specific organization.
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope a query to only include records for the current user's organization.
     */
    public function scopeForCurrentOrganization($query)
    {
        if (auth()->check() && auth()->user()->organization_id) {
            return $query->where('organization_id', auth()->user()->organization_id);
        }
        return $query;
    }

    /**
     * Check if the model belongs to a specific organization.
     */
    public function belongsToOrganization($organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }
}
