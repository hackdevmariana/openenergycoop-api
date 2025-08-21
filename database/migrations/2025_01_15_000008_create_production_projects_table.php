<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('project_type', ['solar_farm', 'wind_farm', 'hydroelectric', 'biomass', 'geothermal', 'hybrid', 'storage', 'grid_upgrade', 'other']);
            $table->enum('status', ['planning', 'approved', 'in_progress', 'on_hold', 'completed', 'cancelled', 'maintenance']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'critical']);
            $table->date('start_date');
            $table->date('expected_completion_date');
            $table->date('actual_completion_date')->nullable();
            $table->decimal('budget', 15, 2);
            $table->decimal('spent_amount', 15, 2)->default(0);
            $table->decimal('remaining_budget', 15, 2);
            $table->decimal('planned_capacity_mw', 10, 2);
            $table->decimal('actual_capacity_mw', 10, 2)->nullable();
            $table->decimal('efficiency_rating', 5, 2)->nullable();
            $table->text('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('technical_specifications')->nullable();
            $table->text('environmental_impact')->nullable();
            $table->text('regulatory_compliance')->nullable();
            $table->text('safety_measures')->nullable();
            $table->json('project_team')->nullable();
            $table->json('stakeholders')->nullable();
            $table->json('contractors')->nullable();
            $table->json('suppliers')->nullable();
            $table->json('milestones')->nullable();
            $table->json('risks')->nullable();
            $table->json('mitigation_strategies')->nullable();
            $table->json('quality_standards')->nullable();
            $table->json('documentation')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('project_manager')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_number', 'status']);
            $table->index(['project_type', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['start_date', 'expected_completion_date']);
            $table->index(['project_manager', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);

            $table->foreign('project_manager')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_projects');
    }
};
