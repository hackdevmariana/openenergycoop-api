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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->longText('text');
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('slug');
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('comment_enabled')->default(true);
            $table->boolean('featured')->default(false);
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->integer('reading_time')->nullable();
            $table->string('seo_focus_keyword')->nullable();
            $table->json('related_articles')->nullable();
            $table->integer('social_shares_count')->default(0);
            $table->integer('number_of_views')->default(0);
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('language', 5)->default('es');
            $table->boolean('is_draft')->default(true);
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
            $table->index(['organization_id', 'language', 'status']);
            $table->index(['slug', 'organization_id', 'language']);
            $table->index(['published_at', 'featured']);
            $table->index(['author_id', 'published_at']);
            $table->index(['category_id', 'published_at']);
            $table->index(['number_of_views']);
            $table->index(['scheduled_at']);
            $table->index(['status', 'published_at']);
            
            // Constraint único para slug por organización y idioma
            $table->unique(['slug', 'organization_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
