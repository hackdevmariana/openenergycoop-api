<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'free_shipping', 'buy_one_get_one', 'tiered']);
            $table->decimal('discount_value', 15, 2);
            $table->decimal('minimum_purchase_amount', 15, 2)->nullable();
            $table->decimal('maximum_discount_amount', 15, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'expired', 'depleted']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('per_user_limit')->default(1);
            $table->boolean('is_first_time_only')->default(false);
            $table->boolean('is_new_customer_only')->default(false);
            $table->json('applicable_products')->nullable();
            $table->json('excluded_products')->nullable();
            $table->json('applicable_categories')->nullable();
            $table->json('excluded_categories')->nullable();
            $table->json('applicable_user_groups')->nullable();
            $table->json('excluded_user_groups')->nullable();
            $table->boolean('can_be_combined')->default(false);
            $table->json('combination_rules')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('usage_instructions')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'status']);
            $table->index(['status', 'start_date', 'end_date']);
            $table->index(['discount_type', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
