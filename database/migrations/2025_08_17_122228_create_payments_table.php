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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Identificación y referencias
            $table->string('payment_code')->unique()->index();
            $table->string('external_id')->nullable()->index(); // ID del gateway externo
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            
            // Información del pago
            $table->enum('type', ['payment', 'refund', 'charge', 'credit', 'debit'])->default('payment');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'expired'])->default('pending');
            $table->enum('method', ['card', 'bank_transfer', 'paypal', 'stripe', 'energy_credits', 'wallet'])->default('card');
            
            // Montos y moneda
            $table->decimal('amount', 12, 4); // Monto bruto
            $table->decimal('fee', 10, 4)->default(0); // Comisiones
            $table->decimal('net_amount', 12, 4); // Monto neto (amount - fee)
            $table->string('currency', 3)->default('EUR');
            $table->decimal('exchange_rate', 10, 6)->nullable(); // Para conversiones
            $table->decimal('original_amount', 12, 4)->nullable(); // Monto en moneda original
            $table->string('original_currency', 3)->nullable();
            
            // Gateway de pago
            $table->string('gateway')->nullable(); // stripe, paypal, etc.
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->json('gateway_response')->nullable(); // Respuesta del gateway
            $table->json('gateway_metadata')->nullable(); // Metadata del gateway
            
            // Información de la tarjeta/método (encriptada)
            $table->string('card_last_four')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('payment_method_id')->nullable(); // Para métodos guardados
            
            // Fechas y timestamps
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            
            // Información adicional
            $table->text('description')->nullable();
            $table->string('reference')->nullable(); // Referencia externa
            $table->json('metadata')->nullable(); // Metadata adicional
            $table->string('failure_reason')->nullable();
            $table->text('notes')->nullable();
            
            // Relacionado con energía
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->onDelete('set null');
            $table->string('energy_contract_id')->nullable(); // Referencia a contrato
            $table->decimal('energy_amount_kwh', 10, 4)->nullable(); // Cantidad de energía pagada
            
            // Auditoría y seguridad
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_test')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->foreignId('parent_payment_id')->nullable()->constrained('payments')->onDelete('set null');
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['gateway', 'gateway_transaction_id']);
            $table->index(['currency', 'amount']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};