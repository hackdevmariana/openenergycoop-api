<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'milestone_type',
        'status',
        'priority',
        'target_date',
        'start_date',
        'completion_date',
        'target_value',
        'current_value',
        'progress_percentage',
        'success_criteria',
        'dependencies',
        'risks',
        'mitigation_strategies',
        'parent_milestone_id',
        'assigned_to',
        'created_by',
        'tags',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'tags' => 'array',
    ];

    // Enums
    const MILESTONE_TYPE_PROJECT = 'project';
    const MILESTONE_TYPE_FINANCIAL = 'financial';
    const MILESTONE_TYPE_OPERATIONAL = 'operational';
    const MILESTONE_TYPE_REGULATORY = 'regulatory';
    const MILESTONE_TYPE_COMMUNITY = 'community';
    const MILESTONE_TYPE_ENVIRONMENTAL = 'environmental';
    const MILESTONE_TYPE_OTHER = 'other';

    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_OVERDUE = 'overdue';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    public static function getMilestoneTypes(): array
    {
        return [
            self::MILESTONE_TYPE_PROJECT => 'Proyecto',
            self::MILESTONE_TYPE_FINANCIAL => 'Financiero',
            self::MILESTONE_TYPE_OPERATIONAL => 'Operacional',
            self::MILESTONE_TYPE_REGULATORY => 'Regulatorio',
            self::MILESTONE_TYPE_COMMUNITY => 'Comunitario',
            self::MILESTONE_TYPE_ENVIRONMENTAL => 'Ambiental',
            self::MILESTONE_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NOT_STARTED => 'No Iniciado',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_ON_HOLD => 'En Espera',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_OVERDUE => 'Vencido',
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

    // Relaciones
    public function parentMilestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class, 'parent_milestone_id');
    }

    public function subMilestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'parent_milestone_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('milestone_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeProject($query)
    {
        return $query->where('milestone_type', self::MILESTONE_TYPE_PROJECT);
    }

    public function scopeFinancial($query)
    {
        return $query->where('milestone_type', self::MILESTONE_TYPE_FINANCIAL);
    }

    public function scopeOperational($query)
    {
        return $query->where('milestone_type', self::MILESTONE_TYPE_OPERATIONAL);
    }

    public function scopeRegulatory($query)
    {
        return $query->where('milestone_type', self::MILESTONE_TYPE_REGULATORY);
    }

    public function scopeCommunity($query)
    {
        return $query->where('milestone_type', self::MILESTONE_TYPE_COMMUNITY);
    }

    public function scopeEnvironmental($query)
    {
        return $query->where('milestone_type', self::MILESTONE_TYPE_ENVIRONMENTAL);
    }

    public function scopeNotStarted($query)
    {
        return $query->where('status', self::STATUS_NOT_STARTED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', self::STATUS_ON_HOLD);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE);
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

    public function scopeMediumPriority($query)
    {
        return $query->where('priority', self::PRIORITY_MEDIUM);
    }

    public function scopeByTargetDate($query, $date)
    {
        return $query->whereDate('target_date', $date);
    }

    public function scopeByStartDate($query, $date)
    {
        return $query->whereDate('start_date', $date);
    }

    public function scopeByCompletionDate($query, $date)
    {
        return $query->whereDate('completion_date', $date);
    }

    public function scopeDueSoon($query, $days = 7)
    {
        $dueDate = now()->addDays($days);
        return $query->where('target_date', '<=', $dueDate)
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeOverdueStatus($query)
    {
        return $query->where('target_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeByAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeByParentMilestone($query, $parentId)
    {
        return $query->where('parent_milestone_id', $parentId);
    }

    public function scopeRootMilestones($query)
    {
        return $query->whereNull('parent_milestone_id');
    }

    public function scopeByProgressRange($query, $minProgress, $maxProgress)
    {
        return $query->whereBetween('progress_percentage', [$minProgress, $maxProgress]);
    }

    public function scopeByValueRange($query, $minValue, $maxValue)
    {
        return $query->whereBetween('target_value', [$minValue, $maxValue]);
    }

    // Métodos de validación
    public function isProject(): bool
    {
        return $this->milestone_type === self::MILESTONE_TYPE_PROJECT;
    }

    public function isFinancial(): bool
    {
        return $this->milestone_type === self::MILESTONE_TYPE_FINANCIAL;
    }

    public function isOperational(): bool
    {
        return $this->milestone_type === self::MILESTONE_TYPE_OPERATIONAL;
    }

    public function isRegulatory(): bool
    {
        return $this->milestone_type === self::MILESTONE_TYPE_REGULATORY;
    }

    public function isCommunity(): bool
    {
        return $this->milestone_type === self::MILESTONE_TYPE_COMMUNITY;
    }

    public function isEnvironmental(): bool
    {
        return $this->milestone_type === self::MILESTONE_TYPE_ENVIRONMENTAL;
    }

    public function isOther(): bool
    {
        return $this->milestone_type === self::MILESTONE_TYPE_OTHER;
    }

    public function isNotStarted(): bool
    {
        return $this->status === self::STATUS_NOT_STARTED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE;
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

    public function isRoot(): bool
    {
        return is_null($this->parent_milestone_id);
    }

    public function hasParent(): bool
    {
        return !is_null($this->parent_milestone_id);
    }

    public function hasSubMilestones(): bool
    {
        return $this->subMilestones()->exists();
    }

    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to);
    }

    // Métodos de cálculo
    public function getDaysUntilTarget(): int
    {
        if (!$this->target_date) {
            return 0;
        }
        
        if ($this->target_date->isPast()) {
            return 0;
        }
        
        return now()->diffInDays($this->target_date, false);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->target_date || $this->target_date->isFuture()) {
            return 0;
        }
        
        return now()->diffInDays($this->target_date);
    }

    public function getDaysSinceStart(): int
    {
        if (!$this->start_date) {
            return 0;
        }
        
        return now()->diffInDays($this->start_date);
    }

    public function getDaysSinceCompletion(): int
    {
        if (!$this->completion_date) {
            return 0;
        }
        
        return now()->diffInDays($this->completion_date);
    }

    public function getProgressPercentage(): float
    {
        if (!$this->target_value || $this->target_value <= 0) {
            return 0;
        }
        
        if (!$this->current_value) {
            return 0;
        }
        
        return min(100, ($this->current_value / $this->target_value) * 100);
    }

    public function getRemainingValue(): float
    {
        if (!$this->target_value || !$this->current_value) {
            return 0;
        }
        
        return max(0, $this->target_value - $this->current_value);
    }

    public function isDueSoon(int $days = 7): bool
    {
        if (!$this->target_date) {
            return false;
        }
        
        return $this->target_date->between(now(), now()->addDays($days));
    }

    public function isDueToday(): bool
    {
        if (!$this->target_date) {
            return false;
        }
        
        return $this->target_date->isToday();
    }

    public function isDueThisWeek(): bool
    {
        if (!$this->target_date) {
            return false;
        }
        
        return $this->target_date->between(
            now()->startOfWeek(),
            now()->endOfWeek()
        );
    }

    public function isDueThisMonth(): bool
    {
        if (!$this->target_date) {
            return false;
        }
        
        return $this->target_date->between(
            now()->startOfMonth(),
            now()->endOfMonth()
        );
    }

    public function canStart(): bool
    {
        return $this->status === self::STATUS_NOT_STARTED;
    }

    public function canComplete(): bool
    {
        return in_array($this->status, [
            self::STATUS_NOT_STARTED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_ON_HOLD,
        ]);
    }

    public function canCancel(): bool
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function canPutOnHold(): bool
    {
        return in_array($this->status, [
            self::STATUS_NOT_STARTED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    // Métodos de formato
    public function getFormattedMilestoneType(): string
    {
        return self::getMilestoneTypes()[$this->milestone_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority] ?? 'Desconocida';
    }

    public function getFormattedTargetDate(): string
    {
        if (!$this->target_date) {
            return 'No establecida';
        }
        
        return $this->target_date->format('d/m/Y');
    }

    public function getFormattedStartDate(): string
    {
        if (!$this->start_date) {
            return 'No iniciado';
        }
        
        return $this->start_date->format('d/m/Y');
    }

    public function getFormattedCompletionDate(): string
    {
        if (!$this->completion_date) {
            return 'No completado';
        }
        
        return $this->completion_date->format('d/m/Y');
    }

    public function getFormattedTargetValue(): string
    {
        if (!$this->target_value) {
            return 'N/A';
        }
        
        return number_format($this->target_value, 2);
    }

    public function getFormattedCurrentValue(): string
    {
        if (!$this->current_value) {
            return '0.00';
        }
        
        return number_format($this->current_value, 2);
    }

    public function getFormattedRemainingValue(): string
    {
        $remaining = $this->getRemainingValue();
        if ($remaining <= 0) {
            return '0.00';
        }
        
        return number_format($remaining, 2);
    }

    public function getFormattedProgressPercentage(): string
    {
        $progress = $this->getProgressPercentage();
        return number_format($progress, 1) . '%';
    }

    public function getFormattedDaysUntilTarget(): string
    {
        $days = $this->getDaysUntilTarget();
        if ($days <= 0) {
            return 'Ya vencido';
        }
        
        if ($days === 1) {
            return '1 día';
        }
        
        return "{$days} días";
    }

    public function getFormattedDaysOverdue(): string
    {
        $days = $this->getDaysOverdue();
        if ($days <= 0) {
            return 'No vencido';
        }
        
        if ($days === 1) {
            return '1 día vencido';
        }
        
        return "{$days} días vencido";
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_NOT_STARTED => 'bg-gray-100 text-gray-800',
            self::STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_ON_HOLD => 'bg-yellow-100 text-yellow-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            self::STATUS_OVERDUE => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getMilestoneTypeBadgeClass(): string
    {
        return match($this->milestone_type) {
            self::MILESTONE_TYPE_PROJECT => 'bg-blue-100 text-blue-800',
            self::MILESTONE_TYPE_FINANCIAL => 'bg-green-100 text-green-800',
            self::MILESTONE_TYPE_OPERATIONAL => 'bg-yellow-100 text-yellow-800',
            self::MILESTONE_TYPE_REGULATORY => 'bg-purple-100 text-purple-800',
            self::MILESTONE_TYPE_COMMUNITY => 'bg-indigo-100 text-indigo-800',
            self::MILESTONE_TYPE_ENVIRONMENTAL => 'bg-teal-100 text-teal-800',
            self::MILESTONE_TYPE_OTHER => 'bg-gray-100 text-gray-800',
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

    public function getTargetDateBadgeClass(): string
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->isOverdue()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isDueToday()) {
            return 'bg-orange-100 text-orange-800';
        }
        
        if ($this->isDueSoon(7)) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        return 'bg-green-100 text-green-800';
    }

    public function getProgressBadgeClass(): string
    {
        $progress = $this->getProgressPercentage();
        
        if ($progress >= 100) {
            return 'bg-green-100 text-green-800';
        } elseif ($progress >= 75) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($progress >= 50) {
            return 'bg-yellow-100 text-yellow-800';
        } elseif ($progress >= 25) {
            return 'bg-orange-100 text-orange-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }
}
