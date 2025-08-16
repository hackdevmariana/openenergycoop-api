<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained('municipalities')->onDelete('cascade');
            $table->decimal('temperature', 5, 2)->nullable(); // Temperatura en grados Celsius (-99.99 a 999.99)
            $table->decimal('cloud_coverage', 5, 2)->nullable(); // Cobertura de nubes en porcentaje (0.00 a 100.00)
            $table->decimal('solar_radiation', 8, 2)->nullable(); // Radiación solar en W/m² (0.00 a 999999.99)
            $table->timestamp('timestamp'); // Momento exacto de la medición
            $table->timestamps();

            // Índices para consultas eficientes
            $table->index('municipality_id');
            $table->index('timestamp');
            $table->index(['municipality_id', 'timestamp']);
            
            // Índice para consultas de rangos temporales
            $table->index(['timestamp', 'municipality_id']);
            
            // Evitar duplicados exactos
            $table->unique(['municipality_id', 'timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_snapshots');
    }
};