<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'transferable_id',
        'transferable_type',
        'quantity_kwh',
        'transfer_type',
        'status',
        'scheduled_at',
        'executed_at',
        'cancelled_at',
        'rejected_at',
        'rejection_reason',
        'transfer_fee',
        'fee_type',
        'fee_amount',
        'notes',
        'external_reference',
        'approval_required',
        'approved_by',
        'approved_at',
        'approval_notes',
        'transfer_conditions',
        'energy_source',
        'renewable_percentage',
        'carbon_footprint',
        'delivery_location',
        'delivery_time',
        'priority_level',
    ];

    protected $casts = [
        'quantity_kwh' => 'decimal:4',
        'scheduled_at' => 'datetime',
        'executed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rejected_at' => 'datetime',
        'transfer_fee' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'approval_required' => 'boolean',
        'approved_at' => 'datetime',
        'transfer_conditions' => 'array',
        'delivery_location' => 'array',
        'delivery_time' => 'array',
        'priority_level' => 'integer',
    ];

    // Enums
    const TRANSFER_TYPE_GIFT = 'gift';
    const TRANSFER_TYPE_SALE = 'sale';
    const TRANSFER_TYPE_LOAN = 'loan';
    const TRANSFER_TYPE_RENTAL = 'rental';
    const TRANSFER_TYPE_DONATION = 'donation';
    const TRANSFER_TYPE_COMPENSATION = 'compensation';
    const TRANSFER_TYPE_REWARD = 'reward';
    const TRANSFER_TYPE_REFUND = 'refund';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FAILED = 'failed';

    const FEE_TYPE_PERCENTAGE = 'percentage';
    const FEE_TYPE_FIXED = 'fixed';
    const FEE_TYPE_TIERED = 'tiered';
    const FEE_TYPE_NONE = 'none';

    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_URGENT = 4;

    public static function getTransferTypes(): array
    {
        return [
            self::TRANSFER_TYPE_GIFT => 'Regalo',
            self::TRANSFER_TYPE_SALE => 'Venta',
            self::TRANSFER_TYPE_LOAN => 'Préstamo',
            self::TRANSFER_TYPE_RENTAL => 'Alquiler',
            self::TRANSFER_TYPE_DONATION => 'Donación',
            self::TRANSFER_TYPE_COMPENSATION => 'Compensación',
            self::TRANSFER_TYPE_REWARD => 'Recompensa',
            self::TRANSFER_TYPE_REFUND => 'Reembolso',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_SCHEDULED => 'Programado',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_REJECTED => 'Rechazado',
            self::STATUS_FAILED => 'Fallido',
        ];
    }

    public static function getFeeTypes(): array
    {
        return [
            self::FEE_TYPE_PERCENTAGE => 'Porcentaje',
            self::FEE_TYPE_FIXED => 'Fijo',
            self::FEE_TYPE_TIERED => 'Por Escalones',
            self::FEE_TYPE_NONE => 'Sin Cargo',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
        ];
    }

    // Relaciones
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function transferable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('transfer_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByFromUser($query, $userId)
    {
        return $query->where('from_user_id', $userId);
    }

    public function scopeByToUser($query, $userId)
    {
        return $query->where('to_user_id', $userId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('from_user_id', $userId)
              ->orWhere('to_user_id', $userId);
        });
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('scheduled_at', [$startDate, $endDate]);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority_level', [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT
        ]);
    }

    public function scopeRequiresApproval($query)
    {
        return $query->where('approval_required', true);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_required', true)
                    ->where('status', self::STATUS_PENDING);
    }

    public function scopeGift($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_GIFT);
    }

    public function scopeSale($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_SALE);
    }

    public function scopeDonation($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_DONATION);
    }

    // Métodos
    public function isGift(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_GIFT;
    }

    public function isSale(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_SALE;
    }

    public function isLoan(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_LOAN;
    }

    public function isRental(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_RENTAL;
    }

    public function isDonation(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_DONATION;
    }

    public function isCompensation(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_COMPENSATION;
    }

    public function isReward(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_REWARD;
    }

    public function isRefund(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_REFUND;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isScheduledFor(): bool
    {
        return $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    public function isOverdue(): bool
    {
        return $this->scheduled_at && $this->scheduled_at->isPast() && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_REJECTED]);
    }

    public function isHighPriority(): bool
    {
        return in_array($this->priority_level, [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT
        ]);
    }

    public function isUrgent(): bool
    {
        return $this->priority_level === self::PRIORITY_URGENT;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_SCHEDULED,
        ]);
    }

    public function canBeRejected(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
        ]);
    }

    public function canBeExecuted(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_SCHEDULED,
        ]);
    }

    public function getTransferFee(): float
    {
        if ($this->fee_type === self::FEE_TYPE_PERCENTAGE) {
            return ($this->quantity_kwh * $this->transfer_fee) / 100;
        } elseif ($this->fee_type === self::FEE_TYPE_FIXED) {
            return $this->fee_amount;
        } elseif ($this->fee_type === self::FEE_TYPE_TIERED) {
            // Implementar lógica de escalones
            return $this->fee_amount;
        }
        
        return 0;
    }

    public function getTotalAmount(): float
    {
        return $this->quantity_kwh + $this->getTransferFee();
    }

    public function getDaysUntilScheduled(): ?int
    {
        if (!$this->scheduled_at) {
            return null;
        }
        
        return now()->diffInDays($this->scheduled_at, false);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->scheduled_at, false));
    }

    public function getRenewablePercentage(): float
    {
        return $this->renewable_percentage ?? 0;
    }

    public function getCarbonFootprint(): float
    {
        return $this->carbon_footprint ?? 0;
    }

    public function getFormattedQuantity(): string
    {
        return number_format($this->quantity_kwh, 2) . ' kWh';
    }

    public function getFormattedTransferFee(): string
    {
        return '€' . number_format($this->getTransferFee(), 2);
    }

    public function getFormattedTotalAmount(): string
    {
        return '€' . number_format($this->getTotalAmount(), 2);
    }

    public function getFormattedScheduledAt(): string
    {
        if (!$this->scheduled_at) {
            return 'No programado';
        }
        
        return $this->scheduled_at->format('d/m/Y H:i:s');
    }

    public function getFormattedExecutedAt(): string
    {
        if (!$this->executed_at) {
            return 'No ejecutado';
        }
        
        return $this->executed_at->format('d/m/Y H:i:s');
    }

    public function getFormattedCancelledAt(): string
    {
        if (!$this->cancelled_at) {
            return 'No cancelado';
        }
        
        return $this->cancelled_at->format('d/m/Y H:i:s');
    }

    public function getFormattedRejectedAt(): string
    {
        if (!$this->rejected_at) {
            return 'No rechazado';
        }
        
        return $this->rejected_at->format('d/m/Y H:i:s');
    }

    public function getFormattedTransferType(): string
    {
        return self::getTransferTypes()[$this->transfer_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedFeeType(): string
    {
        return self::getFeeTypes()[$this->fee_type] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority_level] ?? 'Desconocido';
    }

    public function getFormattedRenewablePercentage(): string
    {
        return number_format($this->getRenewablePercentage(), 1) . '%';
    }

    public function getFormattedCarbonFootprint(): string
    {
        return number_format($this->getCarbonFootprint(), 4) . ' kg CO₂/kWh';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_APPROVED => 'bg-blue-100 text-blue-800',
            self::STATUS_SCHEDULED => 'bg-purple-100 text-purple-800',
            self::STATUS_IN_PROGRESS => 'bg-indigo-100 text-indigo-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::STATUS_FAILED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTransferTypeBadgeClass(): string
    {
        return match($this->transfer_type) {
            self::TRANSFER_TYPE_GIFT => 'bg-green-100 text-green-800',
            self::TRANSFER_TYPE_SALE => 'bg-blue-100 text-blue-800',
            self::TRANSFER_TYPE_LOAN => 'bg-yellow-100 text-yellow-800',
            self::TRANSFER_TYPE_RENTAL => 'bg-purple-100 text-purple-800',
            self::TRANSFER_TYPE_DONATION => 'bg-pink-100 text-pink-800',
            self::TRANSFER_TYPE_COMPENSATION => 'bg-orange-100 text-orange-800',
            self::TRANSFER_TYPE_REWARD => 'bg-indigo-100 text-indigo-800',
            self::TRANSFER_TYPE_REFUND => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->priority_level) {
            self::PRIORITY_LOW => 'bg-gray-100 text-gray-800',
            self::PRIORITY_NORMAL => 'bg-blue-100 text-blue-800',
            self::PRIORITY_HIGH => 'bg-yellow-100 text-yellow-800',
            self::PRIORITY_URGENT => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getScheduledBadgeClass(): string
    {
        if ($this->isCompleted() || $this->isCancelled() || $this->isRejected()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->isOverdue()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isScheduled()) {
            $daysUntil = $this->getDaysUntilScheduled();
            if ($daysUntil <= 1) {
                return 'bg-red-100 text-red-800';
            } elseif ($daysUntil <= 7) {
                return 'bg-yellow-100 text-yellow-800';
            }
        }
        
        return 'bg-green-100 text-green-800';
    }
}
