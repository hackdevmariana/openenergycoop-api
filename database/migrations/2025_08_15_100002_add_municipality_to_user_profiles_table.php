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
        Schema::table('user_profiles', function (Blueprint $table) {
            // Solo agregar índices ya que las columnas ya existen
            $table->index(['municipality_id', 'points_total']);
            $table->index(['organization_id', 'points_total']);
            $table->index(['co2_avoided_total']);
            $table->index(['kwh_produced_total']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropIndex(['municipality_id', 'points_total']);
            $table->dropIndex(['organization_id', 'points_total']);
            $table->dropIndex(['co2_avoided_total']);
            $table->dropIndex(['kwh_produced_total']);
        });
    }
};
