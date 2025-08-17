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
        Schema::table('tags', function (Blueprint $table) {
            // Campos adicionales para la tienda
            $table->string('icon')->nullable()->after('color');
            $table->enum('type', [
                'general',
                'energy_source',
                'technology', 
                'sustainability',
                'region',
                'certification',
                'feature',
                'target_audience',
                'price_range',
                'difficulty'
            ])->default('general')->after('tag_type');
            $table->boolean('is_active')->default(true)->after('is_featured');
            $table->integer('sort_order')->default(0)->after('is_active');
            $table->json('metadata')->nullable()->after('sort_order');
            
            // Índices adicionales
            $table->index('type');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['sort_order']);
            
            $table->dropColumn([
                'icon',
                'type',
                'is_active',
                'sort_order',
                'metadata'
            ]);
        });
    }
};