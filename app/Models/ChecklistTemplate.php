<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChecklistTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'template_type',
        'category',
        'subcategory',
        'checklist_items',
        'required_items',
        'optional_items',
        'conditional_items',
        'item_order',
        'scoring_system',
        'pass_threshold',
        'fail_threshold',
        'is_active',
        'is_standard',
        'version',
        'created_by',
        'approved_by',
        'approved_at',
        'tags',
        'notes',
        'department',
        'priority',
        'risk_level',
        'compliance_requirements',
        'quality_standards',
        'safety_requirements',
        'training_required',
        'certification_required',
        'documentation_required',
        'environmental_considerations',
        'budget_code',
        'cost_center',
        'project_code',
        'estimated_completion_time',
        'estimated_cost',
        'required_skills',
        'required_tools',
        'required_parts',
        'work_instructions',
        'reference_documents',
        'best_practices',
        'lessons_learned',
        'continuous_improvement',
        'audit_frequency',
        'last_review_date',
        'next_review_date',
        'reviewed_by',
        'approval_workflow',
        'escalation_procedures',
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'required_items' => 'array',
        'optional_items' => 'array',
        'conditional_items' => 'array',
        'item_order' => 'array',
        'scoring_system' => 'array',
        'pass_threshold' => 'decimal:2',
        'fail_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'is_standard' => 'boolean',
        'approved_at' => 'datetime',
        'tags' => 'array',
        'compliance_requirements' => 'array',
        'quality_standards' => 'array',
        'safety_requirements' => 'array',
        'documentation_required' => 'array',
        'environmental_considerations' => 'array',
        'estimated_completion_time' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'required_skills' => 'array',
        'required_tools' => 'array',
        'required_parts' => 'array',
        'work_instructions' => 'array',
        'reference_documents' => 'array',
        'best_practices' => 'array',
        'lessons_learned' => 'array',
        'continuous_improvement' => 'array',
        'last_review_date' => 'date',
        'next_review_date' => 'date',
        'approval_workflow' => 'array',
        'escalation_procedures' => 'array',
    ];

    // Enums
    const TEMPLATE_TYPE_MAINTENANCE = 'maintenance';
    const TEMPLATE_TYPE_INSPECTION = 'inspection';
    const TEMPLATE_TYPE_SAFETY = 'safety';
    const TEMPLATE_TYPE_QUALITY = 'quality';
    const TEMPLATE_TYPE_COMPLIANCE = 'compliance';
    const TEMPLATE_TYPE_AUDIT = 'audit';
    const TEMPLATE_TYPE_TRAINING = 'training';
    const TEMPLATE_TYPE_OPERATIONS = 'operations';
    const TEMPLATE_TYPE_PROCEDURE = 'procedure';
    const TEMPLATE_TYPE_WORKFLOW = 'workflow';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    const RISK_LEVEL_LOW = 'low';
    const RISK_LEVEL_MEDIUM = 'medium';
    const RISK_LEVEL_HIGH = 'high';
    const RISK_LEVEL_EXTREME = 'extreme';

    public static function getTemplateTypes(): array
    {
        return [
            self::TEMPLATE_TYPE_MAINTENANCE => 'Mantenimiento',
            self::TEMPLATE_TYPE_INSPECTION => 'Inspección',
            self::TEMPLATE_TYPE_SAFETY => 'Seguridad',
            self::TEMPLATE_TYPE_QUALITY => 'Calidad',
            self::TEMPLATE_TYPE_COMPLIANCE => 'Cumplimiento',
            self::TEMPLATE_TYPE_AUDIT => 'Auditoría',
            self::TEMPLATE_TYPE_TRAINING => 'Capacitación',
            self::TEMPLATE_TYPE_OPERATIONS => 'Operaciones',
            self::TEMPLATE_TYPE_PROCEDURE => 'Procedimiento',
            self::TEMPLATE_TYPE_WORKFLOW => 'Flujo de Trabajo',
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
    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeStandard($query)
    {
        return $query->where('is_standard', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('template_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByRiskLevel($query, $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeNeedsReview($query)
    {
        return $query->where(function($q) {
            $q->whereNull('next_review_date')
              ->orWhere('next_review_date', '<=', now());
        });
    }

    // Métodos
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isStandard(): bool
    {
        return $this->is_standard;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function isMaintenance(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_MAINTENANCE;
    }

    public function isInspection(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_INSPECTION;
    }

    public function isSafety(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_SAFETY;
    }

    public function isQuality(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_QUALITY;
    }

    public function isCompliance(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_COMPLIANCE;
    }

    public function isAudit(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_AUDIT;
    }

    public function isTraining(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_TRAINING;
    }

    public function isOperations(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_OPERATIONS;
    }

    public function isProcedure(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_PROCEDURE;
    }

    public function isWorkflow(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_WORKFLOW;
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

    public function needsReview(): bool
    {
        if (!$this->next_review_date) {
            return true;
        }
        
        return $this->next_review_date->isPast();
    }

    public function getTotalItems(): int
    {
        if (!$this->checklist_items) {
            return 0;
        }
        
        return count($this->checklist_items);
    }

    public function getRequiredItemsCount(): int
    {
        if (!$this->required_items) {
            return 0;
        }
        
        return count($this->required_items);
    }

    public function getOptionalItemsCount(): int
    {
        if (!$this->optional_items) {
            return 0;
        }
        
        return count($this->optional_items);
    }

    public function getConditionalItemsCount(): int
    {
        if (!$this->conditional_items) {
            return 0;
        }
        
        return count($this->conditional_items);
    }

    public function getCompletionPercentage(): float
    {
        $totalItems = $this->getTotalItems();
        
        if ($totalItems <= 0) {
            return 0;
        }
        
        $requiredItems = $this->getRequiredItemsCount();
        
        return ($requiredItems / $totalItems) * 100;
    }

    public function getDaysUntilReview(): int
    {
        if (!$this->next_review_date) {
            return 0;
        }
        
        if ($this->next_review_date->isPast()) {
            return 0;
        }
        
        return now()->diffInDays($this->next_review_date, false);
    }

    public function getFormattedEstimatedCompletionTime(): string
    {
        if (!$this->estimated_completion_time) {
            return 'No estimado';
        }
        
        return number_format($this->estimated_completion_time, 2) . ' horas';
    }

    public function getFormattedEstimatedCost(): string
    {
        if (!$this->estimated_cost) {
            return 'No estimado';
        }
        
        return '$' . number_format($this->estimated_cost, 2);
    }

    public function getFormattedPassThreshold(): string
    {
        if (!$this->pass_threshold) {
            return 'No establecido';
        }
        
        return number_format($this->pass_threshold, 1) . '%';
    }

    public function getFormattedFailThreshold(): string
    {
        if (!$this->fail_threshold) {
            return 'No establecido';
        }
        
        return number_format($this->fail_threshold, 1) . '%';
    }

    public function getFormattedLastReviewDate(): string
    {
        if (!$this->last_review_date) {
            return 'Nunca revisado';
        }
        
        return $this->last_review_date->format('d/m/Y');
    }

    public function getFormattedNextReviewDate(): string
    {
        if (!$this->next_review_date) {
            return 'No programado';
        }
        
        return $this->next_review_date->format('d/m/Y');
    }

    public function getFormattedTemplateType(): string
    {
        return self::getTemplateTypes()[$this->template_type] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority] ?? 'Desconocida';
    }

    public function getFormattedRiskLevel(): string
    {
        return self::getRiskLevels()[$this->risk_level] ?? 'Desconocido';
    }

    public function getFormattedCompletionPercentage(): string
    {
        return number_format($this->getCompletionPercentage(), 1) . '%';
    }

    public function getStatusBadgeClass(): string
    {
        if (!$this->is_active) {
            return 'bg-red-100 text-red-800';
        }
        
        if (!$this->isApproved()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->is_standard) {
            return 'bg-green-100 text-green-800';
        }
        
        return 'bg-blue-100 text-blue-800';
    }

    public function getTemplateTypeBadgeClass(): string
    {
        return match($this->template_type) {
            self::TEMPLATE_TYPE_MAINTENANCE => 'bg-blue-100 text-blue-800',
            self::TEMPLATE_TYPE_INSPECTION => 'bg-green-100 text-green-800',
            self::TEMPLATE_TYPE_SAFETY => 'bg-red-100 text-red-800',
            self::TEMPLATE_TYPE_QUALITY => 'bg-purple-100 text-purple-800',
            self::TEMPLATE_TYPE_COMPLIANCE => 'bg-indigo-100 text-indigo-800',
            self::TEMPLATE_TYPE_AUDIT => 'bg-yellow-100 text-yellow-800',
            self::TEMPLATE_TYPE_TRAINING => 'bg-cyan-100 text-cyan-800',
            self::TEMPLATE_TYPE_OPERATIONS => 'bg-orange-100 text-orange-800',
            self::TEMPLATE_TYPE_PROCEDURE => 'bg-pink-100 text-pink-800',
            self::TEMPLATE_TYPE_WORKFLOW => 'bg-teal-100 text-teal-800',
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

    public function getReviewStatusBadgeClass(): string
    {
        if ($this->needsReview()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->getDaysUntilReview() <= 7) {
            return 'bg-orange-100 text-orange-800';
        }
        
        if ($this->getDaysUntilReview() <= 30) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        return 'bg-green-100 text-green-800';
    }
}
