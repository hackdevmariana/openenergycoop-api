<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'schedule_type',
        'frequency_type',
        'frequency_value',
        'start_date',
        'end_date',
        'next_maintenance_date',
        'last_maintenance_date',
        'equipment_id',
        'equipment_type',
        'location_id',
        'location_type',
        'maintenance_tasks',
        'estimated_duration_hours',
        'estimated_cost',
        'assigned_technicians',
        'priority',
        'is_active',
        'auto_generate_tasks',
        'task_template_id',
        'checklist_template_id',
        'required_parts',
        'required_tools',
        'safety_requirements',
        'technical_requirements',
        'vendor_id',
        'contract_number',
        'warranty_terms',
        'notes',
        'tags',
        'created_by',
        'approved_by',
        'approved_at',
        'department',
        'category',
        'subcategory',
        'risk_assessment',
        'compliance_requirements',
        'documentation_required',
        'quality_standards',
        'environmental_considerations',
        'budget_code',
        'cost_center',
        'project_code',
        'maintenance_window_start',
        'maintenance_window_end',
        'downtime_impact',
        'backup_equipment_available',
        'emergency_contacts',
        'escalation_procedures',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_maintenance_date' => 'datetime',
        'last_maintenance_date' => 'datetime',
        'frequency_value' => 'integer',
        'maintenance_tasks' => 'array',
        'estimated_duration_hours' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'assigned_technicians' => 'array',
        'is_active' => 'boolean',
        'auto_generate_tasks' => 'boolean',
        'required_parts' => 'array',
        'required_tools' => 'array',
        'safety_requirements' => 'array',
        'technical_requirements' => 'array',
        'warranty_terms' => 'array',
        'tags' => 'array',
        'approved_at' => 'datetime',
        'risk_assessment' => 'array',
        'compliance_requirements' => 'array',
        'documentation_required' => 'array',
        'quality_standards' => 'array',
        'environmental_considerations' => 'array',
        'maintenance_window_start' => 'datetime',
        'maintenance_window_end' => 'datetime',
        'downtime_impact' => 'array',
        'backup_equipment_available' => 'boolean',
        'emergency_contacts' => 'array',
        'escalation_procedures' => 'array',
    ];

    // Enums
    const SCHEDULE_TYPE_PREVENTIVE = 'preventive';
    const SCHEDULE_TYPE_PREDICTIVE = 'predictive';
    const SCHEDULE_TYPE_CONDITION_BASED = 'condition_based';
    const SCHEDULE_TYPE_TIME_BASED = 'time_based';
    const SCHEDULE_TYPE_USAGE_BASED = 'usage_based';
    const SCHEDULE_TYPE_CALENDAR_BASED = 'calendar_based';
    const SCHEDULE_TYPE_EVENT_BASED = 'event_based';
    const SCHEDULE_TYPE_MANUAL = 'manual';

    const FREQUENCY_TYPE_DAILY = 'daily';
    const FREQUENCY_TYPE_WEEKLY = 'weekly';
    const FREQUENCY_TYPE_MONTHLY = 'monthly';
    const FREQUENCY_TYPE_QUARTERLY = 'quarterly';
    const FREQUENCY_TYPE_SEMI_ANNUALLY = 'semi_annually';
    const FREQUENCY_TYPE_ANNUALLY = 'annually';
    const FREQUENCY_TYPE_CUSTOM = 'custom';
    const FREQUENCY_TYPE_HOURS = 'hours';
    const FREQUENCY_TYPE_CYCLES = 'cycles';
    const FREQUENCY_TYPE_MILES = 'miles';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    public static function getScheduleTypes(): array
    {
        return [
            self::SCHEDULE_TYPE_PREVENTIVE => 'Preventivo',
            self::SCHEDULE_TYPE_PREDICTIVE => 'Predictivo',
            self::SCHEDULE_TYPE_CONDITION_BASED => 'Basado en Condición',
            self::SCHEDULE_TYPE_TIME_BASED => 'Basado en Tiempo',
            self::SCHEDULE_TYPE_USAGE_BASED => 'Basado en Uso',
            self::SCHEDULE_TYPE_CALENDAR_BASED => 'Basado en Calendario',
            self::SCHEDULE_TYPE_EVENT_BASED => 'Basado en Eventos',
            self::SCHEDULE_TYPE_MANUAL => 'Manual',
        ];
    }

    public static function getFrequencyTypes(): array
    {
        return [
            self::FREQUENCY_TYPE_DAILY => 'Diario',
            self::FREQUENCY_TYPE_WEEKLY => 'Semanal',
            self::FREQUENCY_TYPE_MONTHLY => 'Mensual',
            self::FREQUENCY_TYPE_QUARTERLY => 'Trimestral',
            self::FREQUENCY_TYPE_SEMI_ANNUALLY => 'Semestral',
            self::FREQUENCY_TYPE_ANNUALLY => 'Anual',
            self::FREQUENCY_TYPE_CUSTOM => 'Personalizado',
            self::FREQUENCY_TYPE_HOURS => 'Horas',
            self::FREQUENCY_TYPE_CYCLES => 'Ciclos',
            self::FREQUENCY_TYPE_MILES => 'Millas',
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
    public function equipment(): MorphTo
    {
        return $this->morphTo();
    }

    public function location(): MorphTo
    {
        return $this->morphTo();
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function taskTemplate(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class);
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
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

    public function scopeByType($query, $type)
    {
        return $query->where('schedule_type', $type);
    }

    public function scopeByFrequencyType($query, $frequencyType)
    {
        return $query->where('frequency_type', $frequencyType);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
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

    public function scopePreventive($query)
    {
        return $query->where('schedule_type', self::SCHEDULE_TYPE_PREVENTIVE);
    }

    public function scopePredictive($query)
    {
        return $query->where('schedule_type', self::SCHEDULE_TYPE_PREDICTIVE);
    }

    public function scopeConditionBased($query)
    {
        return $query->where('schedule_type', self::SCHEDULE_TYPE_CONDITION_BASED);
    }

    public function scopeTimeBased($query)
    {
        return $query->where('schedule_type', self::SCHEDULE_TYPE_TIME_BASED);
    }

    public function scopeUsageBased($query)
    {
        return $query->where('schedule_type', self::SCHEDULE_TYPE_USAGE_BASED);
    }

    public function scopeCalendarBased($query)
    {
        return $query->where('schedule_type', self::SCHEDULE_TYPE_CALENDAR_BASED);
    }

    public function scopeEventBased($query)
    {
        return $query->where('schedule_type', self::SCHEDULE_TYPE_EVENT_BASED);
    }

    public function scopeManual($query)
    {
        return $query->where('schedule_type', self::SCHEDULE_TYPE_MANUAL);
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

    public function scopeDueSoon($query, $days = 30)
    {
        return $query->where('next_maintenance_date', '<=', now()->addDays($days))
                    ->where('is_active', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('next_maintenance_date', '<', now())
                    ->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeAutoGenerateTasks($query)
    {
        return $query->where('auto_generate_tasks', true);
    }

    public function scopeByEquipment($query, $equipmentId, $equipmentType = null)
    {
        $query->where('equipment_id', $equipmentId);
        
        if ($equipmentType) {
            $query->where('equipment_type', $equipmentType);
        }
        
        return $query;
    }

    public function scopeByLocation($query, $locationId, $locationType = null)
    {
        $query->where('location_id', $locationId);
        
        if ($locationType) {
            $query->where('location_type', $locationType);
        }
        
        return $query;
    }

    // Métodos
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function isPreventive(): bool
    {
        return $this->schedule_type === self::SCHEDULE_TYPE_PREVENTIVE;
    }

    public function isPredictive(): bool
    {
        return $this->schedule_type === self::SCHEDULE_TYPE_PREDICTIVE;
    }

    public function isConditionBased(): bool
    {
        return $this->schedule_type === self::SCHEDULE_TYPE_CONDITION_BASED;
    }

    public function isTimeBased(): bool
    {
        return $this->schedule_type === self::SCHEDULE_TYPE_TIME_BASED;
    }

    public function isUsageBased(): bool
    {
        return $this->schedule_type === self::SCHEDULE_TYPE_USAGE_BASED;
    }

    public function isCalendarBased(): bool
    {
        return $this->schedule_type === self::SCHEDULE_TYPE_CALENDAR_BASED;
    }

    public function isEventBased(): bool
    {
        return $this->schedule_type === self::SCHEDULE_TYPE_EVENT_BASED;
    }

    public function isManual(): bool
    {
        return $this->schedule_type === self::SCHEDULE_TYPE_MANUAL;
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

    public function isDaily(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_DAILY;
    }

    public function isWeekly(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_WEEKLY;
    }

    public function isMonthly(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_MONTHLY;
    }

    public function isQuarterly(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_QUARTERLY;
    }

    public function isSemiAnnually(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_SEMI_ANNUALLY;
    }

    public function isAnnually(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_ANNUALLY;
    }

    public function isCustom(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_CUSTOM;
    }

    public function isHoursBased(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_HOURS;
    }

    public function isCyclesBased(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_CYCLES;
    }

    public function isMilesBased(): bool
    {
        return $this->frequency_type === self::FREQUENCY_TYPE_MILES;
    }

    public function isOverdue(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        return $this->next_maintenance_date && $this->next_maintenance_date->isPast();
    }

    public function isDueSoon(int $days = 30): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        return $this->next_maintenance_date && 
               $this->next_maintenance_date->between(now(), now()->addDays($days));
    }

    public function isDueToday(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        return $this->next_maintenance_date && $this->next_maintenance_date->isToday();
    }

    public function isDueThisWeek(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        return $this->next_maintenance_date && $this->next_maintenance_date->between(
            now()->startOfWeek(),
            now()->endOfWeek()
        );
    }

    public function isDueThisMonth(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        return $this->next_maintenance_date && $this->next_maintenance_date->between(
            now()->startOfMonth(),
            now()->endOfMonth()
        );
    }

    public function canGenerateTask(): bool
    {
        if (!$this->isActive() || !$this->isApproved()) {
            return false;
        }
        
        if (!$this->auto_generate_tasks) {
            return false;
        }
        
        if ($this->isOverdue()) {
            return true;
        }
        
        if ($this->isDueToday()) {
            return true;
        }
        
        return false;
    }

    public function getDaysUntilDue(): int
    {
        if (!$this->next_maintenance_date) {
            return 0;
        }
        
        if ($this->next_maintenance_date->isPast()) {
            return 0;
        }
        
        return now()->diffInDays($this->next_maintenance_date, false);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->next_maintenance_date);
    }

    public function getNextMaintenanceDate(): ?string
    {
        if (!$this->next_maintenance_date) {
            return null;
        }
        
        return $this->next_maintenance_date->format('Y-m-d H:i:s');
    }

    public function getLastMaintenanceDate(): ?string
    {
        if (!$this->last_maintenance_date) {
            return null;
        }
        
        return $this->last_maintenance_date->format('Y-m-d H:i:s');
    }

    public function getFormattedStartDate(): string
    {
        if (!$this->start_date) {
            return 'No establecida';
        }
        
        return $this->start_date->format('d/m/Y');
    }

    public function getFormattedEndDate(): string
    {
        if (!$this->end_date) {
            return 'Sin fecha de fin';
        }
        
        return $this->end_date->format('d/m/Y');
    }

    public function getFormattedNextMaintenanceDate(): string
    {
        if (!$this->next_maintenance_date) {
            return 'No programado';
        }
        
        return $this->next_maintenance_date->format('d/m/Y H:i');
    }

    public function getFormattedLastMaintenanceDate(): string
    {
        if (!$this->last_maintenance_date) {
            return 'Nunca mantenido';
        }
        
        return $this->last_maintenance_date->format('d/m/Y H:i');
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

    public function getFormattedScheduleType(): string
    {
        return self::getScheduleTypes()[$this->schedule_type] ?? 'Desconocido';
    }

    public function getFormattedFrequencyType(): string
    {
        return self::getFrequencyTypes()[$this->frequency_type] ?? 'Desconocido';
    }

    public function getFormattedPriority(): string
    {
        return self::getPriorities()[$this->priority] ?? 'Desconocida';
    }

    public function getFormattedFrequency(): string
    {
        $type = $this->getFormattedFrequencyType();
        $value = $this->frequency_value;
        
        if (!$value) {
            return $type;
        }
        
        switch ($this->frequency_type) {
            case self::FREQUENCY_TYPE_HOURS:
                return "Cada {$value} horas";
            case self::FREQUENCY_TYPE_CYCLES:
                return "Cada {$value} ciclos";
            case self::FREQUENCY_TYPE_MILES:
                return "Cada {$value} millas";
            case self::FREQUENCY_TYPE_CUSTOM:
                return "Cada {$value} días";
            default:
                return $type;
        }
    }

    public function getStatusBadgeClass(): string
    {
        if (!$this->is_active) {
            return 'bg-red-100 text-red-800';
        }
        
        if (!$this->isApproved()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->isOverdue()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isDueSoon(7)) {
            return 'bg-orange-100 text-orange-800';
        }
        
        if ($this->isDueSoon(30)) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        return 'bg-green-100 text-green-800';
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

    public function getScheduleTypeBadgeClass(): string
    {
        return match($this->schedule_type) {
            self::SCHEDULE_TYPE_PREVENTIVE => 'bg-green-100 text-green-800',
            self::SCHEDULE_TYPE_PREDICTIVE => 'bg-blue-100 text-blue-800',
            self::SCHEDULE_TYPE_CONDITION_BASED => 'bg-yellow-100 text-yellow-800',
            self::SCHEDULE_TYPE_TIME_BASED => 'bg-indigo-100 text-indigo-800',
            self::SCHEDULE_TYPE_USAGE_BASED => 'bg-purple-100 text-purple-800',
            self::SCHEDULE_TYPE_CALENDAR_BASED => 'bg-cyan-100 text-cyan-800',
            self::SCHEDULE_TYPE_EVENT_BASED => 'bg-pink-100 text-pink-800',
            self::SCHEDULE_TYPE_MANUAL => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFrequencyTypeBadgeClass(): string
    {
        return match($this->frequency_type) {
            self::FREQUENCY_TYPE_DAILY => 'bg-blue-100 text-blue-800',
            self::FREQUENCY_TYPE_WEEKLY => 'bg-green-100 text-green-800',
            self::FREQUENCY_TYPE_MONTHLY => 'bg-yellow-100 text-yellow-800',
            self::FREQUENCY_TYPE_QUARTERLY => 'bg-orange-100 text-orange-800',
            self::FREQUENCY_TYPE_SEMI_ANNUALLY => 'bg-purple-100 text-purple-800',
            self::FREQUENCY_TYPE_ANNUALLY => 'bg-indigo-100 text-indigo-800',
            self::FREQUENCY_TYPE_CUSTOM => 'bg-gray-100 text-gray-800',
            self::FREQUENCY_TYPE_HOURS => 'bg-cyan-100 text-cyan-800',
            self::FREQUENCY_TYPE_CYCLES => 'bg-pink-100 text-pink-800',
            self::FREQUENCY_TYPE_MILES => 'bg-teal-100 text-teal-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getOverdueBadgeClass(): string
    {
        if (!$this->isOverdue()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        $daysOverdue = $this->getDaysOverdue();
        
        if ($daysOverdue <= 7) {
            return 'bg-yellow-100 text-yellow-800';
        } elseif ($daysOverdue <= 30) {
            return 'bg-orange-100 text-orange-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    public function getDueSoonBadgeClass(): string
    {
        if ($this->isOverdue()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isDueToday()) {
            return 'bg-orange-100 text-orange-800';
        }
        
        if ($this->isDueSoon(7)) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->isDueSoon(30)) {
            return 'bg-blue-100 text-blue-800';
        }
        
        return 'bg-green-100 text-green-800';
    }
}
