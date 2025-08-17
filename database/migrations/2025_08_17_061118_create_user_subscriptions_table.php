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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Usuario suscrito
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->nullOnDelete(); // Cooperativa (opcional)
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete(); // Proveedor de servicios
            
            // Información del plan/servicio
            $table->string('subscription_type'); // Tipo de suscripción
            $table->string('plan_name'); // Nombre del plan
            $table->text('plan_description')->nullable(); // Descripción del plan
            $table->string('service_category'); // Categoría del servicio
            $table->json('included_services')->nullable(); // Servicios incluidos
            
            // Estado y ciclo de vida
            $table->enum('status', [
                'pending',          // Pendiente de activación
                'active',           // Activa
                'paused',          // Pausada temporalmente
                'cancelled',       // Cancelada por el usuario
                'expired',         // Expirada
                'suspended',       // Suspendida por incumplimiento
                'terminated'       // Terminada definitivamente
            ])->default('pending');
            
            // Fechas importantes
            $table->date('start_date'); // Fecha de inicio
            $table->date('end_date')->nullable(); // Fecha de finalización (null = indefinida)
            $table->date('trial_end_date')->nullable(); // Fin del período de prueba
            $table->date('next_billing_date')->nullable(); // Próxima fecha de facturación
            $table->date('cancellation_date')->nullable(); // Fecha de cancelación
            $table->timestamp('last_renewed_at')->nullable(); // Última renovación
            
            // Configuración de facturación
            $table->enum('billing_frequency', [
                'weekly',          // Semanal
                'monthly',         // Mensual
                'quarterly',       // Trimestral
                'semi_annual',     // Semestral
                'annual',          // Anual
                'one_time'         // Pago único
            ])->default('monthly');
            
            $table->decimal('price', 10, 2); // Precio del plan
            $table->string('currency', 3)->default('EUR'); // Moneda
            $table->decimal('discount_percentage', 5, 2)->nullable(); // Descuento aplicado
            $table->decimal('discount_amount', 10, 2)->nullable(); // Monto de descuento fijo
            $table->string('promo_code')->nullable(); // Código promocional aplicado
            
            // Configuración del servicio energético
            $table->decimal('energy_allowance_kwh', 12, 4)->nullable(); // Allowance mensual de energía
            $table->decimal('overage_rate_per_kwh', 8, 4)->nullable(); // Tarifa por exceso
            $table->json('peak_hours_config')->nullable(); // Configuración de horas pico
            $table->boolean('includes_renewable_energy')->default(false); // Incluye energía renovable
            $table->decimal('renewable_percentage', 5, 2)->nullable(); // Porcentaje renovable
            
            // Preferencias y configuración
            $table->json('preferences')->nullable(); // Preferencias del usuario
            $table->json('notification_settings')->nullable(); // Configuración de notificaciones
            $table->boolean('auto_renewal')->default(true); // Renovación automática
            $table->integer('renewal_reminder_days')->default(7); // Días antes para recordatorio
            
            // Métricas de uso
            $table->decimal('current_period_usage_kwh', 12, 4)->default(0); // Uso actual del período
            $table->decimal('total_usage_kwh', 15, 4)->default(0); // Uso total histórico
            $table->decimal('current_period_cost', 10, 2)->default(0); // Costo del período actual
            $table->decimal('total_cost_paid', 12, 2)->default(0); // Total pagado histórico
            $table->integer('billing_cycles_completed')->default(0); // Ciclos de facturación completados
            
            // Beneficios y recompensas
            $table->integer('loyalty_points')->default(0); // Puntos de lealtad acumulados
            $table->json('benefits_earned')->nullable(); // Beneficios ganados
            $table->decimal('referral_credits', 10, 2)->default(0); // Créditos por referidos
            $table->integer('referrals_count')->default(0); // Número de referidos
            
            // Información de pago
            $table->string('payment_method')->nullable(); // Método de pago preferido
            $table->json('payment_details')->nullable(); // Detalles del método de pago (encriptados)
            $table->date('last_payment_date')->nullable(); // Última fecha de pago
            $table->decimal('last_payment_amount', 10, 2)->nullable(); // Último monto pagado
            $table->enum('payment_status', [
                'current',         // Al día
                'overdue',         // Atrasado
                'failed',          // Fallo en el pago
                'pending',         // Pago pendiente
                'refunded'         // Reembolsado
            ])->default('current');
            
            // Gestión de cancelación
            $table->string('cancellation_reason')->nullable(); // Motivo de cancelación
            $table->text('cancellation_feedback')->nullable(); // Feedback del usuario
            $table->boolean('eligible_for_reactivation')->default(true); // Elegible para reactivación
            $table->date('reactivation_deadline')->nullable(); // Fecha límite para reactivación
            
            // Soporte y servicio al cliente
            $table->integer('support_tickets_count')->default(0); // Tickets de soporte abiertos
            $table->decimal('satisfaction_rating', 3, 2)->nullable(); // Calificación de satisfacción (1-5)
            $table->text('special_notes')->nullable(); // Notas especiales del account manager
            
            // Metadatos y configuración avanzada
            $table->json('metadata')->nullable(); // Información adicional personalizable
            $table->json('integration_settings')->nullable(); // Configuraciones de integraciones
            $table->string('external_subscription_id')->nullable(); // ID en sistema externo
            $table->json('tags')->nullable(); // Etiquetas para categorización
            
            // Auditoria y seguimiento
            $table->timestamp('activated_at')->nullable(); // Fecha de activación
            $table->timestamp('paused_at')->nullable(); // Fecha de pausa
            $table->timestamp('suspended_at')->nullable(); // Fecha de suspensión
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // Creado por
            $table->foreignId('managed_by')->nullable()->constrained('users')->nullOnDelete(); // Gestionado por
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'status']);
            $table->index(['energy_cooperative_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index(['subscription_type', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['next_billing_date', 'status']);
            $table->index(['billing_frequency', 'auto_renewal']);
            $table->unique(['user_id', 'subscription_type', 'provider_id'], 'user_subscription_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
