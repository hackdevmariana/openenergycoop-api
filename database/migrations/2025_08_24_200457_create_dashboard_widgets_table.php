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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('dashboard_view_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // Tipo de widget
            $table->string('title')->nullable(); // Título del widget
            $table->integer('position')->default(0); // Posición en el grid
            $table->json('settings_json')->nullable(); // Configuración específica del widget
            $table->boolean('visible')->default(true); // Si el widget es visible
            $table->boolean('collapsible')->default(false); // Si se puede colapsar
            $table->boolean('collapsed')->default(false); // Estado colapsado
            $table->string('size')->default('medium'); // Tamaño del widget (small, medium, large)
            $table->json('grid_position')->nullable(); // Posición en el grid (x, y, width, height)
            $table->json('refresh_interval')->nullable(); // Intervalo de actualización
            $table->timestamp('last_refresh')->nullable(); // Última actualización
            $table->json('data_source')->nullable(); // Fuente de datos del widget
            $table->json('filters')->nullable(); // Filtros aplicados
            $table->json('permissions')->nullable(); // Permisos específicos del widget
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['user_id', 'type']);
            $table->index(['dashboard_view_id', 'position']);
            $table->index(['user_id', 'visible']);
            $table->index('type');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
