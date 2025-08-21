<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_sale_offers', function (Blueprint $table) {
            $table->id();
            $table->string('offer_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('offer_type', ['early_bird', 'founder', 'limited_time', 'exclusive', 'beta', 'pilot']);
            $table->enum('status', ['draft', 'active', 'paused', 'expired', 'cancelled', 'completed']);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('early_bird_end_date')->nullable();
            $table->date('founder_end_date')->nullable();
            $table->integer('total_units_available');
            $table->integer('units_reserved')->default(0);
            $table->integer('units_sold')->default(0);
            $table->decimal('early_bird_price', 15, 2);
            $table->decimal('founder_price', 15, 2)->nullable();
            $table->decimal('regular_price', 15, 2);
            $table->decimal('final_price', 15, 2);
            $table->decimal('savings_percentage', 5, 2)->nullable();
            $table->decimal('savings_amount', 15, 2)->nullable();
            $table->integer('max_units_per_customer')->default(1);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->text('terms_conditions')->nullable();
            $table->text('delivery_timeline')->nullable();
            $table->text('risk_disclosure')->nullable();
            $table->json('included_features')->nullable();
            $table->json('excluded_features')->nullable();
            $table->json('bonus_items')->nullable();
            $table->json('early_access_benefits')->nullable();
            $table->json('founder_benefits')->nullable();
            $table->json('marketing_materials')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['offer_number', 'status']);
            $table->index(['offer_type', 'status']);
            $table->index(['status', 'start_date', 'end_date']);
            $table->index(['is_featured', 'status']);
            $table->index(['is_public', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['approved_by', 'status']);

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_sale_offers');
    }
};
