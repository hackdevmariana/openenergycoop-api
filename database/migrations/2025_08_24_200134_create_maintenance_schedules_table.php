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
            $table->enum('schedule_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'])->default('monthly');
            $table->string('cron_expression')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->string('equipment_type')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('location_type')->nullable();
            $table->unsignedBigInteger('assigned_team_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('task_template_id')->nullable();
            $table->unsignedBigInteger('checklist_template_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->json('schedule_config')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['schedule_type', 'is_active']);
            $table->index(['equipment_id', 'equipment_type']);
            $table->index(['location_id', 'location_type']);
            $table->index(['start_date', 'end_date']);
            $table->index(['created_by', 'is_active']);
            $table->index(['approved_by', 'is_active']);
            $table->index(['vendor_id', 'is_active']);
            $table->index(['task_template_id', 'is_active']);
            $table->index(['checklist_template_id', 'is_active']);

            $table->foreign('equipment_id')->references('id')->on('devices')->onDelete('restrict');
            $table->foreign('assigned_team_id')->references('id')->on('teams')->onDelete('restrict');
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
