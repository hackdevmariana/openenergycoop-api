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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_open')->default(false); // Si cualquier persona puede unirse
            $table->integer('max_members')->nullable(); // Límite de miembros
            $table->string('logo_path')->nullable(); // Ruta al logo del equipo
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['organization_id', 'is_open']);
            $table->index('created_by_user_id');
            $table->index('slug');
            $table->unique(['organization_id', 'slug'], 'org_team_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
