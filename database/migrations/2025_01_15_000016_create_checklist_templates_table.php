<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('template_type', ['maintenance', 'inspection', 'safety', 'quality', 'compliance', 'audit', 'training', 'operations', 'procedure', 'workflow']);
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->json('checklist_items')->nullable();
            $table->json('required_items')->nullable();
            $table->json('optional_items')->nullable();
            $table->json('conditional_items')->nullable();
            $table->json('item_order')->nullable();
            $table->json('scoring_system')->nullable();
            $table->decimal('pass_threshold', 5, 2)->nullable();
            $table->decimal('fail_threshold', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_standard')->default(false);
            $table->string('version')->default('1.0');
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->string('department')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'critical'])->default('medium');
            $table->enum('risk_level', ['low', 'medium', 'high', 'extreme'])->default('medium');
            $table->json('compliance_requirements')->nullable();
            $table->json('quality_standards')->nullable();
            $table->json('safety_requirements')->nullable();
            $table->boolean('training_required')->default(false);
            $table->boolean('certification_required')->default(false);
            $table->json('documentation_required')->nullable();
            $table->json('environmental_considerations')->nullable();
            $table->string('budget_code')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('project_code')->nullable();
            $table->decimal('estimated_completion_time', 8, 2)->nullable();
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->json('required_skills')->nullable();
            $table->json('required_tools')->nullable();
            $table->json('required_parts')->nullable();
            $table->json('work_instructions')->nullable();
            $table->json('reference_documents')->nullable();
            $table->json('best_practices')->nullable();
            $table->json('lessons_learned')->nullable();
            $table->json('continuous_improvement')->nullable();
            $table->integer('audit_frequency')->nullable();
            $table->date('last_review_date')->nullable();
            $table->date('next_review_date')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->json('approval_workflow')->nullable();
            $table->json('escalation_procedures')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
            $table->index(['template_type', 'is_active']);
            $table->index(['category', 'is_active']);
            $table->index(['subcategory', 'is_active']);
            $table->index(['priority', 'is_active']);
            $table->index(['risk_level', 'is_active']);
            $table->index(['department', 'is_active']);
            $table->index(['is_standard', 'is_active']);
            $table->index(['reviewed_by', 'is_active']);
            $table->index(['created_by', 'is_active']);
            $table->index(['approved_by', 'is_active']);

            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_templates');
    }
};
