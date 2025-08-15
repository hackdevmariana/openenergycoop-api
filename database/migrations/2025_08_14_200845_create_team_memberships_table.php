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
        Schema::create('team_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->enum('role', ['member', 'admin', 'moderator'])->default('member');
            $table->timestamp('left_at')->nullable(); // Cuando el usuario dejó el equipo
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['team_id', 'left_at']); // Para obtener miembros activos
            $table->index(['user_id', 'left_at']); // Para obtener equipos de un usuario
            $table->index('role');
            $table->unique(['team_id', 'user_id'], 'team_user_unique'); // Un usuario solo puede estar una vez en un equipo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_memberships');
    }
};
