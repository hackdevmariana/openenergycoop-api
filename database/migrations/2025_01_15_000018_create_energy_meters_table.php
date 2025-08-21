<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_meters', function (Blueprint $table) {
            $table->id();
            $table->string('meter_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('meter_type', ['smart_meter', 'digital_meter', 'analog_meter', 'prepaid_meter', 'postpaid_meter', 'bi_directional', 'net_meter', 'sub_meter', 'other']);
            $table->enum('status', ['active', 'inactive', 'maintenance', 'faulty', 'replaced', 'decommissioned', 'calibrating']);
            $table->enum('meter_category', ['electricity', 'water', 'gas', 'heat', 'steam', 'compressed_air', 'other']);
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('hardware_version')->nullable();
            $table->unsignedBigInteger('installation_id')->nullable();
            $table->unsignedBigInteger('consumption_point_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->text('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->date('installation_date');
            $table->date('commissioning_date')->nullable();
            $table->date('last_calibration_date')->nullable();
            $table->date('next_calibration_date')->nullable();
            $table->date('warranty_expiry_date')->nullable();
            $table->decimal('voltage_rating', 8, 2)->nullable();
            $table->string('voltage_unit')->nullable();
            $table->decimal('current_rating', 8, 2)->nullable();
            $table->string('current_unit')->nullable();
            $table->string('phase_type')->nullable();
            $table->string('connection_type')->nullable();
            $table->decimal('accuracy_class', 5, 2)->nullable();
            $table->decimal('measurement_range_min', 10, 2)->nullable();
            $table->decimal('measurement_range_max', 10, 2)->nullable();
            $table->string('measurement_unit')->nullable();
            $table->decimal('pulse_constant', 8, 2)->nullable();
            $table->string('pulse_unit')->nullable();
            $table->boolean('is_smart_meter')->default(false);
            $table->boolean('has_remote_reading')->default(false);
            $table->boolean('has_two_way_communication')->default(false);
            $table->string('communication_protocol')->nullable();
            $table->string('communication_frequency')->nullable();
            $table->string('data_logging_interval')->nullable();
            $table->integer('data_retention_days')->nullable();
            $table->text('technical_specifications')->nullable();
            $table->text('calibration_requirements')->nullable();
            $table->text('maintenance_requirements')->nullable();
            $table->text('safety_features')->nullable();
            $table->json('meter_features')->nullable();
            $table->json('communication_settings')->nullable();
            $table->json('alarm_settings')->nullable();
            $table->json('data_formats')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('installed_by')->nullable();
            $table->unsignedBigInteger('managed_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['meter_number', 'status']);
            $table->index(['meter_type', 'status']);
            $table->index(['meter_category', 'status']);
            $table->index(['manufacturer', 'model']);
            $table->index(['serial_number', 'status']);
            $table->index(['installation_id', 'status']);
            $table->index(['consumption_point_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['installed_by', 'status']);
            $table->index(['managed_by', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);

            $table->foreign('installation_id')->references('id')->on('energy_installations')->onDelete('restrict');
            $table->foreign('consumption_point_id')->references('id')->on('consumption_points')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('installed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('managed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_meters');
    }
};
