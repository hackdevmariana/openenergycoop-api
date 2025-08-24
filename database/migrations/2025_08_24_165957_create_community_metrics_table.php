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
        Schema::create('community_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->integer('total_users')->default(0);
            $table->decimal('total_kwh_produced', 15, 4)->default(0);
            $table->decimal('total_co2_avoided', 15, 4)->default(0);
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Índices para optimizar consultas
            $table->index(['organization_id']);
            $table->index(['total_users']);
            $table->index(['total_kwh_produced']);
            $table->index(['total_co2_avoided']);
            $table->index(['updated_at']);
            
            // Índice compuesto para métricas por organización y fecha
            $table->index(['organization_id', 'updated_at']);
            
            // Índice único para evitar duplicados por organización
            $table->unique('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_metrics');
    }
};
