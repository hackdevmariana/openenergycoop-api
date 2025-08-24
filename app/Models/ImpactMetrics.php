<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ImpactMetrics extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'impact_metrics';

    protected $fillable = [
        'user_id',
        'total_kwh_produced',
        'total_co2_avoided_kg',
        'plant_group_id',
        'generated_at'
    ];

    protected $casts = [
        'total_kwh_produced' => 'decimal:4',
        'total_co2_avoided_kg' => 'decimal:4',
        'generated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'generated_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plantGroup()
    {
        return $this->belongsTo(PlantGroup::class);
    }

    // Scopes
    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPlantGroup(Builder $query, $plantGroupId)
    {
        return $query->where('plant_group_id', $plantGroupId);
    }

    public function scopeGlobal(Builder $query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeIndividual(Builder $query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate = null)
    {
        $query->where('generated_at', '>=', $startDate);
        
        if ($endDate) {
            $query->where('generated_at', '<=', $endDate);
        }
        
        return $query;
    }

    public function scopeByCo2Range(Builder $query, $minCo2, $maxCo2 = null)
    {
        $query->where('total_co2_avoided_kg', '>=', $minCo2);
        
        if ($maxCo2 !== null) {
            $query->where('total_co2_avoided_kg', '<=', $maxCo2);
        }
        
        return $query;
    }

    public function scopeByKwhRange(Builder $query, $minKwh, $maxKwh = null)
    {
        $query->where('total_kwh_produced', '>=', $minKwh);
        
        if ($maxKwh !== null) {
            $query->where('total_kwh_produced', '<=', $maxKwh);
        }
        
        return $query;
    }

    public function scopeRecent(Builder $query, $days = 30)
    {
        return $query->where('generated_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeThisMonth(Builder $query)
    {
        return $query->where('generated_at', '>=', Carbon::now()->startOfMonth());
    }

    public function scopeThisYear(Builder $query)
    {
        return $query->where('generated_at', '>=', Carbon::now()->startOfYear());
    }

    public function scopeOrderByImpact(Builder $query, $direction = 'desc')
    {
        return $query->orderBy('total_co2_avoided_kg', $direction);
    }

    public function scopeOrderByProduction(Builder $query, $direction = 'desc')
    {
        return $query->orderBy('total_kwh_produced', $direction);
    }

    public function scopeOrderByDate(Builder $query, $direction = 'desc')
    {
        return $query->orderBy('generated_at', $direction);
    }

    // Accessors
    public function getFormattedKwhAttribute()
    {
        return number_format($this->total_kwh_produced, 2) . ' kWh';
    }

    public function getFormattedCo2Attribute()
    {
        return number_format($this->total_co2_avoided_kg, 2) . ' kg CO₂';
    }

    public function getFormattedDateAttribute()
    {
        return $this->generated_at->format('d/m/Y H:i');
    }

    public function getIsGlobalAttribute()
    {
        return is_null($this->user_id);
    }

    public function getIsIndividualAttribute()
    {
        return !is_null($this->user_id);
    }

    public function getEfficiencyAttribute()
    {
        if ($this->total_kwh_produced > 0) {
            return $this->total_co2_avoided_kg / $this->total_kwh_produced;
        }
        return 0;
    }

    public function getFormattedEfficiencyAttribute()
    {
        return number_format($this->efficiency, 4) . ' kg CO₂/kWh';
    }

    // Métodos
    public function calculateCo2Avoided($kwhProduced, $co2Factor = null)
    {
        if ($co2Factor === null) {
            // Factor por defecto: 0.5 kg CO2 por kWh (promedio de la red eléctrica)
            $co2Factor = 0.5;
        }
        
        return $kwhProduced * $co2Factor;
    }

    public function updateMetrics($kwhProduced, $co2Avoided = null)
    {
        if ($co2Avoided === null) {
            $co2Avoided = $this->calculateCo2Avoided($kwhProduced);
        }

        $this->update([
            'total_kwh_produced' => $this->total_kwh_produced + $kwhProduced,
            'total_co2_avoided_kg' => $this->total_co2_avoided_kg + $co2Avoided,
            'generated_at' => Carbon::now()
        ]);
    }

    public function resetMetrics()
    {
        $this->update([
            'total_kwh_produced' => 0,
            'total_co2_avoided_kg' => 0,
            'generated_at' => Carbon::now()
        ]);
    }

    public function addKwhProduction($kwh, $co2Factor = null)
    {
        $co2Avoided = $this->calculateCo2Avoided($kwh, $co2Factor);
        
        $this->total_kwh_produced += $kwh;
        $this->total_co2_avoided_kg += $co2Avoided;
        $this->generated_at = Carbon::now();
        $this->save();
    }

    public function getImpactPercentage($totalCo2Avoided)
    {
        if ($totalCo2Avoided > 0) {
            return ($this->total_co2_avoided_kg / $totalCo2Avoided) * 100;
        }
        return 0;
    }

    public function getFormattedImpactPercentage($totalCo2Avoided)
    {
        return number_format($this->getImpactPercentage($totalCo2Avoided), 2) . '%';
    }

    // Métodos estáticos
    public static function getTotalGlobalImpact()
    {
        return static::global()->sum('total_co2_avoided_kg');
    }

    public static function getTotalGlobalProduction()
    {
        return static::global()->sum('total_kwh_produced');
    }

    public static function getTopUsersByImpact($limit = 10)
    {
        return static::individual()
            ->with('user')
            ->orderBy('total_co2_avoided_kg', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getTopUsersByProduction($limit = 10)
    {
        return static::individual()
            ->with('user')
            ->orderBy('total_kwh_produced', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getCommunityImpact($organizationId = null)
    {
        $query = static::query();
        
        if ($organizationId) {
            $query->whereHas('user.organizations', function ($q) use ($organizationId) {
                $q->where('organizations.id', $organizationId);
            });
        }
        
        return [
            'total_users' => $query->distinct('user_id')->count(),
            'total_kwh_produced' => $query->sum('total_kwh_produced'),
            'total_co2_avoided' => $query->sum('total_co2_avoided_kg'),
            'average_co2_per_user' => $query->avg('total_co2_avoided_kg'),
            'average_kwh_per_user' => $query->avg('total_kwh_produced')
        ];
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        // Actualizar timestamp cuando se modifican las métricas
        static::updating(function ($impactMetrics) {
            if ($impactMetrics->isDirty(['total_kwh_produced', 'total_co2_avoided_kg'])) {
                $impactMetrics->generated_at = Carbon::now();
            }
        });
    }
}
