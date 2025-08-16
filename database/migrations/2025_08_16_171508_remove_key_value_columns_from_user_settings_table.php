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
        Schema::table('user_settings', function (Blueprint $table) {
            // Remove the old key-value structure columns
            $table->dropIndex(['user_id', 'key']); // Drop the composite index first
            $table->dropIndex(['key']); // Drop the key index
            $table->dropUnique(['user_id', 'key']); // Drop the unique constraint
            
            $table->dropColumn(['key', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            // Add back the old columns
            $table->string('key')->after('user_id');
            $table->json('value')->after('key');
            
            // Add back the indexes and constraints
            $table->index(['user_id', 'key']);
            $table->index('key');
            $table->unique(['user_id', 'key']);
        });
    }
};