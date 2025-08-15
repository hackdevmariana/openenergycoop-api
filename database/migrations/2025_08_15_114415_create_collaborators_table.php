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
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo');
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->enum('collaborator_type', ['partner', 'sponsor', 'member', 'supporter'])->default('partner');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_draft')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'collaborator_type', 'is_active']);
            $table->index(['order']);
            $table->index(['published_at', 'is_draft']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaborators');
    }
};
