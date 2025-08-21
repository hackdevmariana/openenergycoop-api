<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('forecast_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('forecast_type', ['demand', 'generation', 'consumption', 'price', 'weather', 'load', 'renewable', 'storage', 'transmission', 'other']);
            $table->enum('forecast_horizon', ['hourly', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'long_term']);
            $table->enum('forecast_method', ['statistical', 'machine_learning', 'physical_model', 'hybrid', 'expert_judgment', 'other']);
            $table->enum('forecast_status', ['draft', 'active', 'validated', 'expired', 'superseded', 'archived']);
            $table->enum('accuracy_level', ['low', 'medium', 'high', 'very_high'])->default('medium');
            $table->decimal('accuracy_score', 5, 2)->nullable();
            $table->decimal('confidence_interval_lower', 10, 2)->nullable();
            $table->decimal('confidence_interval_upper', 10, 2)->nullable();
            $table->decimal('confidence_level', 5, 2)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_type')->nullable();
            $table->timestamp('forecast_start_time');
            $table->timestamp('forecast_end_time');
            $table->timestamp('generation_time');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->timestamp('expiry_time')->nullable();
            $table->string('time_zone')->nullable();
            $table->string('time_resolution')->nullable();
            $table->integer('forecast_periods')->nullable();
            $table->decimal('total_forecasted_value', 15, 2)->nullable();
            $table->string('forecast_unit')->nullable();
            $table->decimal('baseline_value', 15, 2)->nullable();
            $table->decimal('trend_value', 15, 2)->nullable();
            $table->decimal('seasonal_value', 15, 2)->nullable();
            $table->decimal('cyclical_value', 15, 2)->nullable();
            $table->decimal('irregular_value', 15, 2)->nullable();
            $table->json('forecast_data')->nullable();
            $table->json('baseline_data')->nullable();
            $table->json('trend_data')->nullable();
            $table->json('seasonal_data')->nullable();
            $table->json('cyclical_data')->nullable();
            $table->json('irregular_data')->nullable();
            $table->json('weather_data')->nullable();
            $table->json('input_variables')->nullable();
            $table->json('model_parameters')->nullable();
            $table->json('validation_metrics')->nullable();
            $table->json('performance_history')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['forecast_number', 'forecast_status']);
            $table->index(['forecast_type', 'forecast_status']);
            $table->index(['forecast_horizon', 'forecast_status']);
            $table->index(['forecast_method', 'forecast_status']);
            $table->index(['source_id', 'source_type']);
            $table->index(['target_id', 'target_type']);
            $table->index(['forecast_status', 'forecast_start_time']);
            $table->index(['forecast_status', 'forecast_end_time']);
            $table->index(['accuracy_level', 'forecast_status']);
            $table->index(['created_by', 'forecast_status']);
            $table->index(['approved_by', 'forecast_status']);
            $table->index(['validated_by', 'forecast_status']);

            $table->foreign('source_id')->references('id')->on('energy_sources')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_forecasts');
    }
};
