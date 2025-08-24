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
        Schema::create('impact_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('total_kwh_produced', 15, 4)->default(0);
            $table->decimal('total_co2_avoided_kg', 15, 4)->default(0);
            $table->foreignId('plant_group_id')->nullable()->constrained('plant_groups')->onDelete('set null');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Índices para optimizar consultas
            $table->index(['user_id']);
            $table->index(['plant_group_id']);
            $table->index(['generated_at']);
            $table->index(['total_co2_avoided_kg']);
            $table->index(['total_kwh_produced']);
            
            // Índice compuesto para métricas por usuario y fecha
            $table->index(['user_id', 'generated_at']);
            
            // Índice compuesto para métricas por grupo de plantas
            $table->index(['plant_group_id', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impact_metrics');
    }
};
