<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image',
        'goal_kwh',
        'goal_amount',
        'current_amount',
        'start_date',
        'end_date',
        'status',
        'visibility',
        'organization_id',
        'source',
        'co2_per_kwh',
        'total_generated_value',
        'location',
        'coordinates',
        'installed_power_kw',
        'efficiency_rating',
        'maintenance_cost',
        'auto_reinvest',
    ];

    protected $casts = [
        'goal_kwh' => 'decimal:4',
        'goal_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'co2_per_kwh' => 'decimal:4',
        'total_generated_value' => 'decimal:2',
        'coordinates' => 'array',
        'installed_power_kw' => 'decimal:2',
        'efficiency_rating' => 'decimal:2',
        'maintenance_cost' => 'decimal:2',
        'auto_reinvest' => 'boolean',
    ];

    // Enums
    const STATUS_DRAFT = 'draft';
    const STATUS_PLANNED = 'planned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_CANCELLED = 'cancelled';

    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_INTERNAL = 'internal';

    const SOURCE_SOLAR = 'solar';
    const SOURCE_WIND = 'wind';
    const SOURCE_HYDRO = 'hydro';
    const SOURCE_BIOMASS = 'biomass';
    const SOURCE_GEOTHERMAL = 'geothermal';
    const SOURCE_HYBRID = 'hybrid';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_PLANNED => 'Planificado',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_ARCHIVED => 'Archivado',
            self::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    public static function getVisibilityOptions(): array
    {
        return [
            self::VISIBILITY_PUBLIC => 'Público',
            self::VISIBILITY_PRIVATE => 'Privado',
            self::VISIBILITY_INTERNAL => 'Interno',
        ];
    }

    public static function getSources(): array
    {
        return [
            self::SOURCE_SOLAR => 'Solar',
            self::SOURCE_WIND => 'Eólica',
            self::SOURCE_HYDRO => 'Hidráulica',
            self::SOURCE_BIOMASS => 'Biomasa',
            self::SOURCE_GEOTHERMAL => 'Geotérmica',
            self::SOURCE_HYBRID => 'Híbrida',
        ];
    }

    // Relaciones
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function energySources(): BelongsToMany
    {
        return $this->belongsToMany(EnergySource::class, 'production_project_energy_sources')
                    ->withPivot('percentage')
                    ->withTimestamps();
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(ProductionReservation::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(ProductionParticipation::class);
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PLANNED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    // Métodos
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_PLANNED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    public function getProgressPercentage(): float
    {
        if ($this->goal_kwh <= 0) {
            return 0;
        }
        
        return min(100, ($this->current_amount / $this->goal_kwh) * 100);
    }

    public function getFinancialProgressPercentage(): float
    {
        if ($this->goal_amount <= 0) {
            return 0;
        }
        
        return min(100, ($this->current_amount / $this->goal_amount) * 100);
    }

    public function getRemainingKwh(): float
    {
        return max(0, $this->goal_kwh - $this->current_amount);
    }

    public function getRemainingAmount(): float
    {
        return max(0, $this->goal_amount - $this->current_amount);
    }

    public function getTotalInvestors(): int
    {
        return $this->participations()->count();
    }

    public function getTotalReservations(): int
    {
        return $this->reservations()->where('status', 'confirmed')->count();
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

    public function getCo2Saved(): float
    {
        $totalKwh = $this->getTotalGeneratedKwh();
        return $totalKwh * $this->co2_per_kwh;
    }

    public function getFormattedGoal(): string
    {
        return number_format($this->goal_kwh, 2) . ' kWh';
    }

    public function getFormattedCurrentAmount(): string
    {
        return number_format($this->current_amount, 2) . ' kWh';
    }

    public function getFormattedGoalAmount(): string
    {
        return '€' . number_format($this->goal_amount, 2);
    }

    public function getFormattedCurrentAmountMoney(): string
    {
        return '€' . number_format($this->current_amount, 2);
    }

    public function getFormattedSource(): string
    {
        return self::getSources()[$this->source] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_PLANNED => 'bg-blue-100 text-blue-800',
            self::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_ARCHIVED => 'bg-gray-100 text-gray-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function canAcceptReservations(): bool
    {
        return $this->isActive() && $this->getRemainingKwh() > 0;
    }

    public function canAcceptParticipations(): bool
    {
        return $this->isActive() && $this->getRemainingAmount() > 0;
    }
}
