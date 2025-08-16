<?php

use App\Models\Organization;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    Page::query()->delete();
    
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
});

describe('PageController', function () {
    describe('Index endpoint', function () {
        it('can get published pages', function () {
            // Create some pages
            $publishedPage = Page::factory()->forOrganization($this->organization)->published()->create();
            $draftPage = Page::factory()->forOrganization($this->organization)->draft()->create();
            
            $response = $this->getJson('/api/v1/pages');
            
            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'slug',
                                'route',
                                'language',
                                'is_draft',
                                'template',
                                'meta_data',
                                'full_slug',
                                'url',
                                'template_label'
                            ]
                        ]
                    ]);
            
            // Should only include published pages
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($publishedPage->id);
            expect($data[0]['is_draft'])->toBe(false);
        });

        it('can filter pages by language', function () {
            $spanishPage = Page::factory()->forOrganization($this->organization)->spanish()->published()->create();
            $englishPage = Page::factory()->forOrganization($this->organization)->english()->published()->create();

            $response = $this->getJson('/api/v1/pages?language=es');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['language'])->toBe('es');
        });

        it('can filter pages by template', function () {
            $landingPage = Page::factory()->forOrganization($this->organization)->landingPage()->published()->create();
            $contactPage = Page::factory()->forOrganization($this->organization)->contactPage()->published()->create();

            $response = $this->getJson('/api/v1/pages?template=landing');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['template'])->toBe('landing');
        });

        it('can filter pages by parent_id', function () {
            $parentPage = Page::factory()->forOrganization($this->organization)->published()->create();
            $childPage = Page::factory()->childOf($parentPage)->published()->create();
            $rootPage = Page::factory()->forOrganization($this->organization)->published()->create(['parent_id' => null]);

            // Test filtering by specific parent
            $response = $this->getJson("/api/v1/pages?parent_id={$parentPage->id}");
            
            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['parent_id'])->toBe($parentPage->id);

            // Test filtering for root pages
            $response = $this->getJson('/api/v1/pages?parent_id=null');
            
            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(2); // parentPage and rootPage
        });

        it('orders pages by sort_order and title', function () {
            $page1 = Page::factory()->forOrganization($this->organization)->published()->create(['title' => 'B Page', 'sort_order' => 2]);
            $page2 = Page::factory()->forOrganization($this->organization)->published()->create(['title' => 'A Page', 'sort_order' => 1]);
            $page3 = Page::factory()->forOrganization($this->organization)->published()->create(['title' => 'C Page', 'sort_order' => 1]);

            $response = $this->getJson('/api/v1/pages');

            $response->assertStatus(200);
            $data = $response->json('data');
            
            // Should be ordered by sort_order first, then by title
            expect($data[0]['title'])->toBe('A Page'); // sort_order 1, title A
            expect($data[1]['title'])->toBe('C Page'); // sort_order 1, title C
            expect($data[2]['title'])->toBe('B Page'); // sort_order 2, title B
        });

        it('includes relationships when requested', function () {
            $parentPage = Page::factory()->forOrganization($this->organization)->published()->create();
            $childPage = Page::factory()->childOf($parentPage)->published()->create();

            $response = $this->getJson('/api/v1/pages');

            $response->assertStatus(200);
            $data = $response->json('data');
            
            // Find the child page data
            $childPageData = collect($data)->firstWhere('id', $childPage->id);
            expect($childPageData['parent'])->not->toBeNull();
            expect($childPageData['parent']['id'])->toBe($parentPage->id);
        });
    });

    describe('Store endpoint', function () {
        it('can create a new page when authenticated', function () {
            Sanctum::actingAs($this->user);

            $pageData = [
                'title' => 'Nueva Página',
                'slug' => 'nueva-pagina',
                'language' => 'es',
                'template' => 'default',
                'organization_id' => $this->organization->id,
                'meta_data' => [
                    'title' => 'Meta título',
                    'description' => 'Meta descripción'
                ]
            ];

            $response = $this->postJson('/api/v1/pages', $pageData);

            $response->assertStatus(201)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'title',
                            'slug',
                            'language',
                            'template',
                            'created_by_user_id'
                        ],
                        'message'
                    ]);

            $this->assertDatabaseHas('pages', [
                'title' => 'Nueva Página',
                'slug' => 'nueva-pagina',
                'created_by_user_id' => $this->user->id
            ]);
        });

        it('auto-generates slug from title if not provided', function () {
            Sanctum::actingAs($this->user);

            $pageData = [
                'title' => 'Página Sin Slug',
                'language' => 'es',
                'template' => 'default',
                'organization_id' => $this->organization->id,
            ];

            $response = $this->postJson('/api/v1/pages', $pageData);

            $response->assertStatus(201);
            expect($response->json('data.slug'))->toBe('pagina-sin-slug');
        });

        it('validates required fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/pages', []);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['title', 'slug']);
            
            // Language and template have defaults, so they won't fail validation
        });

        it('validates slug uniqueness within organization and language', function () {
            Sanctum::actingAs($this->user);

            // Create existing page
            Page::factory()->forOrganization($this->organization)->create([
                'slug' => 'existing-page',
                'language' => 'es'
            ]);

            $pageData = [
                'title' => 'Otra Página',
                'slug' => 'existing-page',
                'language' => 'es',
                'template' => 'default',
                'organization_id' => $this->organization->id,
            ];

            $response = $this->postJson('/api/v1/pages', $pageData);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['slug']);
        });

        it('allows same slug in different languages or organizations', function () {
            Sanctum::actingAs($this->user);

            $otherOrg = Organization::factory()->create();

            // Create existing page in Spanish
            Page::factory()->forOrganization($this->organization)->create([
                'slug' => 'same-slug',
                'language' => 'es'
            ]);

            // Should allow same slug in English
            $pageData1 = [
                'title' => 'English Page',
                'slug' => 'same-slug',
                'language' => 'en',
                'template' => 'default',
                'organization_id' => $this->organization->id,
            ];

            $response1 = $this->postJson('/api/v1/pages', $pageData1);
            $response1->assertStatus(201);

            // Should allow same slug in different organization
            $pageData2 = [
                'title' => 'Other Org Page',
                'slug' => 'same-slug',
                'language' => 'es',
                'template' => 'default',
                'organization_id' => $otherOrg->id,
            ];

            $response2 = $this->postJson('/api/v1/pages', $pageData2);
            $response2->assertStatus(201);
        });

        it('validates template-specific requirements', function () {
            Sanctum::actingAs($this->user);

            // Contact page should have contact info in meta_data
            $pageData = [
                'title' => 'Contacto',
                'slug' => 'contacto',
                'language' => 'es',
                'template' => 'contact',
                'organization_id' => $this->organization->id,
                'meta_data' => [] // Missing contact_info
            ];

            $response = $this->postJson('/api/v1/pages', $pageData);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['meta_data']);
        });

        it('validates parent-child relationships', function () {
            Sanctum::actingAs($this->user);

            $otherOrgPage = Page::factory()->create(); // Different organization

            $pageData = [
                'title' => 'Child Page',
                'slug' => 'child-page',
                'language' => 'es',
                'template' => 'default',
                'organization_id' => $this->organization->id,
                'parent_id' => $otherOrgPage->id
            ];

            $response = $this->postJson('/api/v1/pages', $pageData);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['parent_id']);
        });

        it('requires authentication', function () {
            $pageData = [
                'title' => 'Nueva Página',
                'slug' => 'nueva-pagina',
                'language' => 'es',
                'template' => 'default',
            ];

            $response = $this->postJson('/api/v1/pages', $pageData);

            $response->assertStatus(401);
        });
    });

    describe('Show endpoint', function () {
        it('can show a page by ID', function () {
            $page = Page::factory()->forOrganization($this->organization)->published()->create();

            $response = $this->getJson("/api/v1/pages/{$page->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $page->id,
                            'title' => $page->title,
                            'slug' => $page->slug
                        ]
                    ]);
        });

        it('can show a page by slug', function () {
            $page = Page::factory()->forOrganization($this->organization)->published()->create(['slug' => 'test-slug']);

            $response = $this->getJson('/api/v1/pages/test-slug');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $page->id,
                            'slug' => 'test-slug'
                        ]
                    ]);
        });

        it('can show a page by route using show endpoint', function () {
            // Note: The show endpoint can find by ID, slug, or route, but route matching might be tricky with URLs
            // Let's test with a simple route without slashes for the show endpoint
            $page = Page::factory()->forOrganization($this->organization)->published()->create(['route' => 'special-route']);

            $response = $this->getJson('/api/v1/pages/special-route');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $page->id,
                            'route' => 'special-route'
                        ]
                    ]);
        });

        it('includes components when requested', function () {
            $page = Page::factory()->forOrganization($this->organization)->published()->create();

            $response = $this->getJson("/api/v1/pages/{$page->id}?include_components=true");

            $response->assertStatus(200);
            // Note: components relationship should be loaded, even if empty
            expect($response->json('data.components'))->toBeArray();
        });

        it('only shows published pages', function () {
            $draftPage = Page::factory()->forOrganization($this->organization)->draft()->create();

            $response = $this->getJson("/api/v1/pages/{$draftPage->id}");

            $response->assertStatus(404);
        });

        it('returns 404 for non-existent page', function () {
            $response = $this->getJson('/api/v1/pages/999');

            $response->assertStatus(404);
        });

        it('includes computed properties', function () {
            $page = Page::factory()->forOrganization($this->organization)->published()->create([
                'slug' => 'test-page',
                'template' => 'landing'
            ]);

            $response = $this->getJson("/api/v1/pages/{$page->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data['full_slug'])->toBe('test-page');
            expect($data['template_label'])->toBe('Página de Aterrizaje');
            expect($data['can_be_published'])->toBeBool();
            expect($data['is_home_page'])->toBeBool();
        });
    });

    describe('Update endpoint', function () {
        it('can update a page when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $page = Page::factory()->forOrganization($this->organization)->create();

            $updateData = [
                'title' => 'Título Actualizado',
                'meta_data' => [
                    'title' => 'Meta título actualizado',
                    'description' => 'Meta descripción actualizada'
                ]
            ];

            $response = $this->putJson("/api/v1/pages/{$page->id}", $updateData);

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'title' => 'Título Actualizado'
                        ],
                        'message' => 'Página actualizada exitosamente'
                    ]);

            $this->assertDatabaseHas('pages', [
                'id' => $page->id,
                'title' => 'Título Actualizado',
                'updated_by_user_id' => $this->user->id
            ]);
        });

        it('auto-generates slug when title is updated but slug is not provided', function () {
            Sanctum::actingAs($this->user);
            
            $page = Page::factory()->forOrganization($this->organization)->create(['title' => 'Old Title']);

            $updateData = ['title' => 'New Amazing Title'];

            $response = $this->putJson("/api/v1/pages/{$page->id}", $updateData);

            $response->assertStatus(200);
            expect($response->json('data.slug'))->toBe('new-amazing-title');
        });

        it('validates slug uniqueness on update', function () {
            Sanctum::actingAs($this->user);
            
            $page1 = Page::factory()->forOrganization($this->organization)->create(['slug' => 'page-1']);
            $page2 = Page::factory()->forOrganization($this->organization)->create(['slug' => 'page-2']);

            $updateData = ['slug' => 'page-1'];

            $response = $this->putJson("/api/v1/pages/{$page2->id}", $updateData);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['slug']);
        });

        it('prevents circular parent-child references', function () {
            Sanctum::actingAs($this->user);
            
            $parent = Page::factory()->forOrganization($this->organization)->create();
            $child = Page::factory()->childOf($parent)->create();

            // Try to make parent a child of child (circular reference)
            $updateData = ['parent_id' => $child->id];

            $response = $this->putJson("/api/v1/pages/{$parent->id}", $updateData);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['parent_id']);
        });

        it('prevents setting page as its own parent', function () {
            Sanctum::actingAs($this->user);
            
            $page = Page::factory()->forOrganization($this->organization)->create();

            $updateData = ['parent_id' => $page->id];

            $response = $this->putJson("/api/v1/pages/{$page->id}", $updateData);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['parent_id']);
        });

        it('validates publishing requirements when setting is_draft to false', function () {
            Sanctum::actingAs($this->user);
            
            $page = Page::factory()->forOrganization($this->organization)->draft()->create([
                'title' => '',
                'slug' => ''
            ]);

            $updateData = ['is_draft' => false];

            $response = $this->putJson("/api/v1/pages/{$page->id}", $updateData);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['title', 'slug']);
        });

        it('requires authentication', function () {
            $page = Page::factory()->forOrganization($this->organization)->create();

            $response = $this->putJson("/api/v1/pages/{$page->id}", ['title' => 'New Title']);

            $response->assertStatus(401);
        });
    });

    describe('Destroy endpoint', function () {
        it('can delete a page when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $page = Page::factory()->forOrganization($this->organization)->create();

            $response = $this->deleteJson("/api/v1/pages/{$page->id}");

            $response->assertStatus(200)
                    ->assertJson(['message' => 'Página eliminada exitosamente']);

            $this->assertDatabaseMissing('pages', ['id' => $page->id]);
        });

        it('prevents deletion of page with children', function () {
            Sanctum::actingAs($this->user);
            
            $parent = Page::factory()->forOrganization($this->organization)->create();
            $child = Page::factory()->childOf($parent)->create();

            $response = $this->deleteJson("/api/v1/pages/{$parent->id}");

            $response->assertStatus(422)
                    ->assertJson(['message' => 'No se puede eliminar una página que tiene páginas hijas']);

            $this->assertDatabaseHas('pages', ['id' => $parent->id]);
        });

        it('can delete page after children are deleted', function () {
            Sanctum::actingAs($this->user);
            
            $parent = Page::factory()->forOrganization($this->organization)->create();
            $child = Page::factory()->childOf($parent)->create();

            // Delete child first
            $child->delete();

            $response = $this->deleteJson("/api/v1/pages/{$parent->id}");

            $response->assertStatus(200);
            $this->assertDatabaseMissing('pages', ['id' => $parent->id]);
        });

        it('requires authentication', function () {
            $page = Page::factory()->forOrganization($this->organization)->create();

            $response = $this->deleteJson("/api/v1/pages/{$page->id}");

            $response->assertStatus(401);
        });
    });

    describe('Special endpoints', function () {
        describe('byRoute endpoint', function () {
            it('can get page by route', function () {
                $page = Page::factory()->forOrganization($this->organization)->published()->create(['route' => '/about-us']);

                $response = $this->getJson('/api/v1/pages/by-route/about-us');

                $response->assertStatus(200)
                        ->assertJson([
                            'data' => [
                                'id' => $page->id,
                                'route' => '/about-us'
                            ]
                        ]);
            });

            it('handles routes with leading slash', function () {
                $page = Page::factory()->forOrganization($this->organization)->published()->create(['route' => '/contact']);

                $response = $this->getJson('/api/v1/pages/by-route/contact');

                $response->assertStatus(200)
                        ->assertJson([
                            'data' => [
                                'id' => $page->id,
                                'route' => '/contact'
                            ]
                        ]);
            });

            it('returns 404 for non-existent route', function () {
                $response = $this->getJson('/api/v1/pages/by-route/non-existent');

                $response->assertStatus(404);
            });

            it('only returns published pages', function () {
                $draftPage = Page::factory()->forOrganization($this->organization)->draft()->create(['route' => '/draft-route']);

                $response = $this->getJson('/api/v1/pages/by-route/draft-route');

                $response->assertStatus(404);
            });
        });

        describe('hierarchy endpoint', function () {
            it('returns hierarchical structure of pages', function () {
                $rootPage = Page::factory()->forOrganization($this->organization)->published()->create(['parent_id' => null]);
                $childPage = Page::factory()->childOf($rootPage)->published()->create();

                $response = $this->getJson('/api/v1/pages/hierarchy');

                $response->assertStatus(200);
                $data = $response->json('data');
                
                expect($data)->toHaveCount(1); // Only root pages at top level
                expect($data[0]['id'])->toBe($rootPage->id);
                expect($data[0]['children'])->toHaveCount(1);
                expect($data[0]['children'][0]['id'])->toBe($childPage->id);
            });

            it('can filter hierarchy by language', function () {
                $spanishRoot = Page::factory()->forOrganization($this->organization)->spanish()->published()->create(['parent_id' => null]);
                $englishRoot = Page::factory()->forOrganization($this->organization)->english()->published()->create(['parent_id' => null]);

                $response = $this->getJson('/api/v1/pages/hierarchy?language=es');

                $response->assertStatus(200);
                $data = $response->json('data');
                
                expect($data)->toHaveCount(1);
                expect($data[0]['language'])->toBe('es');
            });

            it('orders pages correctly in hierarchy', function () {
                $page1 = Page::factory()->forOrganization($this->organization)->published()->create(['parent_id' => null, 'sort_order' => 2, 'title' => 'B']);
                $page2 = Page::factory()->forOrganization($this->organization)->published()->create(['parent_id' => null, 'sort_order' => 1, 'title' => 'A']);

                $response = $this->getJson('/api/v1/pages/hierarchy');

                $response->assertStatus(200);
                $data = $response->json('data');
                
                expect($data[0]['title'])->toBe('A');
                expect($data[1]['title'])->toBe('B');
            });
        });

        describe('search endpoint', function () {
            it('can search pages by title', function () {
                $matchingPage = Page::factory()->forOrganization($this->organization)->published()->create(['title' => 'Energía Solar']);
                $nonMatchingPage = Page::factory()->forOrganization($this->organization)->published()->create(['title' => 'Otro Tema']);

                $response = $this->getJson('/api/v1/pages/search?q=energía');

                $response->assertStatus(200);
                $data = $response->json('data');
                
                expect($data)->toHaveCount(1);
                expect($data[0]['id'])->toBe($matchingPage->id);
                expect($response->json('query'))->toBe('energía');
                expect($response->json('total'))->toBe(1);
            });

            it('can search pages by search keywords', function () {
                $page = Page::factory()->forOrganization($this->organization)->published()->create([
                    'title' => 'Página de Prueba',
                    'search_keywords' => ['solar', 'renovable', 'energía']
                ]);

                $response = $this->getJson('/api/v1/pages/search?q=solar');

                $response->assertStatus(200);
                $data = $response->json('data');
                
                expect($data)->toHaveCount(1);
                expect($data[0]['id'])->toBe($page->id);
            });

            it('validates search query length', function () {
                $response = $this->getJson('/api/v1/pages/search?q=ab');

                $response->assertStatus(422)
                        ->assertJsonValidationErrors(['q']);
            });

            it('can filter search by language', function () {
                $spanishPage = Page::factory()->forOrganization($this->organization)->spanish()->published()->create(['title' => 'Energía']);
                $englishPage = Page::factory()->forOrganization($this->organization)->english()->published()->create(['title' => 'Energy']);

                $response = $this->getJson('/api/v1/pages/search?q=ener&language=es');

                $response->assertStatus(200);
                $data = $response->json('data');
                
                expect($data)->toHaveCount(1);
                expect($data[0]['language'])->toBe('es');
            });

            it('limits search results', function () {
                // Create more than 20 pages
                Page::factory()->count(25)->forOrganization($this->organization)->published()->create(['title' => 'Test Page']);

                $response = $this->getJson('/api/v1/pages/search?q=test');

                $response->assertStatus(200);
                $data = $response->json('data');
                
                expect($data)->toHaveCount(20); // Limited to 20 results
            });

            it('orders search results by last_reviewed_at and title', function () {
                $oldPage = Page::factory()->forOrganization($this->organization)->published()->create([
                    'title' => 'Test Page A',
                    'last_reviewed_at' => now()->subDays(10)
                ]);
                $newPage = Page::factory()->forOrganization($this->organization)->published()->create([
                    'title' => 'Test Page B',
                    'last_reviewed_at' => now()->subDays(1)
                ]);

                $response = $this->getJson('/api/v1/pages/search?q=test');

                $response->assertStatus(200);
                $data = $response->json('data');
                
                expect($data[0]['id'])->toBe($newPage->id); // Most recent first
                expect($data[1]['id'])->toBe($oldPage->id);
            });
        });
    });

    describe('Model business logic', function () {
        it('auto-generates slug from title on creation', function () {
            $page = Page::factory()->create(['title' => 'Test Page Title', 'slug' => '']);

            expect($page->slug)->toBe('test-page-title');
        });

        it('calculates full slug correctly', function () {
            $parent = Page::factory()->create(['slug' => 'parent']);
            $child = Page::factory()->childOf($parent)->create(['slug' => 'child']);
            $grandchild = Page::factory()->childOf($child)->create(['slug' => 'grandchild']);

            expect($grandchild->getFullSlug())->toBe('parent/child/grandchild');
        });

        it('generates breadcrumb correctly', function () {
            $parent = Page::factory()->create(['title' => 'Parent', 'slug' => 'parent']);
            $child = Page::factory()->childOf($parent)->create(['title' => 'Child', 'slug' => 'child']);

            $breadcrumb = $child->getBreadcrumb();

            expect($breadcrumb)->toHaveCount(2);
            expect($breadcrumb[0]['title'])->toBe('Parent');
            expect($breadcrumb[1]['title'])->toBe('Child');
        });

        it('identifies home page correctly', function () {
            $homePage = Page::factory()->create(['slug' => 'home']);
            $routePage = Page::factory()->create(['route' => 'home']);
            $normalPage = Page::factory()->create(['slug' => 'normal']);

            expect($homePage->isHomePage())->toBe(true);
            expect($routePage->isHomePage())->toBe(true);
            expect($normalPage->isHomePage())->toBe(false);
        });

        it('validates publishing requirements', function () {
            $validPage = Page::factory()->create(['title' => 'Valid', 'slug' => 'valid']);
            $invalidPage = Page::factory()->create(['title' => '', 'slug' => '']);

            expect($validPage->canBePublished())->toBe(false); // No components
            expect($invalidPage->canBePublished())->toBe(false); // No title/slug
        });

        it('has correct template labels', function () {
            $page = Page::factory()->create(['template' => 'landing']);

            expect($page->getTemplateLabel())->toBe('Página de Aterrizaje');
        });

        it('sets cache duration based on template', function () {
            $landingPage = Page::factory()->make(['template' => 'landing']);
            $landingPage->save();

            $articlePage = Page::factory()->make(['template' => 'article_list']);
            $articlePage->save();

            expect($landingPage->cache_duration)->toBe(30);
            expect($articlePage->cache_duration)->toBe(15);
        });
    });
});
