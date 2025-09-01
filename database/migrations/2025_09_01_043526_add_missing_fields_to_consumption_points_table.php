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
        Schema::table('consumption_points', function (Blueprint $table) {
            // Agregar campos faltantes
            $table->boolean('remote_reading_enabled')->default(false)->after('notes');
            $table->enum('consumption_type', ['basic', 'intermediate', 'high', 'very_high', 'industrial'])->nullable()->after('remote_reading_enabled');
            $table->enum('supply_type', ['single_phase', 'three_phase', 'dc', 'hybrid'])->nullable()->after('consumption_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumption_points', function (Blueprint $table) {
            $table->dropColumn(['remote_reading_enabled', 'consumption_type', 'supply_type']);
        });
    }
};
