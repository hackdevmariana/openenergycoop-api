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
        'transfer_number',
        'name',
        'description',
        'transfer_type',
        'status',
        'priority',
        'source_id',
        'source_type',
        'destination_id',
        'destination_type',
        'source_meter_id',
        'destination_meter_id',
        'transfer_amount_kwh',
        'transfer_amount_mwh',
        'transfer_rate_kw',
        'transfer_rate_mw',
        'transfer_unit',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_start_time',
        'actual_end_time',
        'completion_time',
        'duration_hours',
        'efficiency_percentage',
        'loss_percentage',
        'loss_amount_kwh',
        'net_transfer_amount_kwh',
        'net_transfer_amount_mwh',
        'cost_per_kwh',
        'total_cost',
        'currency',
        'exchange_rate',
        'transfer_method',
        'transfer_medium',
        'transfer_protocol',
        'is_automated',
        'requires_approval',
        'is_approved',
        'is_verified',
        'transfer_conditions',
        'safety_requirements',
        'quality_standards',
        'transfer_parameters',
        'monitoring_data',
        'alarm_settings',
        'event_logs',
        'performance_metrics',
        'tags',
        'scheduled_by',
        'initiated_by',
        'approved_by',
        'approved_at',
        'verified_by',
        'verified_at',
        'completed_by',
        'completed_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'transfer_amount_kwh' => 'decimal:2',
        'transfer_amount_mwh' => 'decimal:2',
        'transfer_rate_kw' => 'decimal:2',
        'transfer_rate_mw' => 'decimal:2',
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'completion_time' => 'datetime',
        'duration_hours' => 'decimal:2',
        'efficiency_percentage' => 'decimal:2',
        'loss_percentage' => 'decimal:2',
        'loss_amount_kwh' => 'decimal:2',
        'net_transfer_amount_kwh' => 'decimal:2',
        'net_transfer_amount_mwh' => 'decimal:2',
        'cost_per_kwh' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'is_automated' => 'boolean',
        'requires_approval' => 'boolean',
        'is_approved' => 'boolean',
        'is_verified' => 'boolean',
        'approved_at' => 'datetime',
        'verified_at' => 'datetime',
        'completed_at' => 'datetime',
        'transfer_parameters' => 'array',
        'monitoring_data' => 'array',
        'alarm_settings' => 'array',
        'event_logs' => 'array',
        'performance_metrics' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const TRANSFER_TYPE_GENERATION = 'generation';
    const TRANSFER_TYPE_CONSUMPTION = 'consumption';
    const TRANSFER_TYPE_STORAGE = 'storage';
    const TRANSFER_TYPE_GRID_IMPORT = 'grid_import';
    const TRANSFER_TYPE_GRID_EXPORT = 'grid_export';
    const TRANSFER_TYPE_PEER_TO_PEER = 'peer_to_peer';
    const TRANSFER_TYPE_VIRTUAL = 'virtual';
    const TRANSFER_TYPE_PHYSICAL = 'physical';
    const TRANSFER_TYPE_CONTRACTUAL = 'contractual';
    const TRANSFER_TYPE_OTHER = 'other';

    const STATUS_PENDING = 'pending';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_REVERSED = 'reversed';

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    public static function getTransferTypes(): array
    {
        return [
            self::TRANSFER_TYPE_GENERATION => 'Generación',
            self::TRANSFER_TYPE_CONSUMPTION => 'Consumo',
            self::TRANSFER_TYPE_STORAGE => 'Almacenamiento',
            self::TRANSFER_TYPE_GRID_IMPORT => 'Importación de Red',
            self::TRANSFER_TYPE_GRID_EXPORT => 'Exportación a Red',
            self::TRANSFER_TYPE_PEER_TO_PEER => 'P2P',
            self::TRANSFER_TYPE_VIRTUAL => 'Virtual',
            self::TRANSFER_TYPE_PHYSICAL => 'Físico',
            self::TRANSFER_TYPE_CONTRACTUAL => 'Contractual',
            self::TRANSFER_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_SCHEDULED => 'Programado',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_FAILED => 'Fallido',
            self::STATUS_ON_HOLD => 'En Espera',
            self::STATUS_REVERSED => 'Reversado',
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
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function destination(): MorphTo
    {
        return $this->morphTo();
    }

    public function sourceMeter(): BelongsTo
    {
        return $this->belongsTo(EnergyMeter::class, 'source_meter_id');
    }

    public function destinationMeter(): BelongsTo
    {
        return $this->belongsTo(EnergyMeter::class, 'destination_meter_id');
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByTransferType($query, $transferType)
    {
        return $query->where('transfer_type', $transferType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeBySource($query, $sourceId, $sourceType = null)
    {
        $query = $query->where('source_id', $sourceId);
        if ($sourceType) {
            $query->where('source_type', $sourceType);
        }
        return $query;
    }

    public function scopeByDestination($query, $destinationId, $destinationType = null)
    {
        $query = $query->where('destination_id', $destinationId);
        if ($destinationType) {
            $query->where('destination_type', $destinationType);
        }
        return $query;
    }

    public function scopeBySourceMeter($query, $meterId)
    {
        return $query->where('source_meter_id', $meterId);
    }

    public function scopeByDestinationMeter($query, $meterId)
    {
        return $query->where('destination_meter_id', $meterId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('scheduled_start_time', [$startDate, $endDate]);
    }

    public function scopeByScheduledStartTime($query, $date)
    {
        return $query->whereDate('scheduled_start_time', $date);
    }

    public function scopeByScheduledEndTime($query, $date)
    {
        return $query->whereDate('scheduled_end_time', $date);
    }

    public function scopeByActualStartTime($query, $date)
    {
        return $query->whereDate('actual_start_time', $date);
    }

    public function scopeByActualEndTime($query, $date)
    {
        return $query->whereDate('actual_end_time', $date);
    }

    public function scopeByCompletionTime($query, $date)
    {
        return $query->whereDate('completion_time', $date);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', self::STATUS_ON_HOLD);
    }

    public function scopeReversed($query)
    {
        return $query->where('status', self::STATUS_REVERSED);
    }

    public function scopeGeneration($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_GENERATION);
    }

    public function scopeConsumption($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_CONSUMPTION);
    }

    public function scopeStorage($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_STORAGE);
    }

    public function scopeGridImport($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_GRID_IMPORT);
    }

    public function scopeGridExport($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_GRID_EXPORT);
    }

    public function scopePeerToPeer($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_PEER_TO_PEER);
    }

    public function scopeVirtual($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_VIRTUAL);
    }

    public function scopePhysical($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_PHYSICAL);
    }

    public function scopeContractual($query)
    {
        return $query->where('transfer_type', self::TRANSFER_TYPE_CONTRACTUAL);
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

    public function scopeAutomated($query)
    {
        return $query->where('is_automated', true);
    }

    public function scopeManual($query)
    {
        return $query->where('is_automated', false);
    }

    public function scopeRequiresApproval($query)
    {
        return $query->where('requires_approval', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeNotApproved($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeNotVerified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('transfer_amount_kwh', [$minAmount, $maxAmount]);
    }

    public function scopeByCostRange($query, $minCost, $maxCost)
    {
        return $query->whereBetween('total_cost', [$minCost, $maxCost]);
    }

    public function scopeByEfficiencyRange($query, $minEfficiency, $maxEfficiency)
    {
        return $query->whereBetween('efficiency_percentage', [$minEfficiency, $maxEfficiency]);
    }

    public function scopeByLossRange($query, $minLoss, $maxLoss)
    {
        return $query->whereBetween('loss_percentage', [$minLoss, $maxLoss]);
    }

    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_end_time', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_FAILED]);
    }

    public function scopeDueSoon($query, $hours = 24)
    {
        $dueTime = now()->addHours($hours);
        return $query->where('scheduled_start_time', '<=', $dueTime)
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_FAILED]);
    }

    // Métodos de validación
    public function isGeneration(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_GENERATION;
    }

    public function isConsumption(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_CONSUMPTION;
    }

    public function isStorage(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_STORAGE;
    }

    public function isGridImport(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_GRID_IMPORT;
    }

    public function isGridExport(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_GRID_EXPORT;
    }

    public function isPeerToPeer(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_PEER_TO_PEER;
    }

    public function isVirtual(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_VIRTUAL;
    }

    public function isPhysical(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_PHYSICAL;
    }

    public function isContractual(): bool
    {
        return $this->transfer_type === self::TRANSFER_TYPE_CONTRACTUAL;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
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

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function isReversed(): bool
    {
        return $this->status === self::STATUS_REVERSED;
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

    public function isAutomated(): bool
    {
        return $this->is_automated;
    }

    public function isManual(): bool
    {
        return !$this->is_automated;
    }

    public function requiresApproval(): bool
    {
        return $this->requires_approval;
    }

    public function isApproved(): bool
    {
        return $this->is_approved;
    }

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function isScheduledFor(): bool
    {
        return $this->scheduled_start_time && $this->scheduled_start_time->isFuture();
    }

    public function isOverdue(): bool
    {
        return $this->scheduled_end_time && $this->scheduled_end_time->isPast() && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_FAILED]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_SCHEDULED,
        ]);
    }

    public function canBeStarted(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_SCHEDULED,
        ]);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    // Métodos de cálculo
    public function getTransferDuration(): float
    {
        if (!$this->scheduled_start_time || !$this->scheduled_end_time) {
            return 0;
        }
        
        return $this->scheduled_start_time->diffInHours($this->scheduled_end_time);
    }

    public function getActualDuration(): float
    {
        if (!$this->actual_start_time || !$this->actual_end_time) {
            return 0;
        }
        
        return $this->actual_start_time->diffInHours($this->actual_end_time);
    }

    public function getTimeToStart(): ?int
    {
        if (!$this->scheduled_start_time) {
            return null;
        }
        
        return now()->diffInSeconds($this->scheduled_start_time, false);
    }

    public function getTimeToEnd(): ?int
    {
        if (!$this->scheduled_end_time) {
            return null;
        }
        
        return now()->diffInSeconds($this->scheduled_end_time, false);
    }

    public function isStartingSoon(int $hours = 1): bool
    {
        $timeToStart = $this->getTimeToStart();
        if ($timeToStart === null) {
            return false;
        }
        
        return $timeToStart <= ($hours * 3600);
    }

    public function isEndingSoon(int $hours = 1): bool
    {
        $timeToEnd = $this->getTimeToEnd();
        if ($timeToEnd === null) {
            return false;
        }
        
        return $timeToEnd <= ($hours * 3600);
    }

    public function getEfficiencyPercentage(): float
    {
        return $this->efficiency_percentage ?? 0;
    }

    public function getLossPercentage(): float
    {
        return $this->loss_percentage ?? 0;
    }

    public function getLossAmountKwh(): float
    {
        return $this->loss_amount_kwh ?? 0;
    }

    public function getNetTransferAmountKwh(): float
    {
        return $this->net_transfer_amount_kwh ?? 0;
    }

    public function getNetTransferAmountMwh(): float
    {
        return $this->net_transfer_amount_mwh ?? 0;
    }

    public function getCostPerKwh(): float
    {
        return $this->cost_per_kwh ?? 0;
    }

    public function getTotalCost(): float
    {
        return $this->total_cost ?? 0;
    }

    public function getTransferAmountKwh(): float
    {
        return $this->transfer_amount_kwh ?? 0;
    }

    public function getTransferAmountMwh(): float
    {
        return $this->transfer_amount_mwh ?? 0;
    }

    public function getTransferRateKw(): float
    {
        return $this->transfer_rate_kw ?? 0;
    }

    public function getTransferRateMw(): float
    {
        return $this->transfer_rate_mw ?? 0;
    }

    public function getDurationHours(): float
    {
        return $this->duration_hours ?? 0;
    }

    public function getExchangeRate(): float
    {
        return $this->exchange_rate ?? 1;
    }

    // Métodos de formato
    public function getFormattedTransferType(): string
    {
        return self::getTransferTypes()[$this->transfer_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority] ?? 'Desconocida';
    }

    public function getFormattedScheduledStartTime(): string
    {
        return $this->scheduled_start_time->format('d/m/Y H:i:s');
    }

    public function getFormattedScheduledEndTime(): string
    {
        return $this->scheduled_end_time->format('d/m/Y H:i:s');
    }

    public function getFormattedActualStartTime(): string
    {
        return $this->actual_start_time ? $this->actual_start_time->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedActualEndTime(): string
    {
        return $this->actual_end_time ? $this->actual_end_time->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedCompletionTime(): string
    {
        return $this->completion_time ? $this->completion_time->format('d/m/Y H:i:s') : 'N/A';
    }

    public function getFormattedTransferAmountKwh(): string
    {
        return number_format($this->getTransferAmountKwh(), 2) . ' kWh';
    }

    public function getFormattedTransferAmountMwh(): string
    {
        return number_format($this->getTransferAmountMwh(), 2) . ' MWh';
    }

    public function getFormattedTransferRateKw(): string
    {
        if (!$this->getTransferRateKw()) {
            return 'N/A';
        }
        return number_format($this->getTransferRateKw(), 2) . ' kW';
    }

    public function getFormattedTransferRateMw(): string
    {
        if (!$this->getTransferRateMw()) {
            return 'N/A';
        }
        return number_format($this->getTransferRateMw(), 2) . ' MW';
    }

    public function getFormattedNetTransferAmountKwh(): string
    {
        return number_format($this->getNetTransferAmountKwh(), 2) . ' kWh';
    }

    public function getFormattedNetTransferAmountMwh(): string
    {
        return number_format($this->getNetTransferAmountMwh(), 2) . ' MWh';
    }

    public function getFormattedLossAmountKwh(): string
    {
        if (!$this->getLossAmountKwh()) {
            return 'N/A';
        }
        return number_format($this->getLossAmountKwh(), 2) . ' kWh';
    }

    public function getFormattedEfficiencyPercentage(): string
    {
        if (!$this->getEfficiencyPercentage()) {
            return 'N/A';
        }
        return number_format($this->getEfficiencyPercentage(), 1) . '%';
    }

    public function getFormattedLossPercentage(): string
    {
        if (!$this->getLossPercentage()) {
            return 'N/A';
        }
        return number_format($this->getLossPercentage(), 1) . '%';
    }

    public function getFormattedCostPerKwh(): string
    {
        if (!$this->getCostPerKwh()) {
            return 'N/A';
        }
        return $this->currency . ' ' . number_format($this->getCostPerKwh(), 4) . '/kWh';
    }

    public function getFormattedTotalCost(): string
    {
        if (!$this->getTotalCost()) {
            return 'N/A';
        }
        return $this->currency . ' ' . number_format($this->getTotalCost(), 2);
    }

    public function getFormattedDurationHours(): string
    {
        if (!$this->getDurationHours()) {
            return 'N/A';
        }
        return number_format($this->getDurationHours(), 2) . ' horas';
    }

    public function getFormattedExchangeRate(): string
    {
        return number_format($this->getExchangeRate(), 6);
    }

    public function getFormattedTransferDuration(): string
    {
        $duration = $this->getTransferDuration();
        if ($duration <= 0) {
            return 'N/A';
        }
        
        if ($duration < 1) {
            return number_format($duration * 60, 0) . ' minutos';
        } elseif ($duration < 24) {
            return number_format($duration, 1) . ' horas';
        } else {
            return number_format($duration / 24, 1) . ' días';
        }
    }

    public function getFormattedActualDuration(): string
    {
        $duration = $this->getActualDuration();
        if ($duration <= 0) {
            return 'N/A';
        }
        
        if ($duration < 1) {
            return number_format($duration * 60, 0) . ' minutos';
        } elseif ($duration < 24) {
            return number_format($duration, 1) . ' horas';
        } else {
            return number_format($duration / 24, 1) . ' días';
        }
    }

    public function getFormattedTimeToStart(): string
    {
        $timeToStart = $this->getTimeToStart();
        if ($timeToStart === null) {
            return 'N/A';
        }
        
        if ($timeToStart <= 0) {
            return 'Ya inició';
        }
        
        if ($timeToStart < 3600) {
            return number_format($timeToStart / 60, 0) . ' minutos';
        } elseif ($timeToStart < 86400) {
            return number_format($timeToStart / 3600, 1) . ' horas';
        } else {
            return number_format($timeToStart / 86400, 1) . ' días';
        }
    }

    public function getFormattedTimeToEnd(): string
    {
        $timeToEnd = $this->getTimeToEnd();
        if ($timeToEnd === null) {
            return 'N/A';
        }
        
        if ($timeToEnd <= 0) {
            return 'Ya terminó';
        }
        
        if ($timeToEnd < 3600) {
            return number_format($timeToEnd / 60, 0) . ' minutos';
        } elseif ($timeToEnd < 86400) {
            return number_format($timeToEnd / 3600, 1) . ' horas';
        } else {
            return number_format($timeToEnd / 86400, 1) . ' días';
        }
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_SCHEDULED => 'bg-blue-100 text-blue-800',
            self::STATUS_IN_PROGRESS => 'bg-indigo-100 text-indigo-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
            self::STATUS_FAILED => 'bg-red-100 text-red-800',
            self::STATUS_ON_HOLD => 'bg-orange-100 text-orange-800',
            self::STATUS_REVERSED => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTransferTypeBadgeClass(): string
    {
        return match($this->transfer_type) {
            self::TRANSFER_TYPE_GENERATION => 'bg-green-100 text-green-800',
            self::TRANSFER_TYPE_CONSUMPTION => 'bg-red-100 text-red-800',
            self::TRANSFER_TYPE_STORAGE => 'bg-blue-100 text-blue-800',
            self::TRANSFER_TYPE_GRID_IMPORT => 'bg-yellow-100 text-yellow-800',
            self::TRANSFER_TYPE_GRID_EXPORT => 'bg-orange-100 text-orange-800',
            self::TRANSFER_TYPE_PEER_TO_PEER => 'bg-purple-100 text-purple-800',
            self::TRANSFER_TYPE_VIRTUAL => 'bg-indigo-100 text-indigo-800',
            self::TRANSFER_TYPE_PHYSICAL => 'bg-cyan-100 text-cyan-800',
            self::TRANSFER_TYPE_CONTRACTUAL => 'bg-teal-100 text-teal-800',
            self::TRANSFER_TYPE_OTHER => 'bg-gray-100 text-gray-800',
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

    public function getAutomatedBadgeClass(): string
    {
        return $this->is_automated ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
    }

    public function getApprovalBadgeClass(): string
    {
        if (!$this->requires_approval) {
            return 'bg-gray-100 text-gray-800';
        }
        
        return $this->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
    }

    public function getVerificationBadgeClass(): string
    {
        return $this->is_verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
    }

    public function getScheduledBadgeClass(): string
    {
        if ($this->isCompleted() || $this->isCancelled() || $this->isFailed()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->isOverdue()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isStartingSoon(1)) { // 1 hora
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isStartingSoon(24)) { // 24 horas
            return 'bg-yellow-100 text-yellow-800';
        }
        
        return 'bg-green-100 text-green-800';
    }

    public function getEfficiencyBadgeClass(): string
    {
        $efficiency = $this->getEfficiencyPercentage();
        if ($efficiency <= 0) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($efficiency >= 90) {
            return 'bg-green-100 text-green-800';
        } elseif ($efficiency >= 80) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($efficiency >= 70) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    public function getLossBadgeClass(): string
    {
        $loss = $this->getLossPercentage();
        if ($loss <= 0) {
            return 'bg-green-100 text-green-800';
        }
        
        if ($loss <= 5) {
            return 'bg-green-100 text-green-800';
        } elseif ($loss <= 10) {
            return 'bg-yellow-100 text-yellow-800';
        } elseif ($loss <= 20) {
            return 'bg-orange-100 text-orange-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }
}
