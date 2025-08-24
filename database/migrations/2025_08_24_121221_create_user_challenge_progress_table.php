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
        Schema::create('user_challenge_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained('energy_challenges')->onDelete('cascade');
            $table->decimal('progress_kwh', 10, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['user_id', 'challenge_id']);
            $table->index('challenge_id');
            $table->index('completed_at');
            
            // Asegurar que un usuario solo puede tener un progreso por desafío
            $table->unique(['user_id', 'challenge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_challenge_progress');
    }
};
