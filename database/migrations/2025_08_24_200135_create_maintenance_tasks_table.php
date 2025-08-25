<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('task_type', ['inspection', 'repair', 'replacement', 'calibration', 'cleaning', 'lubrication', 'testing', 'upgrade', 'installation', 'demolition']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'critical']);
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'on_hold', 'completed', 'cancelled', 'overdue', 'scheduled', 'waiting_parts', 'waiting_approval']);
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->date('due_date');
            $table->date('start_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->nullable();
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->string('equipment_type')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('location_type')->nullable();
            $table->unsignedBigInteger('maintenance_schedule_id')->nullable();
            $table->json('checklist_items')->nullable();
            $table->json('required_tools')->nullable();
            $table->json('required_parts')->nullable();
            $table->text('safety_notes')->nullable();
            $table->text('technical_notes')->nullable();
            $table->decimal('cost_estimate', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->boolean('warranty_work')->default(false);
            $table->boolean('recurring')->default(false);
            $table->string('recurrence_pattern')->nullable();
            $table->date('next_recurrence_date')->nullable();
            $table->json('attachments')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('work_order_number')->nullable();
            $table->string('department')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'extreme'])->default('medium');
            $table->text('completion_notes')->nullable();
            $table->integer('quality_score')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->boolean('preventive_maintenance')->default(false);
            $table->boolean('corrective_maintenance')->default(false);
            $table->boolean('emergency_maintenance')->default(false);
            $table->boolean('planned_maintenance')->default(false);
            $table->boolean('unplanned_maintenance')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['title', 'status']);
            $table->index(['task_type', 'status']);
            $table->index(['priority', 'status']);
            $table->index(['status', 'due_date']);
            $table->index(['assigned_to', 'status']);
            $table->index(['assigned_by', 'status']);
            $table->index(['equipment_id', 'equipment_type']);
            $table->index(['location_id', 'location_type']);
            $table->index(['maintenance_schedule_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('maintenance_schedule_id')->references('id')->on('maintenance_schedules')->onDelete('restrict');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_tasks');
    }
};
