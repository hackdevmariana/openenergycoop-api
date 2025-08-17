<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'currency',
        'is_frozen',
        'last_transaction_at',
        'daily_limit',
        'monthly_limit',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:6',
        'is_frozen' => 'boolean',
        'last_transaction_at' => 'datetime',
        'daily_limit' => 'decimal:2',
        'monthly_limit' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCurrency($query, string $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeActive($query)
    {
        return $query->where('is_frozen', false);
    }

    public function scopeWithPositiveBalance($query)
    {
        return $query->where('amount', '>', 0);
    }

    // Métodos de negocio
    public function addTransaction(
        string $type,
        float $amount,
        string $description,
        ?Model $relatedModel = null,
        array $metadata = []
    ): BalanceTransaction {
        if ($this->is_frozen && $type === 'expense') {
            throw new \Exception('Balance is frozen');
        }

        $balanceBefore = $this->amount;
        
        $transactionAmount = $type === 'expense' ? -abs($amount) : abs($amount);
        $balanceAfter = $balanceBefore + $transactionAmount;

        if ($balanceAfter < 0 && !$this->allowsNegativeBalance()) {
            throw new \Exception('Insufficient balance');
        }

        // Verificar límites diarios/mensuales
        if ($type === 'expense') {
            $this->checkLimits(abs($amount));
        }

        $transaction = BalanceTransaction::create([
            'balance_id' => $this->id,
            'type' => $type,
            'amount' => abs($amount),
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
            'related_model_type' => $relatedModel ? get_class($relatedModel) : null,
            'related_model_id' => $relatedModel ? $relatedModel->id : null,
            'net_amount' => abs($amount),
            'metadata' => $metadata,
        ]);

        $this->update([
            'amount' => $balanceAfter,
            'last_transaction_at' => now(),
        ]);

        return $transaction;
    }

    public function debit(float $amount, string $description, ?Model $relatedModel = null): BalanceTransaction
    {
        return $this->addTransaction('expense', $amount, $description, $relatedModel);
    }

    public function credit(float $amount, string $description, ?Model $relatedModel = null): BalanceTransaction
    {
        return $this->addTransaction('income', $amount, $description, $relatedModel);
    }

    public function transfer(Balance $toBalance, float $amount, string $description): array
    {
        if ($this->user_id === $toBalance->user_id) {
            throw new \Exception('Cannot transfer to same user');
        }

        if ($this->type !== $toBalance->type) {
            throw new \Exception('Balance types must match');
        }

        $batchId = \Illuminate\Support\Str::uuid();

        $outTransaction = $this->addTransaction(
            'transfer_out',
            $amount,
            $description,
            null,
            ['batch_id' => $batchId, 'to_user_id' => $toBalance->user_id]
        );

        $inTransaction = $toBalance->addTransaction(
            'transfer_in',
            $amount,
            $description,
            null,
            ['batch_id' => $batchId, 'from_user_id' => $this->user_id]
        );

        return [$outTransaction, $inTransaction];
    }

    public function getDailySpent(): float
    {
        return $this->transactions()
            ->whereDate('created_at', today())
            ->whereIn('type', ['expense', 'transfer_out'])
            ->sum('amount');
    }

    public function getMonthlySpent(): float
    {
        return $this->transactions()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('type', ['expense', 'transfer_out'])
            ->sum('amount');
    }

    public function getRemainingDailyLimit(): float
    {
        if (!$this->daily_limit) {
            return PHP_FLOAT_MAX;
        }

        return max(0, $this->daily_limit - $this->getDailySpent());
    }

    public function getRemainingMonthlyLimit(): float
    {
        if (!$this->monthly_limit) {
            return PHP_FLOAT_MAX;
        }

        return max(0, $this->monthly_limit - $this->getMonthlySpent());
    }

    public function freeze(string $reason = null): void
    {
        $this->update([
            'is_frozen' => true,
            'metadata' => array_merge($this->metadata ?? [], [
                'frozen_at' => now()->toISOString(),
                'freeze_reason' => $reason,
            ]),
        ]);
    }

    public function unfreeze(): void
    {
        $this->update([
            'is_frozen' => false,
            'metadata' => array_merge($this->metadata ?? [], [
                'unfrozen_at' => now()->toISOString(),
            ]),
        ]);
    }

    public function getFormattedAmount(): string
    {
        return match ($this->type) {
            'wallet' => number_format($this->amount, 2) . ' ' . $this->currency,
            'energy_kwh' => number_format($this->amount, 3) . ' kWh',
            'mining_ths' => number_format($this->amount, 3) . ' TH/s',
            'storage_capacity' => number_format($this->amount, 2) . ' kWh',
            'carbon_credits' => number_format($this->amount, 2) . ' tCO₂e',
            'production_rights' => number_format($this->amount, 3) . ' kWh',
            'loyalty_points' => number_format($this->amount, 0) . ' puntos',
            default => number_format($this->amount, 2),
        };
    }

    public function getHistoryForPeriod(string $period = '30d'): array
    {
        $days = match ($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '1y' => 365,
            default => 30,
        };

        return $this->transactions()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at')
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'amount' => $transaction->balance_after,
                    'change' => $transaction->type === 'income' ? $transaction->amount : -$transaction->amount,
                    'description' => $transaction->description,
                ];
            })
            ->toArray();
    }

    private function checkLimits(float $amount): void
    {
        if ($this->daily_limit && ($this->getDailySpent() + $amount) > $this->daily_limit) {
            throw new \Exception('Daily limit exceeded');
        }

        if ($this->monthly_limit && ($this->getMonthlySpent() + $amount) > $this->monthly_limit) {
            throw new \Exception('Monthly limit exceeded');
        }
    }

    private function allowsNegativeBalance(): bool
    {
        // Solo los balances de wallet pueden ser negativos (crédito)
        return $this->type === 'wallet';
    }

    // Métodos estáticos
    public static function getOrCreateForUser(int $userId, string $type, string $currency = 'EUR'): self
    {
        return static::firstOrCreate([
            'user_id' => $userId,
            'type' => $type,
            'currency' => $currency,
        ]);
    }

    // Constantes
    public const TYPES = [
        'wallet' => 'Cartera',
        'energy_kwh' => 'Energía kWh',
        'mining_ths' => 'Minería TH/s',
        'storage_capacity' => 'Capacidad de Almacenamiento',
        'carbon_credits' => 'Créditos de Carbono',
        'production_rights' => 'Derechos de Producción',
        'loyalty_points' => 'Puntos de Fidelidad',
    ];

    public const CURRENCIES = [
        'EUR' => 'Euro',
        'USD' => 'Dólar Estadounidense',
        'BTC' => 'Bitcoin',
        'ETH' => 'Ethereum',
    ];
}