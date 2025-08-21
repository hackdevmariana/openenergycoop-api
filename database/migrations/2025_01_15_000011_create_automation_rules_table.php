<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('rule_type', ['scheduled', 'event_driven', 'condition_based', 'manual', 'webhook']);
            $table->enum('trigger_type', ['time', 'event', 'condition', 'threshold', 'pattern', 'external']);
            $table->json('trigger_conditions')->nullable();
            $table->enum('action_type', ['email', 'sms', 'webhook', 'database', 'api_call', 'system_command', 'notification', 'report']);
            $table->json('action_parameters')->nullable();
            $table->unsignedBigInteger('target_entity_id')->nullable();
            $table->string('target_entity_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(5);
            $table->enum('execution_frequency', ['once', 'hourly', 'daily', 'weekly', 'monthly', 'custom'])->nullable();
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamp('next_execution_at')->nullable();
            $table->integer('execution_count')->default(0);
            $table->integer('max_executions')->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->text('last_error_message')->nullable();
            $table->string('schedule_cron')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('retry_on_failure')->default(false);
            $table->integer('max_retries')->default(3);
            $table->integer('retry_delay_minutes')->default(5);
            $table->json('notification_emails')->nullable();
            $table->string('webhook_url')->nullable();
            $table->json('webhook_headers')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
            $table->index(['rule_type', 'is_active']);
            $table->index(['trigger_type', 'is_active']);
            $table->index(['action_type', 'is_active']);
            $table->index(['priority', 'is_active']);
            $table->index(['execution_frequency', 'is_active']);
            $table->index(['is_active', 'next_execution_at']);
            $table->index(['created_by', 'is_active']);
            $table->index(['approved_by', 'is_active']);

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
