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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable'); // Polimórfico: Article, Page, etc.
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('author_name')->nullable(); // Si no está registrado
            $table->string('author_email')->nullable(); // Si no está registrado
            $table->longText('content');
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('likes_count')->default(0);
            $table->integer('dislikes_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices para performance (morphs ya crea commentable_type, commentable_id)
            $table->index(['status', 'created_at']);
            $table->index(['parent_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['approved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
