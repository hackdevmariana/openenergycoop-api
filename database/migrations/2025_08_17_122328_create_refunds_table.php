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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            
            // Identificación y referencia
            $table->string('refund_code')->unique()->index(); // RFD-2024-001
            $table->string('external_refund_id')->nullable()->index(); // ID del gateway externo
            $table->string('reference')->nullable(); // Referencia externa
            
            // Relaciones principales
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que recibe el refund
            $table->unsignedBigInteger('payment_id')->index(); // Pago original
            $table->unsignedBigInteger('invoice_id')->nullable()->index(); // Factura relacionada
            $table->unsignedBigInteger('transaction_id')->nullable()->index(); // Transacción relacionada
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->onDelete('set null');
            
            // Tipo y categoría de refund
            $table->enum('type', [
                'full', 'partial', 'processing_fee', 'overpayment', 'cancellation',
                'dispute', 'chargeback', 'energy_not_delivered', 'service_failure'
            ])->default('partial');
            
            $table->enum('reason', [
                'customer_request', 'service_cancellation', 'energy_not_delivered',
                'service_failure', 'billing_error', 'overpayment', 'dispute_resolution',
                'chargeback', 'technical_error', 'admin_adjustment', 'duplicate_payment'
            ]);
            
            $table->enum('status', [
                'pending', 'approved', 'processing', 'completed', 'failed', 
                'cancelled', 'disputed', 'expired'
            ])->default('pending');
            
            // Información financiera
            $table->decimal('refund_amount', 12, 4); // Cantidad a reembolsar
            $table->decimal('original_amount', 12, 4); // Cantidad original del pago
            $table->decimal('processing_fee', 10, 4)->default(0); // Comisión de procesamiento
            $table->decimal('net_refund_amount', 12, 4); // Cantidad neta del reembolso
            $table->string('currency', 3)->default('EUR');
            
            // Para conversiones de moneda
            $table->decimal('exchange_rate', 10, 6)->nullable();
            $table->decimal('original_currency_amount', 12, 4)->nullable();
            $table->string('original_currency', 3)->nullable();
            
            // Información del método de refund
            $table->enum('refund_method', [
                'original_payment_method', 'bank_transfer', 'wallet_credit', 
                'energy_credit', 'check', 'paypal', 'manual'
            ])->default('original_payment_method');
            
            $table->string('refund_destination')->nullable(); // Destino del refund (cuenta, wallet, etc.)
            $table->json('refund_details')->nullable(); // Detalles específicos del método
            
            // Información del gateway de pago
            $table->string('gateway')->nullable(); // Gateway utilizado (stripe, paypal, etc.)
            $table->string('gateway_refund_id')->nullable()->index(); // ID del refund en el gateway
            $table->json('gateway_response')->nullable(); // Respuesta del gateway
            $table->string('gateway_status')->nullable(); // Estado en el gateway
            
            // Fechas importantes
            $table->timestamp('requested_at'); // Fecha de solicitud
            $table->timestamp('approved_at')->nullable(); // Fecha de aprobación
            $table->timestamp('processed_at')->nullable(); // Fecha de procesamiento
            $table->timestamp('completed_at')->nullable(); // Fecha de finalización
            $table->timestamp('failed_at')->nullable(); // Fecha de fallo
            $table->timestamp('expires_at')->nullable(); // Fecha de expiración
            
            // Información de la solicitud
            $table->text('description'); // Descripción del refund
            $table->text('customer_reason')->nullable(); // Razón proporcionada por el cliente
            $table->text('internal_notes')->nullable(); // Notas internas
            $table->json('supporting_documents')->nullable(); // Documentos de soporte
            
            // Información energética (si aplica)
            $table->decimal('energy_amount_kwh', 10, 4)->nullable(); // Cantidad de energía a reembolsar
            $table->decimal('energy_price_per_kwh', 8, 6)->nullable(); // Precio por kWh
            $table->timestamp('energy_service_date')->nullable(); // Fecha del servicio energético
            $table->string('energy_contract_id')->nullable(); // Contrato energético relacionado
            
            // Información de aprobación y autorización
            $table->foreignId('requested_by_id')->nullable()->constrained('users')->onDelete('set null'); // Quien solicitó
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null'); // Quien aprobó
            $table->foreignId('processed_by_id')->nullable()->constrained('users')->onDelete('set null'); // Quien procesó
            
            $table->boolean('requires_approval')->default(true); // Requiere aprobación manual
            $table->boolean('auto_approved')->default(false); // Fue aprobado automáticamente
            $table->decimal('auto_approval_threshold', 10, 4)->nullable(); // Umbral de aprobación automática
            
            // Información de disputas y chargebacks
            $table->boolean('is_chargeback')->default(false); // Si es un chargeback
            $table->string('chargeback_id')->nullable(); // ID del chargeback
            $table->timestamp('chargeback_date')->nullable(); // Fecha del chargeback
            $table->text('dispute_details')->nullable(); // Detalles de la disputa
            
            // Auditoría y tracking
            $table->ipAddress('request_ip')->nullable(); // IP de la solicitud
            $table->string('user_agent')->nullable(); // User agent
            $table->boolean('is_test')->default(false); // Refund de prueba
            $table->json('audit_trail')->nullable(); // Registro de auditoría
            
            // Información de notificaciones
            $table->boolean('customer_notified')->default(false); // Cliente notificado
            $table->timestamp('customer_notified_at')->nullable();
            $table->json('notification_history')->nullable(); // Historial de notificaciones
            
            // Configuración y metadata
            $table->json('metadata')->nullable(); // Metadata adicional
            $table->string('failure_reason')->nullable(); // Razón del fallo
            $table->integer('retry_count')->default(0); // Número de reintentos
            $table->timestamp('next_retry_at')->nullable(); // Próximo reintento
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['user_id', 'status']);
            $table->index(['payment_id', 'status']);
            $table->index(['status', 'requested_at']);
            $table->index(['type', 'reason']);
            $table->index(['gateway', 'gateway_refund_id']);
            $table->index(['requires_approval', 'approved_at']);
            $table->index(['is_chargeback', 'chargeback_date']);
            $table->index(['energy_cooperative_id', 'status']);
            $table->index(['auto_approved', 'auto_approval_threshold']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};