<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_indicators', function (Blueprint $table) {
            $table->id();
            
            // Información básica del indicador
            $table->string('indicator_name');
            $table->string('indicator_code')->unique();
            $table->text('description')->nullable();
            $table->enum('indicator_type', [
                'kpi', 'metric', 'target', 'benchmark', 'efficiency',
                'utilization', 'quality', 'satisfaction', 'custom'
            ]);
            $table->enum('category', [
                'operational', 'financial', 'technical', 'customer',
                'environmental', 'safety', 'quality', 'strategic'
            ]);
            
            // Clasificación y prioridad
            $table->enum('criticality', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->integer('priority')->default(1); // 1-5
            $table->enum('frequency', [
                'real_time', 'hourly', 'daily', 'weekly', 'monthly',
                'quarterly', 'yearly', 'on_demand'
            ]);
            $table->boolean('is_active')->default(true);
            
            // Alcance y entidad relacionada
            $table->enum('scope', [
                'system', 'cooperative', 'user', 'provider', 'product',
                'energy_sharing', 'subscription', 'asset', 'custom'
            ]);
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_name')->nullable(); // Para referencia rápida
            
            // Período de medición
            $table->timestamp('measurement_timestamp');
            $table->date('measurement_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('period_type', [
                'instant', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'
            ]);
            
            // Valores del indicador
            $table->decimal('current_value', 15, 4);
            $table->string('unit', 50)->nullable(); // %, €, kWh, ratio, etc.
            $table->decimal('target_value', 15, 4)->nullable();
            $table->decimal('baseline_value', 15, 4)->nullable();
            $table->decimal('previous_value', 15, 4)->nullable();
            $table->decimal('best_value', 15, 4)->nullable();
            $table->decimal('worst_value', 15, 4)->nullable();
            
            // Análisis y tendencias
            $table->decimal('change_absolute', 15, 4)->nullable();
            $table->decimal('change_percentage', 8, 2)->nullable();
            $table->decimal('target_achievement_percentage', 8, 2)->nullable();
            $table->enum('trend_direction', ['up', 'down', 'stable', 'volatile'])->nullable();
            $table->decimal('trend_strength', 5, 2)->nullable(); // 0-100
            $table->enum('performance_status', [
                'excellent', 'good', 'acceptable', 'poor', 'critical'
            ])->nullable();
            
            // Cálculo y metodología
            $table->text('calculation_formula')->nullable();
            $table->json('calculation_parameters')->nullable();
            $table->json('data_sources')->nullable();
            $table->enum('calculation_method', [
                'automatic', 'manual', 'semi_automatic', 'imported'
            ])->default('automatic');
            $table->decimal('confidence_level', 5, 2)->nullable(); // 0-100
            
            // Benchmarking y comparación
            $table->decimal('industry_benchmark', 15, 4)->nullable();
            $table->decimal('competitor_benchmark', 15, 4)->nullable();
            $table->decimal('internal_benchmark', 15, 4)->nullable();
            $table->enum('benchmark_comparison', [
                'above', 'at', 'below', 'significantly_above', 'significantly_below'
            ])->nullable();
            
            // Contexto y factores influyentes
            $table->json('influencing_factors')->nullable();
            $table->text('context_notes')->nullable();
            $table->json('external_conditions')->nullable(); // Clima, mercado, etc.
            $table->decimal('seasonality_factor', 5, 2)->nullable();
            $table->boolean('weather_dependent')->default(false);
            
            // Alertas y umbrales
            $table->boolean('alerts_enabled')->default(false);
            $table->decimal('alert_threshold_min', 15, 4)->nullable();
            $table->decimal('alert_threshold_max', 15, 4)->nullable();
            $table->decimal('warning_threshold_min', 15, 4)->nullable();
            $table->decimal('warning_threshold_max', 15, 4)->nullable();
            $table->enum('current_alert_level', [
                'normal', 'warning', 'critical', 'emergency'
            ])->default('normal');
            $table->timestamp('last_alert_sent_at')->nullable();
            
            // Acciones y mejoras
            $table->text('improvement_actions')->nullable();
            $table->text('corrective_actions')->nullable();
            $table->decimal('improvement_potential', 8, 2)->nullable(); // % mejora posible
            $table->date('next_review_date')->nullable();
            $table->enum('action_priority', ['low', 'medium', 'high', 'urgent'])->nullable();
            
            // Impacto y valor empresarial
            $table->enum('business_impact', [
                'low', 'medium', 'high', 'strategic'
            ])->default('medium');
            $table->decimal('financial_impact_eur', 10, 2)->nullable();
            $table->text('business_value_description')->nullable();
            $table->json('stakeholders')->nullable(); // Partes interesadas
            
            // Métricas específicas por categoría
            // Operacionales
            $table->decimal('efficiency_percentage', 5, 2)->nullable();
            $table->decimal('utilization_percentage', 5, 2)->nullable();
            $table->decimal('availability_percentage', 5, 2)->nullable();
            $table->integer('downtime_minutes')->nullable();
            
            // Financieras
            $table->decimal('cost_per_unit', 10, 4)->nullable();
            $table->decimal('revenue_impact_eur', 10, 2)->nullable();
            $table->decimal('roi_percentage', 8, 2)->nullable();
            $table->decimal('payback_months', 6, 2)->nullable();
            
            // Calidad y satisfacción
            $table->decimal('quality_score', 5, 2)->nullable(); // 0-100
            $table->decimal('satisfaction_score', 5, 2)->nullable(); // 0-100
            $table->integer('defects_count')->nullable();
            $table->decimal('error_rate_percentage', 5, 2)->nullable();
            
            // Técnicas
            $table->decimal('system_load_percentage', 5, 2)->nullable();
            $table->decimal('response_time_ms', 10, 2)->nullable();
            $table->decimal('throughput_per_hour', 10, 2)->nullable();
            $table->integer('concurrent_users')->nullable();
            
            // Relaciones
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('energy_report_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('created_by_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('validated_by_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Validación y auditoría
            $table->boolean('is_validated')->default(false);
            $table->timestamp('validated_at')->nullable();
            $table->text('validation_notes')->nullable();
            $table->json('audit_log')->nullable();
            $table->integer('revision_number')->default(1);
            
            // Configuración de dashboard
            $table->boolean('show_in_dashboard')->default(true);
            $table->json('dashboard_config')->nullable();
            $table->enum('chart_type', [
                'line', 'bar', 'pie', 'gauge', 'number', 'trend', 'heatmap'
            ])->nullable();
            $table->integer('dashboard_order')->nullable();
            
            // Automatización
            $table->boolean('auto_calculate')->default(true);
            $table->time('calculation_time')->nullable();
            $table->json('automation_rules')->nullable();
            $table->timestamp('last_calculated_at')->nullable();
            $table->integer('calculation_attempts')->default(0);
            $table->text('last_calculation_error')->nullable();
            
            // Metadata y tags
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('public_name')->nullable();
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['indicator_type', 'category']);
            $table->index(['scope', 'entity_type', 'entity_id']);
            $table->index(['measurement_date', 'period_type']);
            $table->index(['frequency', 'is_active']);
            $table->index(['performance_status', 'current_alert_level'], 'perf_status_alert_idx');
            $table->index(['energy_cooperative_id', 'user_id']);
            $table->index(['auto_calculate', 'calculation_time']);
            $table->index(['show_in_dashboard', 'dashboard_order']);
            $table->index(['criticality', 'priority']);
            
            // Índice único para evitar duplicados
            $table->unique([
                'indicator_code', 'entity_type', 'entity_id', 
                'measurement_timestamp'
            ], 'performance_indicators_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_indicators');
    }
};