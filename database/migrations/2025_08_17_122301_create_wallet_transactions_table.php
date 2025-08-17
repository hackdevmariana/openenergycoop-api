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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            
            // Identificación y referencia
            $table->string('transaction_code')->unique()->index(); // WTX-2024-001
            $table->string('reference')->nullable()->index(); // Referencia externa
            
            // Relaciones principales
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Propietario del wallet
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null'); // Usuario relacionado (transferencias)
            $table->unsignedBigInteger('transaction_id')->nullable()->index(); // Transacción principal relacionada
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->onDelete('set null');
            
            // Tipo de transacción en wallet
            $table->enum('type', [
                'credit', 'debit', 'transfer_in', 'transfer_out', 'purchase', 'sale',
                'reward', 'bonus', 'penalty', 'refund', 'conversion', 'expiration',
                'energy_credit', 'energy_debit', 'membership_fee', 'service_fee'
            ]);
            
            // Subtipo para mayor granularidad
            $table->enum('subtype', [
                'energy_purchase', 'energy_sale', 'energy_sharing', 'membership_payment',
                'service_payment', 'referral_bonus', 'loyalty_reward', 'penalty_fee',
                'admin_adjustment', 'system_bonus', 'cashback', 'interest_payment',
                'subscription_fee', 'overpayment_refund', 'cancellation_refund'
            ])->nullable();
            
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'expired'])->default('pending');
            
            // Información del token/crédito
            $table->enum('token_type', ['energy_credit', 'carbon_credit', 'loyalty_point', 'service_credit', 'cash_equivalent']);
            $table->decimal('amount', 15, 6); // Cantidad de tokens/créditos
            $table->decimal('rate', 10, 6)->nullable(); // Tasa de conversión (si aplica)
            $table->string('currency', 3)->default('EUR'); // Moneda de referencia
            $table->decimal('equivalent_value', 12, 4)->nullable(); // Valor equivalente en moneda
            
            // Balance tracking
            $table->decimal('balance_before', 15, 6)->nullable(); // Balance antes de la transacción
            $table->decimal('balance_after', 15, 6)->nullable(); // Balance después de la transacción
            
            // Información energética específica
            $table->decimal('energy_amount_kwh', 10, 4)->nullable(); // Cantidad de energía relacionada
            $table->decimal('energy_price_per_kwh', 8, 6)->nullable(); // Precio por kWh
            $table->string('energy_source')->nullable(); // Fuente de energía (solar, wind, etc.)
            $table->boolean('is_renewable')->nullable(); // Si es energía renovable
            $table->timestamp('energy_generation_date')->nullable(); // Fecha de generación de energía
            $table->timestamp('energy_consumption_date')->nullable(); // Fecha de consumo de energía
            
            // Fechas importantes
            $table->timestamp('processed_at')->nullable(); // Fecha de procesamiento
            $table->timestamp('expires_at')->nullable(); // Fecha de expiración (para créditos temporales)
            $table->timestamp('locked_until')->nullable(); // Fecha hasta la cual está bloqueado
            $table->timestamp('available_at')->nullable(); // Fecha desde la cual está disponible
            
            // Información de la transacción
            $table->text('description'); // Descripción de la transacción
            $table->text('notes')->nullable(); // Notas adicionales
            $table->json('metadata')->nullable(); // Metadata adicional
            
            // Información de origen (para transferencias y conversiones)
            $table->string('source_wallet_id')->nullable(); // ID del wallet origen
            $table->string('source_transaction_code')->nullable(); // Código de transacción origen
            $table->decimal('source_amount', 15, 6)->nullable(); // Cantidad en wallet origen
            $table->string('source_token_type')->nullable(); // Tipo de token origen
            
            // Configuración de expiración y bloqueos
            $table->boolean('has_expiration')->default(false); // Si los créditos expiran
            $table->integer('expiration_days')->nullable(); // Días hasta expiración
            $table->boolean('is_locked')->default(false); // Si está bloqueado
            $table->string('lock_reason')->nullable(); // Razón del bloqueo
            
            // Información de aprobación (para transacciones grandes)
            $table->boolean('requires_approval')->default(false); // Requiere aprobación
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Auditoría y seguridad
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_internal')->default(false); // Transacción interna del sistema
            $table->boolean('is_test')->default(false); // Transacción de prueba
            
            // Información de reversión
            $table->boolean('is_reversible')->default(true); // Se puede revertir
            $table->foreignId('reversal_transaction_id')->nullable()->constrained('wallet_transactions')->onDelete('set null');
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            
            // Información de procesamiento por lotes
            $table->string('batch_id')->nullable()->index(); // Para procesamiento en lotes
            $table->integer('batch_sequence')->nullable(); // Secuencia en el lote
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'token_type']);
            $table->index(['type', 'status']);
            $table->index(['token_type', 'status']);
            $table->index(['status', 'processed_at']);
            $table->index(['expires_at', 'status']);
            $table->index(['energy_cooperative_id', 'type']);
            $table->index(['batch_id', 'batch_sequence']);
            $table->index(['is_locked', 'locked_until']);
            $table->index(['requires_approval', 'approved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};