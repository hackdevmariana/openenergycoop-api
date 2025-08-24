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
        Schema::create('dashboard_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable(); // Para vistas guardadas
            $table->json('layout_json'); // Configuración del layout
            $table->boolean('is_default')->default(false);
            $table->string('theme')->default('default'); // Tema de la vista
            $table->string('color_scheme')->default('light'); // Esquema de colores
            $table->json('widget_settings')->nullable(); // Configuración global de widgets
            $table->boolean('is_public')->default(false); // Si la vista es pública
            $table->string('description')->nullable(); // Descripción de la vista
            $table->json('access_permissions')->nullable(); // Permisos de acceso
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['user_id', 'is_default']);
            $table->index(['user_id', 'is_public']);
            $table->index('is_default');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_views');
    }
};
