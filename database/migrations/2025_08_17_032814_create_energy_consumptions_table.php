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
        Schema::create('energy_consumptions', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('energy_contract_id')->nullable()->constrained()->nullOnDelete();
            $table->string('meter_id')->nullable(); // ID del medidor
            
            // Información temporal
            $table->datetime('measurement_datetime');
            $table->date('measurement_date'); // Para índices rápidos
            $table->time('measurement_time');
            $table->enum('period_type', [
                'instant',          // Lectura instantánea
                'hourly',          // Consumo por hora
                'daily',           // Consumo diario
                'monthly',         // Consumo mensual
                'billing_period'   // Período de facturación
            ]);
            
            // Mediciones energéticas
            $table->decimal('consumption_kwh', 12, 4); // Consumo en kWh
            $table->decimal('peak_power_kw', 10, 3)->nullable(); // Potencia pico en kW
            $table->decimal('average_power_kw', 10, 3)->nullable(); // Potencia promedio
            $table->decimal('power_factor', 4, 3)->nullable(); // Factor de potencia
            
            // Discriminación horaria
            $table->decimal('peak_hours_consumption', 10, 4)->nullable(); // Horas punta
            $table->decimal('standard_hours_consumption', 10, 4)->nullable(); // Horas llano
            $table->decimal('valley_hours_consumption', 10, 4)->nullable(); // Horas valle
            
            // Información de tarifa
            $table->string('tariff_type')->nullable();
            $table->decimal('unit_price_eur_kwh', 8, 5)->nullable(); // Precio por kWh
            $table->decimal('total_cost_eur', 10, 2)->nullable();
            
            // Fuentes de energía
            $table->decimal('renewable_percentage', 5, 2)->default(0); // % renovable
            $table->decimal('grid_consumption_kwh', 12, 4)->default(0); // Consumo de red
            $table->decimal('self_consumption_kwh', 12, 4)->default(0); // Autoconsumo
            
            // Calidad de la energía
            $table->decimal('voltage_v', 8, 2)->nullable();
            $table->decimal('frequency_hz', 6, 3)->nullable();
            $table->decimal('thd_voltage_percentage', 5, 2)->nullable(); // Distorsión armónica
            $table->decimal('thd_current_percentage', 5, 2)->nullable();
            
            // Eficiencia y sostenibilidad
            $table->decimal('efficiency_percentage', 5, 2)->nullable();
            $table->decimal('estimated_co2_emissions_kg', 10, 3)->nullable();
            $table->decimal('carbon_intensity_kg_co2_kwh', 8, 5)->nullable();
            
            // Comparativas y benchmarks
            $table->decimal('vs_previous_period_percentage', 6, 2)->nullable();
            $table->decimal('vs_similar_users_percentage', 6, 2)->nullable();
            $table->decimal('efficiency_score', 5, 2)->nullable(); // 0-100
            
            // Condiciones ambientales (si aplica)
            $table->decimal('temperature_celsius', 5, 2)->nullable();
            $table->decimal('humidity_percentage', 5, 2)->nullable();
            $table->string('weather_condition')->nullable();
            
            // Metadatos del dispositivo/medidor
            $table->json('device_info')->nullable();
            $table->enum('data_quality', [
                'excellent',
                'good',
                'fair',
                'poor',
                'estimated'
            ])->default('good');
            $table->boolean('is_estimated')->default(false);
            $table->text('estimation_method')->nullable();
            
            // Alertas y notificaciones
            $table->boolean('consumption_alert_triggered')->default(false);
            $table->decimal('alert_threshold_kwh', 10, 4)->nullable();
            $table->text('alert_message')->nullable();
            
            // Información de procesamiento
            $table->timestamp('processed_at')->nullable();
            $table->json('processing_metadata')->nullable();
            $table->boolean('is_validated')->default(true);
            $table->text('validation_notes')->nullable();
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['user_id', 'measurement_date']);
            $table->index(['user_id', 'period_type', 'measurement_date']);
            $table->index(['energy_contract_id', 'measurement_date']);
            $table->index(['measurement_datetime']);
            $table->index(['meter_id', 'measurement_date']);
            $table->index(['period_type', 'measurement_date']);
            $table->unique(['user_id', 'measurement_datetime', 'period_type', 'meter_id'], 'energy_consumption_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_consumptions');
    }
};