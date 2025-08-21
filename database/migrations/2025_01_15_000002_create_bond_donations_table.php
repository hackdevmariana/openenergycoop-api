<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bond_donations', function (Blueprint $table) {
            $table->id();
            $table->string('donation_number')->unique();
            $table->unsignedBigInteger('donor_id');
            $table->unsignedBigInteger('energy_bond_id');
            $table->enum('donation_type', ['charitable', 'educational', 'environmental', 'community', 'research', 'other']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed', 'expired']);
            $table->decimal('donation_amount', 15, 2);
            $table->integer('bond_units');
            $table->decimal('unit_price_at_donation', 15, 2);
            $table->decimal('total_value_at_donation', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->date('donation_date');
            $table->date('approval_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('donation_purpose');
            $table->text('impact_description')->nullable();
            $table->text('recipient_organization')->nullable();
            $table->text('recipient_beneficiaries')->nullable();
            $table->text('project_description')->nullable();
            $table->enum('project_status', ['not_started', 'in_progress', 'completed', 'on_hold', 'cancelled'])->nullable();
            $table->decimal('project_budget', 15, 2)->nullable();
            $table->decimal('project_spent', 15, 2)->nullable();
            $table->date('project_start_date')->nullable();
            $table->date('project_end_date')->nullable();
            $table->text('project_milestones')->nullable();
            $table->text('project_outcomes')->nullable();
            $table->text('project_challenges')->nullable();
            $table->text('project_lessons_learned')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_frequency', ['monthly', 'quarterly', 'semi_annually', 'annually'])->nullable();
            $table->date('next_recurrence_date')->nullable();
            $table->integer('recurrence_count')->default(0);
            $table->integer('max_recurrences')->nullable();
            $table->boolean('is_matched')->default(false);
            $table->decimal('matching_ratio', 5, 2)->nullable();
            $table->decimal('matching_amount', 15, 2)->nullable();
            $table->string('matching_organization')->nullable();
            $table->text('matching_terms')->nullable();
            $table->boolean('is_tax_deductible')->default(false);
            $table->string('tax_deduction_reference')->nullable();
            $table->decimal('tax_deduction_amount', 15, 2)->nullable();
            $table->text('tax_deduction_notes')->nullable();
            $table->json('donor_preferences')->nullable();
            $table->json('communication_preferences')->nullable();
            $table->json('reporting_preferences')->nullable();
            $table->json('recognition_preferences')->nullable();
            $table->text('special_instructions')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['donor_id', 'status']);
            $table->index(['energy_bond_id', 'status']);
            $table->index(['donation_type', 'status']);
            $table->index(['status', 'donation_date']);
            $table->index(['is_anonymous', 'status']);
            $table->index(['is_recurring', 'status']);
            $table->index(['is_matched', 'status']);
            $table->index(['is_tax_deductible', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);
            $table->index(['processed_by', 'status']);

            // Claves foráneas
            $table->foreign('donor_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('energy_bond_id')->references('id')->on('energy_bonds')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bond_donations');
    }
};
