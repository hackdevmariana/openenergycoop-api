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
        Schema::create('energy_storages', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_asset_id')->nullable()->constrained()->nullOnDelete();
            
            // Identificación del sistema
            $table->string('system_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Tipo y tecnología
            $table->enum('storage_type', [
                'battery_lithium',      // Batería de litio
                'battery_lead_acid',    // Batería de plomo
                'battery_flow',         // Batería de flujo
                'pumped_hydro',         // Bombeo hidráulico
                'compressed_air',       // Aire comprimido
                'flywheel',            // Volante de inercia
                'thermal',             // Almacenamiento térmico
                'hydrogen'             // Hidrógeno
            ]);
            
            $table->string('technology_details')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->year('installation_year')->nullable();
            
            // Capacidades y especificaciones técnicas
            $table->decimal('capacity_kwh', 12, 3); // Capacidad total en kWh
            $table->decimal('usable_capacity_kwh', 12, 3); // Capacidad utilizable
            $table->decimal('max_charge_power_kw', 10, 3); // Potencia máxima de carga
            $table->decimal('max_discharge_power_kw', 10, 3); // Potencia máxima de descarga
            
            // Estado actual del sistema
            $table->decimal('current_charge_kwh', 12, 3)->default(0);
            $table->decimal('charge_level_percentage', 5, 2)->default(0); // 0-100%
            $table->enum('status', [
                'online',              // En línea
                'offline',             // Fuera de línea
                'charging',            // Cargando
                'discharging',         // Descargando
                'standby',             // En espera
                'maintenance',         // Mantenimiento
                'error'                // Error
            ])->default('offline');
            
            // Métricas de rendimiento
            $table->decimal('round_trip_efficiency', 5, 2)->nullable(); // Eficiencia ida y vuelta %
            $table->decimal('charge_efficiency', 5, 2)->nullable(); // Eficiencia de carga %
            $table->decimal('discharge_efficiency', 5, 2)->nullable(); // Eficiencia de descarga %
            $table->integer('cycle_count')->default(0); // Número de ciclos
            $table->decimal('depth_of_discharge_avg', 5, 2)->nullable(); // Profundidad promedio de descarga
            
            // Vida útil y degradación
            $table->integer('expected_lifecycle_years')->nullable();
            $table->integer('expected_cycles')->nullable();
            $table->decimal('capacity_degradation_percentage', 5, 2)->default(0);
            $table->decimal('current_health_percentage', 5, 2)->default(100);
            
            // Información económica
            $table->decimal('installation_cost', 12, 2)->nullable();
            $table->decimal('maintenance_cost_annual', 10, 2)->nullable();
            $table->decimal('replacement_cost', 12, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            
            // Configuración operativa
            $table->decimal('min_charge_level', 5, 2)->default(10); // Nivel mínimo de carga %
            $table->decimal('max_charge_level', 5, 2)->default(95); // Nivel máximo de carga %
            $table->boolean('auto_management_enabled')->default(true);
            $table->json('charge_schedule')->nullable(); // Horarios de carga programada
            $table->json('discharge_schedule')->nullable(); // Horarios de descarga programada
            
            // Integración con red y tarifas
            $table->boolean('grid_tied')->default(true);
            $table->boolean('islanding_capable')->default(false);
            $table->decimal('feed_in_tariff_eur_kwh', 8, 5)->nullable();
            $table->decimal('time_of_use_optimization', 5, 2)->nullable();
            
            // Condiciones ambientales
            $table->decimal('operating_temp_min', 6, 2)->nullable();
            $table->decimal('operating_temp_max', 6, 2)->nullable();
            $table->decimal('current_temperature', 6, 2)->nullable();
            $table->decimal('humidity_percentage', 5, 2)->nullable();
            $table->string('location_description')->nullable();
            
            // Seguridad y protecciones
            $table->json('safety_systems')->nullable();
            $table->boolean('fire_suppression')->default(false);
            $table->boolean('theft_protection')->default(false);
            $table->json('protective_devices')->nullable();
            
            // Monitorización y control
            $table->boolean('remote_monitoring')->default(true);
            $table->boolean('remote_control')->default(false);
            $table->string('monitoring_system')->nullable();
            $table->json('communication_protocols')->nullable();
            
            // Sostenibilidad
            $table->decimal('co2_footprint_manufacturing_kg', 12, 2)->nullable();
            $table->decimal('co2_savings_annual_kg', 10, 2)->nullable();
            $table->boolean('recyclable_materials')->default(false);
            $table->decimal('recycling_percentage', 5, 2)->nullable();
            
            // Mantenimiento
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->integer('maintenance_interval_months')->default(12);
            $table->text('maintenance_notes')->nullable();
            
            // Garantías y seguros
            $table->date('warranty_end_date')->nullable();
            $table->string('warranty_provider')->nullable();
            $table->decimal('insurance_value', 12, 2)->nullable();
            $table->date('insurance_expiry_date')->nullable();
            
            // Metadatos
            $table->json('technical_specifications')->nullable();
            $table->json('certifications')->nullable();
            $table->json('custom_fields')->nullable();
            $table->text('notes')->nullable();
            
            // Estado del registro
            $table->boolean('is_active')->default(true);
            $table->timestamp('commissioned_at')->nullable();
            $table->timestamp('decommissioned_at')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'is_active']);
            $table->index(['provider_id', 'storage_type']);
            $table->index(['status', 'is_active']);
            $table->index(['system_id']);
            $table->index(['storage_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_storages');
    }
};