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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('avatar')->nullable(); // Ruta al avatar
            $table->text('bio')->nullable();
            $table->string('municipality_id')->nullable(); // Para rankings locales
            $table->date('join_date')->nullable();
            $table->string('role_in_cooperative')->nullable(); // "miembro", "voluntario", "promotor"...
            $table->boolean('profile_completed')->default(false);
            $table->boolean('newsletter_opt_in')->default(false);
            $table->boolean('show_in_rankings')->default(true); // Gamificación pública
            $table->decimal('co2_avoided_total', 10, 2)->default(0); // Cacheado
            $table->decimal('kwh_produced_total', 10, 2)->default(0); // Cacheado
            $table->integer('points_total')->default(0); // Gamificación
            $table->json('badges_earned')->nullable(); // JSON con badges
            $table->date('birth_date')->nullable();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('team_id')->nullable(); // Para equipos dentro de la cooperativa
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->unique('user_id');
            $table->index(['organization_id', 'show_in_rankings']);
            $table->index(['municipality_id', 'show_in_rankings']);
            $table->index(['points_total', 'show_in_rankings']);
            $table->index('profile_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
