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
        Schema::create('event_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['registered', 'attended', 'cancelled', 'no_show'])->default('registered');
            $table->dateTime('registered_at');
            $table->dateTime('checked_in_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('checkin_token', 64)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            // Índices para mejorar el rendimiento
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'registered_at']);
            $table->index(['checkin_token']);
            $table->unique(['event_id', 'user_id']); // Un usuario solo puede registrarse una vez por evento
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_attendances');
    }
};
