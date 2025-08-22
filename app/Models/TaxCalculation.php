<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxCalculation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'calculation_number',
        'name',
        'description',
        'tax_type',
        'calculation_type',
        'status',
        'priority',
        'entity_id',
        'entity_type',
        'transaction_id',
        'transaction_type',
        'tax_period_start',
        'tax_period_end',
        'calculation_date',
        'due_date',
        'payment_date',
        'taxable_amount',
        'tax_rate',
        'tax_amount',
        'tax_base_amount',
        'exemption_amount',
        'deduction_amount',
        'credit_amount',
        'net_tax_amount',
        'penalty_amount',
        'interest_amount',
        'total_amount_due',
        'amount_paid',
        'amount_remaining',
        'currency',
        'exchange_rate',
        'tax_jurisdiction',
        'tax_authority',
        'tax_registration_number',
        'tax_filing_frequency',
        'tax_filing_method',
        'is_estimated',
        'is_final',
        'is_amended',
        'amendment_reason',
        'calculation_notes',
        'review_notes',
        'approval_notes',
        'calculation_details',
        'tax_breakdown',
        'supporting_documents',
        'audit_trail',
        'tags',
        'calculated_by',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'applied_by',
        'applied_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'tax_period_start' => 'date',
        'tax_period_end' => 'date',
        'calculation_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'taxable_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_base_amount' => 'decimal:2',
        'exemption_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'net_tax_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_remaining' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'is_estimated' => 'boolean',
        'is_final' => 'boolean',
        'is_amended' => 'boolean',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'applied_at' => 'datetime',
        'calculation_details' => 'array',
        'tax_breakdown' => 'array',
        'supporting_documents' => 'array',
        'audit_trail' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const TAX_TYPE_INCOME_TAX = 'income_tax';
    const TAX_TYPE_SALES_TAX = 'sales_tax';
    const TAX_TYPE_VALUE_ADDED_TAX = 'value_added_tax';
    const TAX_TYPE_PROPERTY_TAX = 'property_tax';
    const TAX_TYPE_EXCISE_TAX = 'excise_tax';
    const TAX_TYPE_CUSTOMS_DUTY = 'customs_duty';
    const TAX_TYPE_ENERGY_TAX = 'energy_tax';
    const TAX_TYPE_CARBON_TAX = 'carbon_tax';
    const TAX_TYPE_ENVIRONMENTAL_TAX = 'environmental_tax';
    const TAX_TYPE_OTHER = 'other';

    const CALCULATION_TYPE_AUTOMATIC = 'automatic';
    const CALCULATION_TYPE_MANUAL = 'manual';
    const CALCULATION_TYPE_SCHEDULED = 'scheduled';
    const CALCULATION_TYPE_EVENT_TRIGGERED = 'event_triggered';
    const CALCULATION_TYPE_BATCH = 'batch';
    const CALCULATION_TYPE_REAL_TIME = 'real_time';
    const CALCULATION_TYPE_OTHER = 'other';

    const STATUS_DRAFT = 'draft';
    const STATUS_CALCULATED = 'calculated';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_APPROVED = 'approved';
    const STATUS_APPLIED = 'applied';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ERROR = 'error';

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    public static function getTaxTypes(): array
    {
        return [
            self::TAX_TYPE_INCOME_TAX => 'Impuesto sobre la Renta',
            self::TAX_TYPE_SALES_TAX => 'Impuesto sobre Ventas',
            self::TAX_TYPE_VALUE_ADDED_TAX => 'IVA',
            self::TAX_TYPE_PROPERTY_TAX => 'Impuesto sobre la Propiedad',
            self::TAX_TYPE_EXCISE_TAX => 'Impuesto de Consumo',
            self::TAX_TYPE_CUSTOMS_DUTY => 'Arancel de Aduanas',
            self::TAX_TYPE_ENERGY_TAX => 'Impuesto sobre la Energía',
            self::TAX_TYPE_CARBON_TAX => 'Impuesto sobre el Carbono',
            self::TAX_TYPE_ENVIRONMENTAL_TAX => 'Impuesto Ambiental',
            self::TAX_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getCalculationTypes(): array
    {
        return [
            self::CALCULATION_TYPE_AUTOMATIC => 'Automático',
            self::CALCULATION_TYPE_MANUAL => 'Manual',
            self::CALCULATION_TYPE_SCHEDULED => 'Programado',
            self::CALCULATION_TYPE_EVENT_TRIGGERED => 'Activado por Evento',
            self::CALCULATION_TYPE_BATCH => 'Por Lotes',
            self::CALCULATION_TYPE_REAL_TIME => 'Tiempo Real',
            self::CALCULATION_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_CALCULATED => 'Calculado',
            self::STATUS_REVIEWED => 'Revisado',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_APPLIED => 'Aplicado',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_ERROR => 'Error',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            self::PRIORITY_CRITICAL => 'Crítica',
        ];
    }

    // Relaciones
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function transaction(): MorphTo
    {
        return $this->morphTo();
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByTaxType($query, $taxType)
    {
        return $query->where('tax_type', $taxType);
    }

    public function scopeByCalculationType($query, $calculationType)
    {
        return $query->where('calculation_type', $calculationType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByEntity($query, $entityId, $entityType = null)
    {
        $query = $query->where('entity_id', $entityId);
        if ($entityType) {
            $query->where('entity_type', $entityType);
        }
        return $query;
    }

    public function scopeByTransaction($query, $transactionId, $transactionType = null)
    {
        $query = $query->where('transaction_id', $transactionId);
        if ($transactionType) {
            $query->where('transaction_type', $transactionType);
        }
        return $query;
    }

    public function scopeByTaxPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('tax_period_start', [$startDate, $endDate])
                    ->orWhereBetween('tax_period_end', [$startDate, $endDate]);
    }

    public function scopeByCalculationDate($query, $date)
    {
        return $query->whereDate('calculation_date', $date);
    }

    public function scopeByDueDate($query, $date)
    {
        return $query->whereDate('due_date', $date);
    }

    public function scopeByPaymentDate($query, $date)
    {
        return $query->whereDate('payment_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('calculation_date', [$startDate, $endDate]);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeCalculated($query)
    {
        return $query->where('status', self::STATUS_CALCULATED);
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', self::STATUS_REVIEWED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeApplied($query)
    {
        return $query->where('status', self::STATUS_APPLIED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeError($query)
    {
        return $query->where('status', self::STATUS_ERROR);
    }

    public function scopeIncomeTax($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_INCOME_TAX);
    }

    public function scopeSalesTax($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_SALES_TAX);
    }

    public function scopeValueAddedTax($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_VALUE_ADDED_TAX);
    }

    public function scopePropertyTax($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_PROPERTY_TAX);
    }

    public function scopeExciseTax($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_EXCISE_TAX);
    }

    public function scopeCustomsDuty($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_CUSTOMS_DUTY);
    }

    public function scopeEnergyTax($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_ENERGY_TAX);
    }

    public function scopeCarbonTax($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_CARBON_TAX);
    }

    public function scopeEnvironmentalTax($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_ENVIRONMENTAL_TAX);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('calculation_type', self::CALCULATION_TYPE_AUTOMATIC);
    }

    public function scopeManual($query)
    {
        return $query->where('calculation_type', self::CALCULATION_TYPE_MANUAL);
    }

    public function scopeScheduled($query)
    {
        return $query->where('calculation_type', self::CALCULATION_TYPE_SCHEDULED);
    }

    public function scopeEventTriggered($query)
    {
        return $query->where('calculation_type', self::CALCULATION_TYPE_EVENT_TRIGGERED);
    }

    public function scopeBatch($query)
    {
        return $query->where('calculation_type', self::CALCULATION_TYPE_BATCH);
    }

    public function scopeRealTime($query)
    {
        return $query->where('calculation_type', self::CALCULATION_TYPE_REAL_TIME);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ]);
    }

    public function scopeLowPriority($query)
    {
        return $query->where('priority', self::PRIORITY_LOW);
    }

    public function scopeNormalPriority($query)
    {
        return $query->where('priority', self::PRIORITY_NORMAL);
    }

    public function scopeEstimated($query)
    {
        return $query->where('is_estimated', true);
    }

    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    public function scopeAmended($query)
    {
        return $query->where('is_amended', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_APPLIED, self::STATUS_CANCELLED]);
    }

    public function scopeDueSoon($query, $days = 30)
    {
        $dueDate = now()->addDays($days);
        return $query->where('due_date', '<=', $dueDate)
                    ->whereNotIn('status', [self::STATUS_APPLIED, self::STATUS_CANCELLED]);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_APPLIED);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', [self::STATUS_APPLIED, self::STATUS_CANCELLED]);
    }

    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeByTaxJurisdiction($query, $jurisdiction)
    {
        return $query->where('tax_jurisdiction', $jurisdiction);
    }

    public function scopeByTaxAuthority($query, $authority)
    {
        return $query->where('tax_authority', $authority);
    }

    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('total_amount_due', [$minAmount, $maxAmount]);
    }

    public function scopeByTaxRateRange($query, $minRate, $maxRate)
    {
        return $query->whereBetween('tax_rate', [$minRate, $maxRate]);
    }

    // Métodos de validación
    public function isIncomeTax(): bool
    {
        return $this->tax_type === self::TAX_TYPE_INCOME_TAX;
    }

    public function isSalesTax(): bool
    {
        return $this->tax_type === self::TAX_TYPE_SALES_TAX;
    }

    public function isValueAddedTax(): bool
    {
        return $this->tax_type === self::TAX_TYPE_VALUE_ADDED_TAX;
    }

    public function isPropertyTax(): bool
    {
        return $this->tax_type === self::TAX_TYPE_PROPERTY_TAX;
    }

    public function isExciseTax(): bool
    {
        return $this->tax_type === self::TAX_TYPE_EXCISE_TAX;
    }

    public function isCustomsDuty(): bool
    {
        return $this->tax_type === self::TAX_TYPE_CUSTOMS_DUTY;
    }

    public function isEnergyTax(): bool
    {
        return $this->tax_type === self::TAX_TYPE_ENERGY_TAX;
    }

    public function isCarbonTax(): bool
    {
        return $this->tax_type === self::TAX_TYPE_CARBON_TAX;
    }

    public function isEnvironmentalTax(): bool
    {
        return $this->tax_type === self::TAX_TYPE_ENVIRONMENTAL_TAX;
    }

    public function isAutomatic(): bool
    {
        return $this->calculation_type === self::CALCULATION_TYPE_AUTOMATIC;
    }

    public function isManual(): bool
    {
        return $this->calculation_type === self::CALCULATION_TYPE_MANUAL;
    }

    public function isScheduled(): bool
    {
        return $this->calculation_type === self::CALCULATION_TYPE_SCHEDULED;
    }

    public function isEventTriggered(): bool
    {
        return $this->calculation_type === self::CALCULATION_TYPE_EVENT_TRIGGERED;
    }

    public function isBatch(): bool
    {
        return $this->calculation_type === self::CALCULATION_TYPE_BATCH;
    }

    public function isRealTime(): bool
    {
        return $this->calculation_type === self::CALCULATION_TYPE_REAL_TIME;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isCalculated(): bool
    {
        return $this->status === self::STATUS_CALCULATED;
    }

    public function isReviewed(): bool
    {
        return $this->status === self::STATUS_REVIEWED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isApplied(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    public function isHighPriority(): bool
    {
        return in_array($this->priority, [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ]);
    }

    public function isLowPriority(): bool
    {
        return $this->priority === self::PRIORITY_LOW;
    }

    public function isNormalPriority(): bool
    {
        return $this->priority === self::PRIORITY_NORMAL;
    }

    public function isEstimated(): bool
    {
        return $this->is_estimated;
    }

    public function isFinal(): bool
    {
        return $this->is_final;
    }

    public function isAmended(): bool
    {
        return $this->is_amended;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && 
               !in_array($this->status, [self::STATUS_APPLIED, self::STATUS_CANCELLED]);
    }

    public function isDueSoon(int $days = 30): bool
    {
        if ($this->isApplied() || $this->isCancelled()) {
            return false;
        }
        
        if (!$this->due_date) {
            return false;
        }
        
        $dueSoonDate = now()->addDays($days);
        return $this->due_date->isBefore($dueSoonDate);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public function isUnpaid(): bool
    {
        return !in_array($this->status, [self::STATUS_APPLIED, self::STATUS_CANCELLED]);
    }

    // Métodos de cálculo
    public function getDaysOverdue(): int
    {
        if (!$this->due_date || $this->isPaid() || $this->isCancelled()) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->due_date, false));
    }

    public function getDaysUntilDue(): int
    {
        if (!$this->due_date) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    public function getEffectiveTaxRate(): float
    {
        if ($this->taxable_amount <= 0) {
            return 0;
        }
        
        return ($this->tax_amount / $this->taxable_amount) * 100;
    }

    public function getNetTaxableAmount(): float
    {
        return $this->taxable_amount - $this->exemption_amount - $this->deduction_amount;
    }

    public function getTotalAmountWithPenaltyAndInterest(): float
    {
        return $this->total_amount_due + $this->penalty_amount + $this->interest_amount;
    }

    public function getPaymentPercentage(): float
    {
        if ($this->total_amount_due <= 0) {
            return 0;
        }
        
        return min(100, ($this->amount_paid / $this->total_amount_due) * 100);
    }

    public function getRemainingPercentage(): float
    {
        return 100 - $this->getPaymentPercentage();
    }

    public function getTaxPeriodDuration(): int
    {
        if (!$this->tax_period_start || !$this->tax_period_end) {
            return 0;
        }
        
        return $this->tax_period_start->diffInDays($this->tax_period_end) + 1;
    }

    public function getCalculationAge(): int
    {
        if (!$this->calculation_date) {
            return 0;
        }
        
        return now()->diffInDays($this->calculation_date);
    }

    // Métodos de formato
    public function getFormattedTaxType(): string
    {
        return self::getTaxTypes()[$this->tax_type] ?? 'Desconocido';
    }

    public function getFormattedCalculationType(): string
    {
        return self::getCalculationTypes()[$this->calculation_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority] ?? 'Desconocida';
    }

    public function getFormattedTaxPeriodStart(): string
    {
        return $this->tax_period_start->format('d/m/Y');
    }

    public function getFormattedTaxPeriodEnd(): string
    {
        return $this->tax_period_end->format('d/m/Y');
    }

    public function getFormattedCalculationDate(): string
    {
        return $this->calculation_date->format('d/m/Y');
    }

    public function getFormattedDueDate(): string
    {
        return $this->due_date ? $this->due_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedPaymentDate(): string
    {
        return $this->payment_date ? $this->payment_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedTaxableAmount(): string
    {
        return $this->currency . ' ' . number_format($this->taxable_amount, 2);
    }

    public function getFormattedTaxRate(): string
    {
        return number_format($this->tax_rate, 2) . '%';
    }

    public function getFormattedTaxAmount(): string
    {
        return $this->currency . ' ' . number_format($this->tax_amount, 2);
    }

    public function getFormattedTaxBaseAmount(): string
    {
        if (!$this->tax_base_amount) {
            return 'N/A';
        }
        return $this->currency . ' ' . number_format($this->tax_base_amount, 2);
    }

    public function getFormattedExemptionAmount(): string
    {
        return $this->currency . ' ' . number_format($this->exemption_amount, 2);
    }

    public function getFormattedDeductionAmount(): string
    {
        return $this->currency . ' ' . number_format($this->deduction_amount, 2);
    }

    public function getFormattedCreditAmount(): string
    {
        return $this->currency . ' ' . number_format($this->credit_amount, 2);
    }

    public function getFormattedNetTaxAmount(): string
    {
        return $this->currency . ' ' . number_format($this->net_tax_amount, 2);
    }

    public function getFormattedPenaltyAmount(): string
    {
        return $this->currency . ' ' . number_format($this->penalty_amount, 2);
    }

    public function getFormattedInterestAmount(): string
    {
        return $this->currency . ' ' . number_format($this->interest_amount, 2);
    }

    public function getFormattedTotalAmountDue(): string
    {
        return $this->currency . ' ' . number_format($this->total_amount_due, 2);
    }

    public function getFormattedAmountPaid(): string
    {
        return $this->currency . ' ' . number_format($this->amount_paid, 2);
    }

    public function getFormattedAmountRemaining(): string
    {
        return $this->currency . ' ' . number_format($this->amount_remaining, 2);
    }

    public function getFormattedExchangeRate(): string
    {
        return number_format($this->exchange_rate, 6);
    }

    public function getFormattedNetTaxableAmount(): string
    {
        return $this->currency . ' ' . number_format($this->getNetTaxableAmount(), 2);
    }

    public function getFormattedTotalAmountWithPenaltyAndInterest(): string
    {
        return $this->currency . ' ' . number_format($this->getTotalAmountWithPenaltyAndInterest(), 2);
    }

    public function getFormattedPaymentPercentage(): string
    {
        return number_format($this->getPaymentPercentage(), 1) . '%';
    }

    public function getFormattedRemainingPercentage(): string
    {
        return number_format($this->getRemainingPercentage(), 1) . '%';
    }

    public function getFormattedTaxPeriodDuration(): string
    {
        $duration = $this->getTaxPeriodDuration();
        if ($duration <= 0) {
            return 'N/A';
        }
        
        if ($duration < 30) {
            return $duration . ' días';
        } elseif ($duration < 365) {
            return number_format($duration / 30, 1) . ' meses';
        } else {
            return number_format($duration / 365, 1) . ' años';
        }
    }

    public function getFormattedCalculationAge(): string
    {
        $age = $this->getCalculationAge();
        if ($age <= 0) {
            return 'Hoy';
        }
        
        if ($age < 30) {
            return $age . ' días';
        } elseif ($age < 365) {
            return number_format($age / 30, 1) . ' meses';
        } else {
            return number_format($age / 365, 1) . ' años';
        }
    }

    public function getFormattedDaysOverdue(): string
    {
        $days = $this->getDaysOverdue();
        if ($days <= 0) {
            return 'No vencido';
        }
        
        return $days . ' días';
    }

    public function getFormattedDaysUntilDue(): string
    {
        $days = $this->getDaysUntilDue();
        if ($days <= 0) {
            return 'Vencido';
        }
        
        return $days . ' días';
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_CALCULATED => 'bg-blue-100 text-blue-800',
            self::STATUS_REVIEWED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_APPLIED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            self::STATUS_ERROR => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTaxTypeBadgeClass(): string
    {
        return match($this->tax_type) {
            self::TAX_TYPE_INCOME_TAX => 'bg-blue-100 text-blue-800',
            self::TAX_TYPE_SALES_TAX => 'bg-green-100 text-green-800',
            self::TAX_TYPE_VALUE_ADDED_TAX => 'bg-purple-100 text-purple-800',
            self::TAX_TYPE_PROPERTY_TAX => 'bg-indigo-100 text-indigo-800',
            self::TAX_TYPE_EXCISE_TAX => 'bg-yellow-100 text-yellow-800',
            self::TAX_TYPE_CUSTOMS_DUTY => 'bg-orange-100 text-orange-800',
            self::TAX_TYPE_ENERGY_TAX => 'bg-cyan-100 text-cyan-800',
            self::TAX_TYPE_CARBON_TAX => 'bg-red-100 text-red-800',
            self::TAX_TYPE_ENVIRONMENTAL_TAX => 'bg-teal-100 text-teal-800',
            self::TAX_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getCalculationTypeBadgeClass(): string
    {
        return match($this->calculation_type) {
            self::CALCULATION_TYPE_AUTOMATIC => 'bg-green-100 text-green-800',
            self::CALCULATION_TYPE_MANUAL => 'bg-blue-100 text-blue-800',
            self::CALCULATION_TYPE_SCHEDULED => 'bg-yellow-100 text-yellow-800',
            self::CALCULATION_TYPE_EVENT_TRIGGERED => 'bg-purple-100 text-purple-800',
            self::CALCULATION_TYPE_BATCH => 'bg-indigo-100 text-indigo-800',
            self::CALCULATION_TYPE_REAL_TIME => 'bg-cyan-100 text-cyan-800',
            self::CALCULATION_TYPE_OTHER => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'bg-gray-100 text-gray-800',
            self::PRIORITY_NORMAL => 'bg-blue-100 text-blue-800',
            self::PRIORITY_HIGH => 'bg-yellow-100 text-yellow-800',
            self::PRIORITY_URGENT => 'bg-orange-100 text-orange-800',
            self::PRIORITY_CRITICAL => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getDueDateBadgeClass(): string
    {
        if ($this->isPaid() || $this->isCancelled()) {
            return 'bg-green-100 text-green-800';
        }
        
        if ($this->isOverdue()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isDueSoon(7)) { // 7 días
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isDueSoon(30)) { // 30 días
            return 'bg-yellow-100 text-yellow-800';
        }
        
        return 'bg-green-100 text-green-800';
    }

    public function getEstimatedBadgeClass(): string
    {
        return $this->is_estimated ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800';
    }

    public function getFinalBadgeClass(): string
    {
        return $this->is_final ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
    }

    public function getAmendedBadgeClassClass(): string
    {
        return $this->is_amended ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800';
    }
}
