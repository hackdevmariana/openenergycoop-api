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
        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ej. "Pino", "Vid", "Plátano"
            $table->decimal('co2_equivalent_per_unit_kg', 8, 4); // kg de CO2 por unidad
            $table->string('image')->nullable(); // URL o path de la imagen
            $table->text('description')->nullable();
            $table->string('unit_label'); // ej. "árbol", "planta", "viña", etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('name');
            $table->index('is_active');
            $table->index('co2_equivalent_per_unit_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
