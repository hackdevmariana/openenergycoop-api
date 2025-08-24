<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PlantGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'plant_id',
        'number_of_plants',
        'co2_avoided_total',
        'custom_label',
        'is_active'
    ];

    protected $casts = [
        'number_of_plants' => 'integer',
        'co2_avoided_total' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPlant($query, $plantId)
    {
        return $query->where('plant_id', $plantId);
    }

    public function scopeCollective($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeIndividual($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeByCo2Range($query, $minCo2, $maxCo2 = null)
    {
        $query->where('co2_avoided_total', '>=', $minCo2);
        
        if ($maxCo2 !== null) {
            $query->where('co2_avoided_total', '<=', $maxCo2);
        }
        
        return $query;
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        if ($this->custom_label) {
            return $this->custom_label;
        }
        
        return $this->name;
    }

    public function getFormattedCo2AvoidedAttribute()
    {
        return number_format($this->co2_avoided_total, 2) . ' kg CO2';
    }

    public function getFormattedPlantCountAttribute()
    {
        return number_format($this->number_of_plants) . ' ' . $this->plant->unit_label;
    }

    public function getIsCollectiveAttribute()
    {
        return is_null($this->user_id);
    }

    public function getIsIndividualAttribute()
    {
        return !is_null($this->user_id);
    }

    // Métodos
    public function addPlants($count, $co2Avoided = null)
    {
        $this->number_of_plants += $count;
        
        if ($co2Avoided === null) {
            $co2Avoided = $this->plant->calculateCo2Avoided($count);
        }
        
        $this->co2_avoided_total += $co2Avoided;
        $this->save();
        
        return $this;
    }

    public function removePlants($count, $co2Avoided = null)
    {
        $this->number_of_plants = max(0, $this->number_of_plants - $count);
        
        if ($co2Avoided === null) {
            $co2Avoided = $this->plant->calculateCo2Avoided($count);
        }
        
        $this->co2_avoided_total = max(0, $this->co2_avoided_total - $co2Avoided);
        $this->save();
        
        return $this;
    }

    public function updateCo2Avoided($newTotal)
    {
        $this->co2_avoided_total = max(0, $newTotal);
        $this->save();
        
        return $this;
    }

    public function calculateEfficiency()
    {
        if ($this->number_of_plants == 0) {
            return 0;
        }
        
        return $this->co2_avoided_total / $this->number_of_plants;
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
              ->orWhere('custom_label', 'like', "%{$search}%");
        });
    }

    // Eventos
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($plantGroup) {
            // Actualizar automáticamente el timestamp
            $plantGroup->updated_at = Carbon::now();
        });
    }
}
