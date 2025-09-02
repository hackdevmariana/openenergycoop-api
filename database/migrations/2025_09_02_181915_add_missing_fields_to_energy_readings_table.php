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
        Schema::table('energy_readings', function (Blueprint $table) {
            // Campos de validación y estimación
            $table->boolean('is_validated')->default(false)->after('quality_notes');
            $table->boolean('is_estimated')->default(false)->after('is_validated');
            
            // Campos de confianza y calidad
            $table->decimal('confidence_level', 5, 2)->nullable()->after('is_estimated');
            $table->string('data_quality')->nullable()->after('confidence_level');
            
            // Campos de energía y potencia
            $table->decimal('active_energy', 15, 4)->nullable()->after('consumption_value');
            $table->decimal('reactive_energy', 15, 4)->nullable()->after('active_energy');
            $table->decimal('instantaneous_power', 15, 4)->nullable()->after('reactive_energy');
            $table->decimal('apparent_power', 15, 4)->nullable()->after('instantaneous_power');
            $table->decimal('reactive_power', 15, 4)->nullable()->after('apparent_power');
            
            // Campos adicionales de datos
            $table->string('data_source')->nullable()->after('reading_source');
            $table->json('metadata')->nullable()->after('tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('energy_readings', function (Blueprint $table) {
            $table->dropColumn([
                'is_validated',
                'is_estimated',
                'confidence_level',
                'data_quality',
                'active_energy',
                'reactive_energy',
                'instantaneous_power',
                'apparent_power',
                'reactive_power',
                'data_source',
                'metadata'
            ]);
        });
    }
};
