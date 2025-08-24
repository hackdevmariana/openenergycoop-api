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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', [
                'smart_meter',
                'battery',
                'ev_charger',
                'solar_panel',
                'wind_turbine',
                'heat_pump',
                'thermostat',
                'smart_plug',
                'energy_monitor',
                'grid_connection',
                'other'
            ]);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('consumption_point_id')->nullable()->constrained('consumption_points')->onDelete('set null');
            $table->string('api_endpoint')->nullable();
            $table->json('api_credentials')->nullable(); // Para almacenar credenciales de forma segura
            $table->json('device_config')->nullable(); // Configuración específica del dispositivo
            $table->boolean('active')->default(true);
            $table->string('model')->nullable(); // Modelo del dispositivo
            $table->string('manufacturer')->nullable(); // Fabricante
            $table->string('serial_number')->nullable(); // Número de serie
            $table->string('firmware_version')->nullable(); // Versión del firmware
            $table->timestamp('last_communication')->nullable(); // Última comunicación
            $table->json('capabilities')->nullable(); // Capacidades del dispositivo
            $table->string('location')->nullable(); // Ubicación física
            $table->text('notes')->nullable(); // Notas adicionales
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['user_id', 'type']);
            $table->index(['type', 'active']);
            $table->index('active');
            $table->index('last_communication');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
