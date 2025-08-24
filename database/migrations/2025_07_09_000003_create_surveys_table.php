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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('anonymous_allowed')->default(false);
            $table->boolean('visible_results')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices para mejorar el rendimiento
            $table->index(['starts_at', 'ends_at']);
            $table->index(['anonymous_allowed']);
            $table->index(['visible_results']);
            $table->index(['starts_at']);
            $table->index(['ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
