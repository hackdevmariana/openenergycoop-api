<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'task_type',
        'priority',
        'status',
        'assigned_to',
        'assigned_by',
        'due_date',
        'start_date',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'equipment_id',
        'equipment_type',
        'location_id',
        'location_type',
        'maintenance_schedule_id',
        'checklist_items',
        'required_tools',
        'required_parts',
        'safety_notes',
        'technical_notes',
        'cost_estimate',
        'actual_cost',
        'vendor_id',
        'warranty_work',
        'recurring',
        'recurrence_pattern',
        'next_recurrence_date',
        'attachments',
        'tags',
        'notes',
        'work_order_number',
        'department',
        'category',
        'subcategory',
        'risk_level',
        'completion_notes',
        'quality_score',
        'customer_feedback',
        'follow_up_required',
        'follow_up_date',
        'preventive_maintenance',
        'corrective_maintenance',
        'emergency_maintenance',
        'planned_maintenance',
        'unplanned_maintenance',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'start_date' => 'date',
        'completed_at' => 'datetime',
        'next_recurrence_date' => 'date',
        'follow_up_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'cost_estimate' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'quality_score' => 'integer',
        'warranty_work' => 'boolean',
        'recurring' => 'boolean',
        'follow_up_required' => 'boolean',
        'preventive_maintenance' => 'boolean',
        'corrective_maintenance' => 'boolean',
        'emergency_maintenance' => 'boolean',
        'planned_maintenance' => 'boolean',
        'unplanned_maintenance' => 'boolean',
        'approved_at' => 'datetime',
        'checklist_items' => 'array',
        'required_tools' => 'array',
        'required_parts' => 'array',
        'attachments' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const TASK_TYPE_INSPECTION = 'inspection';
    const TASK_TYPE_REPAIR = 'repair';
    const TASK_TYPE_REPLACEMENT = 'replacement';
    const TASK_TYPE_CALIBRATION = 'calibration';
    const TASK_TYPE_CLEANING = 'cleaning';
    const TASK_TYPE_LUBRICATION = 'lubrication';
    const TASK_TYPE_TESTING = 'testing';
    const TASK_TYPE_UPGRADE = 'upgrade';
    const TASK_TYPE_INSTALLATION = 'installation';
    const TASK_TYPE_DEMOLITION = 'demolition';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_WAITING_PARTS = 'waiting_parts';
    const STATUS_WAITING_APPROVAL = 'waiting_approval';

    const RISK_LEVEL_LOW = 'low';
    const RISK_LEVEL_MEDIUM = 'medium';
    const RISK_LEVEL_HIGH = 'high';
    const RISK_LEVEL_EXTREME = 'extreme';

    public static function getTaskTypes(): array
    {
        return [
            self::TASK_TYPE_INSPECTION => 'Inspección',
            self::TASK_TYPE_REPAIR => 'Reparación',
            self::TASK_TYPE_REPLACEMENT => 'Reemplazo',
            self::TASK_TYPE_CALIBRATION => 'Calibración',
            self::TASK_TYPE_CLEANING => 'Limpieza',
            self::TASK_TYPE_LUBRICATION => 'Lubricación',
            self::TASK_TYPE_TESTING => 'Pruebas',
            self::TASK_TYPE_UPGRADE => 'Actualización',
            self::TASK_TYPE_INSTALLATION => 'Instalación',
            self::TASK_TYPE_DEMOLITION => 'Demolición',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_MEDIUM => 'Media',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            self::PRIORITY_CRITICAL => 'Crítica',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_ASSIGNED => 'Asignada',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_ON_HOLD => 'En Espera',
            self::STATUS_COMPLETED => 'Completada',
            self::STATUS_CANCELLED => 'Cancelada',
            self::STATUS_OVERDUE => 'Vencida',
            self::STATUS_SCHEDULED => 'Programada',
            self::STATUS_WAITING_PARTS => 'Esperando Partes',
            self::STATUS_WAITING_APPROVAL => 'Esperando Aprobación',
        ];
    }

    public static function getRiskLevels(): array
    {
        return [
            self::RISK_LEVEL_LOW => 'Bajo',
            self::RISK_LEVEL_MEDIUM => 'Medio',
            self::RISK_LEVEL_HIGH => 'Alto',
            self::RISK_LEVEL_EXTREME => 'Extremo',
        ];
    }

    // Relaciones
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function equipment(): MorphTo
    {
        return $this->morphTo();
    }

    public function location(): MorphTo
    {
        return $this->morphTo();
    }

    public function maintenanceSchedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(MaintenanceTaskStatusLog::class, 'entity_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MaintenanceTaskAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MaintenanceTaskComment::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(MaintenanceTaskTimeLog::class);
    }

    public function costLogs(): HasMany
    {
        return $this->hasMany(MaintenanceTaskCostLog::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByTaskType($query, $taskType)
    {
        return $query->where('task_type', $taskType);
    }

    public function scopeByAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT, self::PRIORITY_CRITICAL]);
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', self::PRIORITY_CRITICAL);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', self::STATUS_ON_HOLD);
    }

    public function scopePreventive($query)
    {
        return $query->where('preventive_maintenance', true);
    }

    public function scopeCorrective($query)
    {
        return $query->where('corrective_maintenance', true);
    }

    public function scopeEmergency($query)
    {
        return $query->where('emergency_maintenance', true);
    }

    public function scopePlanned($query)
    {
        return $query->where('planned_maintenance', true);
    }

    public function scopeUnplanned($query)
    {
        return $query->where('unplanned_maintenance', true);
    }

    public function scopeByRiskLevel($query, $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', [self::RISK_LEVEL_HIGH, self::RISK_LEVEL_EXTREME]);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySubcategory($query, $subcategory)
    {
        return $query->where('subcategory', $subcategory);
    }

    public function scopeRecurring($query)
    {
        return $query->where('recurring', true);
    }

    public function scopeWarrantyWork($query)
    {
        return $query->where('warranty_work', true);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeFollowUpRequired($query)
    {
        return $query->where('follow_up_required', true);
    }

    // Métodos
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAssigned(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isWaitingParts(): bool
    {
        return $this->status === self::STATUS_WAITING_PARTS;
    }

    public function isWaitingApproval(): bool
    {
        return $this->status === self::STATUS_WAITING_APPROVAL;
    }

    public function isLowPriority(): bool
    {
        return $this->priority === self::PRIORITY_LOW;
    }

    public function isMediumPriority(): bool
    {
        return $this->priority === self::PRIORITY_MEDIUM;
    }

    public function isHighPriority(): bool
    {
        return $this->priority === self::PRIORITY_HIGH;
    }

    public function isUrgent(): bool
    {
        return $this->priority === self::PRIORITY_URGENT;
    }

    public function isCritical(): bool
    {
        return $this->priority === self::PRIORITY_CRITICAL;
    }

    public function isInspection(): bool
    {
        return $this->task_type === self::TASK_TYPE_INSPECTION;
    }

    public function isRepair(): bool
    {
        return $this->task_type === self::TASK_TYPE_REPAIR;
    }

    public function isReplacement(): bool
    {
        return $this->task_type === self::TASK_TYPE_REPLACEMENT;
    }

    public function isCalibration(): bool
    {
        return $this->task_type === self::TASK_TYPE_CALIBRATION;
    }

    public function isCleaning(): bool
    {
        return $this->task_type === self::TASK_TYPE_CLEANING;
    }

    public function isLubrication(): bool
    {
        return $this->task_type === self::TASK_TYPE_LUBRICATION;
    }

    public function isTesting(): bool
    {
        return $this->task_type === self::TASK_TYPE_TESTING;
    }

    public function isUpgrade(): bool
    {
        return $this->task_type === self::TASK_TYPE_UPGRADE;
    }

    public function isInstallation(): bool
    {
        return $this->task_type === self::TASK_TYPE_INSTALLATION;
    }

    public function isDemolition(): bool
    {
        return $this->task_type === self::TASK_TYPE_DEMOLITION;
    }

    public function isLowRisk(): bool
    {
        return $this->risk_level === self::RISK_LEVEL_LOW;
    }

    public function isMediumRisk(): bool
    {
        return $this->risk_level === self::RISK_LEVEL_MEDIUM;
    }

    public function isHighRisk(): bool
    {
        return $this->risk_level === self::RISK_LEVEL_HIGH;
    }

    public function isExtremeRisk(): bool
    {
        return $this->risk_level === self::RISK_LEVEL_EXTREME;
    }

    public function isOverdue(): bool
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return false;
        }
        
        return $this->due_date && $this->due_date->isPast();
    }

    public function isDueToday(): bool
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return false;
        }
        
        return $this->due_date && $this->due_date->isToday();
    }

    public function isDueThisWeek(): bool
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return false;
        }
        
        return $this->due_date && $this->due_date->between(
            now()->startOfWeek(),
            now()->endOfWeek()
        );
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function canStart(): bool
    {
        return in_array($this->status, [self::STATUS_ASSIGNED, self::STATUS_SCHEDULED]);
    }

    public function canComplete(): bool
    {
        return in_array($this->status, [self::STATUS_IN_PROGRESS, self::STATUS_ON_HOLD]);
    }

    public function canCancel(): bool
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function canPutOnHold(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canResume(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function getProgressPercentage(): int
    {
        if ($this->isCompleted()) {
            return 100;
        }
        
        if ($this->isPending() || $this->isAssigned()) {
            return 0;
        }
        
        if ($this->isInProgress()) {
            return 50;
        }
        
        if ($this->isOnHold()) {
            return 75;
        }
        
        return 0;
    }

    public function getTimeVariance(): ?float
    {
        if (!$this->estimated_hours || !$this->actual_hours) {
            return null;
        }
        
        return $this->actual_hours - $this->estimated_hours;
    }

    public function getTimeVariancePercentage(): ?float
    {
        if (!$this->estimated_hours || !$this->actual_hours) {
            return null;
        }
        
        return (($this->actual_hours - $this->estimated_hours) / $this->estimated_hours) * 100;
    }

    public function getCostVariance(): ?float
    {
        if (!$this->cost_estimate || !$this->actual_cost) {
            return null;
        }
        
        return $this->actual_cost - $this->cost_estimate;
    }

    public function getCostVariancePercentage(): ?float
    {
        if (!$this->cost_estimate || !$this->actual_cost) {
            return null;
        }
        
        return (($this->actual_cost - $this->cost_estimate) / $this->cost_estimate) * 100;
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date);
    }

    public function getDaysUntilDue(): int
    {
        if (!$this->due_date) {
            return 0;
        }
        
        if ($this->due_date->isPast()) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    public function getFormattedDueDate(): string
    {
        if (!$this->due_date) {
            return 'No establecida';
        }
        
        return $this->due_date->format('d/m/Y');
    }

    public function getFormattedStartDate(): string
    {
        if (!$this->start_date) {
            return 'No iniciada';
        }
        
        return $this->start_date->format('d/m/Y');
    }

    public function getFormattedCompletedDate(): string
    {
        if (!$this->completed_at) {
            return 'No completada';
        }
        
        return $this->completed_at->format('d/m/Y H:i');
    }

    public function getFormattedEstimatedHours(): string
    {
        if (!$this->estimated_hours) {
            return 'No estimado';
        }
        
        return number_format($this->estimated_hours, 2) . ' horas';
    }

    public function getFormattedActualHours(): string
    {
        if (!$this->actual_hours) {
            return 'No registrado';
        }
        
        return number_format($this->actual_hours, 2) . ' horas';
    }

    public function getFormattedCostEstimate(): string
    {
        if (!$this->cost_estimate) {
            return 'No estimado';
        }
        
        return '$' . number_format($this->cost_estimate, 2);
    }

    public function getFormattedActualCost(): string
    {
        if (!$this->actual_cost) {
            return 'No registrado';
        }
        
        return '$' . number_format($this->actual_cost, 2);
    }

    public function getFormattedTimeVariance(): string
    {
        $variance = $this->getTimeVariance();
        
        if ($variance === null) {
            return 'N/A';
        }
        
        $sign = $variance >= 0 ? '+' : '';
        return $sign . number_format($variance, 2) . ' horas';
    }

    public function getFormattedCostVariance(): string
    {
        $variance = $this->getCostVariance();
        
        if ($variance === null) {
            return 'N/A';
        }
        
        $sign = $variance >= 0 ? '+' : '';
        return $sign . '$' . number_format($variance, 2);
    }

    public function getFormattedTaskType(): string
    {
        return self::getTaskTypes()[$this->task_type] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority] ?? 'Desconocida';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedRiskLevel(): string
    {
        return self::getRiskLevels()[$this->risk_level] ?? 'Desconocido';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-gray-100 text-gray-800',
            self::STATUS_ASSIGNED => 'bg-blue-100 text-blue-800',
            self::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
            self::STATUS_ON_HOLD => 'bg-orange-100 text-orange-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            self::STATUS_OVERDUE => 'bg-red-100 text-red-800',
            self::STATUS_SCHEDULED => 'bg-indigo-100 text-indigo-800',
            self::STATUS_WAITING_PARTS => 'bg-purple-100 text-purple-800',
            self::STATUS_WAITING_APPROVAL => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'bg-gray-100 text-gray-800',
            self::PRIORITY_MEDIUM => 'bg-blue-100 text-blue-800',
            self::PRIORITY_HIGH => 'bg-yellow-100 text-yellow-800',
            self::PRIORITY_URGENT => 'bg-orange-100 text-orange-800',
            self::PRIORITY_CRITICAL => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTaskTypeBadgeClass(): string
    {
        return match($this->task_type) {
            self::TASK_TYPE_INSPECTION => 'bg-blue-100 text-blue-800',
            self::TASK_TYPE_REPAIR => 'bg-yellow-100 text-yellow-800',
            self::TASK_TYPE_REPLACEMENT => 'bg-orange-100 text-orange-800',
            self::TASK_TYPE_CALIBRATION => 'bg-purple-100 text-purple-800',
            self::TASK_TYPE_CLEANING => 'bg-green-100 text-green-800',
            self::TASK_TYPE_LUBRICATION => 'bg-indigo-100 text-indigo-800',
            self::TASK_TYPE_TESTING => 'bg-cyan-100 text-cyan-800',
            self::TASK_TYPE_UPGRADE => 'bg-pink-100 text-pink-800',
            self::TASK_TYPE_INSTALLATION => 'bg-teal-100 text-teal-800',
            self::TASK_TYPE_DEMOLITION => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getRiskLevelBadgeClass(): string
    {
        return match($this->risk_level) {
            self::RISK_LEVEL_LOW => 'bg-green-100 text-green-800',
            self::RISK_LEVEL_MEDIUM => 'bg-yellow-100 text-yellow-800',
            self::RISK_LEVEL_HIGH => 'bg-orange-100 text-orange-800',
            self::RISK_LEVEL_EXTREME => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getOverdueBadgeClass(): string
    {
        if (!$this->isOverdue()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        $daysOverdue = $this->getDaysOverdue();
        
        if ($daysOverdue <= 3) {
            return 'bg-yellow-100 text-yellow-800';
        } elseif ($daysOverdue <= 7) {
            return 'bg-orange-100 text-orange-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }
}
