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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Identificación única
            $table->string('transaction_code')->unique()->index(); // TXN-2024-001
            $table->string('reference')->nullable()->index(); // Referencia externa
            $table->string('batch_id')->nullable()->index(); // Para transacciones en lote
            
            // Relaciones principales
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->onDelete('set null');
            
            // Tipo y categoría de transacción
            $table->enum('type', [
                'payment', 'refund', 'transfer', 'fee', 'commission', 'bonus', 
                'penalty', 'adjustment', 'energy_purchase', 'energy_sale',
                'subscription_fee', 'membership_fee', 'deposit', 'withdrawal'
            ]);
            $table->enum('category', [
                'energy', 'financial', 'administrative', 'penalty', 'bonus',
                'membership', 'service', 'adjustment', 'fee', 'commission'
            ]);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'reversed'])->default('pending');
            
            // Información financiera
            $table->decimal('amount', 12, 4); // Monto de la transacción
            $table->decimal('fee', 10, 4)->default(0); // Comisiones aplicadas
            $table->decimal('net_amount', 12, 4); // Monto neto (amount - fee)
            $table->string('currency', 3)->default('EUR');
            
            // Para transacciones con conversión de moneda
            $table->decimal('exchange_rate', 10, 6)->nullable();
            $table->decimal('original_amount', 12, 4)->nullable();
            $table->string('original_currency', 3)->nullable();
            
            // Información de origen y destino
            $table->string('from_account_type')->nullable(); // 'user_wallet', 'bank_account', 'energy_credit'
            $table->string('from_account_id')->nullable();
            $table->string('to_account_type')->nullable();
            $table->string('to_account_id')->nullable();
            
            // Balance tracking
            $table->decimal('balance_before', 12, 4)->nullable(); // Balance antes de la transacción
            $table->decimal('balance_after', 12, 4)->nullable(); // Balance después de la transacción
            
            // Información energética (si aplica)
            $table->decimal('energy_amount_kwh', 10, 4)->nullable(); // Cantidad de energía
            $table->decimal('energy_price_per_kwh', 8, 6)->nullable(); // Precio por kWh
            $table->string('energy_contract_id')->nullable(); // Referencia al contrato
            $table->timestamp('energy_delivery_date')->nullable(); // Fecha de entrega de energía
            
            // Fechas importantes
            $table->timestamp('processed_at')->nullable(); // Fecha de procesamiento
            $table->timestamp('settled_at')->nullable(); // Fecha de liquidación
            $table->timestamp('failed_at')->nullable(); // Fecha de fallo
            $table->timestamp('cancelled_at')->nullable(); // Fecha de cancelación
            $table->timestamp('expires_at')->nullable(); // Fecha de expiración
            
            // Información de procesamiento
            $table->string('processor')->nullable(); // Procesador utilizado
            $table->string('processor_transaction_id')->nullable()->index();
            $table->json('processor_response')->nullable(); // Respuesta del procesador
            $table->string('authorization_code')->nullable(); // Código de autorización
            
            // Descripción y metadatos
            $table->text('description'); // Descripción de la transacción
            $table->text('notes')->nullable(); // Notas adicionales
            $table->json('metadata')->nullable(); // Metadata adicional
            $table->string('failure_reason')->nullable(); // Razón del fallo
            
            // Información de auditoría
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            // Flags de control
            $table->boolean('is_internal')->default(false); // Transacción interna del sistema
            $table->boolean('is_test')->default(false); // Transacción de prueba
            $table->boolean('is_recurring')->default(false); // Parte de una serie recurrente
            $table->boolean('requires_approval')->default(false); // Requiere aprobación manual
            $table->boolean('is_reversible')->default(true); // Se puede revertir
            
            // Transacciones relacionadas
            $table->foreignId('parent_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->foreignId('reversal_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['category', 'created_at']);
            $table->index(['status', 'processed_at']);
            $table->index(['currency', 'amount']);
            $table->index(['energy_cooperative_id', 'type']);
            $table->index(['is_internal', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};