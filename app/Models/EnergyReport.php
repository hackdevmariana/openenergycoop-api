<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class EnergyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'report_code',
        'description',
        'report_type',
        'report_category',
        'scope',
        'scope_filters',
        'period_start',
        'period_end',
        'period_type',
        'generation_frequency',
        'generation_time',
        'generation_config',
        'auto_generate',
        'send_notifications',
        'notification_recipients',
        'status',
        'generated_at',
        'scheduled_for',
        'generation_attempts',
        'generation_error',
        'file_size_bytes',
        'data_summary',
        'metrics',
        'charts_config',
        'tables_data',
        'insights',
        'recommendations',
        'pdf_path',
        'excel_path',
        'csv_path',
        'json_path',
        'export_formats',
        'dashboard_config',
        'is_public',
        'public_share_token',
        'public_expires_at',
        'access_permissions',
        'total_records_processed',
        'processing_time_seconds',
        'data_quality_score',
        'data_sources',
        'include_comparison',
        'comparison_period_start',
        'comparison_period_end',
        'comparison_metrics',
        'user_id',
        'energy_cooperative_id',
        'created_by_id',
        'template_id',
        'cache_enabled',
        'cache_duration_minutes',
        'cache_expires_at',
        'cache_key',
        'view_count',
        'download_count',
        'last_viewed_at',
        'last_downloaded_at',
        'viewer_stats',
        'tags',
        'metadata',
        'notes',
        'priority',
    ];

    protected $casts = [
        'scope_filters' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'generation_config' => 'array',
        'auto_generate' => 'boolean',
        'send_notifications' => 'boolean',
        'notification_recipients' => 'array',
        'generated_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'generation_attempts' => 'integer',
        'file_size_bytes' => 'integer',
        'data_summary' => 'array',
        'metrics' => 'array',
        'charts_config' => 'array',
        'tables_data' => 'array',
        'export_formats' => 'array',
        'dashboard_config' => 'array',
        'is_public' => 'boolean',
        'public_expires_at' => 'datetime',
        'access_permissions' => 'array',
        'total_records_processed' => 'integer',
        'processing_time_seconds' => 'decimal:3',
        'data_quality_score' => 'integer',
        'data_sources' => 'array',
        'include_comparison' => 'boolean',
        'comparison_period_start' => 'date',
        'comparison_period_end' => 'date',
        'comparison_metrics' => 'array',
        'cache_enabled' => 'boolean',
        'cache_duration_minutes' => 'integer',
        'cache_expires_at' => 'datetime',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'last_viewed_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'viewer_stats' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'priority' => 'integer',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function energyCooperative(): BelongsTo
    {
        return $this->belongsTo(EnergyCooperative::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EnergyReport::class, 'template_id');
    }

    public function sustainabilityMetrics(): HasMany
    {
        return $this->hasMany(SustainabilityMetric::class);
    }

    public function performanceIndicators(): HasMany
    {
        return $this->hasMany(PerformanceIndicator::class);
    }

    // Scopes
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('report_type', $type);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('report_category', $category);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByScope(Builder $query, string $scope): Builder
    {
        return $query->where('scope', $scope);
    }

    public function scopeAutoGenerate(Builder $query): Builder
    {
        return $query->where('auto_generate', true);
    }

    public function scopeScheduledForGeneration(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_for', '<=', now());
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true)
            ->where(function($q) {
                $q->whereNull('public_expires_at')
                  ->orWhere('public_expires_at', '>', now());
            });
    }

    public function scopeByPeriod(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->where('period_start', '>=', $start)
            ->where('period_end', '<=', $end);
    }

    // Métodos de utilidad
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isGenerating(): bool
    {
        return $this->status === 'generating';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function canGenerate(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'failed']);
    }

    public function isExpired(): bool
    {
        if (!$this->is_public || !$this->public_expires_at) {
            return false;
        }
        
        return $this->public_expires_at < now();
    }

    public function isCacheValid(): bool
    {
        if (!$this->cache_enabled || !$this->cache_expires_at) {
            return false;
        }
        
        return $this->cache_expires_at > now();
    }

    public function hasExportFormat(string $format): bool
    {
        return in_array($format, $this->export_formats ?? []);
    }

    public function getExportPath(string $format): ?string
    {
        return match($format) {
            'pdf' => $this->pdf_path,
            'excel' => $this->excel_path,
            'csv' => $this->csv_path,
            'json' => $this->json_path,
            default => null,
        };
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
        $this->update(['last_viewed_at' => now()]);
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    public function generatePublicShareToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update(['public_share_token' => $token]);
        return $token;
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'generated_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'generation_error' => $error,
            'generation_attempts' => $this->generation_attempts + 1,
        ]);
    }

    public function scheduleGeneration(Carbon $scheduledFor): void
    {
        $this->update([
            'status' => 'scheduled',
            'scheduled_for' => $scheduledFor,
        ]);
    }

    public function getDataQualityLevel(): string
    {
        $score = $this->data_quality_score ?? 0;
        
        return match(true) {
            $score >= 90 => 'excellent',
            $score >= 80 => 'good',
            $score >= 70 => 'acceptable',
            $score >= 60 => 'poor',
            default => 'critical',
        };
    }

    public function getPriorityLevel(): string
    {
        return match($this->priority) {
            5 => 'critical',
            4 => 'high',
            3 => 'medium',
            2 => 'low',
            default => 'lowest',
        };
    }

    // Métodos de generación de reportes
    public function shouldGenerate(): bool
    {
        if (!$this->auto_generate) {
            return false;
        }

        if (!$this->isScheduled()) {
            return false;
        }

        return $this->scheduled_for <= now();
    }

    public function getNextGenerationTime(): ?Carbon
    {
        if (!$this->auto_generate || !$this->generation_time) {
            return null;
        }

        $nextGeneration = Carbon::today()->setTimeFromTimeString($this->generation_time);

        // Si ya pasó la hora de hoy, programar para mañana
        if ($nextGeneration < now()) {
            $nextGeneration->addDay();
        }

        // Ajustar según la frecuencia
        return match($this->generation_frequency) {
            'weekly' => $nextGeneration->addWeek(),
            'monthly' => $nextGeneration->addMonth(),
            'quarterly' => $nextGeneration->addQuarter(),
            'yearly' => $nextGeneration->addYear(),
            default => $nextGeneration,
        };
    }

    public function calculatePeriodDuration(): int
    {
        return $this->period_start->diffInDays($this->period_end);
    }

    public function getScopeDescription(): string
    {
        $scopeLabels = [
            'user' => 'Usuario',
            'cooperative' => 'Cooperativa',
            'provider' => 'Proveedor',
            'system' => 'Sistema Completo',
            'custom' => 'Personalizado',
        ];

        return $scopeLabels[$this->scope] ?? 'Desconocido';
    }
}