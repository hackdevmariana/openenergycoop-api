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
        Schema::create('page_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->string('componentable_type'); // Polimórfico: Hero, TextContent, Banner, etc.
            $table->unsignedBigInteger('componentable_id');
            $table->integer('position')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('page_components')->onDelete('cascade');
            $table->string('language', 5)->default('es');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_draft')->default(true);
            $table->string('version')->default('1.0');
            $table->timestamp('published_at')->nullable();
            $table->string('preview_token')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('cache_enabled')->default(true);
            $table->json('visibility_rules')->nullable();
            $table->string('ab_test_group')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['page_id', 'position']);
            $table->index(['componentable_type', 'componentable_id']);
            $table->index(['organization_id', 'language', 'is_draft']);
            $table->index(['published_at']);
            $table->index(['preview_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_components');
    }
};
