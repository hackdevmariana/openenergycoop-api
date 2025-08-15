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
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->morphs('taggable'); // Polimórfico: Article, Page, Document, etc.
            $table->timestamp('tagged_at')->default(now());
            $table->foreignId('tagged_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices para performance (morphs ya crea taggable_type, taggable_id)
            $table->index(['tag_id', 'taggable_type']);
            $table->index(['tagged_at']);
            
            // Constraint único: una etiqueta por objeto
            $table->unique(['tag_id', 'taggable_type', 'taggable_id']);
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
