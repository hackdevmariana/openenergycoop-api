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
        Schema::create('energy_productions', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('energy_storage_id')->nullable();
            
            // Identificación del sistema
            $table->string('system_id')->nullable();
            $table->string('inverter_id')->nullable();
            
            // Información temporal
            $table->datetime('production_datetime');
            $table->date('production_date'); // Para índices rápidos
            $table->time('production_time');
            $table->enum('period_type', [
                'instant',          // Lectura instantánea
                'hourly',          // Producción por hora
                'daily',           // Producción diaria
                'monthly',         // Producción mensual
                'annual'           // Producción anual
            ]);
            
            // Tipo de fuente energética
            $table->enum('energy_source', [
                'solar_pv',         // Fotovoltaica
                'solar_thermal',    // Solar térmica
                'wind',             // Eólica
                'hydro',           // Hidroeléctrica
                'biomass',         // Biomasa
                'geothermal',      // Geotérmica
                'biogas',          // Biogás
                'combined'         // Combinada
            ]);
            
            // Mediciones de producción
            $table->decimal('production_kwh', 12, 4); // Producción en kWh
            $table->decimal('peak_power_kw', 10, 3)->nullable(); // Potencia pico generada
            $table->decimal('average_power_kw', 10, 3)->nullable(); // Potencia promedio
            $table->decimal('instantaneous_power_kw', 10, 3)->nullable(); // Potencia instantánea
            
            // Distribución de la energía producida
            $table->decimal('self_consumption_kwh', 12, 4)->default(0); // Autoconsumo
            $table->decimal('grid_injection_kwh', 12, 4)->default(0); // Inyección a red
            $table->decimal('storage_charge_kwh', 12, 4)->default(0); // Carga de almacenamiento
            $table->decimal('curtailed_kwh', 12, 4)->default(0); // Energía vertida/perdida
            
            // Eficiencia del sistema
            $table->decimal('system_efficiency', 5, 2)->nullable(); // Eficiencia global %
            $table->decimal('inverter_efficiency', 5, 2)->nullable(); // Eficiencia del inversor %
            $table->decimal('performance_ratio', 5, 2)->nullable(); // PR del sistema %
            $table->decimal('capacity_factor', 5, 2)->nullable(); // Factor de capacidad %
            
            // Condiciones ambientales que afectan la producción
            $table->decimal('irradiance_w_m2', 8, 2)->nullable(); // Irradiancia solar
            $table->decimal('wind_speed_ms', 6, 2)->nullable(); // Velocidad del viento
            $table->decimal('wind_direction_degrees', 5, 1)->nullable(); // Dirección del viento
            $table->decimal('ambient_temperature', 6, 2)->nullable(); // Temperatura ambiente
            $table->decimal('module_temperature', 6, 2)->nullable(); // Temperatura de módulos
            $table->decimal('humidity_percentage', 5, 2)->nullable(); // Humedad relativa
            $table->decimal('atmospheric_pressure', 8, 2)->nullable(); // Presión atmosférica
            
            // Información económica
            $table->decimal('feed_in_tariff_eur_kwh', 8, 5)->nullable(); // Tarifa de inyección
            $table->decimal('market_price_eur_kwh', 8, 5)->nullable(); // Precio de mercado
            $table->decimal('revenue_eur', 10, 2)->nullable(); // Ingresos generados
            $table->decimal('savings_eur', 10, 2)->nullable(); // Ahorros por autoconsumo
            
            // Calidad de la energía
            $table->decimal('voltage_v', 8, 2)->nullable(); // Tensión
            $table->decimal('frequency_hz', 6, 3)->nullable(); // Frecuencia
            $table->decimal('power_factor', 4, 3)->nullable(); // Factor de potencia
            $table->decimal('thd_percentage', 5, 2)->nullable(); // Distorsión armónica total
            
            // Predicciones vs realidad
            $table->decimal('forecasted_production_kwh', 12, 4)->nullable();
            $table->decimal('forecast_accuracy_percentage', 5, 2)->nullable();
            $table->text('forecast_model_used')->nullable();
            
            // Sostenibilidad e impacto ambiental
            $table->decimal('co2_avoided_kg', 10, 3)->nullable(); // CO2 evitado
            $table->decimal('carbon_intensity_avoided', 8, 5)->nullable(); // Intensidad carbono evitada
            $table->decimal('renewable_percentage', 5, 2)->default(100); // % renovable
            
            // Estado operativo del sistema
            $table->enum('operational_status', [
                'online',           // En línea
                'offline',          // Fuera de línea
                'maintenance',      // En mantenimiento
                'error',           // Con errores
                'curtailed',       // Limitado
                'standby'          // En espera
            ])->default('online');
            
            $table->text('status_notes')->nullable();
            
            // Alertas y anomalías
            $table->boolean('underperformance_alert')->default(false);
            $table->decimal('underperformance_threshold', 5, 2)->nullable();
            $table->json('system_alerts')->nullable();
            $table->json('error_codes')->nullable();
            
            // Información de mantenimiento
            $table->boolean('cleaning_required')->default(false);
            $table->date('last_cleaning_date')->nullable();
            $table->decimal('soiling_losses_percentage', 5, 2)->nullable();
            $table->decimal('shading_losses_percentage', 5, 2)->nullable();
            
            // Datos del inversor
            $table->json('inverter_data')->nullable();
            $table->decimal('inverter_temperature', 6, 2)->nullable();
            $table->string('inverter_status')->nullable();
            
            // Metadatos de calidad de datos
            $table->enum('data_quality', [
                'measured',         // Medido
                'estimated',        // Estimado
                'interpolated',     // Interpolado
                'forecasted'        // Pronosticado
            ])->default('measured');
            
            $table->boolean('is_validated')->default(true);
            $table->text('validation_notes')->nullable();
            $table->json('measurement_metadata')->nullable();
            
            // Información de procesamiento
            $table->timestamp('processed_at')->nullable();
            $table->string('data_source')->nullable(); // Origen de los datos
            $table->json('processing_flags')->nullable();
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['user_id', 'production_date']);
            $table->index(['user_id', 'energy_source', 'production_date']);
            $table->index(['user_asset_id', 'production_date']);
            $table->index(['period_type', 'production_date']);
            $table->index(['energy_source', 'production_date']);
            $table->index(['production_datetime']);
            $table->index(['operational_status', 'production_date']);
            $table->unique(['user_id', 'system_id', 'production_datetime', 'period_type'], 'energy_production_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_productions');
    }
};