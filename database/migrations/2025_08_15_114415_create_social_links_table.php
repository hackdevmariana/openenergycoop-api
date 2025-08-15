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
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); // facebook, twitter, instagram, linkedin, etc.
            $table->string('url');
            $table->string('icon')->nullable();
            $table->string('css_class')->nullable();
            $table->string('color')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('followers_count')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_draft')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'is_active']);
            $table->index(['platform', 'organization_id']);
            $table->index(['order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
