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
        Schema::create('energy_zone_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('zone_name');                    // ej. Cuarte de Huerva
            $table->string('postal_code', 10);             // ej. 50410
            $table->foreignId('municipality_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('estimated_production_kwh_day', 10, 2)->default(0);
            $table->decimal('reserved_kwh_day', 10, 2)->default(0);
            $table->decimal('requested_kwh_day', 10, 2)->default(0);
            $table->decimal('available_kwh_day', 10, 2)->default(0);
            $table->enum('status', ['verde', 'naranja', 'rojo'])->default('verde');
            $table->timestamp('last_updated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['postal_code']);
            $table->index(['status']);
            $table->index(['municipality_id']);
            $table->index(['last_updated_at']);
            $table->index(['available_kwh_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_zone_summaries');
    }
};
