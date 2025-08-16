<?php

namespace Database\Factories;

use App\Models\AppSetting;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppSetting>
 */
class AppSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->company(),
            'slogan' => $this->faker->catchPhrase(),
            'primary_color' => $this->faker->hexColor(),
            'secondary_color' => $this->faker->hexColor(),
            'locale' => $this->faker->randomElement(['es', 'en', 'fr', 'de']),
            'custom_js' => $this->faker->optional(0.3)->text(500),
            'favicon_path' => $this->faker->optional(0.5)->filePath(),
        ];
    }

    /**
     * Create setting with Spanish locale.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'es',
            'name' => 'Cooperativa de Energía',
            'slogan' => 'Energía renovable para todos',
        ]);
    }

    /**
     * Create setting with English locale.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => 'Energy Cooperative',
            'slogan' => 'Renewable energy for everyone',
        ]);
    }

    /**
     * Create setting with green theme colors.
     */
    public function greenTheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'primary_color' => '#10B981',
            'secondary_color' => '#059669',
        ]);
    }

    /**
     * Create setting with blue theme colors.
     */
    public function blueTheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'primary_color' => '#3B82F6',
            'secondary_color' => '#1D4ED8',
        ]);
    }

    /**
     * Create setting with red theme colors.
     */
    public function redTheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'primary_color' => '#EF4444',
            'secondary_color' => '#DC2626',
        ]);
    }

    /**
     * Create setting with custom JavaScript.
     */
    public function withCustomJs(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_js' => 'console.log("Custom app loaded");',
        ]);
    }

    /**
     * Create setting without custom JavaScript.
     */
    public function withoutCustomJs(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_js' => null,
        ]);
    }

    /**
     * Create setting with favicon path.
     */
    public function withFavicon(): static
    {
        return $this->state(fn (array $attributes) => [
            'favicon_path' => '/images/favicon.ico',
        ]);
    }

    /**
     * Create setting without favicon path.
     */
    public function withoutFavicon(): static
    {
        return $this->state(fn (array $attributes) => [
            'favicon_path' => null,
        ]);
    }

    /**
     * Create setting for specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create setting with default OpenEnergyCoop values.
     */
    public function defaultOpenEnergy(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'OpenEnergyCoop',
            'slogan' => 'Energía renovable para todos',
            'primary_color' => '#10B981',
            'secondary_color' => '#059669',
            'locale' => 'es',
            'custom_js' => null,
        ]);
    }

    /**
     * Create setting with complete configuration.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Complete Energy Coop',
            'slogan' => 'Sustainable energy solutions for everyone',
            'primary_color' => '#10B981',
            'secondary_color' => '#059669',
            'locale' => 'es',
            'custom_js' => 'console.log("App initialized"); gtag("config", "GA_MEASUREMENT_ID");',
            'favicon_path' => '/assets/favicon.ico',
        ]);
    }

    /**
     * Create setting with minimal configuration.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Simple Coop',
            'slogan' => null,
            'primary_color' => null,
            'secondary_color' => null,
            'locale' => 'es',
            'custom_js' => null,
            'favicon_path' => null,
        ]);
    }

    /**
     * Create setting with long content for testing.
     */
    public function withLongContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Very Long Cooperative Name That Tests Maximum Length Constraints',
            'slogan' => $this->faker->paragraph(3),
            'custom_js' => $this->faker->text(2000),
        ]);
    }

    /**
     * Create setting with special characters.
     */
    public function withSpecialCharacters(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Energía & Sostenibilidad S.L.',
            'slogan' => 'Más energía, menos CO₂ - ¡Únete!',
            'custom_js' => '// Special chars: àáâãäåæçèéêëìíîïñòóôõöøùúûüý',
        ]);
    }

    /**
     * Create setting with invalid color format (for testing validation).
     */
    public function withInvalidColors(): static
    {
        return $this->state(fn (array $attributes) => [
            'primary_color' => 'invalid-color',
            'secondary_color' => '#GGGGGG',
        ]);
    }

    /**
     * Create setting with empty values.
     */
    public function withEmptyValues(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '',
            'slogan' => '',
            'primary_color' => '',
            'secondary_color' => '',
            'custom_js' => '',
            'favicon_path' => '',
        ]);
    }
}
