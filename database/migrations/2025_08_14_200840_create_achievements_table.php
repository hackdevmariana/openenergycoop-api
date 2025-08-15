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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Para mostrar iconos/badges
            $table->enum('type', ['energy', 'participation', 'community', 'milestone'])->default('energy');
            $table->json('criteria')->nullable(); // Criterios para desbloquear el logro
            $table->integer('points_reward')->default(0); // Puntos otorgados por el logro
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['is_active', 'type']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
