<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'user_asset_id', 'energy_storage_id', 'system_id', 'production_datetime',
        'period_type', 'energy_source', 'production_kwh', 'peak_production_kw', 'capacity_factor',
        'system_efficiency', 'renewable_percentage', 'grid_injection_kwh', 'self_consumption_kwh',
        'curtailment_kwh', 'revenue_eur', 'feed_in_tariff_eur', 'co2_avoided_kg', 'irradiance_wm2',
        'wind_speed_ms', 'temperature_c', 'operational_status'
    ];

    protected $casts = [
        'production_datetime' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userAsset()
    {
        return $this->belongsTo(UserAsset::class);
    }
}
