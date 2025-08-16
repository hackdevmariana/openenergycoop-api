<?php

namespace Database\Factories;

use App\Models\UserSettings;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserSettings>
 */
class UserSettingsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'language' => $this->faker->randomElement(UserSettings::SUPPORTED_LANGUAGES),
            'timezone' => $this->faker->randomElement([
                'Europe/Madrid', 'Europe/London', 'America/New_York', 
                'America/Los_Angeles', 'Asia/Tokyo', 'Australia/Sydney'
            ]),
            'theme' => $this->faker->randomElement(UserSettings::SUPPORTED_THEMES),
            'notifications_enabled' => $this->faker->boolean(80),
            'email_notifications' => $this->faker->boolean(70),
            'push_notifications' => $this->faker->boolean(60),
            'sms_notifications' => $this->faker->boolean(20),
            'marketing_emails' => $this->faker->boolean(40),
            'newsletter_subscription' => $this->faker->boolean(50),
            'privacy_level' => $this->faker->randomElement(UserSettings::PRIVACY_LEVELS),
            'profile_visibility' => $this->faker->randomElement(UserSettings::PROFILE_VISIBILITY_LEVELS),
            'show_achievements' => $this->faker->boolean(75),
            'show_statistics' => $this->faker->boolean(70),
            'show_activity' => $this->faker->boolean(65),
            'date_format' => $this->faker->randomElement(['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y']),
            'time_format' => $this->faker->randomElement(UserSettings::TIME_FORMATS),
            'currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP', 'JPY', 'CAD']),
            'measurement_unit' => $this->faker->randomElement(UserSettings::MEASUREMENT_UNITS),
            'energy_unit' => $this->faker->randomElement(UserSettings::ENERGY_UNITS),
            'custom_settings' => $this->faker->optional(0.3)->randomElement([
                ['dashboard_layout' => 'grid', 'chart_type' => 'line'],
                ['dark_mode_auto' => true, 'compact_view' => false],
                ['show_tips' => true, 'animation_speed' => 'normal'],
            ]),
        ];
    }

    /**
     * State for default settings (all default values)
     */
    public function defaults(): static
    {
        return $this->state(fn (array $attributes) => UserSettings::DEFAULT_VALUES);
    }

    /**
     * State for Spanish language settings
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
            'timezone' => 'Europe/Madrid',
            'currency' => 'EUR',
            'date_format' => 'd/m/Y',
        ]);
    }

    /**
     * State for English language settings
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
            'timezone' => 'Europe/London',
            'currency' => 'GBP',
            'date_format' => 'd/m/Y',
        ]);
    }

    /**
     * State for notifications enabled
     */
    public function notificationsEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'notifications_enabled' => true,
            'email_notifications' => true,
            'push_notifications' => true,
            'marketing_emails' => true,
            'newsletter_subscription' => true,
        ]);
    }

    /**
     * State for notifications disabled
     */
    public function notificationsDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'notifications_enabled' => false,
            'email_notifications' => false,
            'push_notifications' => false,
            'sms_notifications' => false,
            'marketing_emails' => false,
            'newsletter_subscription' => false,
        ]);
    }

    /**
     * State for public privacy settings
     */
    public function publicPrivacy(): static
    {
        return $this->state(fn (array $attributes) => [
            'privacy_level' => 'public',
            'profile_visibility' => 'public',
            'show_achievements' => true,
            'show_statistics' => true,
            'show_activity' => true,
        ]);
    }

    /**
     * State for private privacy settings
     */
    public function privatePrivacy(): static
    {
        return $this->state(fn (array $attributes) => [
            'privacy_level' => 'private',
            'profile_visibility' => 'private',
            'show_achievements' => false,
            'show_statistics' => false,
            'show_activity' => false,
        ]);
    }

    /**
     * State for dark theme
     */
    public function darkTheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'theme' => 'dark',
        ]);
    }

    /**
     * State for light theme
     */
    public function lightTheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'theme' => 'light',
        ]);
    }

    /**
     * State for custom settings
     */
    public function withCustomSettings(array $customSettings): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_settings' => $customSettings,
        ]);
    }

    /**
     * State for specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * State for metric units
     */
    public function metricUnits(): static
    {
        return $this->state(fn (array $attributes) => [
            'measurement_unit' => 'metric',
            'energy_unit' => 'kWh',
        ]);
    }

    /**
     * State for imperial units
     */
    public function imperialUnits(): static
    {
        return $this->state(fn (array $attributes) => [
            'measurement_unit' => 'imperial',
            'energy_unit' => 'kWh', // Energy unit typically stays the same
        ]);
    }

    /**
     * State for 12-hour time format
     */
    public function twelveHourFormat(): static
    {
        return $this->state(fn (array $attributes) => [
            'time_format' => '12',
        ]);
    }

    /**
     * State for 24-hour time format
     */
    public function twentyFourHourFormat(): static
    {
        return $this->state(fn (array $attributes) => [
            'time_format' => '24',
        ]);
    }
}