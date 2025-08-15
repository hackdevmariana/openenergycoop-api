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
        Schema::create('text_contents', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->longText('text');
            $table->string('version')->default('1.0');
            $table->string('language', 5)->default('es');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_draft')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('text_contents')->onDelete('cascade');
            $table->text('excerpt')->nullable();
            $table->integer('reading_time')->nullable();
            $table->string('seo_focus_keyword')->nullable();
            $table->integer('number_of_views')->default(0);
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
            $table->index(['slug', 'organization_id', 'language']);
            $table->index(['published_at', 'is_draft']);
            $table->index(['author_id', 'published_at']);
            $table->index(['number_of_views']);
            $table->index(['parent_id']);
            
            // Constraint único para slug por organización y idioma
            $table->unique(['slug', 'organization_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('text_contents');
    }
};
