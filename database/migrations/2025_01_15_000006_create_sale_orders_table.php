<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('affiliate_id')->nullable();
            $table->enum('order_type', ['standard', 'pre_order', 'subscription', 'wholesale', 'bulk', 'custom']);
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'on_hold']);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'failed', 'refunded', 'cancelled']);
            $table->enum('shipping_status', ['pending', 'processing', 'shipped', 'delivered', 'returned', 'lost']);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('refunded_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_date')->nullable();
            $table->timestamp('delivered_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('special_instructions')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('order_items')->nullable();
            $table->json('applied_discounts')->nullable();
            $table->json('shipping_details')->nullable();
            $table->json('customer_notes')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->unsignedBigInteger('shipped_by')->nullable();
            $table->unsignedBigInteger('delivered_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_number', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['affiliate_id', 'status']);
            $table->index(['order_type', 'status']);
            $table->index(['payment_status', 'status']);
            $table->index(['shipping_status', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['processed_by', 'status']);

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('shipped_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('delivered_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_orders');
    }
};
