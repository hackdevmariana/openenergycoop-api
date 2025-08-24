<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CommunityMetrics extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'community_metrics';

    protected $fillable = [
        'organization_id',
        'total_users',
        'total_kwh_produced',
        'total_co2_avoided',
        'updated_at'
    ];

    protected $casts = [
        'total_users' => 'integer',
        'total_kwh_produced' => 'decimal:4',
        'total_co2_avoided' => 'decimal:4',
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at'
    ];

    // Relaciones
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeByOrganization(Builder $query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByUserCount(Builder $query, $minUsers, $maxUsers = null)
    {
        $query->where('total_users', '>=', $minUsers);
        
        if ($maxUsers !== null) {
            $query->where('total_users', '<=', $maxUsers);
        }
        
        return $query;
    }

    public function scopeByCo2Range(Builder $query, $minCo2, $maxCo2 = null)
    {
        $query->where('total_co2_avoided', '>=', $minCo2);
        
        if ($maxCo2 !== null) {
            $query->where('total_co2_avoided', '<=', $maxCo2);
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

    public function scopeActive(Builder $query)
    {
        return $query->where('total_users', '>', 0);
    }

    public function scopeInactive(Builder $query)
    {
        return $query->where('total_users', 0);
    }

    public function scopeOrderByUsers(Builder $query, $direction = 'desc')
    {
        return $query->orderBy('total_users', $direction);
    }

    public function scopeOrderByImpact(Builder $query, $direction = 'desc')
    {
        return $query->orderBy('total_co2_avoided', $direction);
    }

    public function scopeOrderByProduction(Builder $query, $direction = 'desc')
    {
        return $query->orderBy('total_kwh_produced', $direction);
    }

    public function scopeOrderByDate(Builder $query, $direction = 'desc')
    {
        return $query->orderBy('updated_at', $direction);
    }

    public function scopeRecent(Builder $query, $days = 30)
    {
        return $query->where('updated_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeThisMonth(Builder $query)
    {
        return $query->where('updated_at', '>=', Carbon::now()->startOfMonth());
    }

    public function scopeThisYear(Builder $query)
    {
        return $query->where('updated_at', '>=', Carbon::now()->startOfYear());
    }

    // Accessors
    public function getFormattedUsersAttribute()
    {
        return number_format($this->total_users) . ' usuarios';
    }

    public function getFormattedKwhAttribute()
    {
        return number_format($this->total_kwh_produced, 2) . ' kWh';
    }

    public function getFormattedCo2Attribute()
    {
        return number_format($this->total_co2_avoided, 2) . ' kg CO₂';
    }

    public function getFormattedDateAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }

    public function getEfficiencyAttribute()
    {
        if ($this->total_users > 0) {
            return $this->total_co2_avoided / $this->total_users;
        }
        return 0;
    }

    public function getFormattedEfficiencyAttribute()
    {
        return number_format($this->efficiency, 2) . ' kg CO₂/usuario';
    }

    public function getProductionPerUserAttribute()
    {
        if ($this->total_users > 0) {
            return $this->total_kwh_produced / $this->total_users;
        }
        return 0;
    }

    public function getFormattedProductionPerUserAttribute()
    {
        return number_format($this->production_per_user, 2) . ' kWh/usuario';
    }

    public function getCo2PerUserAttribute()
    {
        if ($this->total_users > 0) {
            return $this->total_co2_avoided / $this->total_users;
        }
        return 0;
    }

    public function getFormattedCo2PerUserAttribute()
    {
        return number_format($this->co2_per_user, 2) . ' kg CO₂/usuario';
    }

    public function getIsActiveAttribute()
    {
        return $this->total_users > 0;
    }

    public function getIsInactiveAttribute()
    {
        return $this->total_users === 0;
    }

    // Métodos
    public function updateMetrics($totalUsers, $totalKwh, $totalCo2)
    {
        $this->update([
            'total_users' => $totalUsers,
            'total_kwh_produced' => $totalKwh,
            'total_co2_avoided' => $totalCo2,
            'updated_at' => Carbon::now()
        ]);
    }

    public function addUser()
    {
        $this->increment('total_users');
        $this->updated_at = Carbon::now();
        $this->save();
    }

    public function removeUser()
    {
        if ($this->total_users > 0) {
            $this->decrement('total_users');
            $this->updated_at = Carbon::now();
            $this->save();
        }
    }

    public function addKwhProduction($kwh)
    {
        $this->total_kwh_produced += $kwh;
        $this->updated_at = Carbon::now();
        $this->save();
    }

    public function addCo2Avoided($co2)
    {
        $this->total_co2_avoided += $co2;
        $this->updated_at = Carbon::now();
        $this->save();
    }

    public function resetMetrics()
    {
        $this->update([
            'total_users' => 0,
            'total_kwh_produced' => 0,
            'total_co2_avoided' => 0,
            'updated_at' => Carbon::now()
        ]);
    }

    public function calculateEfficiency()
    {
        if ($this->total_users > 0 && $this->total_kwh_produced > 0) {
            return $this->total_co2_avoided / $this->total_kwh_produced;
        }
        return 0;
    }

    public function getFormattedCalculatedEfficiencyAttribute()
    {
        $efficiency = $this->calculateEfficiency();
        return number_format($efficiency, 4) . ' kg CO₂/kWh';
    }

    public function getRankingPosition($metric = 'co2')
    {
        $query = static::query();
        
        switch ($metric) {
            case 'co2':
                $query->orderBy('total_co2_avoided', 'desc');
                break;
            case 'kwh':
                $query->orderBy('total_kwh_produced', 'desc');
                break;
            case 'users':
                $query->orderBy('total_users', 'desc');
                break;
            default:
                $query->orderBy('total_co2_avoided', 'desc');
        }
        
        $rankings = $query->pluck('id')->toArray();
        $position = array_search($this->id, $rankings);
        
        return $position !== false ? $position + 1 : null;
    }

    public function getFormattedRankingAttribute()
    {
        $position = $this->getRankingPosition();
        if ($position) {
            $suffix = $this->getOrdinalSuffix($position);
            return $position . $suffix . ' lugar';
        }
        return 'Sin ranking';
    }

    private function getOrdinalSuffix($number)
    {
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            return 'º';
        }
        
        switch ($number % 10) {
            case 1:
                return 'º';
            case 2:
                return 'º';
            case 3:
                return 'º';
            default:
                return 'º';
        }
    }

    // Métodos estáticos
    public static function getTotalCommunityImpact()
    {
        return static::sum('total_co2_avoided');
    }

    public static function getTotalCommunityProduction()
    {
        return static::sum('total_kwh_produced');
    }

    public static function getTotalCommunityUsers()
    {
        return static::sum('total_users');
    }

    public static function getTopOrganizationsByImpact($limit = 10)
    {
        return static::with('organization')
            ->orderBy('total_co2_avoided', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getTopOrganizationsByProduction($limit = 10)
    {
        return static::with('organization')
            ->orderBy('total_kwh_produced', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getTopOrganizationsByUsers($limit = 10)
    {
        return static::with('organization')
            ->orderBy('total_users', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getAverageMetrics()
    {
        $totalOrganizations = static::count();
        
        if ($totalOrganizations === 0) {
            return [
                'avg_users' => 0,
                'avg_kwh' => 0,
                'avg_co2' => 0
            ];
        }
        
        return [
            'avg_users' => static::avg('total_users'),
            'avg_kwh' => static::avg('total_kwh_produced'),
            'avg_co2' => static::avg('total_co2_avoided')
        ];
    }

    public static function getFormattedAverageMetrics()
    {
        $averages = static::getAverageMetrics();
        
        return [
            'avg_users' => number_format($averages['avg_users'], 1) . ' usuarios',
            'avg_kwh' => number_format($averages['avg_kwh'], 2) . ' kWh',
            'avg_co2' => number_format($averages['avg_co2'], 2) . ' kg CO₂'
        ];
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        // Actualizar timestamp cuando se modifican las métricas
        static::updating(function ($communityMetrics) {
            if ($communityMetrics->isDirty(['total_users', 'total_kwh_produced', 'total_co2_avoided'])) {
                $communityMetrics->updated_at = Carbon::now();
            }
        });
    }
}
