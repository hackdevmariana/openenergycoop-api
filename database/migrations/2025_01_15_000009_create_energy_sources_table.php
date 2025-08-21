<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('source_type', ['solar', 'wind', 'hydroelectric', 'biomass', 'geothermal', 'nuclear', 'fossil_fuel', 'hybrid', 'other']);
            $table->enum('status', ['active', 'inactive', 'maintenance', 'decommissioned', 'planned', 'under_construction']);
            $table->enum('energy_category', ['renewable', 'non_renewable', 'hybrid']);
            $table->decimal('installed_capacity_mw', 10, 2);
            $table->decimal('operational_capacity_mw', 10, 2);
            $table->decimal('efficiency_rating', 5, 2);
            $table->decimal('availability_factor', 5, 2);
            $table->decimal('capacity_factor', 5, 2);
            $table->decimal('annual_production_mwh', 12, 2);
            $table->decimal('monthly_production_mwh', 12, 2);
            $table->decimal('daily_production_mwh', 12, 2);
            $table->decimal('hourly_production_mwh', 12, 2);
            $table->text('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->date('commissioning_date')->nullable();
            $table->date('decommissioning_date')->nullable();
            $table->integer('expected_lifespan_years')->nullable();
            $table->decimal('construction_cost', 15, 2)->nullable();
            $table->decimal('operational_cost_per_mwh', 10, 2)->nullable();
            $table->decimal('maintenance_cost_per_mwh', 10, 2)->nullable();
            $table->text('technical_specifications')->nullable();
            $table->text('environmental_impact')->nullable();
            $table->text('regulatory_compliance')->nullable();
            $table->text('safety_features')->nullable();
            $table->json('equipment_details')->nullable();
            $table->json('maintenance_schedule')->nullable();
            $table->json('performance_metrics')->nullable();
            $table->json('environmental_data')->nullable();
            $table->json('regulatory_documents')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('managed_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'status']);
            $table->index(['source_type', 'status']);
            $table->index(['energy_category', 'status']);
            $table->index(['status', 'operational_capacity_mw']);
            $table->index(['managed_by', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);

            $table->foreign('managed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_sources');
    }
};
