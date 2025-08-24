<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Plant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'co2_equivalent_per_unit_kg',
        'image',
        'description',
        'unit_label',
        'is_active'
    ];

    protected $casts = [
        'co2_equivalent_per_unit_kg' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relaciones
    public function plantGroups()
    {
        return $this->hasMany(PlantGroup::class);
    }

    public function cooperativeConfigs()
    {
        return $this->hasMany(CooperativePlantConfig::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    public function scopeByUnitLabel($query, $unitLabel)
    {
        return $query->where('unit_label', $unitLabel);
    }

    public function scopeByCo2Range($query, $minCo2, $maxCo2 = null)
    {
        $query->where('co2_equivalent_per_unit_kg', '>=', $minCo2);
        
        if ($maxCo2 !== null) {
            $query->where('co2_equivalent_per_unit_kg', '<=', $maxCo2);
        }
        
        return $query;
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::disk('public')->url($this->image);
        }
        
        return asset('images/default-plant.png');
    }

    public function getFormattedCo2Attribute()
    {
        return number_format($this->co2_equivalent_per_unit_kg, 2) . ' kg CO2';
    }

    public function getDisplayNameAttribute()
    {
        return "{$this->name} ({$this->unit_label})";
    }

    // Métodos
    public function calculateCo2Avoided($numberOfUnits)
    {
        return $this->co2_equivalent_per_unit_kg * $numberOfUnits;
    }

    public function isAvailable()
    {
        return $this->is_active;
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function toggleActive()
    {
        $this->update(['is_active' => !$this->is_active]);
    }

    // Búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('unit_label', 'like', "%{$search}%");
        });
    }
}
