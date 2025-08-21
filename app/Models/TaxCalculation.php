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
        'taxable_id',
        'taxable_type',
        'tax_type',
        'tax_rate',
        'taxable_amount',
        'tax_amount',
        'tax_percentage',
        'exempt_amount',
        'exemption_reason',
        'tax_year',
        'tax_period',
        'calculation_date',
        'due_date',
        'paid_date',
        'payment_method',
        'payment_reference',
        'status',
        'notes',
        'external_reference',
        'created_by',
        'approved_by',
        'approved_at',
        'filing_date',
        'filing_reference',
        'audit_status',
        'audit_notes',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:4',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'exempt_amount' => 'decimal:2',
        'calculation_date' => 'datetime',
        'due_date' => 'datetime',
        'paid_date' => 'datetime',
        'approved_at' => 'datetime',
        'filing_date' => 'datetime',
    ];

    // Enums
    const TAX_TYPE_INCOME = 'income';
    const TAX_TYPE_VAT = 'vat';
    const TAX_TYPE_ENERGY = 'energy';
    const TAX_TYPE_CARBON = 'carbon';
    const TAX_TYPE_PROPERTY = 'property';
    const TAX_TYPE_TRANSACTION = 'transaction';
    const TAX_TYPE_IMPORT = 'import';
    const TAX_TYPE_EXPORT = 'export';

    const STATUS_PENDING = 'pending';
    const STATUS_CALCULATED = 'calculated';
    const STATUS_FILED = 'filed';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_AUDITED = 'audited';
    const STATUS_DISPUTED = 'disputed';

    const AUDIT_STATUS_PENDING = 'pending';
    const AUDIT_STATUS_IN_PROGRESS = 'in_progress';
    const AUDIT_STATUS_COMPLETED = 'completed';
    const AUDIT_STATUS_ISSUES_FOUND = 'issues_found';
    const AUDIT_STATUS_APPROVED = 'approved';
    const AUDIT_STATUS_REJECTED = 'rejected';

    public static function getTaxTypes(): array
    {
        return [
            self::TAX_TYPE_INCOME => 'Impuesto sobre la Renta',
            self::TAX_TYPE_VAT => 'IVA',
            self::TAX_TYPE_ENERGY => 'Impuesto sobre la Energía',
            self::TAX_TYPE_CARBON => 'Impuesto sobre el Carbono',
            self::TAX_TYPE_PROPERTY => 'Impuesto sobre la Propiedad',
            self::TAX_TYPE_TRANSACTION => 'Impuesto sobre Transacciones',
            self::TAX_TYPE_IMPORT => 'Impuesto de Importación',
            self::TAX_TYPE_EXPORT => 'Impuesto de Exportación',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_CALCULATED => 'Calculado',
            self::STATUS_FILED => 'Presentado',
            self::STATUS_PAID => 'Pagado',
            self::STATUS_OVERDUE => 'Vencido',
            self::STATUS_REFUNDED => 'Reembolsado',
            self::STATUS_AUDITED => 'Auditado',
            self::STATUS_DISPUTED => 'Disputado',
        ];
    }

    public static function getAuditStatuses(): array
    {
        return [
            self::AUDIT_STATUS_PENDING => 'Pendiente',
            self::AUDIT_STATUS_IN_PROGRESS => 'En Progreso',
            self::AUDIT_STATUS_COMPLETED => 'Completado',
            self::AUDIT_STATUS_ISSUES_FOUND => 'Problemas Encontrados',
            self::AUDIT_STATUS_APPROVED => 'Aprobado',
            self::AUDIT_STATUS_REJECTED => 'Rechazado',
        ];
    }

    // Relaciones
    public function taxable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByTaxYear($query, $year)
    {
        return $query->where('tax_year', $year);
    }

    public function scopeByTaxPeriod($query, $period)
    {
        return $query->where('tax_period', $period);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('calculation_date', [$startDate, $endDate]);
    }

    public function scopeByDueDate($query, $date)
    {
        return $query->whereDate('due_date', $date);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_REFUNDED]);
    }

    public function scopeDueSoon($query, $days = 30)
    {
        $dueDate = now()->addDays($days);
        return $query->where('due_date', '<=', $dueDate)
                    ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_REFUNDED]);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', [self::STATUS_PAID, self::STATUS_REFUNDED]);
    }

    public function scopeByAuditStatus($query, $auditStatus)
    {
        return $query->where('audit_status', $auditStatus);
    }

    public function scopePendingAudit($query)
    {
        return $query->where('audit_status', self::AUDIT_STATUS_PENDING);
    }

    public function scopeAuditInProgress($query)
    {
        return $query->where('audit_status', self::AUDIT_STATUS_IN_PROGRESS);
    }

    public function scopeAuditCompleted($query)
    {
        return $query->where('audit_status', self::AUDIT_STATUS_COMPLETED);
    }

    public function scopeIncome($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_INCOME);
    }

    public function scopeVAT($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_VAT);
    }

    public function scopeEnergy($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_ENERGY);
    }

    public function scopeCarbon($query)
    {
        return $query->where('tax_type', self::TAX_TYPE_CARBON);
    }

    // Métodos
    public function isIncome(): bool
    {
        return $this->tax_type === self::TAX_TYPE_INCOME;
    }

    public function isVAT(): bool
    {
        return $this->tax_type === self::TAX_TYPE_VAT;
    }

    public function isEnergy(): bool
    {
        return $this->tax_type === self::TAX_TYPE_ENERGY;
    }

    public function isCarbon(): bool
    {
        return $this->tax_type === self::TAX_TYPE_CARBON;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCalculated(): bool
    {
        return $this->status === self::STATUS_CALCULATED;
    }

    public function isFiled(): bool
    {
        return $this->status === self::STATUS_FILED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isAudited(): bool
    {
        return $this->status === self::STATUS_AUDITED;
    }

    public function isDisputed(): bool
    {
        return $this->status === self::STATUS_DISPUTED;
    }

    public function isDueSoon(int $days = 30): bool
    {
        if ($this->isPaid() || $this->isRefunded()) {
            return false;
        }
        
        if (!$this->due_date) {
            return false;
        }
        
        $dueSoonDate = now()->addDays($days);
        return $this->due_date->isBefore($dueSoonDate);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->due_date || $this->isPaid() || $this->isRefunded()) {
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

    public function getNetAmount(): float
    {
        return $this->taxable_amount - $this->exempt_amount;
    }

    public function getTotalAmount(): float
    {
        return $this->taxable_amount + $this->tax_amount;
    }

    public function getExemptionPercentage(): float
    {
        if ($this->taxable_amount <= 0) {
            return 0;
        }
        
        return ($this->exempt_amount / $this->taxable_amount) * 100;
    }

    public function getTaxPercentage(): float
    {
        if ($this->taxable_amount <= 0) {
            return 0;
        }
        
        return ($this->tax_amount / $this->taxable_amount) * 100;
    }

    public function getLatePaymentPenalty(): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        $daysOverdue = $this->getDaysOverdue();
        $penaltyRate = 0.05; // 5% por mes
        
        return $this->tax_amount * ($penaltyRate * ($daysOverdue / 30));
    }

    public function getTotalAmountWithPenalty(): float
    {
        return $this->getTotalAmount() + $this->getLatePaymentPenalty();
    }

    public function getAuditStatusClass(): string
    {
        return match($this->audit_status) {
            self::AUDIT_STATUS_PENDING => 'Pendiente',
            self::AUDIT_STATUS_IN_PROGRESS => 'En Progreso',
            self::AUDIT_STATUS_COMPLETED => 'Completado',
            self::AUDIT_STATUS_ISSUES_FOUND => 'Problemas Encontrados',
            self::AUDIT_STATUS_APPROVED => 'Aprobado',
            self::AUDIT_STATUS_REJECTED => 'Rechazado',
            default => 'Desconocido',
        };
    }

    public function getFormattedTaxRate(): string
    {
        return number_format($this->tax_rate, 2) . '%';
    }

    public function getFormattedTaxableAmount(): string
    {
        return '€' . number_format($this->taxable_amount, 2);
    }

    public function getFormattedTaxAmount(): string
    {
        return '€' . number_format($this->tax_amount, 2);
    }

    public function getFormattedExemptAmount(): string
    {
        return '€' . number_format($this->exempt_amount, 2);
    }

    public function getFormattedNetAmount(): string
    {
        return '€' . number_format($this->getNetAmount(), 2);
    }

    public function getFormattedTotalAmount(): string
    {
        return '€' . number_format($this->getTotalAmount(), 2);
    }

    public function getFormattedLatePaymentPenalty(): string
    {
        return '€' . number_format($this->getLatePaymentPenalty(), 2);
    }

    public function getFormattedTotalAmountWithPenalty(): string
    {
        return '€' . number_format($this->getTotalAmountWithPenalty(), 2);
    }

    public function getFormattedCalculationDate(): string
    {
        if (!$this->calculation_date) {
            return 'No especificada';
        }
        
        return $this->calculation_date->format('d/m/Y H:i:s');
    }

    public function getFormattedDueDate(): string
    {
        if (!$this->due_date) {
            return 'No especificada';
        }
        
        return $this->due_date->format('d/m/Y H:i:s');
    }

    public function getFormattedPaidDate(): string
    {
        if (!$this->paid_date) {
            return 'No pagado';
        }
        
        return $this->paid_date->format('d/m/Y H:i:s');
    }

    public function getFormattedFilingDate(): string
    {
        if (!$this->filing_date) {
            return 'No presentado';
        }
        
        return $this->filing_date->format('d/m/Y H:i:s');
    }

    public function getFormattedTaxType(): string
    {
        return self::getTaxTypes()[$this->tax_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedAuditStatus(): string
    {
        return self::getAuditStatuses()[$this->audit_status] ?? 'Desconocido';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-gray-100 text-gray-800',
            self::STATUS_CALCULATED => 'bg-blue-100 text-blue-800',
            self::STATUS_FILED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_PAID => 'bg-green-100 text-green-800',
            self::STATUS_OVERDUE => 'bg-red-100 text-red-800',
            self::STATUS_REFUNDED => 'bg-purple-100 text-purple-800',
            self::STATUS_AUDITED => 'bg-indigo-100 text-indigo-800',
            self::STATUS_DISPUTED => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTaxTypeBadgeClass(): string
    {
        return match($this->tax_type) {
            self::TAX_TYPE_INCOME => 'bg-blue-100 text-blue-800',
            self::TAX_TYPE_VAT => 'bg-green-100 text-green-800',
            self::TAX_TYPE_ENERGY => 'bg-yellow-100 text-yellow-800',
            self::TAX_TYPE_CARBON => 'bg-red-100 text-red-800',
            self::TAX_TYPE_PROPERTY => 'bg-purple-100 text-purple-800',
            self::TAX_TYPE_TRANSACTION => 'bg-indigo-100 text-indigo-800',
            self::TAX_TYPE_IMPORT => 'bg-orange-100 text-orange-800',
            self::TAX_TYPE_EXPORT => 'bg-cyan-100 text-cyan-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getAuditStatusBadgeClass(): string
    {
        return match($this->audit_status) {
            self::AUDIT_STATUS_PENDING => 'bg-gray-100 text-gray-800',
            self::AUDIT_STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::AUDIT_STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::AUDIT_STATUS_ISSUES_FOUND => 'bg-yellow-100 text-yellow-800',
            self::AUDIT_STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::AUDIT_STATUS_REJECTED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getDueDateBadgeClass(): string
    {
        if ($this->isPaid() || $this->isRefunded()) {
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
}
