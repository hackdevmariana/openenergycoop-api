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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // Ruta del archivo si no usa Media Library
            $table->string('file_type')->nullable(); // pdf, docx, xlsx, etc.
            $table->bigInteger('file_size')->nullable(); // Tamaño en bytes
            $table->string('mime_type')->nullable();
            $table->string('checksum')->nullable(); // Para verificar integridad
            $table->boolean('visible')->default(true);
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('uploaded_at')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('number_of_views')->default(0);
            $table->string('version')->default('1.0');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('requires_auth')->default(false);
            $table->json('allowed_roles')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('language', 5)->default('es');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_draft')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->json('search_keywords')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'language', 'visible']);
            $table->index(['category_id', 'published_at']);
            $table->index(['download_count']);
            $table->index(['number_of_views']);
            $table->index(['expires_at']);
            $table->index(['file_type', 'organization_id']);
            $table->index(['uploaded_by', 'uploaded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
