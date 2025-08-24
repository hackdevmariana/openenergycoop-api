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
        Schema::create('energy_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['individual', 'colectivo'])->default('individual');
            $table->decimal('goal_kwh', 10, 2);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('reward_type', ['symbolic', 'energy_donation', 'badge'])->default('symbolic');
            $table->json('reward_details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para mejorar el rendimiento
            $table->index(['type', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
            $table->index('reward_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_challenges');
    }
};
