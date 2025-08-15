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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('mobile_image')->nullable();
            $table->string('internal_link')->nullable();
            $table->string('url')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('active')->default(true);
            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('exhibition_beginning')->nullable();
            $table->timestamp('exhibition_end')->nullable();
            $table->enum('banner_type', ['header', 'sidebar', 'footer', 'popup', 'inline'])->default('header');
            $table->json('display_rules')->nullable(); // Reglas de dónde mostrar
            $table->integer('click_count')->default(0);
            $table->integer('impression_count')->default(0);
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_draft')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'active', 'banner_type']);
            $table->index(['position']);
            $table->index(['exhibition_beginning', 'exhibition_end']);
            $table->index(['published_at', 'is_draft']);
            $table->index(['click_count']);
            $table->index(['impression_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
