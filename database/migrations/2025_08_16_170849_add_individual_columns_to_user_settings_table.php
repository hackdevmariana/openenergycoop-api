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
            // Remove the key-value columns (keep them commented for reference)
            // $table->dropColumn(['key', 'value']);
            
            // Add individual columns that the controller expects
            
            // General settings
            $table->string('language', 10)->default('es')->after('user_id');
            $table->string('timezone', 50)->default('Europe/Madrid');
            $table->string('theme', 20)->default('light');
            
            // Notification settings
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('marketing_emails')->default(true);
            $table->boolean('newsletter_subscription')->default(true);
            
            // Privacy settings
            $table->string('privacy_level', 20)->default('public');
            $table->string('profile_visibility', 20)->default('public');
            $table->boolean('show_achievements')->default(true);
            $table->boolean('show_statistics')->default(true);
            $table->boolean('show_activity')->default(true);
            
            // Format and display settings
            $table->string('date_format', 20)->default('d/m/Y');
            $table->string('time_format', 10)->default('24');
            $table->string('currency', 10)->default('EUR');
            $table->string('measurement_unit', 20)->default('metric');
            $table->string('energy_unit', 10)->default('kWh');
            
            // Custom settings (JSON)
            $table->json('custom_settings')->nullable();
            
            // Make user_id unique since each user should have only one settings record
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            
            $table->dropColumn([
                'language',
                'timezone', 
                'theme',
                'notifications_enabled',
                'email_notifications',
                'push_notifications',
                'sms_notifications',
                'marketing_emails',
                'newsletter_subscription',
                'privacy_level',
                'profile_visibility',
                'show_achievements',
                'show_statistics',
                'show_activity',
                'date_format',
                'time_format',
                'currency',
                'measurement_unit',
                'energy_unit',
                'custom_settings'
            ]);
        });
    }
};