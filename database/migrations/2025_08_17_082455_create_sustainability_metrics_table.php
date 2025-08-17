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
        Schema::create('sustainability_metrics', function (Blueprint $table) {
            $table->id();
            
            // Información básica de la métrica
            $table->string('metric_name');
            $table->string('metric_code')->unique();
            $table->text('description')->nullable();
            $table->enum('metric_type', [
                'carbon_footprint', 'renewable_percentage', 'energy_efficiency',
                'waste_reduction', 'water_usage', 'biodiversity_impact',
                'social_impact', 'economic_impact', 'custom'
            ]);
            $table->enum('metric_category', [
                'environmental', 'social', 'economic', 'governance', 'operational'
            ]);
            
            // Alcance y entidad relacionada
            $table->enum('entity_type', [
                'user', 'cooperative', 'provider', 'energy_sharing', 
                'energy_production', 'energy_consumption', 'system'
            ]);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_name')->nullable(); // Para referencia rápida
            
            // Período de medición
            $table->date('measurement_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('period_type', [
                'daily', 'weekly', 'monthly', 'quarterly', 'yearly'
            ]);
            
            // Valores de la métrica
            $table->decimal('value', 15, 4);
            $table->string('unit', 50); // kg CO2, %, kWh, etc.
            $table->decimal('baseline_value', 15, 4)->nullable();
            $table->decimal('target_value', 15, 4)->nullable();
            $table->decimal('previous_period_value', 15, 4)->nullable();
            
            // Cálculos y tendencias
            $table->decimal('change_absolute', 15, 4)->nullable();
            $table->decimal('change_percentage', 8, 2)->nullable();
            $table->enum('trend', ['improving', 'declining', 'stable', 'unknown'])->nullable();
            $table->decimal('trend_score', 5, 2)->nullable(); // -100 a +100
            
            // Detalles de cálculo
            $table->json('calculation_method')->nullable(); // Método/fórmula utilizada
            $table->json('data_sources')->nullable(); // Fuentes de datos
            $table->json('calculation_details')->nullable(); // Detalle del cálculo
            $table->decimal('data_quality_score', 5, 2)->nullable(); // 0-100
            $table->json('assumptions')->nullable(); // Suposiciones utilizadas
            
            // Métricas específicas de carbono
            $table->decimal('co2_emissions_kg', 12, 3)->nullable();
            $table->decimal('co2_avoided_kg', 12, 3)->nullable();
            $table->decimal('carbon_offset_kg', 12, 3)->nullable();
            $table->decimal('carbon_intensity', 8, 4)->nullable(); // kg CO2/kWh
            
            // Métricas de energía renovable
            $table->decimal('renewable_energy_kwh', 12, 3)->nullable();
            $table->decimal('total_energy_kwh', 12, 3)->nullable();
            $table->decimal('renewable_percentage', 5, 2)->nullable();
            $table->decimal('fossil_fuel_displacement_kwh', 12, 3)->nullable();
            
            // Métricas económicas
            $table->decimal('cost_savings_eur', 10, 2)->nullable();
            $table->decimal('investment_recovery_eur', 10, 2)->nullable();
            $table->decimal('economic_impact_eur', 10, 2)->nullable();
            $table->integer('jobs_created')->nullable();
            $table->integer('jobs_sustained')->nullable();
            
            // Métricas sociales
            $table->integer('communities_impacted')->nullable();
            $table->integer('people_benefited')->nullable();
            $table->decimal('social_value_eur', 10, 2)->nullable();
            $table->integer('education_hours')->nullable();
            $table->integer('awareness_campaigns')->nullable();
            
            // Certificaciones y validación
            $table->boolean('is_certified')->default(false);
            $table->string('certification_body')->nullable();
            $table->string('certification_number')->nullable();
            $table->date('certification_date')->nullable();
            $table->date('certification_expires_at')->nullable();
            $table->enum('verification_status', [
                'unverified', 'self_reported', 'third_party', 'certified'
            ])->default('unverified');
            
            // Benchmarking
            $table->decimal('industry_benchmark', 15, 4)->nullable();
            $table->decimal('regional_benchmark', 15, 4)->nullable();
            $table->decimal('best_practice_benchmark', 15, 4)->nullable();
            $table->enum('performance_rating', [
                'poor', 'below_average', 'average', 'above_average', 'excellent'
            ])->nullable();
            
            // Objetivos y metas
            $table->boolean('contributes_to_sdg')->default(false); // ODS - Objetivos de Desarrollo Sostenible
            $table->json('sdg_targets')->nullable(); // Targets específicos de ODS
            $table->boolean('paris_agreement_aligned')->default(false);
            $table->json('sustainability_goals')->nullable(); // Objetivos internos
            
            // Reportes y comunicación
            $table->boolean('include_in_reports')->default(true);
            $table->boolean('is_public')->default(false);
            $table->text('public_description')->nullable();
            $table->json('visualization_config')->nullable(); // Config para gráficos
            $table->integer('report_priority')->default(1); // 1-5
            
            // Relaciones
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('energy_report_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('calculated_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Auditoría y trazabilidad
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('audit_trail')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            // Alertas y monitoreo
            $table->boolean('alert_enabled')->default(false);
            $table->decimal('alert_threshold_min', 15, 4)->nullable();
            $table->decimal('alert_threshold_max', 15, 4)->nullable();
            $table->enum('alert_status', ['normal', 'warning', 'critical'])->default('normal');
            $table->timestamp('last_alert_sent_at')->nullable();
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['metric_type', 'metric_category']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['measurement_date', 'period_type']);
            $table->index(['energy_cooperative_id', 'user_id']);
            $table->index(['is_certified', 'verification_status']);
            $table->index(['performance_rating', 'trend']);
            $table->index(['alert_enabled', 'alert_status']);
            
            // Índice único para evitar duplicados
            $table->unique([
                'metric_code', 'entity_type', 'entity_id', 
                'measurement_date', 'period_type'
            ], 'sustainability_metrics_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sustainability_metrics');
    }
};