<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Remover campos duplicados que van en AppSettings
            $table->dropColumn(['primary_color', 'secondary_color']);
            // Remover logo ya que usamos Media Library
            $table->dropColumn('logo');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('logo')->nullable();
        });
    }
};