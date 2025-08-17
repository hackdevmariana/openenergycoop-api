<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'energy_contract_id', 'measurement_datetime', 'period_type', 'consumption_kwh',
        'peak_consumption_kw', 'off_peak_consumption_kwh', 'renewable_percentage', 'grid_consumption_kwh',
        'self_consumption_kwh', 'total_cost_eur', 'energy_cost_eur', 'grid_cost_eur', 'taxes_eur',
        'tariff_type', 'rate_per_kwh', 'temperature_avg_c', 'efficiency_score', 'meter_id'
    ];

    protected $casts = [
        'measurement_datetime' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function energyContract()
    {
        return $this->belongsTo(EnergyContract::class);
    }
}
