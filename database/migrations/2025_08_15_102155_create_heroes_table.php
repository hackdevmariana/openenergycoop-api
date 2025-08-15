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
        Schema::create('heroes', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('mobile_image')->nullable();
            $table->text('text')->nullable();
            $table->text('subtext')->nullable();
            $table->string('text_button')->nullable();
            $table->string('internal_link')->nullable();
            $table->string('cta_link_external')->nullable();
            $table->integer('position')->default(0);
            $table->timestamp('exhibition_beginning')->nullable();
            $table->timestamp('exhibition_end')->nullable();
            $table->boolean('active')->default(true);
            $table->string('video_url')->nullable();
            $table->string('video_background')->nullable();
            $table->enum('text_align', ['left', 'center', 'right'])->default('center');
            $table->integer('overlay_opacity')->default(50);
            $table->string('animation_type')->nullable();
            $table->string('cta_style')->default('primary');
            $table->integer('priority')->default(0);
            $table->string('language', 5)->default('es');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_draft')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'language', 'active']);
            $table->index(['position', 'priority']);
            $table->index(['exhibition_beginning', 'exhibition_end']);
            $table->index(['published_at', 'is_draft']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heroes');
    }
};
