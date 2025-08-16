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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', [
                'wallet',           // Saldo en dinero
                'energy_kwh',       // Energía en kWh
                'mining_ths',       // Minería en TH/s
                'storage_capacity', // Capacidad de almacenamiento
                'carbon_credits',   // Créditos de carbono
                'production_rights',// Derechos de producción
                'loyalty_points'    // Puntos de fidelidad
            ]);
            $table->decimal('amount', 15, 6)->default(0); // Soporte para decimales de alta precisión
            $table->string('currency', 10)->default('EUR'); // EUR, USD, BTC, etc.
            $table->boolean('is_frozen')->default(false); // Para congelar balances
            $table->timestamp('last_transaction_at')->nullable();
            $table->decimal('daily_limit', 12, 2)->nullable(); // Límite diario de gasto
            $table->decimal('monthly_limit', 12, 2)->nullable(); // Límite mensual
            $table->json('metadata')->nullable(); // Información adicional específica del tipo
            $table->timestamps();
            
            // Índices
            $table->unique(['user_id', 'type', 'currency']); // Un balance por usuario, tipo y moneda
            $table->index('type');
            $table->index('amount');
            $table->index('is_frozen');
            $table->index('last_transaction_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};