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
        $settingKeys = array_keys(UserSettings::DEFAULT_SETTINGS);
        $key = $this->faker->randomElement($settingKeys);
        
        return [
            'user_id' => User::factory(),
            'key' => $key,
            'value' => $this->generateValueForKey($key),
        ];
    }

    /**
     * Generar un valor apropiado para una clave específica
     */
    private function generateValueForKey(string $key)
    {
        return match ($key) {
            'dashboard_view' => $this->faker->randomElement(['grid', 'list', 'compact']),
            'theme' => $this->faker->randomElement(['light', 'dark', 'auto']),
            'language' => $this->faker->randomElement(['es', 'en', 'ca']),
            'timezone' => $this->faker->randomElement(['Europe/Madrid', 'Europe/London', 'America/New_York']),
            'date_format' => $this->faker->randomElement(['d/m/Y', 'm/d/Y', 'Y-m-d']),
            'time_format' => $this->faker->randomElement(['H:i', 'h:i A']),
            'currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP']),
            'energy_units' => $this->faker->randomElement(['kwh', 'mwh', 'wh']),
            'notifications' => [
                'email' => [
                    'system' => $this->faker->boolean(80),
                    'marketing' => $this->faker->boolean(30),
                    'achievements' => $this->faker->boolean(70),
                    'team_updates' => $this->faker->boolean(60),
                    'challenges' => $this->faker->boolean(75),
                ],
                'push' => [
                    'system' => $this->faker->boolean(70),
                    'achievements' => $this->faker->boolean(80),
                    'team_updates' => $this->faker->boolean(40),
                    'challenges' => $this->faker->boolean(65),
                ],
                'sms' => [
                    'system' => $this->faker->boolean(20),
                    'security' => $this->faker->boolean(60),
                ],
            ],
            'privacy' => [
                'show_in_rankings' => $this->faker->boolean(70),
                'show_profile_public' => $this->faker->boolean(30),
                'share_achievements' => $this->faker->boolean(60),
            ],
            'dashboard_widgets' => [
                'energy_overview' => $this->faker->boolean(90),
                'recent_achievements' => $this->faker->boolean(75),
                'team_progress' => $this->faker->boolean(80),
                'challenges' => $this->faker->boolean(85),
                'leaderboard' => $this->faker->boolean(45),
            ],
            'chart_preferences' => [
                'default_period' => $this->faker->randomElement(['week', 'month', 'quarter', 'year']),
                'show_comparisons' => $this->faker->boolean(70),
                'show_goals' => $this->faker->boolean(80),
            ],
            default => UserSettings::DEFAULT_SETTINGS[$key] ?? null,
        };
    }

    /**
     * Estado para una configuración específica
     */
    public function forKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
            'value' => $this->generateValueForKey($key),
        ]);
    }

    /**
     * Estado para configuración de notificaciones
     */
    public function notifications(): static
    {
        return $this->forKey('notifications');
    }

    /**
     * Estado para configuración de privacidad
     */
    public function privacy(): static
    {
        return $this->forKey('privacy');
    }

    /**
     * Estado para configuración de widgets del dashboard
     */
    public function dashboardWidgets(): static
    {
        return $this->forKey('dashboard_widgets');
    }

    /**
     * Estado para tema
     */
    public function theme(): static
    {
        return $this->forKey('theme');
    }

    /**
     * Estado para idioma
     */
    public function language(): static
    {
        return $this->forKey('language');
    }

    /**
     * Estado para configuración personalizada
     */
    public function custom(string $key, $value): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
            'value' => $value,
        ]);
    }
}
