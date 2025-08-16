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
        Schema::create('product_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->decimal('relevance_score', 5, 2)->default(1.0); // Puntuación de relevancia
            $table->boolean('is_primary')->default(false); // Tag principal del producto
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // Metadatos específicos de la relación producto-tag
            $table->timestamps();
            
            // Índices
            $table->unique(['product_id', 'tag_id']); // Evitar duplicados
            $table->index('relevance_score');
            $table->index('is_primary');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tags');
    }
};