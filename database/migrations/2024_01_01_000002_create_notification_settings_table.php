<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('channel', ['email', 'push', 'sms', 'in_app']);
            $table->enum('notification_type', ['wallet', 'event', 'message', 'general']);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['user_id', 'channel']);
            $table->index(['user_id', 'notification_type']);
            $table->index(['user_id', 'enabled']);
            
            // Restricción única para evitar duplicados
            $table->unique(['user_id', 'channel', 'notification_type'], 'user_channel_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
