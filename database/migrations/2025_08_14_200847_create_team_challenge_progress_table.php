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
        Schema::create('team_challenge_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade');
            $table->decimal('progress_kwh', 10, 2)->default(0); // Progreso actual en kWh
            $table->timestamp('completed_at')->nullable(); // Cuándo se completó el desafío
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['team_id', 'challenge_id']);
            $table->index('completed_at');
            $table->unique(['team_id', 'challenge_id'], 'team_challenge_unique'); // Un equipo solo puede tener un progreso por desafío
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_challenge_progress');
    }
};
