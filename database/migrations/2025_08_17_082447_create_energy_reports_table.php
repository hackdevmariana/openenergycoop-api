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
        Schema::create('energy_reports', function (Blueprint $table) {
            $table->id();
            
            // Información básica del reporte
            $table->string('title');
            $table->string('report_code')->unique();
            $table->text('description')->nullable();
            $table->enum('report_type', [
                'consumption', 'production', 'trading', 'savings', 
                'cooperative', 'user', 'system', 'custom'
            ]);
            $table->enum('report_category', [
                'energy', 'financial', 'environmental', 'operational', 'performance'
            ]);
            
            // Alcance y filtros del reporte
            $table->enum('scope', ['user', 'cooperative', 'provider', 'system', 'custom']);
            $table->json('scope_filters')->nullable(); // IDs específicos según el scope
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('period_type', [
                'daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'
            ]);
            
            // Configuración de generación
            $table->enum('generation_frequency', [
                'on_demand', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'
            ])->default('on_demand');
            $table->time('generation_time')->nullable(); // Hora de generación automática
            $table->json('generation_config')->nullable(); // Configuración adicional
            $table->boolean('auto_generate')->default(false);
            $table->boolean('send_notifications')->default(true);
            $table->json('notification_recipients')->nullable(); // Emails para envío
            
            // Estado y metadatos
            $table->enum('status', [
                'draft', 'generating', 'completed', 'failed', 'scheduled', 'cancelled'
            ])->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->integer('generation_attempts')->default(0);
            $table->text('generation_error')->nullable();
            $table->integer('file_size_bytes')->nullable();
            
            // Contenido del reporte
            $table->json('data_summary')->nullable(); // Resumen de datos principales
            $table->json('metrics')->nullable(); // Métricas calculadas
            $table->json('charts_config')->nullable(); // Configuración de gráficos
            $table->json('tables_data')->nullable(); // Datos tabulares
            $table->text('insights')->nullable(); // Insights automáticos generados
            $table->text('recommendations')->nullable(); // Recomendaciones
            
            // Archivos generados
            $table->string('pdf_path')->nullable();
            $table->string('excel_path')->nullable();
            $table->string('csv_path')->nullable();
            $table->string('json_path')->nullable();
            $table->json('export_formats')->nullable(); // Formatos disponibles
            
            // Configuración de visualización
            $table->json('dashboard_config')->nullable(); // Config para dashboard web
            $table->boolean('is_public')->default(false);
            $table->string('public_share_token')->nullable();
            $table->timestamp('public_expires_at')->nullable();
            $table->json('access_permissions')->nullable(); // Permisos específicos
            
            // Métricas de rendimiento
            $table->integer('total_records_processed')->nullable();
            $table->decimal('processing_time_seconds', 8, 3)->nullable();
            $table->integer('data_quality_score')->nullable(); // 0-100
            $table->json('data_sources')->nullable(); // Fuentes de datos utilizadas
            
            // Comparación temporal
            $table->boolean('include_comparison')->default(false);
            $table->date('comparison_period_start')->nullable();
            $table->date('comparison_period_end')->nullable();
            $table->json('comparison_metrics')->nullable();
            
            // Relaciones
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('created_by_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('energy_reports')->onDelete('set null');
            
            // Configuración de cache
            $table->boolean('cache_enabled')->default(true);
            $table->integer('cache_duration_minutes')->default(60);
            $table->timestamp('cache_expires_at')->nullable();
            $table->string('cache_key')->nullable();
            
            // Métricas de uso
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('last_downloaded_at')->nullable();
            $table->json('viewer_stats')->nullable(); // Estadísticas de visualización
            
            // Metadata y tags
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->integer('priority')->default(1); // 1-5
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['status', 'generation_frequency']);
            $table->index(['report_type', 'report_category']);
            $table->index(['scope', 'period_start', 'period_end']);
            $table->index(['auto_generate', 'scheduled_for']);
            $table->index(['user_id', 'energy_cooperative_id']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_reports');
    }
};