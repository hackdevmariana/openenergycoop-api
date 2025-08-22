<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_number',
        'name',
        'description',
        'project_type',
        'status',
        'priority',
        'start_date',
        'expected_completion_date',
        'actual_completion_date',
        'budget',
        'spent_amount',
        'remaining_budget',
        'planned_capacity_mw',
        'actual_capacity_mw',
        'efficiency_rating',
        'location_address',
        'latitude',
        'longitude',
        'technical_specifications',
        'environmental_impact',
        'regulatory_compliance',
        'safety_measures',
        'project_team',
        'stakeholders',
        'contractors',
        'suppliers',
        'milestones',
        'risks',
        'mitigation_strategies',
        'quality_standards',
        'documentation',
        'tags',
        'project_manager',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'budget' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
        'planned_capacity_mw' => 'decimal:2',
        'actual_capacity_mw' => 'decimal:2',
        'efficiency_rating' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'approved_at' => 'datetime',
        'project_team' => 'array',
        'stakeholders' => 'array',
        'contractors' => 'array',
        'suppliers' => 'array',
        'milestones' => 'array',
        'risks' => 'array',
        'mitigation_strategies' => 'array',
        'quality_standards' => 'array',
        'documentation' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const PROJECT_TYPE_SOLAR_FARM = 'solar_farm';
    const PROJECT_TYPE_WIND_FARM = 'wind_farm';
    const PROJECT_TYPE_HYDROELECTRIC = 'hydroelectric';
    const PROJECT_TYPE_BIOMASS = 'biomass';
    const PROJECT_TYPE_GEOTHERMAL = 'geothermal';
    const PROJECT_TYPE_HYBRID = 'hybrid';
    const PROJECT_TYPE_STORAGE = 'storage';
    const PROJECT_TYPE_GRID_UPGRADE = 'grid_upgrade';
    const PROJECT_TYPE_OTHER = 'other';

    const STATUS_PLANNING = 'planning';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_MAINTENANCE = 'maintenance';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    public static function getProjectTypes(): array
    {
        return [
            self::PROJECT_TYPE_SOLAR_FARM => 'Granja Solar',
            self::PROJECT_TYPE_WIND_FARM => 'Parque Eólico',
            self::PROJECT_TYPE_HYDROELECTRIC => 'Hidroeléctrica',
            self::PROJECT_TYPE_BIOMASS => 'Biomasa',
            self::PROJECT_TYPE_GEOTHERMAL => 'Geotérmica',
            self::PROJECT_TYPE_HYBRID => 'Híbrida',
            self::PROJECT_TYPE_STORAGE => 'Almacenamiento',
            self::PROJECT_TYPE_GRID_UPGRADE => 'Actualización de Red',
            self::PROJECT_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PLANNING => 'Planificación',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_ON_HOLD => 'En Espera',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_MAINTENANCE => 'Mantenimiento',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_MEDIUM => 'Media',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            self::PRIORITY_CRITICAL => 'Crítica',
        ];
    }

    // Relaciones
    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(EnergyInstallation::class);
    }

    public function meters(): HasMany
    {
        return $this->hasMany(EnergyMeter::class, 'meterable_id')
                    ->where('meterable_type', self::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(EnergyReading::class, 'meterable_id')
                    ->where('meterable_type', self::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ProductionProjectStatusLog::class, 'entity_id');
    }

    public function preSaleOffers(): HasMany
    {
        return $this->hasMany(PreSaleOffer::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('project_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ]);
    }

    public function scopePlanning($query)
    {
        return $query->where('status', self::STATUS_PLANNING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', self::STATUS_ON_HOLD);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_completion_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeByProjectManager($query, $projectManagerId)
    {
        return $query->where('project_manager', $projectManagerId);
    }

    // Métodos de validación
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function isPlanning(): bool
    {
        return $this->status === self::STATUS_PLANNING;
    }



    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    public function isOverdue(): bool
    {
        return $this->expected_completion_date && 
               $this->expected_completion_date->isPast() && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function isHighPriority(): bool
    {
        return in_array($this->priority, [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ]);
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    // Métodos de cálculo
    public function getProgressPercentage(): float
    {
        if ($this->budget <= 0) {
            return 0;
        }
        
        return min(100, ($this->spent_amount / $this->budget) * 100);
    }

    public function getCapacityProgressPercentage(): float
    {
        if ($this->planned_capacity_mw <= 0) {
            return 0;
        }
        
        return min(100, ($this->actual_capacity_mw / $this->planned_capacity_mw) * 100);
    }

    public function getRemainingBudget(): float
    {
        return $this->remaining_budget;
    }

    public function getDaysUntilCompletion(): int
    {
        if (!$this->expected_completion_date) {
            return 0;
        }
        
        return now()->diffInDays($this->expected_completion_date, false);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->expected_completion_date);
    }

    public function getProjectDuration(): int
    {
        if (!$this->start_date) {
            return 0;
        }
        
        $endDate = $this->actual_completion_date ?? $this->expected_completion_date;
        if (!$endDate) {
            return now()->diffInDays($this->start_date);
        }
        
        return $this->start_date->diffInDays($endDate);
    }

    public function getTotalGeneratedKwh(): float
    {
        return $this->readings()->where('type', 'production')->sum('delta_kwh');
    }

    public function getDailyProduction(): float
    {
        $today = now()->startOfDay();
        return $this->readings()
            ->where('type', 'production')
            ->where('timestamp', '>=', $today)
            ->sum('delta_kwh');
    }

    public function getMonthlyProduction(): float
    {
        $thisMonth = now()->startOfMonth();
        return $this->readings()
            ->where('type', 'production')
            ->where('timestamp', '>=', $thisMonth)
            ->sum('delta_kwh');
    }

    public function getYearlyProduction(): float
    {
        $thisYear = now()->startOfYear();
        return $this->readings()
            ->where('type', 'production')
            ->where('timestamp', '>=', $thisYear)
            ->sum('delta_kwh');
    }

    // Métodos de formato
    public function getFormattedProjectType(): string
    {
        return self::getProjectTypes()[$this->project_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority] ?? 'Desconocida';
    }

    public function getFormattedBudget(): string
    {
        return '$' . number_format($this->budget, 2);
    }

    public function getFormattedSpentAmount(): string
    {
        return '$' . number_format($this->spent_amount, 2);
    }

    public function getFormattedRemainingBudget(): string
    {
        return '$' . number_format($this->remaining_budget, 2);
    }

    public function getFormattedPlannedCapacity(): string
    {
        return number_format($this->planned_capacity_mw, 2) . ' MW';
    }

    public function getFormattedActualCapacity(): string
    {
        return $this->actual_capacity_mw ? number_format($this->actual_capacity_mw, 2) . ' MW' : 'N/A';
    }

    public function getFormattedEfficiencyRating(): string
    {
        return $this->efficiency_rating ? number_format($this->efficiency_rating, 2) . '%' : 'N/A';
    }

    public function getFormattedStartDate(): string
    {
        return $this->start_date ? $this->start_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedExpectedCompletionDate(): string
    {
        return $this->expected_completion_date ? $this->expected_completion_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedActualCompletionDate(): string
    {
        return $this->actual_completion_date ? $this->actual_completion_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedProgress(): string
    {
        return number_format($this->getProgressPercentage(), 1) . '%';
    }

    public function getFormattedCapacityProgress(): string
    {
        return number_format($this->getCapacityProgressPercentage(), 1) . '%';
    }

    public function getFormattedDaysUntilCompletion(): string
    {
        $days = $this->getDaysUntilCompletion();
        if ($days > 0) {
            return $days . ' días restantes';
        } elseif ($days < 0) {
            return abs($days) . ' días de retraso';
        } else {
            return 'Vence hoy';
        }
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PLANNING => 'bg-blue-100 text-blue-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
            self::STATUS_ON_HOLD => 'bg-orange-100 text-orange-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            self::STATUS_MAINTENANCE => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'bg-gray-100 text-gray-800',
            self::PRIORITY_MEDIUM => 'bg-blue-100 text-blue-800',
            self::PRIORITY_HIGH => 'bg-yellow-100 text-yellow-800',
            self::PRIORITY_URGENT => 'bg-orange-100 text-orange-800',
            self::PRIORITY_CRITICAL => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getProjectTypeBadgeClass(): string
    {
        return match($this->project_type) {
            self::PROJECT_TYPE_SOLAR_FARM => 'bg-yellow-100 text-yellow-800',
            self::PROJECT_TYPE_WIND_FARM => 'bg-blue-100 text-blue-800',
            self::PROJECT_TYPE_HYDROELECTRIC => 'bg-cyan-100 text-cyan-800',
            self::PROJECT_TYPE_BIOMASS => 'bg-green-100 text-green-800',
            self::PROJECT_TYPE_GEOTHERMAL => 'bg-red-100 text-red-800',
            self::PROJECT_TYPE_HYBRID => 'bg-purple-100 text-purple-800',
            self::PROJECT_TYPE_STORAGE => 'bg-indigo-100 text-indigo-800',
            self::PROJECT_TYPE_GRID_UPGRADE => 'bg-pink-100 text-pink-800',
            self::PROJECT_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getOverdueBadgeClass(): string
    {
        if (!$this->isOverdue()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        $daysOverdue = $this->getDaysOverdue();
        
        if ($daysOverdue <= 7) {
            return 'bg-yellow-100 text-yellow-800';
        } elseif ($daysOverdue <= 30) {
            return 'bg-orange-100 text-orange-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }
}
