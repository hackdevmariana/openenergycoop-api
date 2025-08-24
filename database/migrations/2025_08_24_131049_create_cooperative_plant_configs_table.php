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
        Schema::create('cooperative_plant_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperative_id')->constrained('energy_cooperatives')->onDelete('cascade');
            $table->foreignId('plant_id')->constrained('plants')->onDelete('cascade');
            $table->boolean('default')->default(false);
            $table->boolean('active')->default(true);
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->timestamps();
            
            // Índices
            $table->index('cooperative_id');
            $table->index('plant_id');
            $table->index('default');
            $table->index('active');
            $table->index(['cooperative_id', 'plant_id']);
            $table->index(['cooperative_id', 'active']);
            
            // Una cooperativa solo puede tener una planta por defecto
            $table->unique(['cooperative_id', 'default'], 'unique_default_per_cooperative');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooperative_plant_configs');
    }
};
