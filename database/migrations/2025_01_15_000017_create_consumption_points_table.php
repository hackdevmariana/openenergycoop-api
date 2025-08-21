<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumption_points', function (Blueprint $table) {
            $table->id();
            $table->string('point_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('point_type', ['residential', 'commercial', 'industrial', 'agricultural', 'public', 'street_lighting', 'charging_station', 'other']);
            $table->enum('status', ['active', 'inactive', 'maintenance', 'disconnected', 'planned', 'decommissioned']);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('installation_id')->nullable();
            $table->text('location_address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('peak_demand_kw', 10, 2)->nullable();
            $table->decimal('average_demand_kw', 10, 2)->nullable();
            $table->decimal('annual_consumption_kwh', 12, 2)->nullable();
            $table->decimal('monthly_consumption_kwh', 12, 2)->nullable();
            $table->decimal('daily_consumption_kwh', 12, 2)->nullable();
            $table->decimal('hourly_consumption_kwh', 12, 2)->nullable();
            $table->date('connection_date')->nullable();
            $table->date('disconnection_date')->nullable();
            $table->string('meter_number')->nullable();
            $table->string('meter_type')->nullable();
            $table->string('meter_manufacturer')->nullable();
            $table->string('meter_model')->nullable();
            $table->date('meter_installation_date')->nullable();
            $table->date('meter_last_calibration_date')->nullable();
            $table->date('meter_next_calibration_date')->nullable();
            $table->decimal('voltage_level', 8, 2)->nullable();
            $table->string('voltage_unit')->nullable();
            $table->decimal('current_rating', 8, 2)->nullable();
            $table->string('current_unit')->nullable();
            $table->string('phase_type')->nullable();
            $table->string('connection_type')->nullable();
            $table->text('technical_specifications')->nullable();
            $table->text('safety_features')->nullable();
            $table->json('load_profile')->nullable();
            $table->json('consumption_patterns')->nullable();
            $table->json('peak_hours')->nullable();
            $table->json('off_peak_hours')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('managed_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['point_number', 'status']);
            $table->index(['point_type', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['installation_id', 'status']);
            $table->index(['meter_number', 'status']);
            $table->index(['managed_by', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('installation_id')->references('id')->on('energy_installations')->onDelete('restrict');
            $table->foreign('managed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumption_points');
    }
};
