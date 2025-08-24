<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('schedule_type', ['preventive', 'predictive', 'condition_based', 'time_based', 'usage_based', 'calendar_based', 'event_based', 'manual']);
            $table->enum('frequency_type', ['daily', 'weekly', 'monthly', 'quarterly', 'semi_annually', 'annually', 'custom', 'hours', 'cycles', 'miles']);
            $table->integer('frequency_value')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('next_maintenance_date')->nullable();
            $table->timestamp('last_maintenance_date')->nullable();
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->string('equipment_type')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('location_type')->nullable();
            $table->json('maintenance_tasks')->nullable();
            $table->decimal('estimated_duration_hours', 8, 2)->nullable();
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->json('assigned_technicians')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'critical'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_generate_tasks')->default(false);
            $table->unsignedBigInteger('task_template_id')->nullable();
            $table->unsignedBigInteger('checklist_template_id')->nullable();
            $table->json('required_parts')->nullable();
            $table->json('required_tools')->nullable();
            $table->json('safety_requirements')->nullable();
            $table->json('technical_requirements')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('contract_number')->nullable();
            $table->json('warranty_terms')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('department')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->json('risk_assessment')->nullable();
            $table->json('compliance_requirements')->nullable();
            $table->json('documentation_required')->nullable();
            $table->json('quality_standards')->nullable();
            $table->json('environmental_considerations')->nullable();
            $table->string('budget_code')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('project_code')->nullable();
            $table->timestamp('maintenance_window_start')->nullable();
            $table->timestamp('maintenance_window_end')->nullable();
            $table->json('downtime_impact')->nullable();
            $table->boolean('backup_equipment_available')->default(false);
            $table->json('emergency_contacts')->nullable();
            $table->json('escalation_procedures')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
            $table->index(['schedule_type', 'is_active']);
            $table->index(['frequency_type', 'is_active']);
            $table->index(['priority', 'is_active']);
            $table->index(['is_active', 'next_maintenance_date']);
            $table->index(['equipment_id', 'equipment_type']);
            $table->index(['location_id', 'location_type']);
            $table->index(['vendor_id', 'is_active']);
            $table->index(['task_template_id', 'is_active']);
            $table->index(['checklist_template_id', 'is_active']);
            $table->index(['created_by', 'is_active']);
            $table->index(['approved_by', 'is_active']);

            $table->foreign('equipment_id')->references('id')->on('energy_installations')->onDelete('restrict');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('restrict');
            $table->foreign('task_template_id')->references('id')->on('task_templates')->onDelete('restrict');
            $table->foreign('checklist_template_id')->references('id')->on('checklist_templates')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
