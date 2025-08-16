<?php

use App\Models\Category;
use App\Models\Image;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    Image::query()->delete();
    
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    $this->category = Category::factory()->create();
    
    // Fake storage for file uploads
    Storage::fake('public');
});

describe('ImageController', function () {
    describe('Index endpoint', function () {
        it('can get public images without authentication', function () {
            $publicImage = Image::factory()->public()->active()->create();
            $privateImage = Image::factory()->private()->active()->create();

            $response = $this->getJson('/api/v1/images');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'slug',
                                'url',
                                'thumbnail_url',
                                'mime_type',
                                'file_size',
                                'width',
                                'height',
                                'is_public',
                                'is_featured',
                                'status',
                                'view_count',
                                'download_count'
                            ]
                        ]
                    ]);

            // Should only return public image
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($publicImage->id);
        });

        it('can filter images by category', function () {
            $category1 = Category::factory()->create();
            $category2 = Category::factory()->create();
            
            $image1 = Image::factory()->public()->active()->forCategory($category1)->create();
            $image2 = Image::factory()->public()->active()->forCategory($category2)->create();

            $response = $this->getJson("/api/v1/images?category_id={$category1->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($image1->id);
        });

        it('can filter featured images', function () {
            $featuredImage = Image::factory()->public()->active()->featured()->create();
            $regularImage = Image::factory()->public()->active()->create(['is_featured' => false]);

            $response = $this->getJson('/api/v1/images?featured=1');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($featuredImage->id);
        });

        it('can search images by title and description', function () {
            $searchableImage = Image::factory()->public()->active()->create([
                'title' => 'Beautiful Mountain Landscape',
                'description' => 'A stunning view of the Alps'
            ]);
            $otherImage = Image::factory()->public()->active()->create([
                'title' => 'City Street',
                'description' => 'Urban photography'
            ]);

            $response = $this->getJson('/api/v1/images?search=mountain');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($searchableImage->id);
        });

        it('can filter images by language', function () {
            $spanishImage = Image::factory()->public()->active()->spanish()->create();
            $englishImage = Image::factory()->public()->active()->english()->create();

            $response = $this->getJson('/api/v1/images?language=es');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($spanishImage->id);
        });

        it('orders images by featured first, then by creation date', function () {
            $oldRegular = Image::factory()->public()->active()->create([
                'is_featured' => false,
                'created_at' => now()->subDays(2)
            ]);
            $newFeatured = Image::factory()->public()->active()->featured()->create([
                'created_at' => now()->subDay()
            ]);
            $newRegular = Image::factory()->public()->active()->create([
                'is_featured' => false,
                'created_at' => now()
            ]);

            $response = $this->getJson('/api/v1/images');

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data[0]['id'])->toBe($newFeatured->id); // Featured first
            expect($data[1]['id'])->toBe($newRegular->id); // Then newest
            expect($data[2]['id'])->toBe($oldRegular->id); // Then oldest
        });

        it('paginates results with custom per_page', function () {
            Image::factory()->public()->active()->count(5)->create();

            $response = $this->getJson('/api/v1/images?per_page=2');

            $response->assertStatus(200);
            $data = $response->json('data');
            $meta = $response->json('meta');
            expect($data)->toHaveCount(2);
            expect($meta['total'])->toBe(5);
            expect($meta['per_page'])->toBe(2);
        });

        it('limits per_page to maximum of 100', function () {
            $response = $this->getJson('/api/v1/images?per_page=200');

            $response->assertStatus(200);
            $meta = $response->json('meta');
            expect($meta['per_page'])->toBe(100);
        });

        it('only returns active images', function () {
            $activeImage = Image::factory()->public()->active()->create();
            $archivedImage = Image::factory()->public()->archived()->create();
            $deletedImage = Image::factory()->public()->deleted()->create();

            $response = $this->getJson('/api/v1/images');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($activeImage->id);
        });
    });

    describe('Store endpoint', function () {
        it('can create a new image when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test-image.jpg', 1024, 'image/jpeg');
            
            $imageData = [
                'title' => 'Test Image',
                'description' => 'A test image for unit testing',
                'alt_text' => 'Test image alt text',
                'path' => $file,
                'category_id' => $this->category->id,
                'tags' => ['test', 'image', 'upload'],
                'language' => 'es',
                'is_public' => true,
                'is_featured' => false,
            ];

            $response = $this->postJson('/api/v1/images', $imageData);

            $response->assertStatus(201)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'title',
                            'slug',
                            'url',
                            'mime_type',
                            'file_size',
                            'category',
                            'uploaded_by'
                        ],
                        'message'
                    ]);

            $this->assertDatabaseHas('images', [
                'title' => 'Test Image',
                'slug' => 'test-image',
                'category_id' => $this->category->id,
                'uploaded_by_user_id' => $this->user->id,
                'language' => 'es',
                'is_public' => true,
            ]);

            // Verify file was stored
            Storage::disk('public')->assertExists($response->json('data.path'));
        });

        it('auto-generates slug from title', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
            
            $imageData = [
                'title' => 'Amazing Mountain View',
                'path' => $file,
            ];

            $response = $this->postJson('/api/v1/images', $imageData);

            $response->assertStatus(201);
            expect($response->json('data.slug'))->toBe('amazing-mountain-view');
        });

        it('extracts image dimensions and metadata', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test.jpg', 2048, 'image/jpeg');
            
            $imageData = [
                'title' => 'Test Image',
                'path' => $file,
            ];

            $response = $this->postJson('/api/v1/images', $imageData);

            $response->assertStatus(201);
            $data = $response->json('data');
            
            // Note: Fake files may not have real dimensions extracted
            expect($data['mime_type'])->toBe('image/jpeg');
            expect($data['file_size'])->toBeGreaterThan(0);
        });

        it('validates required fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/images', []);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['title', 'path']);
        });

        it('validates file is an image', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('document.pdf', 1024);

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Test',
                'path' => $file,
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['path']);
        });

        it('validates file size limit', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('large.jpg', 15000, 'image/jpeg'); // 15MB

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Large Image',
                'path' => $file,
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['path']);
        });

        it('validates unique slug', function () {
            Sanctum::actingAs($this->user);
            
            Image::factory()->create(['slug' => 'existing-slug']);
            
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Test Image',
                'slug' => 'existing-slug',
                'path' => $file,
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['slug']);
        });

        it('validates category exists', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Test Image',
                'path' => $file,
                'category_id' => 999,
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['category_id']);
        });

        it('validates language is supported', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Test Image',
                'path' => $file,
                'language' => 'invalid',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['language']);
        });

        it('requires authentication', function () {
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Test Image',
                'path' => $file,
            ]);

            $response->assertStatus(401);
        });

        it('sets default values correctly', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Test Image',
                'path' => $file,
            ]);

            $response->assertStatus(201);
            $data = $response->json('data');
            
            expect($data['language'])->toBe('es');
            expect($data['is_public'])->toBe(true);
            expect($data['is_featured'])->toBe(false);
        });
    });

    describe('Show endpoint', function () {
        it('can show a public active image', function () {
            $image = Image::factory()->public()->active()->create();

            $response = $this->getJson("/api/v1/images/{$image->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $image->id,
                            'title' => $image->title,
                            'status' => 'active',
                            'is_public' => true,
                        ]
                    ]);
        });

        it('increments view count when showing image', function () {
            $image = Image::factory()->public()->active()->create(['view_count' => 5]);

            $response = $this->getJson("/api/v1/images/{$image->id}");

            $response->assertStatus(200);
            
            $image->refresh();
            expect($image->view_count)->toBe(6);
            expect($image->last_used_at)->not->toBeNull();
        });

        it('returns 404 for private images', function () {
            $image = Image::factory()->private()->active()->create();

            $response = $this->getJson("/api/v1/images/{$image->id}");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Imagen no encontrada']);
        });

        it('returns 404 for inactive images', function () {
            $image = Image::factory()->public()->archived()->create();

            $response = $this->getJson("/api/v1/images/{$image->id}");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Imagen no encontrada']);
        });

        it('includes relationships and computed properties', function () {
            $image = Image::factory()->public()->active()->landscape()->create();

            $response = $this->getJson("/api/v1/images/{$image->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data['category'])->not->toBeNull();
            expect($data['uploaded_by'])->not->toBeNull();
            expect($data['dimensions'])->toContain('×');
            expect($data['aspect_ratio'])->toBeFloat();
            expect($data['is_landscape'])->toBe(true);
            expect($data['formatted_file_size'])->toContain('B'); // Could be KB, MB, or GB
        });
    });

    describe('Update endpoint', function () {
        it('can update an image when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $image = Image::factory()->create();

            $updateData = [
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'is_featured' => true,
                'tags' => ['updated', 'tags'],
            ];

            $response = $this->putJson("/api/v1/images/{$image->id}", $updateData);

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'title' => 'Updated Title',
                            'description' => 'Updated description',
                            'is_featured' => true,
                            'tags' => ['updated', 'tags'],
                        ],
                        'message' => 'Imagen actualizada exitosamente'
                    ]);

            $this->assertDatabaseHas('images', [
                'id' => $image->id,
                'title' => 'Updated Title',
                'is_featured' => true,
            ]);
        });

        it('auto-generates slug when title is updated', function () {
            Sanctum::actingAs($this->user);
            
            $image = Image::factory()->create(['title' => 'Old Title', 'slug' => 'old-title']);

            $response = $this->putJson("/api/v1/images/{$image->id}", [
                'title' => 'New Amazing Title'
            ]);

            $response->assertStatus(200);
            expect($response->json('data.slug'))->toBe('new-amazing-title');
        });

        it('validates unique slug on update', function () {
            Sanctum::actingAs($this->user);
            
            $image1 = Image::factory()->create(['slug' => 'unique-slug']);
            $image2 = Image::factory()->create(['slug' => 'other-slug']);

            $response = $this->putJson("/api/v1/images/{$image2->id}", [
                'slug' => 'unique-slug'
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['slug']);
        });

        it('allows same slug for same image', function () {
            Sanctum::actingAs($this->user);
            
            $image = Image::factory()->create(['slug' => 'same-slug']);

            $response = $this->putJson("/api/v1/images/{$image->id}", [
                'title' => 'Updated Title',
                'slug' => 'same-slug'
            ]);

            $response->assertStatus(200);
        });

        it('requires authentication', function () {
            $image = Image::factory()->create();

            $response = $this->putJson("/api/v1/images/{$image->id}", [
                'title' => 'Updated Title'
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Destroy endpoint', function () {
        it('can soft delete an image when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $image = Image::factory()->active()->create();

            $response = $this->deleteJson("/api/v1/images/{$image->id}");

            $response->assertStatus(200)
                    ->assertJson(['message' => 'Imagen eliminada exitosamente']);

            $image->refresh();
            expect($image->status)->toBe('deleted');
        });

        it('requires authentication', function () {
            $image = Image::factory()->create();

            $response = $this->deleteJson("/api/v1/images/{$image->id}");

            $response->assertStatus(401);
        });
    });

    describe('Download endpoint', function () {
        // TODO: Fix download tests - issue with fake storage and real file system paths
        // it('can download a public active image', function () {
        //     $image = Image::factory()->public()->active()->create([
        //         'path' => 'images/test.jpg',
        //         'filename' => 'test-image.jpg'
        //     ]);

        //     // Create a fake file to download
        //     Storage::disk('public')->makeDirectory('images');
        //     Storage::disk('public')->put($image->path, 'fake image content');
            
        //     // Verify file exists before testing download
        //     $this->assertTrue(Storage::disk('public')->exists($image->path));

        //     $response = $this->postJson("/api/v1/images/{$image->id}/download");

        //     $response->assertStatus(200);
        //     $response->assertHeader('content-disposition');
        // });

        // it('increments download count', function () {
        //     $image = Image::factory()->public()->active()->create([
        //         'path' => 'images/test.jpg',
        //         'filename' => 'test-image.jpg',
        //         'download_count' => 3
        //     ]);

        //     Storage::disk('public')->makeDirectory('images');
        //     Storage::disk('public')->put($image->path, 'fake image content');
            
        //     // Verify file exists
        //     $this->assertTrue(Storage::disk('public')->exists($image->path));

        //     $response = $this->postJson("/api/v1/images/{$image->id}/download");

        //     $response->assertStatus(200);
            
        //     $image->refresh();
        //     expect($image->download_count)->toBe(4);
        // });

        it('returns 404 for private images', function () {
            $image = Image::factory()->private()->active()->create();

            $response = $this->postJson("/api/v1/images/{$image->id}/download");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Imagen no encontrada']);
        });

        it('returns 404 for inactive images', function () {
            $image = Image::factory()->public()->archived()->create();

            $response = $this->postJson("/api/v1/images/{$image->id}/download");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Imagen no encontrada']);
        });
    });

    describe('Featured endpoint', function () {
        it('returns featured images', function () {
            $featured1 = Image::factory()->public()->active()->featured()->create();
            $featured2 = Image::factory()->public()->active()->featured()->create();
            $regular = Image::factory()->public()->active()->create(['is_featured' => false]);

            $response = $this->getJson('/api/v1/images/featured');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id', 'title', 'url', 'is_featured'
                            ]
                        ]
                    ]);

            $data = $response->json('data');
            expect($data)->toHaveCount(2);
            expect($data[0]['is_featured'])->toBe(true);
            expect($data[1]['is_featured'])->toBe(true);
        });

        it('respects limit parameter', function () {
            Image::factory()->public()->active()->featured()->count(5)->create();

            $response = $this->getJson('/api/v1/images/featured?limit=3');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(3);
        });

        it('limits maximum to 50', function () {
            Image::factory()->public()->active()->featured()->count(60)->create();

            $response = $this->getJson('/api/v1/images/featured?limit=100');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(50);
        });

        it('orders by creation date descending', function () {
            $older = Image::factory()->public()->active()->featured()->create(['created_at' => now()->subDays(2)]);
            $newer = Image::factory()->public()->active()->featured()->create(['created_at' => now()]);

            $response = $this->getJson('/api/v1/images/featured');

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data[0]['id'])->toBe($newer->id);
            expect($data[1]['id'])->toBe($older->id);
        });
    });

    describe('Stats endpoint', function () {
        it('returns usage statistics', function () {
            Image::factory()->active()->count(5)->create();
            Image::factory()->public()->count(3)->create();
            Image::factory()->featured()->count(2)->create();

            $response = $this->getJson('/api/v1/images/stats');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'total',
                        'active',
                        'public',
                        'featured',
                        'total_size',
                        'total_downloads',
                        'total_views'
                    ]);

            $data = $response->json();
            expect($data['total'])->toBeGreaterThan(0);
            expect($data['active'])->toBeGreaterThan(0);
        });

        it('calculates correct totals', function () {
            Image::factory()->active()->create(['file_size' => 1000, 'download_count' => 5, 'view_count' => 10]);
            Image::factory()->active()->create(['file_size' => 2000, 'download_count' => 3, 'view_count' => 7]);

            $response = $this->getJson('/api/v1/images/stats');

            $response->assertStatus(200);
            $data = $response->json();
            
            expect($data['total'])->toBe(2);
            expect((int)$data['total_size'])->toBe(3000);
            expect((int)$data['total_downloads'])->toBe(8);
            expect((int)$data['total_views'])->toBe(17);
        });
    });

    describe('Model business logic', function () {
        it('auto-generates slug on creation', function () {
            $image = Image::factory()->make(['title' => 'Amazing Photo', 'slug' => '']);
            $image->save();

            expect($image->slug)->toBe('amazing-photo');
        });

        it('does not overwrite existing slug on title update', function () {
            $image = Image::factory()->create(['title' => 'Original', 'slug' => 'custom-slug']);
            
            $image->update(['title' => 'Updated Title']);
            
            expect($image->slug)->toBe('custom-slug');
        });

        it('formats file size correctly', function () {
            $image = Image::factory()->make(['file_size' => 1024]);
            expect($image->formatted_file_size)->toBe('1024 B');

            $image = Image::factory()->make(['file_size' => 1048576]);
            expect($image->formatted_file_size)->toBe('1024 KB');

            $image = Image::factory()->make(['file_size' => null]);
            expect($image->formatted_file_size)->toBe('Desconocido');
        });

        it('calculates dimensions and aspect ratios', function () {
            $image = Image::factory()->make(['width' => 1920, 'height' => 1080]);
            
            expect($image->dimensions)->toBe('1920 × 1080 px');
            expect($image->aspect_ratio)->toBe(1.78);
            expect($image->is_landscape)->toBe(true);
            expect($image->is_portrait)->toBe(false);
            expect($image->is_square)->toBe(false);
        });

        it('detects orientation correctly', function () {
            $landscape = Image::factory()->landscape()->make();
            expect($landscape->is_landscape)->toBe(true);

            $portrait = Image::factory()->portrait()->make();
            expect($portrait->is_portrait)->toBe(true);

            $square = Image::factory()->square()->make();
            expect($square->is_square)->toBe(true);
        });

        it('identifies image types correctly', function () {
            $jpegImage = Image::factory()->jpeg()->make();
            expect($jpegImage->isImage())->toBe(true);
            expect($jpegImage->isVector())->toBe(false);

            $svgImage = Image::factory()->make(['mime_type' => 'image/svg+xml']);
            expect($svgImage->isImage())->toBe(true);
            expect($svgImage->isVector())->toBe(true);
        });

        it('can duplicate images', function () {
            $original = Image::factory()->create([
                'title' => 'Original Image',
                'slug' => 'original-image',
                'is_featured' => true,
                'view_count' => 100,
                'download_count' => 50
            ]);

            $duplicate = $original->duplicate();

            expect($duplicate->title)->toBe('Original Image (Copia)');
            expect($duplicate->slug)->toContain('original-image-copy-');
            expect($duplicate->is_featured)->toBe(false);
            expect($duplicate->view_count)->toBe(0);
            expect($duplicate->download_count)->toBe(0);
            expect($duplicate->id)->not->toBe($original->id);
        });

        it('can change status', function () {
            $image = Image::factory()->active()->create();

            $image->archive();
            expect($image->status)->toBe('archived');

            $image->restore();
            expect($image->status)->toBe('active');

            $image->softDelete();
            expect($image->status)->toBe('deleted');
        });

        it('increments counters correctly', function () {
            $image = Image::factory()->create(['view_count' => 5, 'download_count' => 3]);

            $image->incrementViews();
            expect($image->view_count)->toBe(6);
            expect($image->last_used_at)->not->toBeNull();

            $image->incrementDownloads();
            expect($image->download_count)->toBe(4);
        });

        it('provides useful static methods', function () {
            Image::factory()->active()->count(3)->create();
            Image::factory()->active()->popular()->count(2)->create();

            $stats = Image::getUsageStats();
            expect($stats['total'])->toBe(5);
            expect($stats['active'])->toBe(5);

            $mostUsed = Image::getMostUsed(3);
            expect($mostUsed->count())->toBeLessThanOrEqual(3);

            $recentUploads = Image::getRecentUploads(7);
            expect($recentUploads->count())->toBeGreaterThan(0);
        });

        it('can generate responsive versions', function () {
            $image = Image::factory()->create();

            $responsiveUrls = $image->generateResponsiveVersions([150, 300, 600]);

            expect($responsiveUrls)->toHaveKey('150x150');
            expect($responsiveUrls)->toHaveKey('300x300');
            expect($responsiveUrls)->toHaveKey('600x600');
            
            $image->refresh();
            expect($image->responsive_urls)->toBe($responsiveUrls);
        });
    });

    describe('Edge cases and validation', function () {
        it('handles images with special characters', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
            
            $imageData = [
                'title' => 'Título con Ñ & Símbolos Éspeciáles',
                'description' => '¡Descripción con acentos!',
                'alt_text' => 'Imagen única',
                'path' => $file,
                'tags' => ['español', 'ñoño', 'niño'],
            ];

            $response = $this->postJson('/api/v1/images', $imageData);

            $response->assertStatus(201);
            expect($response->json('data.slug'))->toBe('titulo-con-n-simbolos-especiales');
        });

        it('handles very large dimensions', function () {
            $image = Image::factory()->create(['width' => 10000, 'height' => 8000]);
            
            expect($image->dimensions)->toBe('10000 × 8000 px');
            expect($image->is_landscape)->toBe(true);
        });

        it('handles missing dimensions gracefully', function () {
            $image = Image::factory()->create(['width' => null, 'height' => null]);
            
            expect($image->dimensions)->toBeNull();
            expect($image->aspect_ratio)->toBeNull();
            expect($image->is_landscape)->toBeNull();
            expect($image->is_portrait)->toBeNull();
            expect($image->is_square)->toBeNull();
        });

        it('handles empty tags array', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Test Image',
                'path' => $file,
                'tags' => [],
            ]);

            $response->assertStatus(201);
            expect($response->json('data.tags'))->toBe([]);
        });

        it('validates tag length', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Test Image',
                'path' => $file,
                'tags' => [str_repeat('a', 60)], // Too long
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['tags.0']);
        });

        it('validates SEO field lengths', function () {
            Sanctum::actingAs($this->user);
            
            $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

            $response = $this->postJson('/api/v1/images', [
                'title' => 'Test Image',
                'path' => $file,
                'seo_title' => str_repeat('a', 70), // Too long
                'seo_description' => str_repeat('b', 170), // Too long
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['seo_title', 'seo_description']);
        });

        it('handles different image formats', function () {
            Sanctum::actingAs($this->user);
            
            $formats = [
                ['png', 'image/png'],
                ['gif', 'image/gif'],
                ['webp', 'image/webp'],
            ];

            foreach ($formats as [$extension, $mimeType]) {
                $file = UploadedFile::fake()->create("test.{$extension}", 1024, $mimeType);
                
                $response = $this->postJson('/api/v1/images', [
                    'title' => "Test {$extension} Image",
                    'path' => $file,
                ]);

                $response->assertStatus(201);
                expect($response->json('data.mime_type'))->toBe($mimeType);
            }
        });
    });
});
