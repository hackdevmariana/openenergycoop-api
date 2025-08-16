<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'custom_settings',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'marketing_emails' => 'boolean',
        'newsletter_subscription' => 'boolean',
        'show_achievements' => 'boolean',
        'show_statistics' => 'boolean',
        'show_activity' => 'boolean',
        'custom_settings' => 'array',
    ];

    /**
     * Default values for settings
     */
    public const DEFAULT_VALUES = [
        'language' => 'es',
        'timezone' => 'Europe/Madrid',
        'theme' => 'light',
        'notifications_enabled' => true,
        'email_notifications' => true,
        'push_notifications' => true,
        'sms_notifications' => false,
        'marketing_emails' => true,
        'newsletter_subscription' => true,
        'privacy_level' => 'public',
        'profile_visibility' => 'public',
        'show_achievements' => true,
        'show_statistics' => true,
        'show_activity' => true,
        'date_format' => 'd/m/Y',
        'time_format' => '24',
        'currency' => 'EUR',
        'measurement_unit' => 'metric',
        'energy_unit' => 'kWh',
        'custom_settings' => null,
    ];

    /**
     * Supported languages
     */
    public const SUPPORTED_LANGUAGES = ['es', 'en', 'ca', 'eu', 'gl'];

    /**
     * Supported themes
     */
    public const SUPPORTED_THEMES = ['light', 'dark', 'auto'];

    /**
     * Supported privacy levels
     */
    public const PRIVACY_LEVELS = ['public', 'friends', 'private'];

    /**
     * Supported profile visibility levels
     */
    public const PROFILE_VISIBILITY_LEVELS = ['public', 'registered', 'private'];

    /**
     * Supported time formats
     */
    public const TIME_FORMATS = ['12', '24'];

    /**
     * Supported measurement units
     */
    public const MEASUREMENT_UNITS = ['metric', 'imperial'];

    /**
     * Supported energy units
     */
    public const ENERGY_UNITS = ['kWh', 'MWh', 'GWh'];

    /**
     * Relationship with user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get or create settings for a user with default values
     */
    public static function getForUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            static::DEFAULT_VALUES
        );
    }

    /**
     * Reset all settings to default values
     */
    public function resetToDefaults(): bool
    {
        return $this->update(static::DEFAULT_VALUES);
    }

    /**
     * Check if notifications are enabled
     */
    public function hasNotificationsEnabled(): bool
    {
        return $this->notifications_enabled;
    }

    /**
     * Check if email notifications are enabled
     */
    public function hasEmailNotificationsEnabled(): bool
    {
        return $this->notifications_enabled && $this->email_notifications;
    }

    /**
     * Check if push notifications are enabled
     */
    public function hasPushNotificationsEnabled(): bool
    {
        return $this->notifications_enabled && $this->push_notifications;
    }

    /**
     * Check if SMS notifications are enabled
     */
    public function hasSmsNotificationsEnabled(): bool
    {
        return $this->notifications_enabled && $this->sms_notifications;
    }

    /**
     * Check if marketing emails are enabled
     */
    public function hasMarketingEmailsEnabled(): bool
    {
        return $this->marketing_emails;
    }

    /**
     * Check if newsletter subscription is enabled
     */
    public function hasNewsletterSubscriptionEnabled(): bool
    {
        return $this->newsletter_subscription;
    }

    /**
     * Check if profile is public
     */
    public function isProfilePublic(): bool
    {
        return $this->profile_visibility === 'public';
    }

    /**
     * Check if profile is visible to registered users
     */
    public function isProfileVisibleToRegistered(): bool
    {
        return in_array($this->profile_visibility, ['public', 'registered']);
    }

    /**
     * Check if profile is private
     */
    public function isProfilePrivate(): bool
    {
        return $this->profile_visibility === 'private';
    }

    /**
     * Check if achievements should be shown
     */
    public function shouldShowAchievements(): bool
    {
        return $this->show_achievements;
    }

    /**
     * Check if statistics should be shown
     */
    public function shouldShowStatistics(): bool
    {
        return $this->show_statistics;
    }

    /**
     * Check if activity should be shown
     */
    public function shouldShowActivity(): bool
    {
        return $this->show_activity;
    }

    /**
     * Get formatted date according to user preference
     */
    public function formatDate(\DateTime $date): string
    {
        return $date->format($this->date_format);
    }

    /**
     * Get formatted time according to user preference
     */
    public function formatTime(\DateTime $time): string
    {
        $format = $this->time_format === '12' ? 'g:i A' : 'H:i';
        return $time->format($format);
    }

    /**
     * Get formatted datetime according to user preferences
     */
    public function formatDateTime(\DateTime $datetime): string
    {
        return $this->formatDate($datetime) . ' ' . $this->formatTime($datetime);
    }

    /**
     * Check if user prefers dark theme
     */
    public function prefersDarkTheme(): bool
    {
        return $this->theme === 'dark';
    }

    /**
     * Check if user prefers light theme
     */
    public function prefersLightTheme(): bool
    {
        return $this->theme === 'light';
    }

    /**
     * Check if user has auto theme
     */
    public function hasAutoTheme(): bool
    {
        return $this->theme === 'auto';
    }

    /**
     * Get all notification settings as array
     */
    public function getNotificationSettings(): array
    {
        return [
            'notifications_enabled' => $this->notifications_enabled,
            'email_notifications' => $this->email_notifications,
            'push_notifications' => $this->push_notifications,
            'sms_notifications' => $this->sms_notifications,
            'marketing_emails' => $this->marketing_emails,
            'newsletter_subscription' => $this->newsletter_subscription,
        ];
    }

    /**
     * Get all privacy settings as array
     */
    public function getPrivacySettings(): array
    {
        return [
            'privacy_level' => $this->privacy_level,
            'profile_visibility' => $this->profile_visibility,
            'show_achievements' => $this->show_achievements,
            'show_statistics' => $this->show_statistics,
            'show_activity' => $this->show_activity,
        ];
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(array $settings): bool
    {
        $allowedFields = [
            'notifications_enabled',
            'email_notifications',
            'push_notifications',
            'sms_notifications',
            'marketing_emails',
            'newsletter_subscription',
        ];

        $validSettings = array_intersect_key($settings, array_flip($allowedFields));
        return $this->update($validSettings);
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacySettings(array $settings): bool
    {
        $allowedFields = [
            'privacy_level',
            'profile_visibility',
            'show_achievements',
            'show_statistics',
            'show_activity',
        ];

        $validSettings = array_intersect_key($settings, array_flip($allowedFields));
        return $this->update($validSettings);
    }

    /**
     * Get a specific custom setting
     */
    public function getCustomSetting(string $key, $default = null)
    {
        $customSettings = $this->custom_settings ?? [];
        return $customSettings[$key] ?? $default;
    }

    /**
     * Set a specific custom setting
     */
    public function setCustomSetting(string $key, $value): bool
    {
        $customSettings = $this->custom_settings ?? [];
        $customSettings[$key] = $value;
        return $this->update(['custom_settings' => $customSettings]);
    }

    /**
     * Remove a specific custom setting
     */
    public function removeCustomSetting(string $key): bool
    {
        $customSettings = $this->custom_settings ?? [];
        unset($customSettings[$key]);
        return $this->update(['custom_settings' => $customSettings]);
    }
}