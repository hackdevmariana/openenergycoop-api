<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_installations', function (Blueprint $table) {
            $table->id();
            $table->string('installation_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('installation_type', ['residential', 'commercial', 'industrial', 'utility_scale', 'community', 'microgrid', 'off_grid', 'grid_tied']);
            $table->enum('status', ['planned', 'approved', 'in_progress', 'completed', 'operational', 'maintenance', 'decommissioned', 'cancelled']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'critical']);
            $table->unsignedBigInteger('energy_source_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->decimal('installed_capacity_kw', 10, 2);
            $table->decimal('operational_capacity_kw', 10, 2);
            $table->decimal('efficiency_rating', 5, 2);
            $table->decimal('annual_production_kwh', 12, 2);
            $table->decimal('monthly_production_kwh', 12, 2);
            $table->decimal('daily_production_kwh', 12, 2);
            $table->text('location_address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->date('installation_date');
            $table->date('commissioning_date')->nullable();
            $table->date('warranty_expiry_date')->nullable();
            $table->decimal('installation_cost', 15, 2);
            $table->decimal('operational_cost_per_kwh', 10, 2)->nullable();
            $table->decimal('maintenance_cost_per_kwh', 10, 2)->nullable();
            $table->text('technical_specifications')->nullable();
            $table->text('warranty_terms')->nullable();
            $table->text('maintenance_requirements')->nullable();
            $table->text('safety_features')->nullable();
            $table->json('equipment_details')->nullable();
            $table->json('maintenance_schedule')->nullable();
            $table->json('performance_metrics')->nullable();
            $table->json('warranty_documents')->nullable();
            $table->json('installation_photos')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('installed_by')->nullable();
            $table->unsignedBigInteger('managed_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['installation_number', 'status']);
            $table->index(['installation_type', 'status']);
            $table->index(['energy_source_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['project_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['installed_by', 'status']);
            $table->index(['managed_by', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);

            $table->foreign('energy_source_id')->references('id')->on('energy_sources')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('project_id')->references('id')->on('production_projects')->onDelete('restrict');
            $table->foreign('installed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('managed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_installations');
    }
};
