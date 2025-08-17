<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyStorage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'provider_id', 'system_id', 'name', 'description', 'storage_type',
        'manufacturer', 'model', 'capacity_kwh', 'usable_capacity_kwh', 'current_charge_kwh',
        'charge_level_percentage', 'max_charge_power_kw', 'max_discharge_power_kw',
        'round_trip_efficiency', 'charge_efficiency', 'discharge_efficiency', 'cycle_count',
        'max_cycles', 'current_health_percentage', 'capacity_degradation_percentage',
        'status', 'installation_cost', 'maintenance_cost_annual', 'warranty_end_date',
        'next_maintenance_date', 'location_description', 'is_active', 'max_charge_level',
        'min_charge_level', 'insurance_value'
    ];

    protected $casts = [
        'warranty_end_date' => 'date',
        'next_maintenance_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
