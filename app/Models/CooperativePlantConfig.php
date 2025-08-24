<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CooperativePlantConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperative_id',
        'plant_id',
        'default',
        'active',
        'organization_id'
    ];

    protected $casts = [
        'default' => 'boolean',
        'active' => 'boolean',
    ];

    // Relaciones
    public function cooperative()
    {
        return $this->belongsTo(EnergyCooperative::class, 'cooperative_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('default', true);
    }

    public function scopeByCooperative($query, $cooperativeId)
    {
        return $query->where('cooperative_id', $cooperativeId);
    }

    public function scopeByPlant($query, $plantId)
    {
        return $query->where('plant_id', $plantId);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // Accessors
    public function getIsDefaultAttribute()
    {
        return $this->default;
    }

    public function getIsActiveAttribute()
    {
        return $this->active;
    }

    // Métodos
    public function setAsDefault()
    {
        // Primero, quitar el default de otras configuraciones de la misma cooperativa
        static::where('cooperative_id', $this->cooperative_id)
              ->where('id', '!=', $this->id)
              ->update(['default' => false]);
        
        $this->update(['default' => true]);
        
        return $this;
    }

    public function removeDefault()
    {
        $this->update(['default' => false]);
        
        return $this;
    }

    public function activate()
    {
        $this->update(['active' => true]);
    }

    public function deactivate()
    {
        $this->update(['active' => false]);
    }

    public function toggleActive()
    {
        $this->update(['active' => !$this->active]);
    }

    // Validaciones
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($config) {
            // Si se está marcando como default, asegurar que solo haya uno por cooperativa
            if ($config->default) {
                static::where('cooperative_id', $config->cooperative_id)
                      ->where('id', '!=', $config->id)
                      ->update(['default' => false]);
            }
        });
    }
}
