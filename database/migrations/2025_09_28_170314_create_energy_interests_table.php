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
        Schema::create('energy_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('zone_name');                    // ej. Centro Madrid
            $table->string('postal_code', 10);             // ej. 28001
            $table->enum('type', ['consumer', 'producer', 'mixed']); // tipo de interés
            $table->decimal('estimated_production_kwh_day', 10, 2)->nullable(); // solo si es productor o mixto
            $table->decimal('requested_kwh_day', 10, 2)->nullable(); // solo si es consumidor o mixto
            $table->string('contact_name')->nullable();    // nombre de contacto si no hay usuario
            $table->string('contact_email')->nullable();   // email de contacto si no hay usuario
            $table->string('contact_phone')->nullable();   // teléfono de contacto
            $table->text('notes')->nullable();             // notas adicionales
            $table->enum('status', ['pending', 'approved', 'rejected', 'active'])->default('pending');
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['user_id']);
            $table->index(['zone_name']);
            $table->index(['postal_code']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['contact_email']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_interests');
    }
};
