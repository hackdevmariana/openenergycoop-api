<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('transfer_type', ['generation', 'consumption', 'storage', 'grid_import', 'grid_export', 'peer_to_peer', 'virtual', 'physical', 'contractual', 'other']);
            $table->enum('status', ['pending', 'scheduled', 'in_progress', 'completed', 'cancelled', 'failed', 'on_hold', 'reversed']);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent', 'critical'])->default('normal');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->string('destination_type')->nullable();
            $table->unsignedBigInteger('source_meter_id')->nullable();
            $table->unsignedBigInteger('destination_meter_id')->nullable();
            $table->decimal('transfer_amount_kwh', 12, 2);
            $table->decimal('transfer_amount_mwh', 10, 2);
            $table->decimal('transfer_rate_kw', 10, 2)->nullable();
            $table->decimal('transfer_rate_mw', 8, 2)->nullable();
            $table->string('transfer_unit')->nullable();
            $table->timestamp('scheduled_start_time');
            $table->timestamp('scheduled_end_time');
            $table->timestamp('actual_start_time')->nullable();
            $table->timestamp('actual_end_time')->nullable();
            $table->timestamp('completion_time')->nullable();
            $table->decimal('duration_hours', 8, 2)->nullable();
            $table->decimal('efficiency_percentage', 5, 2)->nullable();
            $table->decimal('loss_percentage', 5, 2)->nullable();
            $table->decimal('loss_amount_kwh', 12, 2)->nullable();
            $table->decimal('net_transfer_amount_kwh', 12, 2);
            $table->decimal('net_transfer_amount_mwh', 10, 2);
            $table->decimal('cost_per_kwh', 10, 4)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->string('transfer_method')->nullable();
            $table->string('transfer_medium')->nullable();
            $table->string('transfer_protocol')->nullable();
            $table->boolean('is_automated')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->text('transfer_conditions')->nullable();
            $table->text('safety_requirements')->nullable();
            $table->text('quality_standards')->nullable();
            $table->json('transfer_parameters')->nullable();
            $table->json('monitoring_data')->nullable();
            $table->json('alarm_settings')->nullable();
            $table->json('event_logs')->nullable();
            $table->json('performance_metrics')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('scheduled_by')->nullable();
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transfer_number', 'status']);
            $table->index(['transfer_type', 'status']);
            $table->index(['source_id', 'source_type']);
            $table->index(['destination_id', 'destination_type']);
            $table->index(['source_meter_id', 'status']);
            $table->index(['destination_meter_id', 'status']);
            $table->index(['status', 'scheduled_start_time']);
            $table->index(['status', 'scheduled_end_time']);
            $table->index(['scheduled_by', 'status']);
            $table->index(['initiated_by', 'status']);
            $table->index(['approved_by', 'status']);
            $table->index(['verified_by', 'status']);
            $table->index(['completed_by', 'status']);
            $table->index(['created_by', 'status']);

            $table->foreign('source_meter_id')->references('id')->on('energy_meters')->onDelete('restrict');
            $table->foreign('destination_meter_id')->references('id')->on('energy_meters')->onDelete('restrict');
            $table->foreign('scheduled_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('initiated_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_transfers');
    }
};
