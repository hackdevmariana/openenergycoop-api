<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('milestone_type', ['project', 'financial', 'operational', 'regulatory', 'community', 'environmental', 'other']);
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'on_hold', 'cancelled', 'overdue']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'critical']);
            $table->date('target_date');
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->text('success_criteria')->nullable();
            $table->text('dependencies')->nullable();
            $table->text('risks')->nullable();
            $table->text('mitigation_strategies')->nullable();
            $table->unsignedBigInteger('parent_milestone_id')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['milestone_type', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['target_date', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['parent_milestone_id', 'status']);

            $table->foreign('parent_milestone_id')->references('id')->on('milestones')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
