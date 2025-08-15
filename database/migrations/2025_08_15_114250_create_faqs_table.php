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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->nullable()->constrained('faq_topics')->onDelete('set null');
            $table->string('question');
            $table->longText('answer');
            $table->integer('position')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->json('tags')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('language', 5)->default('es');
            $table->boolean('is_draft')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'language', 'is_draft']);
            $table->index(['topic_id', 'position']);
            $table->index(['is_featured', 'published_at']);
            $table->index(['views_count']);
            $table->index(['helpful_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
