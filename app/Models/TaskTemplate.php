<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'template_type',
        'category',
        'subcategory',
        'estimated_duration_hours',
        'estimated_cost',
        'required_skills',
        'required_tools',
        'required_parts',
        'safety_requirements',
        'technical_requirements',
        'quality_standards',
        'checklist_items',
        'work_instructions',
        'is_active',
        'is_standard',
        'version',
        'tags',
        'notes',
        'department',
        'priority',
        'risk_level',
        'compliance_requirements',
        'documentation_required',
        'training_required',
        'certification_required',
        'environmental_considerations',
        'budget_code',
        'cost_center',
        'project_code',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'estimated_duration_hours' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'required_skills' => 'array',
        'required_tools' => 'array',
        'required_parts' => 'array',
        'safety_requirements' => 'array',
        'technical_requirements' => 'array',
        'quality_standards' => 'array',
        'checklist_items' => 'array',
        'work_instructions' => 'array',
        'is_active' => 'boolean',
        'is_standard' => 'boolean',
        'training_required' => 'boolean',
        'certification_required' => 'boolean',
        'approved_at' => 'datetime',
        'tags' => 'array',
        'compliance_requirements' => 'array',
        'documentation_required' => 'array',
        'environmental_considerations' => 'array',
    ];

    // Enums
    const TEMPLATE_TYPE_MAINTENANCE = 'maintenance';
    const TEMPLATE_TYPE_INSPECTION = 'inspection';
    const TEMPLATE_TYPE_REPAIR = 'repair';
    const TEMPLATE_TYPE_REPLACEMENT = 'replacement';
    const TEMPLATE_TYPE_CALIBRATION = 'calibration';
    const TEMPLATE_TYPE_CLEANING = 'cleaning';
    const TEMPLATE_TYPE_LUBRICATION = 'lubrication';
    const TEMPLATE_TYPE_TESTING = 'testing';
    const TEMPLATE_TYPE_UPGRADE = 'upgrade';
    const TEMPLATE_TYPE_INSTALLATION = 'installation';

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
            self::TEMPLATE_TYPE_REPAIR => 'Reparación',
            self::TEMPLATE_TYPE_REPLACEMENT => 'Reemplazo',
            self::TEMPLATE_TYPE_CALIBRATION => 'Calibración',
            self::TEMPLATE_TYPE_CLEANING => 'Limpieza',
            self::TEMPLATE_TYPE_LUBRICATION => 'Lubricación',
            self::TEMPLATE_TYPE_TESTING => 'Pruebas',
            self::TEMPLATE_TYPE_UPGRADE => 'Actualización',
            self::TEMPLATE_TYPE_INSTALLATION => 'Instalación',
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

    public function isRepair(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_REPAIR;
    }

    public function isReplacement(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_REPLACEMENT;
    }

    public function isCalibration(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_CALIBRATION;
    }

    public function isCleaning(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_CLEANING;
    }

    public function isLubrication(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_LUBRICATION;
    }

    public function isTesting(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_TESTING;
    }

    public function isUpgrade(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_UPGRADE;
    }

    public function isInstallation(): bool
    {
        return $this->template_type === self::TEMPLATE_TYPE_INSTALLATION;
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

    public function getFormattedEstimatedDuration(): string
    {
        if (!$this->estimated_duration_hours) {
            return 'No estimado';
        }
        
        return number_format($this->estimated_duration_hours, 2) . ' horas';
    }

    public function getFormattedEstimatedCost(): string
    {
        if (!$this->estimated_cost) {
            return 'No estimado';
        }
        
        return '$' . number_format($this->estimated_cost, 2);
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
            self::TEMPLATE_TYPE_REPAIR => 'bg-yellow-100 text-yellow-800',
            self::TEMPLATE_TYPE_REPLACEMENT => 'bg-orange-100 text-orange-800',
            self::TEMPLATE_TYPE_CALIBRATION => 'bg-indigo-100 text-indigo-800',
            self::TEMPLATE_TYPE_CLEANING => 'bg-cyan-100 text-cyan-800',
            self::TEMPLATE_TYPE_LUBRICATION => 'bg-purple-100 text-purple-800',
            self::TEMPLATE_TYPE_TESTING => 'bg-pink-100 text-pink-800',
            self::TEMPLATE_TYPE_UPGRADE => 'bg-teal-100 text-teal-800',
            self::TEMPLATE_TYPE_INSTALLATION => 'bg-red-100 text-red-800',
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
}
