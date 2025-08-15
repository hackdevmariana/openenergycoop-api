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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('alt_text')->nullable(); // Para accesibilidad
            $table->string('filename'); // Nombre del archivo original
            $table->string('path'); // Ruta del archivo
            $table->string('url')->nullable(); // URL pública
            $table->string('mime_type')->nullable(); // image/jpeg, image/png, etc.
            $table->unsignedBigInteger('file_size')->nullable(); // Tamaño en bytes
            $table->unsignedInteger('width')->nullable(); // Ancho en píxeles
            $table->unsignedInteger('height')->nullable(); // Alto en píxeles
            $table->json('metadata')->nullable(); // EXIF, etc.
            
            // Categorización
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->json('tags')->nullable(); // Tags para búsqueda
            
            // Organización
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('language', 5)->default('es');
            
            // Estado
            $table->boolean('is_public')->default(true); // Si es visible públicamente
            $table->boolean('is_featured')->default(false); // Imagen destacada
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            
            // SEO y optimización
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->json('responsive_urls')->nullable(); // URLs de diferentes tamaños
            
            // Uso y estadísticas
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            
            // Auditoría
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['organization_id', 'status', 'is_public']);
            $table->index(['category_id', 'is_featured']);
            $table->index(['mime_type', 'status']);
            $table->index(['uploaded_by_user_id']);
            $table->index(['published_at']);
            $table->index(['language', 'is_public']);
            $table->fullText(['title', 'description', 'alt_text']); // Búsqueda de texto completo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};