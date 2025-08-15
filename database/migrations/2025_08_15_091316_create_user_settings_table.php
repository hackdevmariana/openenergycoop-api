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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('key'); // dashboard_view, notification_preferences, theme, etc.
            $table->json('value'); // Valor flexible (JSON, string, boolean, number)
            $table->timestamps();
            
            // Índices para consultas frecuentes
            $table->index(['user_id', 'key']);
            $table->index('key');
            
            // Constraint único: un usuario no puede tener la misma configuración duplicada
            $table->unique(['user_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
