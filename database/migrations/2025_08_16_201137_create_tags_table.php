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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable(); // Color hex #FF5733
            $table->string('icon')->nullable(); // Icono CSS o SVG
            $table->enum('type', [
                'general',          // General
                'energy_source',    // Solar, Eólica, etc.
                'technology',       // Tecnología específica
                'sustainability',   // Etiquetas de sostenibilidad
                'region',          // Etiquetas geográficas
                'certification',   // Certificaciones
                'feature',         // Características del producto
                'target_audience', // Público objetivo
                'price_range',     // Rango de precios
                'difficulty'       // Nivel de dificultad/complejidad
            ])->default('general');
            $table->integer('usage_count')->default(0); // Contador de uso
            $table->boolean('is_featured')->default(false); // Tags destacados
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // Orden de visualización
            $table->json('metadata')->nullable(); // Metadatos adicionales
            $table->timestamps();
            
            // Índices
            $table->index('type');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('usage_count');
            $table->index('sort_order');
            $table->fullText(['name', 'description']); // Búsqueda de texto completo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};