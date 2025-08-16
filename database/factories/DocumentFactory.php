<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $fileTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip'];
        $fileType = $this->faker->randomElement($fileTypes);
        
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'file_path' => 'documents/' . $this->faker->uuid() . '.' . $fileType,
            'file_type' => $fileType,
            'file_size' => $this->faker->numberBetween(1024, 10485760), // 1KB - 10MB
            'mime_type' => $this->getMimeType($fileType),
            'checksum' => $this->faker->sha256(),
            'visible' => $this->faker->boolean(85), // 85% probabilidad de ser visible
            'category_id' => Category::factory(),
            'uploaded_by' => User::factory(),
            'uploaded_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'download_count' => $this->faker->numberBetween(0, 1000),
            'number_of_views' => $this->faker->numberBetween(0, 5000),
            'version' => $this->faker->randomElement(['1.0', '1.1', '2.0', '2.1', '3.0']),
            'expires_at' => $this->faker->optional(30)->dateTimeBetween('now', '+2 years'), // 30% tiene expiración
            'requires_auth' => $this->faker->boolean(40), // 40% requiere autenticación
            'allowed_roles' => $this->faker->optional(50)->passthrough(['admin', 'member']),
            'thumbnail_path' => $this->faker->optional()->filePath(),
            'language' => $this->faker->randomElement(['es', 'en', 'ca']),
            'organization_id' => Organization::factory(),
            'is_draft' => $this->faker->boolean(25), // 25% probabilidad de ser borrador
            'published_at' => function (array $attributes) {
                return $attributes['is_draft'] ? null : $this->faker->dateTimeBetween('-6 months', 'now');
            },
            'search_keywords' => $this->faker->optional()->passthrough([
                $this->faker->word(),
                $this->faker->word(),
                $this->faker->word()
            ]),
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => User::factory(),
        ];
    }

    /**
     * Get MIME type for file extension
     */
    private function getMimeType(string $fileType): string
    {
        return match ($fileType) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            default => 'application/octet-stream',
        };
    }

    /**
     * Indicate that the document is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the document is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the document is visible.
     */
    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'visible' => true,
        ]);
    }

    /**
     * Indicate that the document is hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'visible' => false,
        ]);
    }

    /**
     * Indicate that the document is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Indicate that the document requires authentication.
     */
    public function requiresAuth(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_auth' => true,
            'allowed_roles' => ['admin', 'member'],
        ]);
    }

    /**
     * Indicate that the document is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_auth' => false,
            'allowed_roles' => null,
        ]);
    }

    /**
     * Set a specific file type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => $type,
            'mime_type' => $this->getMimeType($type),
            'file_path' => 'documents/' . $this->faker->uuid() . '.' . $type,
        ]);
    }

    /**
     * Set specific download count.
     */
    public function withDownloads(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'download_count' => $count,
        ]);
    }

    /**
     * Popular document (high download count).
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'download_count' => $this->faker->numberBetween(500, 2000),
            'number_of_views' => $this->faker->numberBetween(1000, 10000),
        ]);
    }

    /**
     * Recent document.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create PDF document.
     */
    public function pdf(): static
    {
        return $this->ofType('pdf');
    }

    /**
     * Create Word document.
     */
    public function word(): static
    {
        return $this->ofType('docx');
    }

    /**
     * Create Excel document.
     */
    public function excel(): static
    {
        return $this->ofType('xlsx');
    }
}
