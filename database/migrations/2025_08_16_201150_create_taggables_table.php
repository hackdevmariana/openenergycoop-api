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
        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->morphs('taggable'); // taggable_id, taggable_type
            $table->decimal('weight', 5, 2)->default(1.0); // Peso de la etiqueta para el elemento
            $table->integer('sort_order')->default(0); // Orden específico para este elemento
            $table->json('metadata')->nullable(); // Metadatos específicos de la relación
            $table->timestamps();
            
            // Índices
            $table->unique(['tag_id', 'taggable_id', 'taggable_type']); // Evitar duplicados
            $table->index(['taggable_type', 'taggable_id']);
            $table->index('weight');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};