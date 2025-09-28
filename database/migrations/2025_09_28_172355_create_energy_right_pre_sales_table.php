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
        Schema::create('energy_right_pre_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('energy_installation_id')->nullable()->constrained()->onDelete('set null');
            $table->string('zone_name')->nullable();                    // Si no se asocia a una instalación específica
            $table->string('postal_code', 10)->nullable();             // Código postal de la zona
            $table->decimal('kwh_per_month_reserved', 10, 2);          // Derecho reservado por el usuario
            $table->decimal('price_per_kwh', 8, 4);                    // Precio pactado por kWh
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamp('signed_at')->nullable();                // Fecha de firma del contrato
            $table->timestamp('expires_at')->nullable();               // Fecha de expiración
            $table->text('notes')->nullable();                         // Notas adicionales
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['user_id']);
            $table->index(['energy_installation_id']);
            $table->index(['zone_name']);
            $table->index(['postal_code']);
            $table->index(['status']);
            $table->index(['signed_at']);
            $table->index(['expires_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_right_pre_sales');
    }
};
