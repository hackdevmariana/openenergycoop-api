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
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
            $table->timestamp('earned_at')->useCurrent();
            $table->text('custom_message')->nullable(); // Mensaje personalizado
            $table->boolean('reward_granted')->default(false); // Si hubo premio
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->unique(['user_id', 'achievement_id']);
            $table->index(['user_id', 'earned_at']);
            $table->index('achievement_id');
            $table->index('reward_granted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
