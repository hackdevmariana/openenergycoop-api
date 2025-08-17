<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SustainabilityMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_name',
        'metric_code',
        'description',
        'metric_type',
        'metric_category',
        'entity_type',
        'entity_id',
        'entity_name',
        'measurement_date',
        'period_start',
        'period_end',
        'period_type',
        'value',
        'unit',
        'baseline_value',
        'target_value',
        'previous_period_value',
        'change_absolute',
        'change_percentage',
        'trend',
        'trend_score',
        'co2_emissions_kg',
        'co2_avoided_kg',
        'renewable_energy_kwh',
        'total_energy_kwh',
        'renewable_percentage',
        'cost_savings_eur',
        'is_certified',
        'verification_status',
        'performance_rating',
        'contributes_to_sdg',
        'is_public',
        'energy_cooperative_id',
        'user_id',
        'energy_report_id',
        'alert_enabled',
        'alert_status',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'value' => 'decimal:4',
        'baseline_value' => 'decimal:4',
        'target_value' => 'decimal:4',
        'change_percentage' => 'decimal:2',
        'co2_emissions_kg' => 'decimal:3',
        'renewable_percentage' => 'decimal:2',
        'cost_savings_eur' => 'decimal:2',
        'is_certified' => 'boolean',
        'contributes_to_sdg' => 'boolean',
        'is_public' => 'boolean',
        'alert_enabled' => 'boolean',
    ];

    // Relaciones
    public function energyCooperative(): BelongsTo
    {
        return $this->belongsTo(EnergyCooperative::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function energyReport(): BelongsTo
    {
        return $this->belongsTo(EnergyReport::class);
    }

    // Scopes
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('metric_type', $type);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('metric_category', $category);
    }

    public function scopeCertified(Builder $query): Builder
    {
        return $query->where('is_certified', true);
    }

    public function scopeImproving(Builder $query): Builder
    {
        return $query->where('trend', 'improving');
    }

    // Métodos de utilidad
    public function isImproving(): bool
    {
        return $this->trend === 'improving';
    }

    public function meetsTarget(): bool
    {
        if (!$this->target_value) {
            return false;
        }
        return $this->value >= $this->target_value;
    }

    public function formatValueWithUnit(): string
    {
        $formattedValue = number_format($this->value, 2);
        return $this->unit ? "{$formattedValue} {$this->unit}" : $formattedValue;
    }
}
