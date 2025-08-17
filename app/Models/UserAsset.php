<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class UserAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'start_date',
        'end_date',
        'source_type',
        'status',
        'current_value',
        'purchase_price',
        'daily_yield',
        'total_yield_generated',
        'efficiency_rating',
        'maintenance_cost',
        'last_maintenance_date',
        'next_maintenance_date',
        'auto_reinvest',
        'reinvest_threshold',
        'reinvest_percentage',
        'is_transferable',
        'is_delegatable',
        'delegated_to_user_id',
        'metadata',
        'estimated_annual_return',
        'actual_annual_return',
        'performance_history',
        'notifications_enabled',
        'alert_preferences',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'start_date' => 'date',
        'end_date' => 'date',
        'current_value' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'daily_yield' => 'decimal:4',
        'total_yield_generated' => 'decimal:4',
        'efficiency_rating' => 'decimal:2',
        'maintenance_cost' => 'decimal:2',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'auto_reinvest' => 'boolean',
        'reinvest_threshold' => 'decimal:2',
        'reinvest_percentage' => 'decimal:2',
        'is_transferable' => 'boolean',
        'is_delegatable' => 'boolean',
        'metadata' => 'array',
        'estimated_annual_return' => 'decimal:4',
        'actual_annual_return' => 'decimal:4',
        'performance_history' => 'array',
        'notifications_enabled' => 'boolean',
        'alert_preferences' => 'array',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function delegatedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_to_user_id');
    }

    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(BalanceTransaction::class, 'related_model_id')
            ->where('related_model_type', self::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->whereHas('product', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    public function scopeExpiring($query, int $days = 30)
    {
        return $query->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', now());
    }

    public function scopeAutoReinvest($query)
    {
        return $query->where('auto_reinvest', true);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source_type', $source);
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->where('next_maintenance_date', '<=', now());
    }

    // Métodos de negocio
    public function generateDailyYield(): float
    {
        if (!$this->isActive() || !$this->daily_yield) {
            return 0;
        }

        $yield = $this->daily_yield * $this->quantity;
        
        // Aplicar eficiencia
        if ($this->efficiency_rating) {
            $yield *= ($this->efficiency_rating / 100);
        }

        // Restar costes de mantenimiento diarios
        if ($this->maintenance_cost) {
            $dailyMaintenanceCost = $this->maintenance_cost / 365;
            $yield = max(0, $yield - $dailyMaintenanceCost);
        }

        return $yield;
    }

    public function addYield(float $amount): void
    {
        $this->increment('total_yield_generated', $amount);

        // Crear transacción de balance
        $balance = Balance::firstOrCreate([
            'user_id' => $this->user_id,
            'type' => $this->getBalanceType(),
            'currency' => 'EUR',
        ]);

        BalanceTransaction::create([
            'balance_id' => $balance->id,
            'type' => 'income',
            'amount' => $amount,
            'balance_before' => $balance->amount,
            'balance_after' => $balance->amount + $amount,
            'description' => "Rendimiento diario de {$this->product->name}",
            'related_model_type' => self::class,
            'related_model_id' => $this->id,
            'net_amount' => $amount,
        ]);

        $balance->increment('amount', $amount);

        // Auto-reinvertir si está configurado
        if ($this->shouldAutoReinvest()) {
            $this->processAutoReinvest();
        }
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }

    public function isExpiring(int $days = 30): bool
    {
        return $this->end_date && 
               $this->end_date->isFuture() && 
               $this->end_date->diffInDays(now()) <= $days;
    }

    public function calculateCurrentValue(): float
    {
        // Valor basado en el precio actual del producto
        $currentPrice = $this->product->base_sale_price ?? $this->product->base_purchase_price;
        
        if (!$currentPrice) {
            return $this->purchase_price ?? 0;
        }

        $baseValue = $currentPrice * $this->quantity;

        // Aplicar depreciación si es necesario
        if ($this->product->estimated_lifespan_years) {
            $ageInYears = $this->start_date->diffInYears(now());
            $depreciationRate = min(1, $ageInYears / $this->product->estimated_lifespan_years);
            $baseValue *= (1 - ($depreciationRate * 0.5)); // 50% de depreciación máxima
        }

        return $baseValue;
    }

    public function updateCurrentValue(): void
    {
        $this->current_value = $this->calculateCurrentValue();
        $this->save();
    }

    public function calculateROI(): float
    {
        if (!$this->purchase_price) {
            return 0;
        }

        $totalReturn = $this->total_yield_generated + ($this->current_value - $this->purchase_price);
        return ($totalReturn / $this->purchase_price) * 100;
    }

    public function shouldAutoReinvest(): bool
    {
        if (!$this->auto_reinvest || !$this->reinvest_threshold) {
            return false;
        }

        $balance = Balance::where([
            'user_id' => $this->user_id,
            'type' => $this->getBalanceType(),
        ])->first();

        return $balance && $balance->amount >= $this->reinvest_threshold;
    }

    public function processAutoReinvest(): void
    {
        if (!$this->shouldAutoReinvest()) {
            return;
        }

        $balance = Balance::where([
            'user_id' => $this->user_id,
            'type' => $this->getBalanceType(),
        ])->first();

        $reinvestAmount = $balance->amount * ($this->reinvest_percentage / 100);
        
        // Aquí iría la lógica para comprar más del mismo producto
        // Por ahora solo registramos la intención
        
        // TODO: Implementar compra automática
    }

    public function transfer(User $toUser, float $quantity = null): bool
    {
        if (!$this->is_transferable) {
            return false;
        }

        $transferQuantity = $quantity ?? $this->quantity;

        if ($transferQuantity > $this->quantity) {
            return false;
        }

        // Crear nuevo asset para el receptor
        $newAsset = $this->replicate();
        $newAsset->user_id = $toUser->id;
        $newAsset->quantity = $transferQuantity;
        $newAsset->source_type = 'transfer';
        $newAsset->save();

        // Reducir cantidad del asset original
        if ($transferQuantity == $this->quantity) {
            $this->status = 'transferred';
            $this->save();
        } else {
            $this->decrement('quantity', $transferQuantity);
        }

        return true;
    }

    public function needsMaintenance(): bool
    {
        return $this->next_maintenance_date && 
               $this->next_maintenance_date->isPast();
    }

    public function scheduleMaintenance(Carbon $date, float $cost = null): void
    {
        $this->next_maintenance_date = $date;
        if ($cost !== null) {
            $this->maintenance_cost = $cost;
        }
        $this->save();
    }

    private function getBalanceType(): string
    {
        return match ($this->product->type) {
            'energy_kwh' => 'energy_kwh',
            'mining_ths' => 'mining_ths',
            'storage_capacity' => 'storage_capacity',
            'production_right' => 'production_rights',
            default => 'wallet',
        };
    }

    // Constantes
    public const SOURCE_TYPES = [
        'purchase' => 'Compra',
        'transfer' => 'Transferencia',
        'bonus' => 'Bonificación',
        'donation' => 'Donación',
        'reward' => 'Recompensa',
        'conversion' => 'Conversión',
        'mining' => 'Minería',
        'production' => 'Producción',
        'referral' => 'Referido',
    ];

    public const STATUSES = [
        'active' => 'Activo',
        'expired' => 'Expirado',
        'pending' => 'Pendiente',
        'suspended' => 'Suspendido',
        'transferred' => 'Transferido',
    ];
}