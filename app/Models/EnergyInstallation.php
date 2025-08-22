<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyInstallation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'installation_number',
        'name',
        'description',
        'installation_type',
        'status',
        'priority',
        'energy_source_id',
        'customer_id',
        'project_id',
        'installed_capacity_kw',
        'operational_capacity_kw',
        'efficiency_rating',
        'annual_production_kwh',
        'monthly_production_kwh',
        'daily_production_kwh',
        'location_address',
        'latitude',
        'longitude',
        'installation_date',
        'commissioning_date',
        'warranty_expiry_date',
        'installation_cost',
        'operational_cost_per_kwh',
        'maintenance_cost_per_kwh',
        'technical_specifications',
        'warranty_terms',
        'maintenance_requirements',
        'safety_features',
        'equipment_details',
        'maintenance_schedule',
        'performance_metrics',
        'warranty_documents',
        'installation_photos',
        'tags',
        'installed_by',
        'managed_by',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'installed_capacity_kw' => 'decimal:2',
        'operational_capacity_kw' => 'decimal:2',
        'efficiency_rating' => 'decimal:2',
        'annual_production_kwh' => 'decimal:2',
        'monthly_production_kwh' => 'decimal:2',
        'daily_production_kwh' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'installation_date' => 'date',
        'commissioning_date' => 'date',
        'warranty_expiry_date' => 'date',
        'installation_cost' => 'decimal:2',
        'operational_cost_per_kwh' => 'decimal:2',
        'maintenance_cost_per_kwh' => 'decimal:2',
        'approved_at' => 'datetime',
        'equipment_details' => 'array',
        'maintenance_schedule' => 'array',
        'performance_metrics' => 'array',
        'warranty_documents' => 'array',
        'installation_photos' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const INSTALLATION_TYPE_RESIDENTIAL = 'residential';
    const INSTALLATION_TYPE_COMMERCIAL = 'commercial';
    const INSTALLATION_TYPE_INDUSTRIAL = 'industrial';
    const INSTALLATION_TYPE_UTILITY_SCALE = 'utility_scale';
    const INSTALLATION_TYPE_COMMUNITY = 'community';
    const INSTALLATION_TYPE_MICROGRID = 'microgrid';
    const INSTALLATION_TYPE_OFF_GRID = 'off_grid';
    const INSTALLATION_TYPE_GRID_TIED = 'grid_tied';

    const STATUS_PLANNED = 'planned';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_OPERATIONAL = 'operational';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_DECOMMISSIONED = 'decommissioned';
    const STATUS_CANCELLED = 'cancelled';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    public static function getInstallationTypes(): array
    {
        return [
            self::INSTALLATION_TYPE_RESIDENTIAL => 'Residencial',
            self::INSTALLATION_TYPE_COMMERCIAL => 'Comercial',
            self::INSTALLATION_TYPE_INDUSTRIAL => 'Industrial',
            self::INSTALLATION_TYPE_UTILITY_SCALE => 'Escala de Utilidad',
            self::INSTALLATION_TYPE_COMMUNITY => 'Comunitaria',
            self::INSTALLATION_TYPE_MICROGRID => 'Microred',
            self::INSTALLATION_TYPE_OFF_GRID => 'Fuera de la Red',
            self::INSTALLATION_TYPE_GRID_TIED => 'Conectada a la Red',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PLANNED => 'Planificada',
            self::STATUS_APPROVED => 'Aprobada',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_COMPLETED => 'Completada',
            self::STATUS_OPERATIONAL => 'Operativa',
            self::STATUS_MAINTENANCE => 'Mantenimiento',
            self::STATUS_DECOMMISSIONED => 'Desmantelada',
            self::STATUS_CANCELLED => 'Cancelada',
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
    public function energySource(): BelongsTo
    {
        return $this->belongsTo(EnergySource::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProductionProject::class, 'project_id');
    }

    public function installedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(EnergyForecast::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_OPERATIONAL);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('installation_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByEnergySource($query, $energySourceId)
    {
        return $query->where('energy_source_id', $energySourceId);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeOperational($query)
    {
        return $query->where('status', self::STATUS_OPERATIONAL);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ]);
    }

    public function scopeResidential($query)
    {
        return $query->where('installation_type', self::INSTALLATION_TYPE_RESIDENTIAL);
    }

    public function scopeCommercial($query)
    {
        return $query->where('installation_type', self::INSTALLATION_TYPE_COMMERCIAL);
    }

    public function scopeIndustrial($query)
    {
        return $query->where('installation_type', self::INSTALLATION_TYPE_INDUSTRIAL);
    }

    public function scopeUtilityScale($query)
    {
        return $query->where('installation_type', self::INSTALLATION_TYPE_UTILITY_SCALE);
    }

    public function scopeCommunity($query)
    {
        return $query->where('installation_type', self::INSTALLATION_TYPE_COMMUNITY);
    }

    public function scopeMicrogrid($query)
    {
        return $query->where('installation_type', self::INSTALLATION_TYPE_MICROGRID);
    }

    public function scopeOffGrid($query)
    {
        return $query->where('installation_type', self::INSTALLATION_TYPE_OFF_GRID);
    }

    public function scopeGridTied($query)
    {
        return $query->where('installation_type', self::INSTALLATION_TYPE_GRID_TIED);
    }

    // Métodos de validación
    public function isActive(): bool
    {
        return $this->status === self::STATUS_OPERATIONAL;
    }

    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isOperational(): bool
    {
        return $this->status === self::STATUS_OPERATIONAL;
    }

    public function isMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    public function isDecommissioned(): bool
    {
        return $this->status === self::STATUS_DECOMMISSIONED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isResidential(): bool
    {
        return $this->installation_type === self::INSTALLATION_TYPE_RESIDENTIAL;
    }

    public function isCommercial(): bool
    {
        return $this->installation_type === self::INSTALLATION_TYPE_COMMERCIAL;
    }

    public function isIndustrial(): bool
    {
        return $this->installation_type === self::INSTALLATION_TYPE_INDUSTRIAL;
    }

    public function isUtilityScale(): bool
    {
        return $this->installation_type === self::INSTALLATION_TYPE_UTILITY_SCALE;
    }

    public function isCommunity(): bool
    {
        return $this->installation_type === self::INSTALLATION_TYPE_COMMUNITY;
    }

    public function isMicrogrid(): bool
    {
        return $this->installation_type === self::INSTALLATION_TYPE_MICROGRID;
    }

    public function isOffGrid(): bool
    {
        return $this->installation_type === self::INSTALLATION_TYPE_OFF_GRID;
    }

    public function isGridTied(): bool
    {
        return $this->installation_type === self::INSTALLATION_TYPE_GRID_TIED;
    }

    public function isHighPriority(): bool
    {
        return in_array($this->priority, [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ]);
    }

    public function isApprovedByAdmin(): bool
    {
        return !is_null($this->approved_at);
    }

    // Métodos de cálculo
    public function getUtilizationPercentage(): float
    {
        if ($this->installed_capacity_kw <= 0) {
            return 0;
        }
        
        return ($this->operational_capacity_kw / $this->installed_capacity_kw) * 100;
    }

    public function getAgeInYears(): int
    {
        if (!$this->installation_date) {
            return 0;
        }
        
        return $this->installation_date->diffInYears(now());
    }

    public function getDaysUntilWarrantyExpiry(): int
    {
        if (!$this->warranty_expiry_date) {
            return 0;
        }
        
        return now()->diffInDays($this->warranty_expiry_date, false);
    }

    public function getTotalProduction(): float
    {
        return $this->readings()
            ->where('type', 'production')
            ->sum('delta_kwh');
    }

    public function getTotalConsumption(): float
    {
        return $this->readings()
            ->where('type', 'consumption')
            ->sum('delta_kwh');
    }

    public function getDailyProduction(): float
    {
        $today = now()->startOfDay();
        return $this->readings()
            ->where('type', 'production')
            ->where('timestamp', '>=', $today)
            ->sum('delta_kwh');
    }

    public function getDailyConsumption(): float
    {
        $today = now()->startOfDay();
        return $this->readings()
            ->where('type', 'consumption')
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

    public function getMonthlyConsumption(): float
    {
        $thisMonth = now()->startOfMonth();
        return $this->readings()
            ->where('type', 'consumption')
            ->where('timestamp', '>=', $thisMonth)
            ->sum('delta_kwh');
    }

    public function getNetProduction(): float
    {
        return $this->getTotalProduction() - $this->getTotalConsumption();
    }

    public function getTotalAnnualCost(): float
    {
        $operationalCost = $this->annual_production_kwh * ($this->operational_cost_per_kwh ?? 0);
        $maintenanceCost = $this->annual_production_kwh * ($this->maintenance_cost_per_kwh ?? 0);
        
        return $operationalCost + $maintenanceCost;
    }

    public function getCostPerKwh(): float
    {
        if ($this->annual_production_kwh <= 0) {
            return 0;
        }
        
        return $this->getTotalAnnualCost() / $this->annual_production_kwh;
    }

    public function needsMaintenance(): bool
    {
        if (!$this->maintenance_schedule) {
            return false;
        }
        
        // Implementar lógica basada en el cronograma de mantenimiento
        return false; // Placeholder
    }

    public function isUnderWarranty(): bool
    {
        if (!$this->warranty_expiry_date) {
            return false;
        }
        
        return $this->warranty_expiry_date->isFuture();
    }

    public function getEfficiencyClass(): string
    {
        if ($this->efficiency_rating >= 90) {
            return 'A+';
        } elseif ($this->efficiency_rating >= 80) {
            return 'A';
        } elseif ($this->efficiency_rating >= 70) {
            return 'B';
        } elseif ($this->efficiency_rating >= 60) {
            return 'C';
        } else {
            return 'D';
        }
    }

    // Métodos de formato
    public function getFormattedInstallationType(): string
    {
        return self::getInstallationTypes()[$this->installation_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority] ?? 'Desconocida';
    }

    public function getFormattedInstalledCapacity(): string
    {
        return number_format($this->installed_capacity_kw, 2) . ' kW';
    }

    public function getFormattedOperationalCapacity(): string
    {
        return number_format($this->operational_capacity_kw, 2) . ' kW';
    }

    public function getFormattedEfficiencyRating(): string
    {
        return number_format($this->efficiency_rating, 2) . '%';
    }

    public function getFormattedAnnualProduction(): string
    {
        return number_format($this->annual_production_kwh, 2) . ' kWh';
    }

    public function getFormattedMonthlyProduction(): string
    {
        return number_format($this->monthly_production_kwh, 2) . ' kWh';
    }

    public function getFormattedDailyProduction(): string
    {
        return number_format($this->daily_production_kwh, 2) . ' kWh';
    }

    public function getFormattedInstallationCost(): string
    {
        return '$' . number_format($this->installation_cost, 2);
    }

    public function getFormattedOperationalCost(): string
    {
        return $this->operational_cost_per_kwh ? '$' . number_format($this->operational_cost_per_kwh, 2) . '/kWh' : 'N/A';
    }

    public function getFormattedMaintenanceCost(): string
    {
        return $this->maintenance_cost_per_kwh ? '$' . number_format($this->maintenance_cost_per_kwh, 2) . '/kWh' : 'N/A';
    }

    public function getFormattedInstallationDate(): string
    {
        return $this->installation_date ? $this->installation_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedCommissioningDate(): string
    {
        return $this->commissioning_date ? $this->commissioning_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedWarrantyExpiryDate(): string
    {
        return $this->warranty_expiry_date ? $this->warranty_expiry_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedUtilizationPercentage(): string
    {
        return number_format($this->getUtilizationPercentage(), 1) . '%';
    }

    public function getFormattedTotalProduction(): string
    {
        return number_format($this->getTotalProduction(), 2) . ' kWh';
    }

    public function getFormattedTotalConsumption(): string
    {
        return number_format($this->getTotalConsumption(), 2) . ' kWh';
    }

    public function getFormattedNetProduction(): string
    {
        return number_format($this->getNetProduction(), 2) . ' kWh';
    }

    public function getFormattedTotalAnnualCost(): string
    {
        return '$' . number_format($this->getTotalAnnualCost(), 2);
    }

    public function getFormattedCostPerKwh(): string
    {
        return '$' . number_format($this->getCostPerKwh(), 2) . '/kWh';
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PLANNED => 'bg-blue-100 text-blue-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
            self::STATUS_COMPLETED => 'bg-blue-100 text-blue-800',
            self::STATUS_OPERATIONAL => 'bg-green-100 text-green-800',
            self::STATUS_MAINTENANCE => 'bg-orange-100 text-orange-800',
            self::STATUS_DECOMMISSIONED => 'bg-red-100 text-red-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getInstallationTypeBadgeClass(): string
    {
        return match($this->installation_type) {
            self::INSTALLATION_TYPE_RESIDENTIAL => 'bg-blue-100 text-blue-800',
            self::INSTALLATION_TYPE_COMMERCIAL => 'bg-green-100 text-green-800',
            self::INSTALLATION_TYPE_INDUSTRIAL => 'bg-purple-100 text-purple-800',
            self::INSTALLATION_TYPE_UTILITY_SCALE => 'bg-red-100 text-red-800',
            self::INSTALLATION_TYPE_COMMUNITY => 'bg-indigo-100 text-indigo-800',
            self::INSTALLATION_TYPE_MICROGRID => 'bg-pink-100 text-pink-800',
            self::INSTALLATION_TYPE_OFF_GRID => 'bg-yellow-100 text-yellow-800',
            self::INSTALLATION_TYPE_GRID_TIED => 'bg-cyan-100 text-cyan-800',
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

    public function getEfficiencyBadgeClass(): string
    {
        return match($this->getEfficiencyClass()) {
            'A+' => 'bg-green-100 text-green-800',
            'A' => 'bg-blue-100 text-blue-800',
            'B' => 'bg-yellow-100 text-yellow-800',
            'C' => 'bg-orange-100 text-orange-800',
            'D' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
