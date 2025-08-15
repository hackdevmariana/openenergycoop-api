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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('category_type')->default('article'); // article, document, media, etc.
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('language', 5)->default('es');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'category_type', 'is_active']);
            $table->index(['slug', 'organization_id', 'language']);
            $table->index(['parent_id', 'sort_order']);
            
            // Constraint único para slug por organización, idioma y tipo
            $table->unique(['slug', 'organization_id', 'language', 'category_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
