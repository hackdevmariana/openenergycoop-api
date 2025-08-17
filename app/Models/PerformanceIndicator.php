<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PerformanceIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_name',
        'indicator_code',
        'description',
        'indicator_type',
        'category',
        'criticality',
        'priority',
        'frequency',
        'is_active',
        'scope',
        'entity_type',
        'entity_id',
        'entity_name',
        'measurement_timestamp',
        'measurement_date',
        'period_start',
        'period_end',
        'period_type',
        'current_value',
        'unit',
        'target_value',
        'baseline_value',
        'previous_value',
        'change_absolute',
        'change_percentage',
        'target_achievement_percentage',
        'trend_direction',
        'performance_status',
        'calculation_method',
        'confidence_level',
        'industry_benchmark',
        'business_impact',
        'efficiency_percentage',
        'utilization_percentage',
        'quality_score',
        'energy_cooperative_id',
        'user_id',
        'energy_report_id',
        'created_by_id',
        'is_validated',
        'show_in_dashboard',
        'auto_calculate',
        'alerts_enabled',
        'current_alert_level',
    ];

    protected $casts = [
        'measurement_timestamp' => 'datetime',
        'measurement_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'current_value' => 'decimal:4',
        'target_value' => 'decimal:4',
        'baseline_value' => 'decimal:4',
        'previous_value' => 'decimal:4',
        'change_percentage' => 'decimal:2',
        'target_achievement_percentage' => 'decimal:2',
        'confidence_level' => 'decimal:2',
        'industry_benchmark' => 'decimal:4',
        'efficiency_percentage' => 'decimal:2',
        'utilization_percentage' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'is_active' => 'boolean',
        'is_validated' => 'boolean',
        'show_in_dashboard' => 'boolean',
        'auto_calculate' => 'boolean',
        'alerts_enabled' => 'boolean',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    // Scopes
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('indicator_type', $type);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDashboard(Builder $query): Builder
    {
        return $query->where('show_in_dashboard', true)
            ->where('is_active', true);
    }

    public function scopeByCriticality(Builder $query, string $criticality): Builder
    {
        return $query->where('criticality', $criticality);
    }

    public function scopeWithAlerts(Builder $query): Builder
    {
        return $query->where('alerts_enabled', true)
            ->whereIn('current_alert_level', ['warning', 'critical', 'emergency']);
    }

    // Métodos de utilidad
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function meetsTarget(): bool
    {
        if (!$this->target_value) {
            return false;
        }
        return $this->current_value >= $this->target_value;
    }

    public function getPerformanceLevel(): string
    {
        return $this->performance_status ?? 'unknown';
    }

    public function calculateTargetAchievement(): ?float
    {
        if (!$this->target_value || !$this->baseline_value) {
            return null;
        }

        $progress = $this->current_value - $this->baseline_value;
        $target = $this->target_value - $this->baseline_value;

        if ($target == 0) {
            return null;
        }

        return ($progress / $target) * 100;
    }

    public function isInAlert(): bool
    {
        return in_array($this->current_alert_level, ['warning', 'critical', 'emergency']);
    }

    public function formatValueWithUnit(): string
    {
        $formattedValue = number_format($this->current_value, 2);
        return $this->unit ? "{$formattedValue} {$this->unit}" : $formattedValue;
    }

    public function getCriticalityLevel(): string
    {
        return match($this->criticality) {
            'critical' => 'Crítico',
            'high' => 'Alto',
            'medium' => 'Medio',
            'low' => 'Bajo',
            default => 'Desconocido',
        };
    }

    public function getPriorityLevel(): string
    {
        return match($this->priority) {
            5 => 'Crítico',
            4 => 'Alto',
            3 => 'Medio',
            2 => 'Bajo',
            1 => 'Muy Bajo',
            default => 'Sin Prioridad',
        };
    }
}
