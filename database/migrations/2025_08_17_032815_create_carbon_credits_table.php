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
        Schema::create('carbon_credits', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('energy_production_id')->nullable()->constrained()->nullOnDelete();
            
            // Identificación del crédito
            $table->string('credit_id')->unique(); // ID único del crédito
            $table->string('registry_id')->nullable(); // ID del registro oficial
            $table->string('serial_number')->nullable(); // Número de serie
            $table->string('batch_id')->nullable(); // ID del lote
            
            // Tipo y estándar del crédito
            $table->enum('credit_type', [
                'vcs',              // Verified Carbon Standard
                'gold_standard',    // Gold Standard
                'cdm',              // Clean Development Mechanism
                'vcu',              // Verified Carbon Unit
                'cer',              // Certified Emission Reduction
                'rgu',              // Renewable Generation Unit
                'custom'            // Personalizado
            ]);
            
            $table->string('standard_version')->nullable();
            $table->string('methodology')->nullable(); // Metodología utilizada
            
            // Información del proyecto
            $table->string('project_name');
            $table->text('project_description')->nullable();
            $table->string('project_id')->nullable(); // ID del proyecto generador
            $table->string('project_type'); // Tipo de proyecto (renewable energy, forestry, etc.)
            $table->string('project_location'); // Ubicación del proyecto
            $table->string('project_country'); // País del proyecto
            $table->json('project_coordinates')->nullable(); // Coordenadas GPS
            
            // Cantidades y estado
            $table->decimal('total_credits', 12, 4); // Total de créditos en tCO2e
            $table->decimal('available_credits', 12, 4); // Créditos disponibles
            $table->decimal('retired_credits', 12, 4)->default(0); // Créditos retirados
            $table->decimal('transferred_credits', 12, 4)->default(0); // Créditos transferidos
            
            // Estado del crédito
            $table->enum('status', [
                'pending',          // Pendiente de verificación
                'verified',         // Verificado
                'issued',           // Emitido
                'available',        // Disponible para comercio
                'retired',          // Retirado
                'cancelled',        // Cancelado
                'expired'           // Expirado
            ])->default('pending');
            
            // Períodos de tiempo
            $table->date('credit_period_start'); // Inicio del período de crédito
            $table->date('credit_period_end'); // Fin del período de crédito
            $table->date('vintage_year'); // Año vintage
            $table->date('issuance_date')->nullable(); // Fecha de emisión
            $table->date('verification_date')->nullable(); // Fecha de verificación
            $table->date('expiry_date')->nullable(); // Fecha de expiración
            
            // Información económica
            $table->decimal('purchase_price_per_credit', 10, 4)->nullable(); // Precio de compra por tCO2e
            $table->decimal('current_market_price', 10, 4)->nullable(); // Precio actual de mercado
            $table->decimal('total_investment', 12, 2)->nullable(); // Inversión total
            $table->string('currency', 3)->default('EUR');
            
            // Verificación y certificación
            $table->string('verifier_name')->nullable(); // Entidad verificadora
            $table->string('verifier_accreditation')->nullable();
            $table->date('last_verification_date')->nullable();
            $table->date('next_verification_date')->nullable();
            $table->json('verification_documents')->nullable(); // URLs de documentos
            
            // Adicionalidad y beneficios co-beneficios
            $table->boolean('additionality_demonstrated')->default(false);
            $table->text('additionality_justification')->nullable();
            $table->json('co_benefits')->nullable(); // Beneficios adicionales (biodiversidad, social, etc.)
            $table->json('sdg_contributions')->nullable(); // Contribuciones a ODS
            
            // Monitoreo y reporte
            $table->enum('monitoring_frequency', [
                'continuous',       // Continuo
                'daily',           // Diario
                'weekly',          // Semanal
                'monthly',         // Mensual
                'quarterly',       // Trimestral
                'annual'           // Anual
            ])->nullable();
            
            $table->date('last_monitoring_report_date')->nullable();
            $table->json('monitoring_data')->nullable();
            
            // Transferencias y transacciones
            $table->json('transaction_history')->nullable(); // Historial de transacciones
            $table->foreignId('original_owner_id')->nullable()->constrained('users');
            $table->timestamp('last_transfer_date')->nullable();
            $table->decimal('transfer_fees', 10, 2)->nullable();
            
            // Retiro de créditos
            $table->text('retirement_reason')->nullable();
            $table->timestamp('retirement_date')->nullable();
            $table->foreignId('retired_by')->nullable()->constrained('users');
            $table->string('retirement_certificate')->nullable(); // URL del certificado
            
            // Riesgos y garantías
            $table->enum('risk_rating', [
                'very_low',
                'low', 
                'medium',
                'high',
                'very_high'
            ])->nullable();
            
            $table->text('risk_factors')->nullable();
            $table->boolean('insurance_coverage')->default(false);
            $table->decimal('insurance_amount', 12, 2)->nullable();
            
            // Trazabilidad y transparencia
            $table->string('blockchain_hash')->nullable(); // Hash blockchain para trazabilidad
            $table->json('provenance_chain')->nullable(); // Cadena de procedencia
            $table->boolean('public_registry_listed')->default(false);
            $table->string('registry_url')->nullable();
            
            // Impacto ambiental real
            $table->decimal('actual_co2_reduced', 12, 4)->nullable(); // CO2 realmente reducido
            $table->decimal('measurement_uncertainty', 5, 2)->nullable(); // Incertidumbre %
            $table->json('environmental_monitoring')->nullable();
            
            // Información técnica del proyecto
            $table->json('technical_specifications')->nullable();
            $table->decimal('project_capacity_mw', 10, 3)->nullable(); // Capacidad del proyecto
            $table->integer('expected_project_lifetime_years')->nullable();
            $table->decimal('annual_emission_reductions', 10, 4)->nullable(); // tCO2e/año
            
            // Cumplimiento regulatorio
            $table->json('regulatory_approvals')->nullable();
            $table->boolean('meets_article_6_requirements')->default(false); // Artículo 6 Acuerdo París
            $table->boolean('corresponding_adjustment_applied')->default(false);
            $table->json('regulatory_metadata')->nullable();
            
            // Información de sostenibilidad
            $table->json('sustainability_certifications')->nullable();
            $table->boolean('gender_inclusive')->default(false);
            $table->boolean('community_engagement')->default(false);
            $table->text('social_impact_description')->nullable();
            
            // Metadatos y personalización
            $table->json('custom_attributes')->nullable();
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable(); // Documentos adjuntos
            
            // Estado del registro
            $table->boolean('is_active')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'status']);
            $table->index(['provider_id', 'credit_type']);
            $table->index(['project_id', 'vintage_year']);
            $table->index(['credit_type', 'status']);
            $table->index(['vintage_year', 'status']);
            $table->index(['issuance_date', 'status']);
            $table->index(['project_country', 'project_type']);
            $table->index('registry_id');
            $table->index('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carbon_credits');
    }
};