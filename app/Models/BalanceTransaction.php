<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BalanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'balance_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'reference',
        'related_model_type',
        'related_model_id',
        'batch_id',
        'exchange_rate',
        'original_currency',
        'original_amount',
        'tax_amount',
        'fee_amount',
        'net_amount',
        'metadata',
        'created_by_user_id',
        'status',
        'processed_at',
        'notes',
        'accounting_reference',
        'is_reconciled',
        'reconciled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:6',
        'balance_before' => 'decimal:6',
        'balance_after' => 'decimal:6',
        'exchange_rate' => 'decimal:6',
        'original_amount' => 'decimal:6',
        'tax_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:6',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'is_reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    // Relaciones
    public function balance(): BelongsTo
    {
        return $this->belongsTo(Balance::class);
    }

    public function relatedModel(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeIncome($query)
    {
        return $query->whereIn('type', ['income', 'transfer_in']);
    }

    public function scopeExpense($query)
    {
        return $query->whereIn('type', ['expense', 'transfer_out']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByBatch($query, string $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
    }

    // Métodos de negocio
    public function isIncome(): bool
    {
        return in_array($this->type, ['income', 'transfer_in']);
    }

    public function isExpense(): bool
    {
        return in_array($this->type, ['expense', 'transfer_out']);
    }

    public function getSignedAmount(): float
    {
        return $this->isIncome() ? $this->amount : -$this->amount;
    }

    public function getFormattedAmount(): string
    {
        $sign = $this->isIncome() ? '+' : '-';
        return $sign . $this->balance->getFormattedAmount();
    }

    public function process(): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Transaction is not pending');
        }

        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    public function cancel(string $reason = null): void
    {
        if ($this->status === 'completed') {
            throw new \Exception('Cannot cancel completed transaction');
        }

        $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);
    }

    public function fail(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }

    public function reconcile(string $accountingReference = null): void
    {
        $this->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
            'accounting_reference' => $accountingReference,
        ]);
    }

    public function calculateTax(float $rate): float
    {
        return $this->net_amount * ($rate / 100);
    }

    public function applyTax(float $taxAmount): void
    {
        $this->update([
            'tax_amount' => $taxAmount,
            'net_amount' => $this->amount - $taxAmount - $this->fee_amount,
        ]);
    }

    public function applyFee(float $feeAmount): void
    {
        $this->update([
            'fee_amount' => $feeAmount,
            'net_amount' => $this->amount - $this->tax_amount - $feeAmount,
        ]);
    }

    public function getBatchTransactions()
    {
        if (!$this->batch_id) {
            return collect([$this]);
        }

        return static::where('batch_id', $this->batch_id)->get();
    }

    public function createReversal(string $reason): self
    {
        if ($this->status !== 'completed') {
            throw new \Exception('Can only reverse completed transactions');
        }

        $reversalType = match ($this->type) {
            'income' => 'expense',
            'expense' => 'income',
            'transfer_in' => 'transfer_out',
            'transfer_out' => 'transfer_in',
            default => 'adjustment',
        };

        $reversal = static::create([
            'balance_id' => $this->balance_id,
            'type' => $reversalType,
            'amount' => $this->amount,
            'balance_before' => $this->balance->amount,
            'balance_after' => $this->balance->amount + $this->getSignedAmount(),
            'description' => "Reversión: {$this->description}",
            'reference' => $this->reference,
            'related_model_type' => $this->related_model_type,
            'related_model_id' => $this->related_model_id,
            'net_amount' => $this->amount,
            'metadata' => array_merge($this->metadata ?? [], [
                'reversal_of' => $this->id,
                'reversal_reason' => $reason,
            ]),
            'created_by_user_id' => auth()->id(),
        ]);

        $this->balance->update([
            'amount' => $reversal->balance_after,
            'last_transaction_at' => now(),
        ]);

        return $reversal;
    }

    public function exportForAccounting(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->created_at->format('Y-m-d'),
            'reference' => $this->reference ?? $this->id,
            'description' => $this->description,
            'account' => $this->balance->type,
            'debit' => $this->isExpense() ? $this->amount : 0,
            'credit' => $this->isIncome() ? $this->amount : 0,
            'tax_amount' => $this->tax_amount,
            'net_amount' => $this->net_amount,
            'currency' => $this->balance->currency,
            'user_id' => $this->balance->user_id,
            'status' => $this->status,
            'reconciled' => $this->is_reconciled,
        ];
    }

    // Constantes
    public const TYPES = [
        'income' => 'Ingreso',
        'expense' => 'Gasto',
        'transfer_in' => 'Transferencia Entrante',
        'transfer_out' => 'Transferencia Saliente',
        'adjustment' => 'Ajuste',
    ];

    public const STATUSES = [
        'pending' => 'Pendiente',
        'completed' => 'Completada',
        'failed' => 'Fallida',
        'cancelled' => 'Cancelada',
    ];
}