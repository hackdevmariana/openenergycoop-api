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
        Schema::create('energy_contracts', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            // Información básica del contrato
            $table->string('contract_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Tipo y estado del contrato
            $table->enum('type', [
                'supply',           // Suministro de energía
                'generation',       // Generación de energía
                'storage',          // Almacenamiento
                'hybrid'            // Combinado
            ]);
            $table->enum('status', [
                'draft',            // Borrador
                'pending',          // Pendiente de aprobación
                'active',           // Activo
                'suspended',        // Suspendido
                'terminated',       // Terminado
                'expired'           // Expirado
            ])->default('draft');
            
            // Términos económicos
            $table->decimal('total_value', 15, 2);
            $table->decimal('monthly_payment', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->boolean('deposit_paid')->default(false);
            
            // Términos energéticos
            $table->decimal('contracted_power', 10, 3); // kW
            $table->decimal('estimated_annual_consumption', 12, 2)->nullable(); // kWh
            $table->decimal('guaranteed_supply_percentage', 5, 2)->default(100); // %
            $table->decimal('green_energy_percentage', 5, 2)->default(0); // %
            
            // Fechas del contrato
            $table->date('start_date');
            $table->date('end_date');
            $table->date('signed_date')->nullable();
            $table->date('activation_date')->nullable();
            
            // Términos y condiciones
            $table->text('terms_conditions')->nullable();
            $table->json('special_clauses')->nullable();
            $table->boolean('auto_renewal')->default(false);
            $table->integer('renewal_period_months')->nullable();
            $table->decimal('early_termination_fee', 10, 2)->nullable();
            
            // Facturación
            $table->enum('billing_frequency', [
                'monthly',
                'quarterly',
                'semi_annual',
                'annual'
            ])->default('monthly');
            $table->date('next_billing_date')->nullable();
            $table->date('last_billing_date')->nullable();
            
            // Rendimiento y métricas
            $table->json('performance_metrics')->nullable();
            $table->decimal('current_satisfaction_score', 3, 2)->nullable();
            $table->integer('total_claims')->default(0);
            $table->integer('resolved_claims')->default(0);
            
            // Información de sostenibilidad
            $table->decimal('estimated_co2_reduction', 10, 2)->nullable(); // kg CO2/año
            $table->json('sustainability_certifications')->nullable();
            $table->boolean('carbon_neutral')->default(false);
            
            // Metadatos y personalización
            $table->json('custom_fields')->nullable();
            $table->json('attachments')->nullable(); // URLs de documentos
            $table->text('notes')->nullable();
            
            // Campos de auditoría
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('terminated_at')->nullable();
            $table->foreignId('terminated_by')->nullable()->constrained('users');
            $table->text('termination_reason')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('contract_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_contracts');
    }
};