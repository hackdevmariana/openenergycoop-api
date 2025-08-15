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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->string('tag_type')->default('content'); // content, user, document, etc.
            $table->integer('usage_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('language', 5)->default('es');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'tag_type', 'language']);
            $table->index(['slug', 'organization_id']);
            $table->index(['usage_count']);
            $table->index(['is_featured']);
            
            // Constraint único para slug por organización y tipo
            $table->unique(['slug', 'organization_id', 'tag_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
