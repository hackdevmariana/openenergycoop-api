<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Image;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    Category::query()->delete();
    
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
});

describe('CategoryController', function () {
    describe('Index endpoint', function () {
        it('can get active categories without authentication', function () {
            $activeCategory = Category::factory()->active()->create();
            $inactiveCategory = Category::factory()->inactive()->create();

            $response = $this->getJson('/api/v1/categories');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'slug',
                                'description',
                                'category_type',
                                'color',
                                'icon',
                                'language',
                                'is_active',
                                'sort_order',
                                'parent_id',
                                'content_count'
                            ]
                        ]
                    ]);

            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($activeCategory->id);
        });

        it('can filter categories by parent_id', function () {
            $parent = Category::factory()->active()->create();
            $child = Category::factory()->active()->childOf($parent)->create();
            $other = Category::factory()->active()->create();

            $response = $this->getJson("/api/v1/categories?parent_id={$parent->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($child->id);
        });

        it('can filter root categories with parent_id=null', function () {
            $parent = Category::factory()->active()->root()->create();
            $child = Category::factory()->active()->childOf($parent)->create();

            $response = $this->getJson('/api/v1/categories?parent_id=null');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($parent->id);
        });

        it('can filter categories by language', function () {
            $spanishCategory = Category::factory()->active()->spanish()->create();
            $englishCategory = Category::factory()->active()->english()->create();

            $response = $this->getJson('/api/v1/categories?language=es');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($spanishCategory->id);
        });

        it('can filter categories by type', function () {
            $articleCategory = Category::factory()->active()->article()->create();
            $documentCategory = Category::factory()->active()->document()->create();

            $response = $this->getJson('/api/v1/categories?type=article');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($articleCategory->id);
        });

        it('orders categories by sort_order and name', function () {
            $categoryC = Category::factory()->active()->create([
                'name' => 'Category C',
                'sort_order' => 2
            ]);
            $categoryA = Category::factory()->active()->create([
                'name' => 'Category A', 
                'sort_order' => 1
            ]);
            $categoryB = Category::factory()->active()->create([
                'name' => 'Category B',
                'sort_order' => 1
            ]);

            $response = $this->getJson('/api/v1/categories');

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data[0]['id'])->toBe($categoryA->id); // sort_order 1, name A
            expect($data[1]['id'])->toBe($categoryB->id); // sort_order 1, name B
            expect($data[2]['id'])->toBe($categoryC->id); // sort_order 2
        });

        it('only returns active categories', function () {
            Category::factory()->active()->count(3)->create();
            Category::factory()->inactive()->count(2)->create();

            $response = $this->getJson('/api/v1/categories');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(3);
        });
    });

    describe('Store endpoint', function () {
        it('can create a new category when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $categoryData = [
                'name' => 'Nueva Categoría',
                'description' => 'Descripción de la nueva categoría',
                'category_type' => 'article',
                'color' => '#FF5733',
                'icon' => 'heroicon-o-folder',
                'language' => 'es',
                'organization_id' => $this->organization->id,
            ];

            $response = $this->postJson('/api/v1/categories', $categoryData);

            $response->assertStatus(201)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'name',
                            'slug',
                            'description',
                            'category_type',
                            'color',
                            'icon',
                            'organization'
                        ],
                        'message'
                    ])
                    ->assertJson([
                        'data' => [
                            'name' => 'Nueva Categoría',
                            'slug' => 'nueva-categoria',
                            'category_type' => 'article',
                            'color' => '#FF5733',
                            'language' => 'es',
                        ],
                        'message' => 'Categoría creada exitosamente'
                    ]);

            $this->assertDatabaseHas('categories', [
                'name' => 'Nueva Categoría',
                'slug' => 'nueva-categoria',
                'category_type' => 'article',
                'organization_id' => $this->organization->id,
            ]);
        });

        it('auto-generates slug from name', function () {
            Sanctum::actingAs($this->user);
            
            $categoryData = [
                'name' => 'Categoría con Espacios y Acentos',
                'category_type' => 'article',
            ];

            $response = $this->postJson('/api/v1/categories', $categoryData);

            $response->assertStatus(201);
            expect($response->json('data.slug'))->toBe('categoria-con-espacios-y-acentos');
        });

        it('validates required fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/categories', []);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['name']);
        });

        it('validates unique slug', function () {
            Sanctum::actingAs($this->user);
            
            Category::factory()->create(['slug' => 'existing-slug']);

            $response = $this->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'slug' => 'existing-slug',
                'category_type' => 'article',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['slug']);
        });

        it('validates parent category exists', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'parent_id' => 999,
                'category_type' => 'article',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['parent_id']);
        });

        it('validates color format', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'color' => 'invalid-color',
                'category_type' => 'article',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['color']);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'category_type' => 'article',
            ]);

            $response->assertStatus(401);
        });

        it('sets default values correctly', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/categories', [
                'name' => 'Test Category',
            ]);

            $response->assertStatus(201);
            $data = $response->json('data');
            
            expect($data['language'])->toBe('es');
            expect($data['is_active'])->toBe(true);
            expect($data['sort_order'])->toBeInt();
        });
    });

    describe('Show endpoint', function () {
        it('can show an active category', function () {
            $category = Category::factory()->active()->create();

            $response = $this->getJson("/api/v1/categories/{$category->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $category->id,
                            'name' => $category->name,
                            'is_active' => true,
                        ]
                    ]);
        });

        it('returns 404 for inactive categories', function () {
            $category = Category::factory()->inactive()->create();

            $response = $this->getJson("/api/v1/categories/{$category->id}");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Categoría no encontrada']);
        });

        it('includes relationships and counts', function () {
            $parent = Category::factory()->active()->create();
            $category = Category::factory()->active()->childOf($parent)->create();
            $child = Category::factory()->active()->childOf($category)->create();
            
            // Create some content
            Article::factory()->create(['category_id' => $category->id]);
            Image::factory()->create(['category_id' => $category->id]);

            $response = $this->getJson("/api/v1/categories/{$category->id}");

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data['parent'])->not->toBeNull();
            expect($data['children'])->toBeArray();
            expect($data['organization'])->not->toBeNull();
            expect($data['articles_count'])->toBe(1);
            expect($data['images_count'])->toBe(1);
        });
    });

    describe('Update endpoint', function () {
        it('can update a category when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $category = Category::factory()->create();

            $updateData = [
                'name' => 'Updated Category Name',
                'description' => 'Updated description',
                'color' => '#123456',
                'is_active' => false,
            ];

            $response = $this->putJson("/api/v1/categories/{$category->id}", $updateData);

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'name' => 'Updated Category Name',
                            'description' => 'Updated description',
                            'color' => '#123456',
                            'is_active' => false,
                        ],
                        'message' => 'Categoría actualizada exitosamente'
                    ]);
        });

        it('prevents setting category as its own parent', function () {
            Sanctum::actingAs($this->user);
            
            $category = Category::factory()->create();

            $response = $this->putJson("/api/v1/categories/{$category->id}", [
                'parent_id' => $category->id
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['parent_id']);
        });

        it('prevents circular hierarchy', function () {
            Sanctum::actingAs($this->user);
            
            $grandparent = Category::factory()->create();
            $parent = Category::factory()->childOf($grandparent)->create();
            $child = Category::factory()->childOf($parent)->create();

            // Try to make grandparent a child of child (circular)
            $response = $this->putJson("/api/v1/categories/{$grandparent->id}", [
                'parent_id' => $child->id
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['parent_id']);
        });

        it('requires authentication', function () {
            $category = Category::factory()->create();

            $response = $this->putJson("/api/v1/categories/{$category->id}", [
                'name' => 'Updated Name'
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Destroy endpoint', function () {
        it('can delete a category with no content when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $category = Category::factory()->create();

            $response = $this->deleteJson("/api/v1/categories/{$category->id}");

            $response->assertStatus(200)
                    ->assertJson(['message' => 'Categoría eliminada exitosamente']);

            $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        });

        it('prevents deletion of category with children', function () {
            Sanctum::actingAs($this->user);
            
            $parent = Category::factory()->create();
            $child = Category::factory()->childOf($parent)->create();

            $response = $this->deleteJson("/api/v1/categories/{$parent->id}");

            $response->assertStatus(422)
                    ->assertJson(['message' => 'No se puede eliminar una categoría que tiene subcategorías']);
        });

        it('prevents deletion of category with articles', function () {
            Sanctum::actingAs($this->user);
            
            $category = Category::factory()->create();
            Article::factory()->create(['category_id' => $category->id]);

            $response = $this->deleteJson("/api/v1/categories/{$category->id}");

            $response->assertStatus(422)
                    ->assertJson(['message' => 'No se puede eliminar una categoría que tiene contenido asociado']);
        });

        it('requires authentication', function () {
            $category = Category::factory()->create();

            $response = $this->deleteJson("/api/v1/categories/{$category->id}");

            $response->assertStatus(401);
        });
    });

    describe('Tree endpoint', function () {
        it('returns tree structure of categories', function () {
            $root1 = Category::factory()->active()->root()->create(['name' => 'Root 1']);
            $root2 = Category::factory()->active()->root()->create(['name' => 'Root 2']);
            $child1 = Category::factory()->active()->childOf($root1)->create(['name' => 'Child 1']);

            $response = $this->getJson('/api/v1/categories/tree');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'children'
                            ]
                        ]
                    ]);

            $data = $response->json('data');
            expect($data)->toHaveCount(2); // Only root categories
        });

        it('can filter tree by language', function () {
            $spanishRoot = Category::factory()->active()->root()->spanish()->create();
            $englishRoot = Category::factory()->active()->root()->english()->create();

            $response = $this->getJson('/api/v1/categories/tree?language=es');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($spanishRoot->id);
        });

        it('can filter tree by type', function () {
            $articleRoot = Category::factory()->active()->root()->article()->create();
            $documentRoot = Category::factory()->active()->root()->document()->create();

            $response = $this->getJson('/api/v1/categories/tree?type=article');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($articleRoot->id);
        });
    });

    describe('Model business logic', function () {
        it('auto-generates slug on creation', function () {
            $category = Category::factory()->make(['name' => 'Amazing Category', 'slug' => '']);
            $category->save();

            expect($category->slug)->toBe('amazing-category');
        });

        it('auto-sets sort order on creation', function () {
            $parent = Category::factory()->create();
            
            $child1 = Category::factory()->childOf($parent)->create(['sort_order' => null]);
            $child2 = Category::factory()->childOf($parent)->create(['sort_order' => null]);

            expect($child2->sort_order)->toBeGreaterThan($child1->sort_order);
        });

        it('prevents circular references', function () {
            $category1 = Category::factory()->create();
            $category2 = Category::factory()->childOf($category1)->create();

            expect(function () use ($category1, $category2) {
                $category1->parent_id = $category2->id;
                $category1->save();
            })->toThrow(\Exception::class);
        });

        it('prevents self-parent relationship', function () {
            $category = Category::factory()->create();

            expect(function () use ($category) {
                $category->parent_id = $category->id;
                $category->save();
            })->toThrow(\InvalidArgumentException::class);
        });

        it('calculates full name correctly', function () {
            $grandparent = Category::factory()->create(['name' => 'Grandparent']);
            $parent = Category::factory()->childOf($grandparent)->create(['name' => 'Parent']);
            $child = Category::factory()->childOf($parent)->create(['name' => 'Child']);

            expect($child->getFullName())->toBe('Grandparent > Parent > Child');
        });

        it('generates breadcrumb correctly', function () {
            $grandparent = Category::factory()->create(['name' => 'Grandparent', 'slug' => 'grandparent']);
            $parent = Category::factory()->childOf($grandparent)->create(['name' => 'Parent', 'slug' => 'parent']);
            $child = Category::factory()->childOf($parent)->create(['name' => 'Child', 'slug' => 'child']);

            $breadcrumb = $child->getBreadcrumb();
            
            expect($breadcrumb)->toHaveCount(3);
            expect($breadcrumb[0]['name'])->toBe('Grandparent');
            expect($breadcrumb[1]['name'])->toBe('Parent');
            expect($breadcrumb[2]['name'])->toBe('Child');
        });

        it('gets type label correctly', function () {
            $category = Category::factory()->article()->create();
            expect($category->getTypeLabel())->toBe('Artículos');

            $category = Category::factory()->create(['category_type' => 'unknown']);
            expect($category->getTypeLabel())->toBe('unknown');
        });

        it('detects children correctly', function () {
            $parent = Category::factory()->create();
            $child = Category::factory()->childOf($parent)->create();

            expect($parent->hasChildren())->toBe(true);
            expect($child->hasChildren())->toBe(false);
        });

        it('detects active children correctly', function () {
            $parent = Category::factory()->create();
            $activeChild = Category::factory()->active()->childOf($parent)->create();
            Category::factory()->inactive()->childOf($parent)->create();

            expect($parent->hasActiveChildren())->toBe(true);

            $activeChild->update(['is_active' => false]);
            $parent->refresh();
            expect($parent->hasActiveChildren())->toBe(false);
        });

        it('calculates depth correctly', function () {
            $root = Category::factory()->create();
            $level1 = Category::factory()->childOf($root)->create();
            $level2 = Category::factory()->childOf($level1)->create();

            expect($root->getDepth())->toBe(0);
            expect($level1->getDepth())->toBe(1);
            expect($level2->getDepth())->toBe(2);
        });

        it('gets all children recursively', function () {
            $root = Category::factory()->create();
            $child1 = Category::factory()->childOf($root)->create();
            $child2 = Category::factory()->childOf($root)->create();
            $grandchild = Category::factory()->childOf($child1)->create();

            $allChildren = $root->getAllChildren();
            expect($allChildren)->toHaveCount(3);
            expect($allChildren->pluck('id')->sort()->values()->all())
                ->toBe([$child1->id, $child2->id, $grandchild->id]);
        });

        it('gets all parents', function () {
            $grandparent = Category::factory()->create();
            $parent = Category::factory()->childOf($grandparent)->create();
            $child = Category::factory()->childOf($parent)->create();

            $allParents = $child->getAllParents();
            expect($allParents)->toHaveCount(2);
            expect($allParents->first()->id)->toBe($grandparent->id);
            expect($allParents->last()->id)->toBe($parent->id);
        });

        it('detects ancestor relationships', function () {
            $grandparent = Category::factory()->create();
            $parent = Category::factory()->childOf($grandparent)->create();
            $child = Category::factory()->childOf($parent)->create();

            expect($grandparent->isAncestorOf($child))->toBe(true);
            expect($parent->isAncestorOf($child))->toBe(true);
            expect($child->isAncestorOf($grandparent))->toBe(false);
        });

        it('detects descendant relationships', function () {
            $grandparent = Category::factory()->create();
            $parent = Category::factory()->childOf($grandparent)->create();
            $child = Category::factory()->childOf($parent)->create();

            expect($child->isDescendantOf($grandparent))->toBe(true);
            expect($child->isDescendantOf($parent))->toBe(true);
            expect($grandparent->isDescendantOf($child))->toBe(false);
        });

        it('moves to new position correctly', function () {
            $parent = Category::factory()->create();
            $cat1 = Category::factory()->childOf($parent)->withOrder(1)->create();
            $cat2 = Category::factory()->childOf($parent)->withOrder(2)->create();
            $cat3 = Category::factory()->childOf($parent)->withOrder(3)->create();

            // Move cat3 to position 1
            $cat3->moveToPosition(1);

            $cat1->refresh();
            $cat2->refresh();
            $cat3->refresh();

            expect($cat3->sort_order)->toBe(1);
            expect($cat1->sort_order)->toBe(2);
            expect($cat2->sort_order)->toBe(3);
        });

        it('generates icon HTML correctly', function () {
            $category1 = Category::factory()->create(['icon' => 'heroicon-o-folder']);
            expect($category1->getIconHtml())->toContain('<x-heroicon-o-folder');

            $category2 = Category::factory()->create(['icon' => 'fa-folder']);
            expect($category2->getIconHtml())->toContain('<i class="fas fa-folder');

            $category3 = Category::factory()->create(['icon' => 'custom-icon']);
            expect($category3->getIconHtml())->toContain('<i class="custom-icon');

            $category4 = Category::factory()->create(['icon' => null]);
            expect($category4->getIconHtml())->toBe('');
        });

        it('generates color style correctly', function () {
            $category1 = Category::factory()->create(['color' => '#FF5733']);
            expect($category1->getColorStyle())->toBe('background-color: #FF5733;');

            $category2 = Category::factory()->create(['color' => null]);
            expect($category2->getColorStyle())->toBe('');
        });
    });

    describe('Edge cases and validation', function () {
        it('handles categories with special characters', function () {
            Sanctum::actingAs($this->user);

            $categoryData = [
                'name' => 'Categoría con Ñ & Símbolos Éspeciáles',
                'description' => '¡Descripción con acentos!',
                'category_type' => 'article',
            ];

            $response = $this->postJson('/api/v1/categories', $categoryData);

            $response->assertStatus(201);
            expect($response->json('data.slug'))->toBe('categoria-con-n-simbolos-especiales');
        });

        it('handles very deep hierarchies', function () {
            $categories = [];
            $parent = null;

            // Create 5-level deep hierarchy
            for ($i = 0; $i < 5; $i++) {
                $category = Category::factory()
                    ->when($parent, fn($factory) => $factory->childOf($parent))
                    ->create(['name' => "Level {$i}"]);
                
                $categories[] = $category;
                $parent = $category;
            }

            $deepest = end($categories);
            expect($deepest->getDepth())->toBe(4);
            expect($deepest->getAllParents())->toHaveCount(4);
        });

        it('handles empty parent_id values', function () {
            $response = $this->getJson('/api/v1/categories?parent_id=');

            $response->assertStatus(200);
            // Should not filter by parent_id when empty
        });

        it('handles unknown category types gracefully', function () {
            $category = Category::factory()->create(['category_type' => 'unknown_type']);
            
            $response = $this->getJson("/api/v1/categories/{$category->id}");

            $response->assertStatus(200);
            expect($response->json('data.category_type'))->toBe('unknown_type');
        });

        it('validates sort_order is positive', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/categories', [
                'name' => 'Test Category',
                'sort_order' => -1,
                'category_type' => 'article',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['sort_order']);
        });
    });
});