<?php
trait HasOrganization
{
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
