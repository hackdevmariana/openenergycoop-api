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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('route')->nullable();
            $table->string('language', 5)->default('es');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_draft')->default(true);
            $table->string('template')->default('default');
            $table->json('meta_data')->nullable();
            $table->integer('cache_duration')->default(60);
            $table->boolean('requires_auth')->default(false);
            $table->json('allowed_roles')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->json('search_keywords')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->text('accessibility_notes')->nullable();
            $table->string('reading_level')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'language', 'is_draft']);
            $table->index(['organization_id', 'published_at']);
            $table->index(['slug', 'organization_id', 'language']);
            $table->index(['parent_id', 'sort_order']);
            $table->index(['template', 'organization_id']);
            
            // Constraint único para slug por organización y idioma
            $table->unique(['slug', 'organization_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
