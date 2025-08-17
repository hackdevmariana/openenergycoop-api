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
        Schema::create('energy_cooperatives', function (Blueprint $table) {
            $table->id();
            
            // Información básica de la cooperativa
            $table->string('name'); // Nombre de la cooperativa
            $table->string('code')->unique(); // Código único identificador
            $table->text('description')->nullable(); // Descripción de la cooperativa
            $table->text('mission_statement')->nullable(); // Declaración de misión
            $table->text('vision_statement')->nullable(); // Declaración de visión
            
            // Información legal y regulatoria
            $table->string('legal_name')->nullable(); // Nombre legal registrado
            $table->string('tax_id')->nullable(); // NIF/CIF fiscal
            $table->string('registration_number')->nullable(); // Número de registro oficial
            $table->string('legal_form')->nullable(); // Forma jurídica (cooperativa, asociación, etc.)
            $table->enum('status', [
                'pending',        // Pendiente de aprobación
                'active',         // Activa y operativa
                'suspended',      // Suspendida temporalmente
                'inactive',       // Inactiva
                'dissolved'       // Disuelta
            ])->default('pending');
            
            // Información de contacto
            $table->json('contact_info')->nullable(); // Email, teléfono, web, etc.
            $table->text('address')->nullable(); // Dirección física
            $table->string('city')->nullable();
            $table->string('state_province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('España');
            $table->decimal('latitude', 10, 8)->nullable(); // Coordenadas geográficas
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Gestión y administración
            $table->foreignId('founder_id')->nullable()->constrained('users')->nullOnDelete(); // Usuario fundador
            $table->foreignId('administrator_id')->nullable()->constrained('users')->nullOnDelete(); // Administrador actual
            $table->date('founded_date')->nullable(); // Fecha de fundación
            $table->date('registration_date')->nullable(); // Fecha de registro oficial
            $table->date('activation_date')->nullable(); // Fecha de activación
            
            // Configuración operativa
            $table->integer('max_members')->nullable(); // Máximo número de miembros
            $table->integer('current_members')->default(0); // Miembros actuales
            $table->decimal('membership_fee', 10, 2)->nullable(); // Cuota de membresía
            $table->enum('membership_fee_frequency', [
                'monthly', 'quarterly', 'semi_annual', 'annual', 'one_time'
            ])->nullable();
            $table->boolean('open_enrollment')->default(true); // Inscripción abierta
            $table->text('enrollment_requirements')->nullable(); // Requisitos para unirse
            
            // Configuración energética
            $table->json('energy_types')->nullable(); // Tipos de energía que maneja
            $table->decimal('total_capacity_kw', 12, 4)->nullable(); // Capacidad total en kW
            $table->decimal('available_capacity_kw', 12, 4)->nullable(); // Capacidad disponible
            $table->boolean('allows_energy_sharing')->default(true); // Permite compartir energía
            $table->boolean('allows_trading')->default(true); // Permite trading energético
            $table->decimal('sharing_fee_percentage', 5, 2)->nullable(); // Comisión por intercambio
            
            // Configuración financiera
            $table->string('currency', 3)->default('EUR'); // Moneda de operación
            $table->json('payment_methods')->nullable(); // Métodos de pago aceptados
            $table->boolean('requires_deposit')->default(false); // Requiere depósito
            $table->decimal('deposit_amount', 10, 2)->nullable(); // Monto del depósito
            
            // Métricas y estadísticas
            $table->decimal('total_energy_shared_kwh', 15, 4)->default(0); // Total energía compartida
            $table->decimal('total_cost_savings_eur', 12, 2)->default(0); // Ahorros totales generados
            $table->decimal('total_co2_reduction_kg', 12, 2)->default(0); // Reducción CO2 total
            $table->integer('total_projects')->default(0); // Proyectos realizados
            $table->decimal('average_member_satisfaction', 3, 2)->nullable(); // Satisfacción promedio (1-5)
            
            // Configuración y preferencias
            $table->json('settings')->nullable(); // Configuraciones específicas
            $table->json('notifications_config')->nullable(); // Configuración de notificaciones
            $table->string('timezone')->default('Europe/Madrid'); // Zona horaria
            $table->string('language', 2)->default('es'); // Idioma principal
            
            // Certificaciones y acreditaciones
            $table->json('certifications')->nullable(); // Certificaciones obtenidas
            $table->json('sustainability_goals')->nullable(); // Objetivos de sostenibilidad
            $table->text('achievements')->nullable(); // Logros destacados
            
            // Metadatos
            $table->json('metadata')->nullable(); // Información adicional personalizable
            $table->text('notes')->nullable(); // Notas internas
            $table->boolean('is_featured')->default(false); // Destacada en la plataforma
            $table->integer('visibility_level')->default(1); // Nivel de visibilidad (1-5)
            
            // Auditoria
            $table->timestamp('last_activity_at')->nullable(); // Última actividad
            $table->timestamp('verified_at')->nullable(); // Fecha de verificación
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Índices
            $table->index(['status', 'country']);
            $table->index(['city', 'state_province']);
            $table->index(['founded_date', 'status']);
            $table->index('open_enrollment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_cooperatives');
    }
};
