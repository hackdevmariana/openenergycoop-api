<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('affiliate_code')->unique();
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending_approval', 'rejected']);
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum', 'diamond']);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->decimal('pending_earnings', 15, 2)->default(0);
            $table->decimal('paid_earnings', 15, 2)->default(0);
            $table->integer('total_referrals')->default(0);
            $table->integer('active_referrals')->default(0);
            $table->integer('converted_referrals')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->date('joined_date');
            $table->date('last_activity_date')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->json('payment_methods')->nullable();
            $table->json('marketing_materials')->nullable();
            $table->json('performance_metrics')->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'tier']);
            $table->index(['affiliate_code', 'status']);
            $table->index(['referred_by', 'status']);
            $table->index(['approved_by', 'status']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('referred_by')->references('id')->on('affiliates')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
