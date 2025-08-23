<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->enum('type', ['info', 'alert', 'success', 'warning', 'error'])->default('info');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
            $table->index('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
