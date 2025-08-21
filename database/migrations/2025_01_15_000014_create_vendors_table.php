<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->enum('vendor_type', ['supplier', 'service_provider', 'contractor', 'consultant', 'manufacturer', 'distributor', 'wholesaler', 'retailer', 'maintenance', 'it_services', 'financial', 'insurance', 'legal', 'marketing', 'transportation', 'waste_management', 'security', 'cleaning', 'catering', 'other']);
            $table->string('industry')->nullable();
            $table->text('description')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('payment_terms')->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('rating', 3, 1)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_preferred')->default(false);
            $table->boolean('is_blacklisted')->default(false);
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->json('contract_terms')->nullable();
            $table->json('insurance_coverage')->nullable();
            $table->json('certifications')->nullable();
            $table->json('licenses')->nullable();
            $table->json('performance_metrics')->nullable();
            $table->json('quality_standards')->nullable();
            $table->json('delivery_terms')->nullable();
            $table->json('warranty_terms')->nullable();
            $table->json('return_policy')->nullable();
            $table->json('tags')->nullable();
            $table->string('logo')->nullable();
            $table->json('documents')->nullable();
            $table->json('bank_account')->nullable();
            $table->json('payment_methods')->nullable();
            $table->json('contact_history')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended', 'terminated', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->enum('risk_level', ['low', 'medium', 'high', 'extreme'])->default('medium');
            $table->enum('compliance_status', ['compliant', 'non_compliant', 'pending_review', 'under_investigation', 'approved', 'rejected'])->default('pending_review');
            $table->integer('audit_frequency')->nullable();
            $table->date('last_audit_date')->nullable();
            $table->date('next_audit_date')->nullable();
            $table->json('financial_stability')->nullable();
            $table->json('market_reputation')->nullable();
            $table->json('competitor_analysis')->nullable();
            $table->json('strategic_importance')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('alternatives')->nullable();
            $table->json('cost_benefit_analysis')->nullable();
            $table->json('performance_reviews')->nullable();
            $table->json('improvement_plans')->nullable();
            $table->json('escalation_procedures')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
            $table->index(['vendor_type', 'is_active']);
            $table->index(['industry', 'is_active']);
            $table->index(['status', 'is_active']);
            $table->index(['risk_level', 'is_active']);
            $table->index(['compliance_status', 'is_active']);
            $table->index(['is_verified', 'is_active']);
            $table->index(['is_preferred', 'is_active']);
            $table->index(['is_blacklisted', 'is_active']);
            $table->index(['rating', 'is_active']);
            $table->index(['created_by', 'is_active']);
            $table->index(['approved_by', 'is_active']);

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
