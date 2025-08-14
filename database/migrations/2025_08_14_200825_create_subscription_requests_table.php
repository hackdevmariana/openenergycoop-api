<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cooperative_id')->constrained('organizations')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_review'])->default('pending');
            $table->enum('type', ['new_subscription', 'ownership_change', 'tenant_request']);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['user_id', 'cooperative_id']);
            $table->index('status');
            $table->index('type');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_requests');
    }
};
