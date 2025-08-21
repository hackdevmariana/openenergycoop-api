<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_bonds', function (Blueprint $table) {
            $table->id();
            $table->string('bond_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('bond_type', ['solar', 'wind', 'hydro', 'biomass', 'geothermal', 'hybrid', 'other']);
            $table->enum('status', ['active', 'inactive', 'expired', 'redeemed', 'cancelled', 'pending_approval']);
            $table->decimal('face_value', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->enum('interest_frequency', ['monthly', 'quarterly', 'semi_annually', 'annually']);
            $table->date('issue_date');
            $table->date('maturity_date');
            $table->date('first_interest_date');
            $table->date('last_interest_payment_date')->nullable();
            $table->date('next_interest_payment_date')->nullable();
            $table->integer('total_interest_payments');
            $table->integer('paid_interest_payments')->default(0);
            $table->decimal('total_interest_paid', 15, 2)->default(0);
            $table->decimal('outstanding_principal', 15, 2);
            $table->decimal('minimum_investment', 15, 2);
            $table->decimal('maximum_investment', 15, 2)->nullable();
            $table->integer('total_units_available');
            $table->integer('units_issued')->default(0);
            $table->integer('units_reserved')->default(0);
            $table->decimal('unit_price', 15, 2);
            $table->enum('payment_schedule', ['monthly', 'quarterly', 'semi_annually', 'annually', 'at_maturity']);
            $table->boolean('is_tax_free')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('is_guaranteed')->default(false);
            $table->string('guarantor_name')->nullable();
            $table->text('guarantee_terms')->nullable();
            $table->boolean('is_collateralized')->default(false);
            $table->text('collateral_description')->nullable();
            $table->decimal('collateral_value', 15, 2)->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'very_high']);
            $table->enum('credit_rating', ['aaa', 'aa', 'a', 'bbb', 'bb', 'b', 'ccc', 'cc', 'c', 'd'])->nullable();
            $table->text('risk_disclosure')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('priority_order')->default(0);
            $table->json('terms_conditions')->nullable();
            $table->json('disclosure_documents')->nullable();
            $table->json('legal_documents')->nullable();
            $table->json('financial_reports')->nullable();
            $table->json('performance_metrics')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('managed_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['bond_type', 'status']);
            $table->index(['status', 'is_public']);
            $table->index(['maturity_date', 'status']);
            $table->index(['interest_rate', 'status']);
            $table->index(['risk_level', 'status']);
            $table->index(['is_featured', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);
            $table->index(['managed_by', 'status']);

            // Claves foráneas
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('managed_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_bonds');
    }
};
