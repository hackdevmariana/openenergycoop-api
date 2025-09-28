<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class EnergyParticipation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_code',                  // Código del plan (raiz, hogar, independencia_22, etc.)
        'monthly_amount',             // Cuota mensual (o null si es aportación única)
        'one_time_amount',            // Solo si es tipo aportación única
        'start_date',                 // Fecha de inicio del plan
        'end_date',                   // Fecha de finalización (si aplica)
        'status',                     // active, suspended, cancelled, completed
        'fidelity_years',             // Años acumulados sin interrupción
        'energy_rights_daily',        // kWh/día generados hasta ahora
        'energy_rights_total_kwh',    // kWh acumulados totales
        'notes',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'one_time_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'energy_rights_daily' => 'decimal:3',
        'energy_rights_total_kwh' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes para el estado
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    // Constantes para códigos de plan
    const PLAN_RAIZ = 'raiz';
    const PLAN_HOGAR = 'hogar';
    const PLAN_INDEPENDENCIA_22 = 'independencia_22';
    const PLAN_INDEPENDENCIA_25 = 'independencia_25';
    const PLAN_INDEPENDENCIA_30 = 'independencia_30';

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(EnergyContribution::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPlanCode($query, string $planCode)
    {
        return $query->where('plan_code', $planCode);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeMonthly($query)
    {
        return $query->whereNotNull('monthly_amount');
    }

    public function scopeOneTime($query)
    {
        return $query->whereNotNull('one_time_amount');
    }

    public function scopeWithEndDate($query)
    {
        return $query->whereNotNull('end_date');
    }

    public function scopeWithoutEndDate($query)
    {
        return $query->whereNull('end_date');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('end_date', '<=', now()->addDays($days))
                    ->where('end_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<=', now());
    }

    // Accessors
    public function getPlanLabelAttribute(): string
    {
        return match($this->plan_code) {
            self::PLAN_RAIZ => 'Raíz',
            self::PLAN_HOGAR => 'Hogar',
            self::PLAN_INDEPENDENCIA_22 => 'Independencia 22',
            self::PLAN_INDEPENDENCIA_25 => 'Independencia 25',
            self::PLAN_INDEPENDENCIA_30 => 'Independencia 30',
            default => 'Desconocido'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_SUSPENDED => 'Suspendido',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_COMPLETED => 'Completado',
            default => 'Desconocido'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => '#10B981',    // Verde
            self::STATUS_SUSPENDED => '#F59E0B', // Naranja
            self::STATUS_CANCELLED => '#EF4444', // Rojo
            self::STATUS_COMPLETED => '#3B82F6', // Azul
            default => '#6B7280'                 // Gris
        };
    }

    public function getTotalAmountAttribute(): float
    {
        if ($this->one_time_amount) {
            return $this->one_time_amount;
        }
        
        if ($this->monthly_amount && $this->start_date) {
            $months = $this->getTotalMonthsAttribute();
            return $this->monthly_amount * $months;
        }
        
        return 0;
    }

    public function getTotalMonthsAttribute(): int
    {
        if (!$this->start_date) {
            return 0;
        }
        
        $endDate = $this->end_date ?? now();
        return $this->start_date->diffInMonths($endDate) + 1;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->end_date && $this->end_date->isFuture() && $this->end_date->diffInDays(now()) <= 30;
    }

    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }
        return $this->end_date->diffInDays(now(), false);
    }

    public function getExpirationStatusAttribute(): string
    {
        if (!$this->end_date) {
            return 'no_expiration';
        }

        if ($this->is_expired) {
            return 'expired';
        }

        if ($this->is_expiring_soon) {
            return 'expiring_soon';
        }

        return 'active';
    }

    public function getIsMonthlyAttribute(): bool
    {
        return !is_null($this->monthly_amount);
    }

    public function getIsOneTimeAttribute(): bool
    {
        return !is_null($this->one_time_amount);
    }

    public function getTotalContributionsAttribute(): float
    {
        return $this->contributions()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->total_contributions;
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }
        return round(($this->total_contributions / $this->total_amount) * 100, 2);
    }

    // Métodos de negocio
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function canBeSuspended(): bool
    {
        return $this->isActive();
    }

    public function canBeCancelled(): bool
    {
        return $this->isActive() || $this->isSuspended();
    }

    public function canBeCompleted(): bool
    {
        return $this->isActive() && $this->completion_percentage >= 100;
    }

    public function suspend(): void
    {
        if ($this->canBeSuspended()) {
            $this->status = self::STATUS_SUSPENDED;
            $this->save();
        }
    }

    public function cancel(): void
    {
        if ($this->canBeCancelled()) {
            $this->status = self::STATUS_CANCELLED;
            $this->save();
        }
    }

    public function complete(): void
    {
        if ($this->canBeCompleted()) {
            $this->status = self::STATUS_COMPLETED;
            $this->save();
        }
    }

    public function activate(): void
    {
        if ($this->isSuspended()) {
            $this->status = self::STATUS_ACTIVE;
            $this->save();
        }
    }

    public function addEnergyRights(float $kwh): void
    {
        $this->energy_rights_total_kwh += $kwh;
        $this->save();
    }

    public function updateDailyEnergyRights(float $kwhPerDay): void
    {
        $this->energy_rights_daily = $kwhPerDay;
        $this->save();
    }

    public function getParticipationSummary(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null,
            'plan_code' => $this->plan_code,
            'plan_label' => $this->plan_label,
            'monthly_amount' => $this->monthly_amount,
            'one_time_amount' => $this->one_time_amount,
            'total_amount' => $this->total_amount,
            'total_months' => $this->total_months,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'fidelity_years' => $this->fidelity_years,
            'energy_rights_daily' => $this->energy_rights_daily,
            'energy_rights_total_kwh' => $this->energy_rights_total_kwh,
            'total_contributions' => $this->total_contributions,
            'remaining_amount' => $this->remaining_amount,
            'completion_percentage' => $this->completion_percentage,
            'is_monthly' => $this->is_monthly,
            'is_one_time' => $this->is_one_time,
            'is_expired' => $this->is_expired,
            'is_expiring_soon' => $this->is_expiring_soon,
            'days_until_expiration' => $this->days_until_expiration,
            'expiration_status' => $this->expiration_status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    // Métodos estáticos
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_SUSPENDED => 'Suspendido',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_COMPLETED => 'Completado',
        ];
    }

    public static function getPlanCodes(): array
    {
        return [
            self::PLAN_RAIZ => 'Raíz',
            self::PLAN_HOGAR => 'Hogar',
            self::PLAN_INDEPENDENCIA_22 => 'Independencia 22',
            self::PLAN_INDEPENDENCIA_25 => 'Independencia 25',
            self::PLAN_INDEPENDENCIA_30 => 'Independencia 30',
        ];
    }

    public static function getParticipationsByPlan(string $planCode): \Illuminate\Database\Eloquent\Collection
    {
        return self::byPlanCode($planCode)->with('user')->get();
    }

    public static function getParticipationsByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return self::byStatus($status)->with('user')->get();
    }

    public static function getSystemSummary(): array
    {
        return [
            'total_participations' => self::count(),
            'by_status' => [
                'active' => self::active()->count(),
                'suspended' => self::suspended()->count(),
                'cancelled' => self::cancelled()->count(),
                'completed' => self::completed()->count(),
            ],
            'by_plan' => [
                'raiz' => self::byPlanCode(self::PLAN_RAIZ)->count(),
                'hogar' => self::byPlanCode(self::PLAN_HOGAR)->count(),
                'independencia_22' => self::byPlanCode(self::PLAN_INDEPENDENCIA_22)->count(),
                'independencia_25' => self::byPlanCode(self::PLAN_INDEPENDENCIA_25)->count(),
                'independencia_30' => self::byPlanCode(self::PLAN_INDEPENDENCIA_30)->count(),
            ],
            'monthly_participations' => self::monthly()->count(),
            'one_time_participations' => self::oneTime()->count(),
            'with_end_date' => self::withEndDate()->count(),
            'without_end_date' => self::withoutEndDate()->count(),
            'expiring_soon' => self::expiringSoon()->count(),
            'expired' => self::expired()->count(),
            'total_monthly_amount' => self::monthly()->sum('monthly_amount'),
            'total_one_time_amount' => self::oneTime()->sum('one_time_amount'),
            'total_energy_rights_kwh' => self::sum('energy_rights_total_kwh'),
            'average_fidelity_years' => self::avg('fidelity_years'),
        ];
    }

    public static function getExpiringParticipations(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return self::expiringSoon($days)->with('user')->get();
    }

    public static function getActiveParticipations(): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()->with('user')->get();
    }

    public static function getTotalMonthlyRevenue(): float
    {
        return self::active()->monthly()->sum('monthly_amount');
    }

    public static function getTotalOneTimeRevenue(): float
    {
        return self::oneTime()->sum('one_time_amount');
    }

    public static function getTotalEnergyRights(): float
    {
        return self::sum('energy_rights_total_kwh');
    }
}