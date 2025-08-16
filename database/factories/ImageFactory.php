<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Image;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $width = $this->faker->randomElement([800, 1024, 1200, 1920, 2048]);
        $height = $this->faker->randomElement([600, 768, 900, 1080, 1536]);
        $fileSize = $this->faker->numberBetween(50000, 5000000); // 50KB to 5MB

        return [
            'title' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(3),
            'description' => $this->faker->optional(0.8)->paragraph(),
            'alt_text' => $this->faker->sentence(),
            'filename' => $this->faker->regexify('[a-z0-9]{8}') . '.jpg',
            'path' => 'images/' . $this->faker->regexify('[a-z0-9]{8}') . '.jpg',
            'url' => $this->faker->imageUrl($width, $height, 'nature'),
            'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
            'file_size' => $fileSize,
            'width' => $width,
            'height' => $height,
            'metadata' => [
                'camera' => $this->faker->optional(0.3)->randomElement(['Canon EOS R5', 'Nikon D850', 'Sony A7R IV']),
                'iso' => $this->faker->optional(0.3)->randomElement([100, 200, 400, 800, 1600]),
                'aperture' => $this->faker->optional(0.3)->randomElement(['f/1.4', 'f/2.8', 'f/4.0', 'f/5.6']),
                'shutter_speed' => $this->faker->optional(0.3)->randomElement(['1/60', '1/125', '1/250', '1/500']),
            ],
            'category_id' => Category::factory(),
            'tags' => $this->faker->randomElements([
                'naturaleza', 'paisaje', 'ciudad', 'retrato', 'arquitectura', 
                'tecnología', 'arte', 'comida', 'viajes', 'deporte'
            ], $this->faker->numberBetween(0, 4)),
            'organization_id' => Organization::factory(),
            'language' => $this->faker->randomElement(['es', 'en', 'ca', 'eu', 'gl']),
            'is_public' => $this->faker->boolean(85), // 85% chance of being public
            'is_featured' => $this->faker->boolean(15), // 15% chance of being featured
            'status' => $this->faker->randomElement(['active', 'archived', 'deleted']),
            'seo_title' => $this->faker->optional(0.6)->sentence(),
            'seo_description' => $this->faker->optional(0.6)->paragraph(),
            'responsive_urls' => $this->faker->optional(0.4)->randomElement([
                [
                    '150x150' => $this->faker->imageUrl(150, 150),
                    '300x300' => $this->faker->imageUrl(300, 300),
                    '600x600' => $this->faker->imageUrl(600, 600),
                ],
                null
            ]),
            'download_count' => $this->faker->numberBetween(0, 1000),
            'view_count' => $this->faker->numberBetween(0, 5000),
            'last_used_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'uploaded_by_user_id' => User::factory(),
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Create an active image.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Create an archived image.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Create a deleted image.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'deleted',
        ]);
    }

    /**
     * Create a public image.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Create a private image.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Create a featured image.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_public' => true,
            'status' => 'active',
        ]);
    }

    /**
     * Create an image in Spanish.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
        ]);
    }

    /**
     * Create an image in English.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
        ]);
    }

    /**
     * Create a JPEG image.
     */
    public function jpeg(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/jpeg',
            'filename' => str_replace('.jpg', '.jpg', $attributes['filename'] ?? 'image.jpg'),
        ]);
    }

    /**
     * Create a PNG image.
     */
    public function png(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/png',
            'filename' => str_replace('.jpg', '.png', $attributes['filename'] ?? 'image.png'),
        ]);
    }

    /**
     * Create a WebP image.
     */
    public function webp(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/webp',
            'filename' => str_replace('.jpg', '.webp', $attributes['filename'] ?? 'image.webp'),
        ]);
    }

    /**
     * Create a landscape image.
     */
    public function landscape(): static
    {
        return $this->state(fn (array $attributes) => [
            'width' => 1920,
            'height' => 1080,
        ]);
    }

    /**
     * Create a portrait image.
     */
    public function portrait(): static
    {
        return $this->state(fn (array $attributes) => [
            'width' => 1080,
            'height' => 1920,
        ]);
    }

    /**
     * Create a square image.
     */
    public function square(): static
    {
        return $this->state(fn (array $attributes) => [
            'width' => 1024,
            'height' => 1024,
        ]);
    }

    /**
     * Create a large image.
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'width' => 4096,
            'height' => 2160,
            'file_size' => $this->faker->numberBetween(2000000, 10000000), // 2MB to 10MB
        ]);
    }

    /**
     * Create a small image.
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'width' => 400,
            'height' => 300,
            'file_size' => $this->faker->numberBetween(10000, 100000), // 10KB to 100KB
        ]);
    }

    /**
     * Create an image with specific tags.
     */
    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $tags,
        ]);
    }

    /**
     * Create an image with metadata.
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], $metadata),
        ]);
    }

    /**
     * Create an image with responsive URLs.
     */
    public function withResponsiveUrls(): static
    {
        return $this->state(fn (array $attributes) => [
            'responsive_urls' => [
                '150x150' => $this->faker->imageUrl(150, 150),
                '300x300' => $this->faker->imageUrl(300, 300),
                '600x600' => $this->faker->imageUrl(600, 600),
                '1200x1200' => $this->faker->imageUrl(1200, 1200),
            ],
        ]);
    }

    /**
     * Create an image for specific category.
     */
    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Create an image for specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create an image uploaded by specific user.
     */
    public function uploadedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_by_user_id' => $user->id,
        ]);
    }

    /**
     * Create a recently uploaded image.
     */
    public function recentlyUploaded(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'published_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create a popular image.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => $this->faker->numberBetween(1000, 10000),
            'download_count' => $this->faker->numberBetween(100, 1000),
            'last_used_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Create an unused image.
     */
    public function unused(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => 0,
            'download_count' => 0,
            'last_used_at' => null,
        ]);
    }

    /**
     * Create an image with SEO data.
     */
    public function withSeo(): static
    {
        return $this->state(fn (array $attributes) => [
            'seo_title' => $this->faker->sentence(),
            'seo_description' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a published image.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'status' => 'active',
            'is_public' => true,
        ]);
    }

    /**
     * Create an unpublished image.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }

    /**
     * Create an image with complex metadata.
     */
    public function withComplexMetadata(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'camera' => 'Canon EOS R5',
                'lens' => 'RF 24-70mm f/2.8L IS USM',
                'iso' => 800,
                'aperture' => 'f/4.0',
                'shutter_speed' => '1/125',
                'focal_length' => '50mm',
                'exposure_mode' => 'Manual',
                'white_balance' => 'Daylight',
                'flash' => false,
                'gps' => [
                    'latitude' => $this->faker->latitude(),
                    'longitude' => $this->faker->longitude(),
                ],
                'keywords' => ['photography', 'nature', 'landscape'],
                'copyright' => '© 2024 Photographer Name',
            ],
        ]);
    }

    /**
     * Create multiple images with different orientations.
     */
    public function orientationMix(): static
    {
        return $this->state(fn (array $attributes) => [
            'width' => $this->faker->randomElement([1920, 1080, 1024]),
            'height' => $this->faker->randomElement([1080, 1920, 1024]),
        ]);
    }

    /**
     * Create an image with special characters in title.
     */
    public function withSpecialCharacters(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Título con Ñ, Ç & Símbolos Éspeciáles',
            'description' => '¡Descripción con acentos, ñ, ç y otros símbolos especiales!',
            'alt_text' => 'Imagen con características únicas',
            'tags' => ['español', 'ñoño', 'niño', 'año'],
        ]);
    }
}