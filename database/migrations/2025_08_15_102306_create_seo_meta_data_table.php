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
        Schema::create('seo_meta_data', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable'); // Polimórfico: Page, Article, Event, etc.
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index,follow'); // index, noindex, follow, etc.
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image_path')->nullable();
            $table->string('og_type')->default('website');
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image_path')->nullable();
            $table->string('twitter_card')->default('summary_large_image');
            $table->json('structured_data')->nullable(); // JSON-LD structured data
            $table->string('focus_keyword')->nullable();
            $table->json('additional_meta')->nullable(); // Meta tags adicionales
            $table->string('language', 5)->default('es');
            $table->timestamps();
            
            // Índices para performance (morphs ya crea el índice seoable_type, seoable_id)
            $table->index(['language']);
            $table->index(['focus_keyword']);
            
            // Constraint único: una entrada SEO por objeto y idioma
            $table->unique(['seoable_type', 'seoable_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_meta_data');
    }
};
