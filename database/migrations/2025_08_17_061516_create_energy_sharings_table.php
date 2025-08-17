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
        Schema::create('energy_sharings', function (Blueprint $table) {
            $table->id();
            
            // Participantes del intercambio
            $table->foreignId('provider_user_id')->constrained('users')->cascadeOnDelete(); // Usuario que proporciona energía
            $table->foreignId('consumer_user_id')->constrained('users')->cascadeOnDelete(); // Usuario que consume energía
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->nullOnDelete(); // Cooperativa mediadora
            
            // Información del intercambio
            $table->string('sharing_code')->unique(); // Código único del intercambio
            $table->string('title'); // Título descriptivo del intercambio
            $table->text('description')->nullable(); // Descripción del acuerdo
            $table->enum('sharing_type', [
                'direct',          // Intercambio directo entre usuarios
                'community',       // A través de la comunidad/cooperativa
                'marketplace',     // Marketplace abierto
                'emergency',       // Intercambio de emergencia
                'scheduled',       // Intercambio programado
                'real_time'        // Intercambio en tiempo real
            ]);
            
            // Estado del intercambio
            $table->enum('status', [
                'proposed',        // Propuesto, esperando aceptación
                'accepted',        // Aceptado por ambas partes
                'active',          // Intercambio activo
                'completed',       // Completado exitosamente
                'cancelled',       // Cancelado antes del inicio
                'failed',          // Falló durante la ejecución
                'disputed',        // En disputa
                'expired'          // Expiró sin completarse
            ])->default('proposed');
            
            // Configuración energética
            $table->decimal('energy_amount_kwh', 12, 4); // Cantidad de energía a intercambiar
            $table->decimal('energy_delivered_kwh', 12, 4)->default(0); // Energía ya entregada
            $table->decimal('energy_remaining_kwh', 12, 4); // Energía pendiente de entrega
            $table->string('energy_source')->nullable(); // Fuente de energía (solar, eólica, etc.)
            $table->boolean('is_renewable')->default(false); // Es energía renovable
            $table->decimal('renewable_percentage', 5, 2)->nullable(); // Porcentaje renovable
            
            // Configuración temporal
            $table->datetime('sharing_start_datetime'); // Inicio del intercambio
            $table->datetime('sharing_end_datetime'); // Fin del intercambio
            $table->datetime('proposal_expiry_datetime'); // Expiración de la propuesta
            $table->integer('duration_hours'); // Duración en horas
            $table->json('time_slots')->nullable(); // Franjas horarias específicas
            $table->boolean('flexible_timing')->default(false); // Horario flexible
            
            // Configuración financiera
            $table->decimal('price_per_kwh', 8, 4); // Precio por kWh acordado
            $table->decimal('total_amount', 10, 2); // Monto total del intercambio
            $table->decimal('platform_fee', 10, 2)->default(0); // Comisión de la plataforma
            $table->decimal('cooperative_fee', 10, 2)->default(0); // Comisión de la cooperativa
            $table->decimal('net_amount', 10, 2); // Monto neto para el proveedor
            $table->string('currency', 3)->default('EUR'); // Moneda
            $table->enum('payment_method', [
                'credits',         // Créditos de la plataforma
                'bank_transfer',   // Transferencia bancaria
                'energy_tokens',   // Tokens energéticos
                'barter',         // Intercambio directo (sin dinero)
                'loyalty_points'   // Puntos de lealtad
            ])->default('credits');
            
            // Métricas de calidad y rendimiento
            $table->decimal('quality_score', 3, 2)->nullable(); // Puntuación de calidad (1-5)
            $table->decimal('reliability_score', 3, 2)->nullable(); // Puntuación de confiabilidad
            $table->decimal('delivery_efficiency', 5, 2)->nullable(); // Eficiencia de entrega (%)
            $table->integer('interruptions_count')->default(0); // Número de interrupciones
            $table->decimal('average_voltage', 8, 2)->nullable(); // Voltaje promedio
            $table->decimal('frequency_stability', 8, 4)->nullable(); // Estabilidad de frecuencia
            
            // Configuración geográfica y técnica
            $table->decimal('max_distance_km', 8, 2)->nullable(); // Distancia máxima permitida
            $table->decimal('actual_distance_km', 8, 2)->nullable(); // Distancia real
            $table->json('grid_connection_details')->nullable(); // Detalles de conexión a la red
            $table->boolean('requires_grid_approval')->default(false); // Requiere aprobación de la red
            $table->string('grid_operator')->nullable(); // Operador de red
            $table->string('connection_type')->nullable(); // Tipo de conexión
            
            // Preferencias y restricciones
            $table->json('provider_preferences')->nullable(); // Preferencias del proveedor
            $table->json('consumer_preferences')->nullable(); // Preferencias del consumidor
            $table->json('technical_requirements')->nullable(); // Requisitos técnicos
            $table->text('special_conditions')->nullable(); // Condiciones especiales
            $table->boolean('allows_partial_delivery')->default(true); // Permite entrega parcial
            $table->decimal('min_delivery_kwh', 12, 4)->nullable(); // Entrega mínima requerida
            
            // Métricas ambientales
            $table->decimal('co2_reduction_kg', 10, 4)->default(0); // Reducción de CO2
            $table->decimal('environmental_impact_score', 5, 2)->nullable(); // Puntuación de impacto ambiental
            $table->json('sustainability_metrics')->nullable(); // Métricas de sostenibilidad
            $table->boolean('certified_green_energy')->default(false); // Energía verde certificada
            $table->string('certification_number')->nullable(); // Número de certificación
            
            // Seguimiento y monitoreo
            $table->json('monitoring_data')->nullable(); // Datos de monitoreo en tiempo real
            $table->datetime('last_monitoring_update')->nullable(); // Última actualización de monitoreo
            $table->integer('monitoring_frequency_minutes')->default(15); // Frecuencia de monitoreo
            $table->boolean('real_time_tracking')->default(false); // Seguimiento en tiempo real
            $table->json('alerts_configuration')->nullable(); // Configuración de alertas
            
            // Resolución de conflictos
            $table->text('dispute_reason')->nullable(); // Motivo de disputa
            $table->text('dispute_resolution')->nullable(); // Resolución de disputa
            $table->foreignId('mediator_id')->nullable()->constrained('users')->nullOnDelete(); // Mediador
            $table->datetime('dispute_opened_at')->nullable(); // Fecha de apertura de disputa
            $table->datetime('dispute_resolved_at')->nullable(); // Fecha de resolución
            
            // Evaluaciones y feedback
            $table->decimal('provider_rating', 3, 2)->nullable(); // Calificación al proveedor
            $table->decimal('consumer_rating', 3, 2)->nullable(); // Calificación al consumidor
            $table->text('provider_feedback')->nullable(); // Feedback para el proveedor
            $table->text('consumer_feedback')->nullable(); // Feedback para el consumidor
            $table->boolean('would_repeat')->nullable(); // ¿Repetirían el intercambio?
            
            // Información de pago y liquidación
            $table->datetime('payment_due_date')->nullable(); // Fecha límite de pago
            $table->datetime('payment_completed_at')->nullable(); // Fecha de pago completado
            $table->enum('payment_status', [
                'pending',         // Pago pendiente
                'processing',      // Procesando pago
                'completed',       // Pago completado
                'failed',          // Pago fallido
                'refunded',        // Reembolsado
                'disputed'         // Pago en disputa
            ])->default('pending');
            $table->string('payment_transaction_id')->nullable(); // ID de transacción
            $table->json('payment_details')->nullable(); // Detalles del pago
            
            // Metadatos y configuración
            $table->json('metadata')->nullable(); // Información adicional
            $table->json('integration_data')->nullable(); // Datos de integraciones externas
            $table->string('external_reference')->nullable(); // Referencia externa
            $table->json('tags')->nullable(); // Etiquetas
            $table->text('notes')->nullable(); // Notas adicionales
            
            // Auditoria
            $table->timestamp('proposed_at')->nullable(); // Fecha de propuesta
            $table->timestamp('accepted_at')->nullable(); // Fecha de aceptación
            $table->timestamp('started_at')->nullable(); // Fecha de inicio
            $table->timestamp('completed_at')->nullable(); // Fecha de finalización
            $table->timestamp('cancelled_at')->nullable(); // Fecha de cancelación
            
            $table->timestamps();
            
            // Índices
            $table->index(['provider_user_id', 'status']);
            $table->index(['consumer_user_id', 'status']);
            $table->index(['energy_cooperative_id', 'status']);
            $table->index(['sharing_type', 'status']);
            $table->index(['sharing_start_datetime', 'sharing_end_datetime'], 'energy_sharing_datetime_index');
            $table->index(['is_renewable', 'certified_green_energy'], 'energy_sharing_renewable_index');
            $table->index(['payment_status', 'payment_due_date'], 'energy_sharing_payment_index');
            $table->index(['quality_score', 'reliability_score'], 'energy_sharing_quality_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_sharings');
    }
};
