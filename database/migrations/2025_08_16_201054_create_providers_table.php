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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('contact_info')->nullable();
            $table->enum('type', ['energy', 'mining', 'physical_goods', 'charity', 'storage', 'trading']);
            $table->boolean('is_active')->default(true);
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->decimal('rating', 3, 2)->nullable(); // 0.00 - 5.00
            $table->integer('total_reviews')->default(0);
            $table->json('certifications')->nullable(); // Certificaciones energéticas
            $table->json('operating_regions')->nullable(); // Zonas de operación
            $table->timestamps();
            
            // Índices
            $table->index('type');
            $table->index('is_active');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};