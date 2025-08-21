<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_readings', function (Blueprint $table) {
            $table->id();
            $table->string('reading_number')->unique();
            $table->unsignedBigInteger('meter_id');
            $table->unsignedBigInteger('installation_id')->nullable();
            $table->unsignedBigInteger('consumption_point_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('reading_type', ['instantaneous', 'interval', 'cumulative', 'demand', 'energy', 'power_factor', 'voltage', 'current', 'frequency', 'other']);
            $table->enum('reading_source', ['manual', 'automatic', 'remote', 'estimated', 'calculated', 'imported']);
            $table->enum('reading_status', ['valid', 'invalid', 'suspicious', 'estimated', 'corrected', 'missing']);
            $table->timestamp('reading_timestamp');
            $table->string('reading_period')->nullable();
            $table->decimal('reading_value', 15, 4);
            $table->string('reading_unit');
            $table->decimal('previous_reading_value', 15, 4)->nullable();
            $table->decimal('consumption_value', 15, 4)->nullable();
            $table->string('consumption_unit')->nullable();
            $table->decimal('demand_value', 15, 4)->nullable();
            $table->string('demand_unit')->nullable();
            $table->decimal('power_factor', 5, 3)->nullable();
            $table->decimal('voltage_value', 8, 2)->nullable();
            $table->string('voltage_unit')->nullable();
            $table->decimal('current_value', 8, 2)->nullable();
            $table->string('current_unit')->nullable();
            $table->decimal('frequency_value', 8, 2)->nullable();
            $table->string('frequency_unit')->nullable();
            $table->decimal('temperature', 6, 2)->nullable();
            $table->string('temperature_unit')->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->string('humidity_unit')->nullable();
            $table->decimal('quality_score', 5, 2)->nullable();
            $table->text('quality_notes')->nullable();
            $table->text('validation_notes')->nullable();
            $table->text('correction_notes')->nullable();
            $table->json('raw_data')->nullable();
            $table->json('processed_data')->nullable();
            $table->json('alarms')->nullable();
            $table->json('events')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('read_by')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->unsignedBigInteger('corrected_by')->nullable();
            $table->timestamp('corrected_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reading_number', 'reading_status']);
            $table->index(['meter_id', 'reading_timestamp']);
            $table->index(['installation_id', 'reading_timestamp']);
            $table->index(['consumption_point_id', 'reading_timestamp']);
            $table->index(['customer_id', 'reading_timestamp']);
            $table->index(['reading_type', 'reading_status']);
            $table->index(['reading_source', 'reading_status']);
            $table->index(['reading_status', 'reading_timestamp']);
            $table->index(['read_by', 'reading_timestamp']);
            $table->index(['validated_by', 'reading_timestamp']);
            $table->index(['corrected_by', 'reading_timestamp']);
            $table->index(['created_by', 'reading_timestamp']);

            $table->foreign('meter_id')->references('id')->on('energy_meters')->onDelete('restrict');
            $table->foreign('installation_id')->references('id')->on('energy_installations')->onDelete('restrict');
            $table->foreign('consumption_point_id')->references('id')->on('consumption_points')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('read_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('corrected_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_readings');
    }
};
