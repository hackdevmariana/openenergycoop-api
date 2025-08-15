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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['individual', 'team', 'organization'])->default('team');
            $table->decimal('target_kwh', 10, 2); // Objetivo en kWh
            $table->integer('points_reward')->default(0); // Puntos por completar el desafío
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->json('criteria')->nullable(); // Criterios adicionales
            $table->string('icon')->nullable(); // Icono del desafío
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['is_active', 'type']);
            $table->index(['start_date', 'end_date']);
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
