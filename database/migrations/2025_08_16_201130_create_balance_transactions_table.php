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
        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balance_id')->constrained('balances')->onDelete('cascade');
            $table->enum('type', ['income', 'expense', 'transfer_in', 'transfer_out', 'adjustment']);
            $table->decimal('amount', 15, 6); // Cantidad del movimiento
            $table->decimal('balance_before', 15, 6); // Balance anterior
            $table->decimal('balance_after', 15, 6); // Balance después
            $table->string('description');
            $table->string('reference')->nullable(); // Referencia externa
            
            // Relación polimórfica con otros modelos
            $table->nullableMorphs('related_model'); // related_model_id, related_model_type
            
            // Para operaciones atómicas
            $table->uuid('batch_id')->nullable(); // Agrupar transacciones relacionadas
            
            // Para conversiones de moneda
            $table->decimal('exchange_rate', 10, 6)->nullable();
            $table->string('original_currency', 10)->nullable();
            $table->decimal('original_amount', 15, 6)->nullable();
            
            // Para gestión fiscal
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 15, 6); // Cantidad neta después de impuestos y comisiones
            
            // Metadatos y auditoria
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            
            // Para reconciliación contable
            $table->string('accounting_reference')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('balance_id');
            $table->index('type');
            $table->index('batch_id');
            $table->index('status');
            $table->index('created_at'); // Para consultas temporales
            $table->index('processed_at');
            $table->index('is_reconciled');
            
            // Índices compuestos para consultas frecuentes
            $table->index(['balance_id', 'created_at']);
            $table->index(['balance_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_transactions');
    }
};