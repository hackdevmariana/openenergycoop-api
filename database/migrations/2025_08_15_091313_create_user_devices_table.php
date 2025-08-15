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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_name')->nullable(); // Nombre personalizable del dispositivo
            $table->enum('device_type', ['web', 'mobile', 'tablet', 'desktop'])->default('web');
            $table->string('platform')->nullable(); // iOS, Android, Windows, macOS, Linux, etc.
            $table->timestamp('last_seen_at')->nullable();
            $table->string('push_token')->nullable(); // Token para push notifications
            $table->text('user_agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->boolean('is_current')->default(false); // Dispositivo actual en uso
            $table->timestamp('revoked_at')->nullable(); // Fecha de revocación del dispositivo
            $table->timestamps();
            
            // Índices para consultas frecuentes
            $table->index(['user_id', 'is_current']);
            $table->index(['user_id', 'last_seen_at']);
            $table->index('push_token');
            $table->index('revoked_at');
            
            // Índice único para push_token (si existe)
            $table->unique('push_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
