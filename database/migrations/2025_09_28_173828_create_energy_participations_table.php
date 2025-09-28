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
        Schema::create('energy_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan_code');                        // Código del plan (raiz, hogar, independencia_22, etc.)
            $table->decimal('monthly_amount', 10, 2)->nullable(); // Cuota mensual (o null si es aportación única)
            $table->decimal('one_time_amount', 10, 2)->nullable(); // Solo si es tipo aportación única
            $table->date('start_date');                         // Fecha de inicio del plan
            $table->date('end_date')->nullable();               // Fecha de finalización (si aplica)
            $table->enum('status', ['active', 'suspended', 'cancelled', 'completed'])->default('active');
            $table->integer('fidelity_years')->default(0);      // Años acumulados sin interrupción
            $table->decimal('energy_rights_daily', 8, 3)->default(0); // kWh/día generados hasta ahora
            $table->decimal('energy_rights_total_kwh', 12, 2)->default(0); // kWh acumulados totales
            $table->text('notes')->nullable();                  // Notas adicionales
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['user_id']);
            $table->index(['plan_code']);
            $table->index(['status']);
            $table->index(['start_date']);
            $table->index(['end_date']);
            $table->index(['monthly_amount']);
            $table->index(['one_time_amount']);
            $table->index(['fidelity_years']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_participations');
    }
};