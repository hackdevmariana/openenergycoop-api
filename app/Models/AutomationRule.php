<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutomationRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'rule_type',
        'trigger_type',
        'trigger_conditions',
        'action_type',
        'action_parameters',
        'target_entity_id',
        'target_entity_type',
        'is_active',
        'priority',
        'execution_frequency',
        'last_executed_at',
        'next_execution_at',
        'execution_count',
        'max_executions',
        'success_count',
        'failure_count',
        'last_error_message',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'tags',
        'schedule_cron',
        'timezone',
        'retry_on_failure',
        'max_retries',
        'retry_delay_minutes',
        'notification_emails',
        'webhook_url',
        'webhook_headers',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'action_parameters' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'execution_count' => 'integer',
        'max_executions' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
        'max_retries' => 'integer',
        'retry_delay_minutes' => 'integer',
        'last_executed_at' => 'datetime',
        'next_execution_at' => 'datetime',
        'approved_at' => 'datetime',
        'tags' => 'array',
        'webhook_headers' => 'array',
    ];

    // Enums
    const RULE_TYPE_SCHEDULED = 'scheduled';
    const RULE_TYPE_EVENT_DRIVEN = 'event_driven';
    const RULE_TYPE_CONDITION_BASED = 'condition_based';
    const RULE_TYPE_MANUAL = 'manual';
    const RULE_TYPE_WEBHOOK = 'webhook';

    const TRIGGER_TYPE_TIME = 'time';
    const TRIGGER_TYPE_EVENT = 'event';
    const TRIGGER_TYPE_CONDITION = 'condition';
    const TRIGGER_TYPE_THRESHOLD = 'threshold';
    const TRIGGER_TYPE_PATTERN = 'pattern';
    const TRIGGER_TYPE_EXTERNAL = 'external';

    const ACTION_TYPE_EMAIL = 'email';
    const ACTION_TYPE_SMS = 'sms';
    const ACTION_TYPE_WEBHOOK = 'webhook';
    const ACTION_TYPE_DATABASE = 'database';
    const ACTION_TYPE_API_CALL = 'api_call';
    const ACTION_TYPE_SYSTEM_COMMAND = 'system_command';
    const ACTION_TYPE_NOTIFICATION = 'notification';
    const ACTION_TYPE_REPORT = 'report';

    const EXECUTION_FREQUENCY_ONCE = 'once';
    const EXECUTION_FREQUENCY_HOURLY = 'hourly';
    const EXECUTION_FREQUENCY_DAILY = 'daily';
    const EXECUTION_FREQUENCY_WEEKLY = 'weekly';
    const EXECUTION_FREQUENCY_MONTHLY = 'monthly';
    const EXECUTION_FREQUENCY_CUSTOM = 'custom';

    public static function getRuleTypes(): array
    {
        return [
            self::RULE_TYPE_SCHEDULED => 'Programado',
            self::RULE_TYPE_EVENT_DRIVEN => 'Dirigido por Eventos',
            self::RULE_TYPE_CONDITION_BASED => 'Basado en Condiciones',
            self::RULE_TYPE_MANUAL => 'Manual',
            self::RULE_TYPE_WEBHOOK => 'Webhook',
        ];
    }

    public static function getTriggerTypes(): array
    {
        return [
            self::TRIGGER_TYPE_TIME => 'Tiempo',
            self::TRIGGER_TYPE_EVENT => 'Evento',
            self::TRIGGER_TYPE_CONDITION => 'Condición',
            self::TRIGGER_TYPE_THRESHOLD => 'Umbral',
            self::TRIGGER_TYPE_PATTERN => 'Patrón',
            self::TRIGGER_TYPE_EXTERNAL => 'Externo',
        ];
    }

    public static function getActionTypes(): array
    {
        return [
            self::ACTION_TYPE_EMAIL => 'Email',
            self::ACTION_TYPE_SMS => 'SMS',
            self::ACTION_TYPE_WEBHOOK => 'Webhook',
            self::ACTION_TYPE_DATABASE => 'Base de Datos',
            self::ACTION_TYPE_API_CALL => 'Llamada API',
            self::ACTION_TYPE_SYSTEM_COMMAND => 'Comando del Sistema',
            self::ACTION_TYPE_NOTIFICATION => 'Notificación',
            self::ACTION_TYPE_REPORT => 'Reporte',
        ];
    }

    public static function getExecutionFrequencies(): array
    {
        return [
            self::EXECUTION_FREQUENCY_ONCE => 'Una vez',
            self::EXECUTION_FREQUENCY_HOURLY => 'Cada hora',
            self::EXECUTION_FREQUENCY_DAILY => 'Diario',
            self::EXECUTION_FREQUENCY_WEEKLY => 'Semanal',
            self::EXECUTION_FREQUENCY_MONTHLY => 'Mensual',
            self::EXECUTION_FREQUENCY_CUSTOM => 'Personalizado',
        ];
    }

    // Relaciones
    public function targetEntity(): MorphTo
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
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('rule_type', $type);
    }

    public function scopeByTriggerType($query, $triggerType)
    {
        return $query->where('trigger_type', $triggerType);
    }

    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 8);
    }

    public function scopeByExecutionFrequency($query, $frequency)
    {
        return $query->where('execution_frequency', $frequency);
    }

    public function scopeScheduled($query)
    {
        return $query->where('rule_type', self::RULE_TYPE_SCHEDULED);
    }

    public function scopeEventDriven($query)
    {
        return $query->where('rule_type', self::RULE_TYPE_EVENT_DRIVEN);
    }

    public function scopeConditionBased($query)
    {
        return $query->where('rule_type', self::RULE_TYPE_CONDITION_BASED);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeReadyToExecute($query)
    {
        return $query->where('is_active', true)
                    ->whereNotNull('approved_at')
                    ->where(function($q) {
                        $q->whereNull('next_execution_at')
                          ->orWhere('next_execution_at', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('max_executions')
                          ->orWhere('execution_count', '<', \DB::raw('max_executions'));
                    });
    }

    public function scopeFailed($query)
    {
        return $query->where('failure_count', '>', 0);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success_count', '>', 0);
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

    public function isScheduled(): bool
    {
        return $this->rule_type === self::RULE_TYPE_SCHEDULED;
    }

    public function isEventDriven(): bool
    {
        return $this->rule_type === self::RULE_TYPE_EVENT_DRIVEN;
    }

    public function isConditionBased(): bool
    {
        return $this->rule_type === self::RULE_TYPE_CONDITION_BASED;
    }

    public function isManual(): bool
    {
        return $this->rule_type === self::RULE_TYPE_MANUAL;
    }

    public function isWebhook(): bool
    {
        return $this->rule_type === self::RULE_TYPE_WEBHOOK;
    }

    public function isTimeTriggered(): bool
    {
        return $this->trigger_type === self::TRIGGER_TYPE_TIME;
    }

    public function isEventTriggered(): bool
    {
        return $this->trigger_type === self::TRIGGER_TYPE_EVENT;
    }

    public function isConditionTriggered(): bool
    {
        return $this->trigger_type === self::TRIGGER_TYPE_CONDITION;
    }

    public function isThresholdTriggered(): bool
    {
        return $this->trigger_type === self::TRIGGER_TYPE_THRESHOLD;
    }

    public function isHighPriority(): bool
    {
        return $this->priority >= 8;
    }

    public function isUrgent(): bool
    {
        return $this->priority >= 9;
    }

    public function canExecute(): bool
    {
        if (!$this->isActive() || !$this->isApproved()) {
            return false;
        }

        if ($this->max_executions && $this->execution_count >= $this->max_executions) {
            return false;
        }

        if ($this->next_execution_at && $this->next_execution_at->isFuture()) {
            return false;
        }

        return true;
    }

    public function shouldRetry(): bool
    {
        if (!$this->retry_on_failure) {
            return false;
        }

        return $this->failure_count < $this->max_retries;
    }

    public function getNextRetryTime(): ?string
    {
        if (!$this->shouldRetry()) {
            return null;
        }

        $delayMinutes = $this->retry_delay_minutes * $this->failure_count;
        return now()->addMinutes($delayMinutes)->format('Y-m-d H:i:s');
    }

    public function getSuccessRate(): float
    {
        $totalExecutions = $this->success_count + $this->failure_count;
        
        if ($totalExecutions <= 0) {
            return 0;
        }
        
        return ($this->success_count / $totalExecutions) * 100;
    }

    public function getFailureRate(): float
    {
        $totalExecutions = $this->success_count + $this->failure_count;
        
        if ($totalExecutions <= 0) {
            return 0;
        }
        
        return ($this->failure_count / $totalExecutions) * 100;
    }

    public function getExecutionStatus(): string
    {
        if (!$this->isActive()) {
            return 'Inactivo';
        }
        
        if (!$this->isApproved()) {
            return 'Pendiente de Aprobación';
        }
        
        if ($this->max_executions && $this->execution_count >= $this->max_executions) {
            return 'Límite Alcanzado';
        }
        
        if ($this->next_execution_at && $this->next_execution_at->isFuture()) {
            return 'Programado';
        }
        
        return 'Listo para Ejecutar';
    }

    public function getFormattedPriority(): string
    {
        if ($this->priority >= 9) {
            return 'Crítico';
        } elseif ($this->priority >= 8) {
            return 'Alto';
        } elseif ($this->priority >= 6) {
            return 'Medio';
        } elseif ($this->priority >= 4) {
            return 'Bajo';
        } else {
            return 'Muy Bajo';
        }
    }

    public function getFormattedLastExecuted(): string
    {
        if (!$this->last_executed_at) {
            return 'Nunca ejecutado';
        }
        
        return $this->last_executed_at->format('d/m/Y H:i:s');
    }

    public function getFormattedNextExecution(): string
    {
        if (!$this->next_execution_at) {
            return 'No programado';
        }
        
        return $this->next_execution_at->format('d/m/Y H:i:s');
    }

    public function getFormattedSuccessRate(): string
    {
        return number_format($this->getSuccessRate(), 1) . '%';
    }

    public function getFormattedFailureRate(): string
    {
        return number_format($this->getFailureRate(), 1) . '%';
    }

    public function getFormattedRuleType(): string
    {
        return self::getRuleTypes()[$this->rule_type] ?? 'Desconocido';
    }

    public function getFormattedTriggerType(): string
    {
        return self::getTriggerTypes()[$this->trigger_type] ?? 'Desconocido';
    }

    public function getFormattedActionType(): string
    {
        return self::getActionTypes()[$this->action_type] ?? 'Desconocido';
    }

    public function getFormattedExecutionFrequency(): string
    {
        return self::getExecutionFrequencies()[$this->execution_frequency] ?? 'Desconocido';
    }

    public function getStatusBadgeClass(): string
    {
        if (!$this->is_active) {
            return 'bg-red-100 text-red-800';
        }
        
        if (!$this->isApproved()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->max_executions && $this->execution_count >= $this->max_executions) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->next_execution_at && $this->next_execution_at->isFuture()) {
            return 'bg-blue-100 text-blue-800';
        }
        
        return 'bg-green-100 text-green-800';
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->getFormattedPriority()) {
            'Crítico' => 'bg-red-100 text-red-800',
            'Alto' => 'bg-orange-100 text-orange-800',
            'Medio' => 'bg-yellow-100 text-yellow-800',
            'Bajo' => 'bg-blue-100 text-blue-800',
            'Muy Bajo' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getRuleTypeBadgeClass(): string
    {
        return match($this->rule_type) {
            self::RULE_TYPE_SCHEDULED => 'bg-blue-100 text-blue-800',
            self::RULE_TYPE_EVENT_DRIVEN => 'bg-green-100 text-green-800',
            self::RULE_TYPE_CONDITION_BASED => 'bg-yellow-100 text-yellow-800',
            self::RULE_TYPE_MANUAL => 'bg-gray-100 text-gray-800',
            self::RULE_TYPE_WEBHOOK => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTriggerTypeBadgeClass(): string
    {
        return match($this->trigger_type) {
            self::TRIGGER_TYPE_TIME => 'bg-blue-100 text-blue-800',
            self::TRIGGER_TYPE_EVENT => 'bg-green-100 text-green-800',
            self::TRIGGER_TYPE_CONDITION => 'bg-yellow-100 text-yellow-800',
            self::TRIGGER_TYPE_THRESHOLD => 'bg-orange-100 text-orange-800',
            self::TRIGGER_TYPE_PATTERN => 'bg-purple-100 text-purple-800',
            self::TRIGGER_TYPE_EXTERNAL => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getActionTypeBadgeClass(): string
    {
        return match($this->action_type) {
            self::ACTION_TYPE_EMAIL => 'bg-blue-100 text-blue-800',
            self::ACTION_TYPE_SMS => 'bg-green-100 text-green-800',
            self::ACTION_TYPE_WEBHOOK => 'bg-purple-100 text-purple-800',
            self::ACTION_TYPE_DATABASE => 'bg-yellow-100 text-yellow-800',
            self::ACTION_TYPE_API_CALL => 'bg-indigo-100 text-indigo-800',
            self::ACTION_TYPE_SYSTEM_COMMAND => 'bg-red-100 text-red-800',
            self::ACTION_TYPE_NOTIFICATION => 'bg-orange-100 text-orange-800',
            self::ACTION_TYPE_REPORT => 'bg-cyan-100 text-cyan-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getExecutionStatusBadgeClass(): string
    {
        return match($this->getExecutionStatus()) {
            'Inactivo' => 'bg-red-100 text-red-800',
            'Pendiente de Aprobación' => 'bg-yellow-100 text-yellow-800',
            'Límite Alcanzado' => 'bg-gray-100 text-gray-800',
            'Programado' => 'bg-blue-100 text-blue-800',
            'Listo para Ejecutar' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
