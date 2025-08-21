<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->string('calculation_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('tax_type', ['income_tax', 'sales_tax', 'value_added_tax', 'property_tax', 'excise_tax', 'customs_duty', 'energy_tax', 'carbon_tax', 'environmental_tax', 'other']);
            $table->enum('calculation_type', ['automatic', 'manual', 'scheduled', 'event_triggered', 'batch', 'real_time', 'other']);
            $table->enum('status', ['draft', 'calculated', 'reviewed', 'approved', 'applied', 'cancelled', 'error']);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent', 'critical'])->default('normal');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('transaction_type')->nullable();
            $table->date('tax_period_start');
            $table->date('tax_period_end');
            $table->date('calculation_date');
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('taxable_amount', 15, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('tax_base_amount', 15, 2)->nullable();
            $table->decimal('exemption_amount', 15, 2)->default(0);
            $table->decimal('deduction_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->decimal('net_tax_amount', 15, 2);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->decimal('interest_amount', 15, 2)->default(0);
            $table->decimal('total_amount_due', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_remaining', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->string('tax_jurisdiction')->nullable();
            $table->string('tax_authority')->nullable();
            $table->string('tax_registration_number')->nullable();
            $table->string('tax_filing_frequency')->nullable();
            $table->string('tax_filing_method')->nullable();
            $table->boolean('is_estimated')->default(false);
            $table->boolean('is_final')->default(false);
            $table->boolean('is_amended')->default(false);
            $table->string('amendment_reason')->nullable();
            $table->text('calculation_notes')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('approval_notes')->nullable();
            $table->json('calculation_details')->nullable();
            $table->json('tax_breakdown')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->json('audit_trail')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('calculated_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('applied_by')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['calculation_number', 'status']);
            $table->index(['tax_type', 'status']);
            $table->index(['calculation_type', 'status']);
            $table->index(['entity_id', 'entity_type']);
            $table->index(['transaction_id', 'transaction_type']);
            $table->index(['status', 'due_date']);
            $table->index(['status', 'calculation_date']);
            $table->index(['calculated_by', 'status']);
            $table->index(['reviewed_by', 'status']);
            $table->index(['approved_by', 'status']);
            $table->index(['applied_by', 'status']);
            $table->index(['created_by', 'status']);

            $table->foreign('calculated_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('applied_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_calculations');
    }
};
