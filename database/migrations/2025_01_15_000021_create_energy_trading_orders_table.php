<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_trading_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->enum('order_type', ['buy', 'sell', 'bid', 'ask', 'market', 'limit', 'stop', 'stop_limit', 'other']);
            $table->enum('order_status', ['pending', 'active', 'filled', 'partially_filled', 'cancelled', 'rejected', 'expired', 'completed']);
            $table->enum('order_side', ['buy', 'sell']);
            $table->unsignedBigInteger('trader_id');
            $table->unsignedBigInteger('pool_id')->nullable();
            $table->unsignedBigInteger('counterparty_id')->nullable();
            $table->decimal('quantity_mwh', 12, 2);
            $table->decimal('filled_quantity_mwh', 12, 2)->default(0);
            $table->decimal('remaining_quantity_mwh', 12, 2);
            $table->decimal('price_per_mwh', 10, 2);
            $table->decimal('total_value', 15, 2);
            $table->decimal('filled_value', 15, 2)->default(0);
            $table->decimal('remaining_value', 15, 2);
            $table->enum('price_type', ['fixed', 'floating', 'indexed', 'formula', 'other']);
            $table->string('price_index')->nullable();
            $table->decimal('price_adjustment', 10, 2)->nullable();
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->timestamp('execution_time')->nullable();
            $table->timestamp('expiry_time')->nullable();
            $table->enum('execution_type', ['immediate', 'good_till_cancelled', 'good_till_date', 'fill_or_kill', 'all_or_nothing', 'other']);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent', 'critical'])->default('normal');
            $table->boolean('is_negotiable')->default(false);
            $table->text('negotiation_terms')->nullable();
            $table->text('special_conditions')->nullable();
            $table->text('delivery_requirements')->nullable();
            $table->text('payment_terms')->nullable();
            $table->json('order_conditions')->nullable();
            $table->json('order_restrictions')->nullable();
            $table->json('order_metadata')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('executed_by')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_number', 'order_status']);
            $table->index(['order_type', 'order_status']);
            $table->index(['order_side', 'order_status']);
            $table->index(['trader_id', 'order_status']);
            $table->index(['pool_id', 'order_status']);
            $table->index(['counterparty_id', 'order_status']);
            $table->index(['order_status', 'valid_from']);
            $table->index(['order_status', 'expiry_time']);
            $table->index(['price_per_mwh', 'order_status']);
            $table->index(['quantity_mwh', 'order_status']);
            $table->index(['created_by', 'order_status']);
            $table->index(['approved_by', 'order_status']);
            $table->index(['executed_by', 'order_status']);

            $table->foreign('trader_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('pool_id')->references('id')->on('energy_pools')->onDelete('restrict');
            $table->foreign('counterparty_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('executed_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_trading_orders');
    }
};
