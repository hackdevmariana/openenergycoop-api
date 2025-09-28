<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class EnergyZoneSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_name',           // ej. Cuarte de Huerva
        'postal_code',         // ej. 50410
        'municipality_id',     // Relación con Municipality
        'estimated_production_kwh_day',
        'reserved_kwh_day',
        'requested_kwh_day',
        'available_kwh_day',
        'status',              // verde, naranja, rojo
        'last_updated_at',     // Última actualización de datos
        'notes',               // Notas adicionales
    ];

    protected $casts = [
        'estimated_production_kwh_day' => 'decimal:2',
        'reserved_kwh_day' => 'decimal:2',
        'requested_kwh_day' => 'decimal:2',
        'available_kwh_day' => 'decimal:2',
        'last_updated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes para el estado
    const STATUS_GREEN = 'verde';
    const STATUS_ORANGE = 'naranja';
    const STATUS_RED = 'rojo';

    // Relaciones
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function energyProductions(): HasMany
    {
        return $this->hasMany(EnergyProduction::class, 'zone_id');
    }

    public function energyConsumptions(): HasMany
    {
        return $this->hasMany(EnergyConsumption::class, 'zone_id');
    }

    public function energyInterests(): HasMany
    {
        return $this->hasMany(EnergyInterest::class, 'zone_name', 'zone_name');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPostalCode($query, string $postalCode)
    {
        return $query->where('postal_code', $postalCode);
    }

    public function scopeInMunicipality($query, int $municipalityId)
    {
        return $query->where('municipality_id', $municipalityId);
    }

    public function scopeWithAvailableEnergy($query)
    {
        return $query->where('available_kwh_day', '>', 0);
    }

    public function scopeRecentlyUpdated($query, int $hours = 24)
    {
        return $query->where('last_updated_at', '>=', now()->subHours($hours));
    }

    // Accessors
    public function getFullZoneNameAttribute(): string
    {
        return $this->zone_name . ' (' . $this->postal_code . ')';
    }

    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->estimated_production_kwh_day <= 0) {
            return 0;
        }
        
        return round(($this->reserved_kwh_day / $this->estimated_production_kwh_day) * 100, 2);
    }

    public function getDemandPercentageAttribute(): float
    {
        if ($this->estimated_production_kwh_day <= 0) {
            return 0;
        }
        
        return round(($this->requested_kwh_day / $this->estimated_production_kwh_day) * 100, 2);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_GREEN => '#10B981',   // Verde
            self::STATUS_ORANGE => '#F59E0B',  // Naranja
            self::STATUS_RED => '#EF4444',     // Rojo
            default => '#6B7280'               // Gris
        };
    }

    // Métodos de negocio
    public function calculateStatus(): string
    {
        $utilization = $this->utilization_percentage;
        $demand = $this->demand_percentage;
        
        // Lógica de estado basada en utilización y demanda
        if ($utilization >= 90 || $demand >= 100) {
            return self::STATUS_RED;
        } elseif ($utilization >= 70 || $demand >= 80) {
            return self::STATUS_ORANGE;
        } else {
            return self::STATUS_GREEN;
        }
    }

    public function updateStatus(): void
    {
        $this->status = $this->calculateStatus();
        $this->last_updated_at = now();
        $this->save();
    }

    public function calculateAvailableEnergy(): float
    {
        return max(0, $this->estimated_production_kwh_day - $this->reserved_kwh_day);
    }

    public function updateAvailableEnergy(): void
    {
        $this->available_kwh_day = $this->calculateAvailableEnergy();
        $this->save();
    }

    public function canReserveEnergy(float $kwh): bool
    {
        return $this->available_kwh_day >= $kwh;
    }

    public function reserveEnergy(float $kwh): bool
    {
        if (!$this->canReserveEnergy($kwh)) {
            return false;
        }

        $this->reserved_kwh_day += $kwh;
        $this->updateAvailableEnergy();
        $this->updateStatus();
        
        return true;
    }

    public function releaseEnergy(float $kwh): void
    {
        $this->reserved_kwh_day = max(0, $this->reserved_kwh_day - $kwh);
        $this->updateAvailableEnergy();
        $this->updateStatus();
    }

    public function addEnergyRequest(float $kwh): void
    {
        $this->requested_kwh_day += $kwh;
        $this->updateStatus();
        $this->save();
    }

    public function removeEnergyRequest(float $kwh): void
    {
        $this->requested_kwh_day = max(0, $this->requested_kwh_day - $kwh);
        $this->updateStatus();
        $this->save();
    }

    public function getEnergySummary(): array
    {
        return [
            'zone_name' => $this->full_zone_name,
            'estimated_production' => $this->estimated_production_kwh_day,
            'reserved' => $this->reserved_kwh_day,
            'requested' => $this->requested_kwh_day,
            'available' => $this->available_kwh_day,
            'utilization_percentage' => $this->utilization_percentage,
            'demand_percentage' => $this->demand_percentage,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'last_updated' => $this->last_updated_at,
        ];
    }

    public function getHistoricalData(Carbon $from = null, Carbon $to = null): array
    {
        $from = $from ?? now()->subDays(30);
        $to = $to ?? now();

        return [
            'productions' => $this->energyProductions()
                ->whereBetween('production_datetime', [$from, $to])
                ->sum('production_kwh'),
            'consumptions' => $this->energyConsumptions()
                ->whereBetween('measurement_datetime', [$from, $to])
                ->sum('consumption_kwh'),
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ]
        ];
    }

    public function isDataStale(int $hours = 24): bool
    {
        return !$this->last_updated_at || $this->last_updated_at->lt(now()->subHours($hours));
    }

    public function needsUpdate(): bool
    {
        return $this->isDataStale() || $this->status !== $this->calculateStatus();
    }

    // Métodos estáticos
    public static function getZonesByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return self::byStatus($status)->with('municipality')->get();
    }

    public static function getZonesWithAvailableEnergy(): \Illuminate\Database\Eloquent\Collection
    {
        return self::withAvailableEnergy()->with('municipality')->get();
    }

    public static function getTotalAvailableEnergy(): float
    {
        return self::sum('available_kwh_day');
    }

    public static function getTotalProduction(): float
    {
        return self::sum('estimated_production_kwh_day');
    }

    public static function getTotalReserved(): float
    {
        return self::sum('reserved_kwh_day');
    }

    public static function getTotalRequested(): float
    {
        return self::sum('requested_kwh_day');
    }

    public static function getSystemSummary(): array
    {
        return [
            'total_zones' => self::count(),
            'total_production' => self::getTotalProduction(),
            'total_reserved' => self::getTotalReserved(),
            'total_requested' => self::getTotalRequested(),
            'total_available' => self::getTotalAvailableEnergy(),
            'zones_by_status' => [
                'green' => self::byStatus(self::STATUS_GREEN)->count(),
                'orange' => self::byStatus(self::STATUS_ORANGE)->count(),
                'red' => self::byStatus(self::STATUS_RED)->count(),
            ],
            'utilization_percentage' => self::getTotalProduction() > 0 
                ? round((self::getTotalReserved() / self::getTotalProduction()) * 100, 2) 
                : 0,
        ];
    }
}
