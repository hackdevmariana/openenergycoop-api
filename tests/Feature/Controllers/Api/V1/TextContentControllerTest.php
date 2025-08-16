<?php

use App\Models\TextContent;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    TextContent::query()->delete();
    
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
});

describe('TextContentController', function () {
    describe('Index endpoint', function () {
        it('can list published text contents without authentication', function () {
            $publishedContent = TextContent::factory()->published()->create();
            $draftContent = TextContent::factory()->draft()->create();

            $response = $this->getJson('/api/v1/text-contents');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'text',
                                'language',
                                'is_draft',
                                'published_at',
                                'created_at',
                                'updated_at'
                            ]
                        ]
                    ]);

            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($publishedContent->id);
        });

        it('can filter by language', function () {
            $spanishContent = TextContent::factory()->published()->spanish()->create();
            $englishContent = TextContent::factory()->published()->english()->create();

            $response = $this->getJson('/api/v1/text-contents?language=es');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($spanishContent->id);
            expect($data[0]['language'])->toBe('es');
        });

        it('can search by title and content', function () {
            $content1 = TextContent::factory()->published()->create([
                'title' => 'Amazing Article',
                'text' => 'This is about technology'
            ]);
            $content2 = TextContent::factory()->published()->create([
                'title' => 'Another Topic',
                'text' => 'This is about nature'
            ]);

            $response = $this->getJson('/api/v1/text-contents?search=Amazing');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($content1->id);
        });

        it('can search by content text', function () {
            $content1 = TextContent::factory()->published()->create([
                'title' => 'Article One',
                'text' => 'This is about technology and innovation'
            ]);
            $content2 = TextContent::factory()->published()->create([
                'title' => 'Article Two',
                'text' => 'This is about nature and environment'
            ]);

            $response = $this->getJson('/api/v1/text-contents?search=technology');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($content1->id);
        });

        it('orders by created_at desc', function () {
            $older = TextContent::factory()->published()->create(['created_at' => now()->subDays(2)]);
            $newer = TextContent::factory()->published()->create(['created_at' => now()->subDay()]);

            $response = $this->getJson('/api/v1/text-contents');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data[0]['id'])->toBe($newer->id);
            expect($data[1]['id'])->toBe($older->id);
        });

        it('includes organization and creator relationships', function () {
            $content = TextContent::factory()
                ->published()
                ->forOrganization($this->organization)
                ->createdBy($this->user)
                ->create();

            $response = $this->getJson('/api/v1/text-contents');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data[0])->toHaveKey('organization');
            expect($data[0])->toHaveKey('created_by');
        });

        it('only returns published content', function () {
            TextContent::factory()->published()->count(3)->create();
            TextContent::factory()->draft()->count(2)->create();

            $response = $this->getJson('/api/v1/text-contents');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(3);
        });
    });

    describe('Store endpoint', function () {
        it('can create text content when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $contentData = [
                'title' => 'New Text Content',
                'text' => 'This is the content of the new text.',
                'language' => 'es',
                'organization_id' => $this->organization->id,
            ];

            $response = $this->postJson('/api/v1/text-contents', $contentData);

            $response->assertStatus(201)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'title',
                            'text',
                            'language',
                            'organization_id',
                            'created_by_user_id'
                        ],
                        'message'
                    ])
                    ->assertJson([
                        'data' => [
                            'title' => 'New Text Content',
                            'text' => 'This is the content of the new text.',
                            'language' => 'es',
                            'created_by_user_id' => $this->user->id,
                        ],
                        'message' => 'Contenido de texto creado exitosamente'
                    ]);

            $this->assertDatabaseHas('text_contents', [
                'title' => 'New Text Content',
                'text' => 'This is the content of the new text.',
                'language' => 'es',
                'created_by_user_id' => $this->user->id,
            ]);
        });

        it('validates required fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/text-contents', []);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['title', 'text', 'language']);
        });

        it('validates language is supported', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/text-contents', [
                'title' => 'Test Content',
                'text' => 'Test content text',
                'language' => 'invalid',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['language']);
        });

        it('validates organization exists', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/text-contents', [
                'title' => 'Test Content',
                'text' => 'Test content text',
                'language' => 'es',
                'organization_id' => 999,
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['organization_id']);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/v1/text-contents', [
                'title' => 'Test Content',
                'text' => 'Test content text',
                'language' => 'es',
            ]);

            $response->assertStatus(401);
        });

        it('auto-generates slug from title', function () {
            Sanctum::actingAs($this->user);
            
            $response = $this->postJson('/api/v1/text-contents', [
                'title' => 'Amazing Content Title',
                'text' => 'Test content text',
                'language' => 'es',
            ]);

            $response->assertStatus(201);
            
            $content = TextContent::latest()->first();
            expect($content->slug)->toBe('amazing-content-title');
        });
    });

    describe('Show endpoint', function () {
        it('can show a published text content', function () {
            Sanctum::actingAs($this->user);
            
            $content = TextContent::factory()->published()->create();

            $response = $this->getJson("/api/v1/text-contents/{$content->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $content->id,
                            'title' => $content->title,
                            'text' => $content->text,
                            'language' => $content->language,
                        ]
                    ]);
        });

        it('returns 404 for draft content', function () {
            Sanctum::actingAs($this->user);
            
            $content = TextContent::factory()->draft()->create();

            $response = $this->getJson("/api/v1/text-contents/{$content->id}");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Contenido no encontrado']);
        });

        it('includes relationships', function () {
            Sanctum::actingAs($this->user);
            
            $content = TextContent::factory()
                ->published()
                ->forOrganization($this->organization)
                ->createdBy($this->user)
                ->create();

            $response = $this->getJson("/api/v1/text-contents/{$content->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveKey('organization');
            expect($data)->toHaveKey('created_by');
        });
    });

    describe('Update endpoint', function () {
        it('can update text content when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $content = TextContent::factory()->published()->create();

            $updateData = [
                'title' => 'Updated Title',
                'text' => 'Updated content text',
                'language' => 'en',
            ];

            $response = $this->putJson("/api/v1/text-contents/{$content->id}", $updateData);

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'title' => 'Updated Title',
                            'text' => 'Updated content text',
                            'language' => 'en',
                            'updated_by_user_id' => $this->user->id,
                        ],
                        'message' => 'Contenido actualizado exitosamente'
                    ]);

            $content->refresh();
            expect($content->title)->toBe('Updated Title');
            expect($content->text)->toBe('Updated content text');
            expect($content->updated_by_user_id)->toBe($this->user->id);
        });

        it('can partially update text content', function () {
            Sanctum::actingAs($this->user);
            
            $content = TextContent::factory()->published()->create([
                'title' => 'Original Title',
                'text' => 'Original text',
                'language' => 'es',
            ]);

            $response = $this->putJson("/api/v1/text-contents/{$content->id}", [
                'title' => 'Updated Title Only',
            ]);

            $response->assertStatus(200);
            
            $content->refresh();
            expect($content->title)->toBe('Updated Title Only');
            expect($content->text)->toBe('Original text'); // Should remain unchanged
            expect($content->language)->toBe('es'); // Should remain unchanged
        });

        it('validates language on update', function () {
            Sanctum::actingAs($this->user);
            
            $content = TextContent::factory()->published()->create();

            $response = $this->putJson("/api/v1/text-contents/{$content->id}", [
                'language' => 'invalid',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['language']);
        });

        it('requires authentication', function () {
            $content = TextContent::factory()->published()->create();

            $response = $this->putJson("/api/v1/text-contents/{$content->id}", [
                'title' => 'Updated Title',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Destroy endpoint', function () {
        it('can delete text content when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $content = TextContent::factory()->published()->create();

            $response = $this->deleteJson("/api/v1/text-contents/{$content->id}");

            $response->assertStatus(200)
                    ->assertJson(['message' => 'Contenido eliminado exitosamente']);

            $this->assertDatabaseMissing('text_contents', ['id' => $content->id]);
        });

        it('requires authentication', function () {
            $content = TextContent::factory()->published()->create();

            $response = $this->deleteJson("/api/v1/text-contents/{$content->id}");

            $response->assertStatus(401);
        });
    });

    describe('Model business logic', function () {
        it('auto-generates slug on creation', function () {
            $content = TextContent::factory()->make([
                'title' => 'Amazing Content Title',
                'slug' => ''
            ]);
            $content->save();

            expect($content->slug)->toBe('amazing-content-title');
        });

        it('calculates word count correctly', function () {
            $content = TextContent::factory()->create([
                'text' => 'This is a test with eight words total.'
            ]);

            expect($content->getWordCount())->toBe(8);
        });

        it('calculates word count ignoring HTML tags', function () {
            $content = TextContent::factory()->create([
                'text' => 'This is <strong>bold text</strong> with <em>emphasis</em>.'
            ]);

            expect($content->getWordCount())->toBe(6); // HTML tags should be stripped
        });

        it('can check if content can be published', function () {
            $validContent = TextContent::factory()->create([
                'text' => 'Valid content',
                'slug' => 'valid-content'
            ]);

            $invalidContent1 = TextContent::factory()->create([
                'text' => '',
                'slug' => 'has-slug'
            ]);

            $invalidContent2 = TextContent::factory()->make([
                'text' => 'Has text',
                'slug' => '',
                'title' => '' // Prevent auto-slug generation
            ]);
            $invalidContent2->save();

            expect($validContent->canBePublished())->toBe(true);
            expect($invalidContent1->canBePublished())->toBe(false);
            expect($invalidContent2->canBePublished())->toBe(false);
        });

        it('can increment view count', function () {
            $content = TextContent::factory()->create(['number_of_views' => 5]);

            $content->incrementViews();

            $content->refresh();
            expect($content->number_of_views)->toBe(6);
        });

        it('has correct relationships', function () {
            $author = User::factory()->create();
            $creator = User::factory()->create();
            $parent = TextContent::factory()->create();
            
            $content = TextContent::factory()->create([
                'author_id' => $author->id,
                'created_by_user_id' => $creator->id,
                'parent_id' => $parent->id,
            ]);

            expect($content->author->id)->toBe($author->id);
            expect($content->createdBy->id)->toBe($creator->id);
            expect($content->parent->id)->toBe($parent->id);
        });

        it('can have child content', function () {
            $parent = TextContent::factory()->create();
            $child = TextContent::factory()->childOf($parent)->create();

            expect($parent->children)->toHaveCount(1);
            expect($parent->children->first()->id)->toBe($child->id);
        });
    });

    describe('Scopes and filtering', function () {
        it('published scope only returns published content', function () {
            $published = TextContent::factory()->published()->count(3)->create();
            $drafts = TextContent::factory()->draft()->count(2)->create();

            $publishedContent = TextContent::published()->get();

            expect($publishedContent)->toHaveCount(3);
        });

        it('can filter by language using where clause', function () {
            $spanishContent = TextContent::factory()->spanish()->count(2)->create();
            $englishContent = TextContent::factory()->english()->count(3)->create();

            $spanish = TextContent::where('language', 'es')->get();

            expect($spanish)->toHaveCount(2);
        });

        it('can filter by organization', function () {
            $org1Content = TextContent::factory()->forOrganization($this->organization)->count(2)->create();
            $org2 = Organization::factory()->create();
            $org2Content = TextContent::factory()->forOrganization($org2)->count(3)->create();

            $org1Results = TextContent::where('organization_id', $this->organization->id)->get();

            expect($org1Results)->toHaveCount(2);
        });
    });

    describe('Edge cases and validation', function () {
        it('handles content with special characters', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/text-contents', [
                'title' => 'Título con Ñ & Símbolos Éspeciáles',
                'text' => '¡Contenido con acentos y símbolos especiales!',
                'language' => 'es',
            ]);

            $response->assertStatus(201);
            
            $content = TextContent::latest()->first();
            expect($content->slug)->toBe('titulo-con-n-simbolos-especiales');
        });

        it('handles empty search gracefully', function () {
            TextContent::factory()->published()->count(3)->create();

            $response = $this->getJson('/api/v1/text-contents?search=');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(3); // Should return all when search is empty
        });

        it('handles non-existent language filter', function () {
            TextContent::factory()->published()->count(3)->create();

            $response = $this->getJson('/api/v1/text-contents?language=invalid');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(0); // Should return empty when no match
        });

        it('handles content without organization', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/text-contents', [
                'title' => 'Content Without Organization',
                'text' => 'This content has no organization',
                'language' => 'es',
            ]);

            $response->assertStatus(201);
            
            $content = TextContent::latest()->first();
            expect($content->organization_id)->toBeNull();
        });

        it('handles very long content', function () {
            Sanctum::actingAs($this->user);

            $longContent = str_repeat('Lorem ipsum dolor sit amet. ', 1000);

            $response = $this->postJson('/api/v1/text-contents', [
                'title' => 'Very Long Content',
                'text' => $longContent,
                'language' => 'es',
            ]);

            $response->assertStatus(201);
            
            $content = TextContent::latest()->first();
            expect($content->getWordCount())->toBeGreaterThan(4000);
        });

        it('preserves HTML in content', function () {
            Sanctum::actingAs($this->user);

            $htmlContent = '<h1>Title</h1><p>This is <strong>bold</strong> text.</p>';

            $response = $this->postJson('/api/v1/text-contents', [
                'title' => 'HTML Content',
                'text' => $htmlContent,
                'language' => 'es',
            ]);

            $response->assertStatus(201);
            
            $content = TextContent::latest()->first();
            expect($content->text)->toBe($htmlContent);
        });
    });
});
