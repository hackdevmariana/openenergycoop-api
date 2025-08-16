<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Article;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, ApiTestHelpers;

    protected User $user;
    protected User $adminUser;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
    }

    #[Test]
    public function it_can_list_active_categories()
    {
        // Limpiar datos existentes
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        // Crear categorías de prueba
        Category::factory()->active()->count(3)->create();
        Category::factory()->inactive()->count(2)->create(); // No deben aparecer

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
            'id',
            'name',
            'slug',
            'description',
            'color',
            'icon',
            'parent_id',
            'sort_order',
            'is_active',
            'category_type',
            'language',
            'parent',
            'children',
            'organization'
                ]
            ]
        ]);
        
        // Solo deben aparecer las categorías activas
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $category) {
            $this->assertTrue((bool)$category['is_active']);
        }
    }

    #[Test]
    public function it_can_filter_categories_by_parent_id()
    {
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        $parentCategory = Category::factory()->active()->create();
        $childCategories = Category::factory()->active()->count(2)->create([
            'parent_id' => $parentCategory->id
        ]);
        $rootCategories = Category::factory()->active()->count(3)->create([
            'parent_id' => null
        ]);

        // Filtrar por categorías hijas
        $response = $this->getJson("/api/v1/categories?parent_id={$parentCategory->id}");
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // Filtrar por categorías raíz
        $response = $this->getJson('/api/v1/categories?parent_id=null');
        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data')); // 3 + 1 padre
    }

    #[Test]
    public function it_can_filter_categories_by_language()
    {
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        Category::factory()->active()->count(3)->create(['language' => 'es']);
        Category::factory()->active()->count(2)->create(['language' => 'en']);

        $response = $this->getJson('/api/v1/categories?language=es');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertCollectionFiltered($response->json('data'), 'language', 'es');
    }

    #[Test]
    public function it_can_filter_categories_by_type()
    {
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        Category::factory()->active()->count(3)->create(['category_type' => 'article']);
        Category::factory()->active()->count(2)->create(['category_type' => 'document']);

        $response = $this->getJson('/api/v1/categories?type=article');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertCollectionFiltered($response->json('data'), 'category_type', 'article');
    }

    #[Test]
    public function it_orders_categories_by_sort_order_and_name()
    {
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        // Crear categorías con diferentes sort_order
        $category1 = Category::factory()->active()->create([
            'name' => 'Zebra',
            'sort_order' => 3
        ]);
        
        $category2 = Category::factory()->active()->create([
            'name' => 'Alpha',
            'sort_order' => 1
        ]);
        
        $category3 = Category::factory()->active()->create([
            'name' => 'Beta',
            'sort_order' => 1
        ]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Verificar orden: sort_order ascendente, luego name ascendente
        $this->assertEquals($category2->id, $data[0]['id']); // Alpha (sort_order 1)
        $this->assertEquals($category3->id, $data[1]['id']); // Beta (sort_order 1)
        $this->assertEquals($category1->id, $data[2]['id']); // Zebra (sort_order 3)
    }

    #[Test]
    public function it_can_create_category()
    {
        Sanctum::actingAs($this->adminUser);

        $categoryData = [
            'name' => 'Nueva Categoría',
            'description' => 'Descripción de la categoría',
            'category_type' => 'article',
            'language' => 'es',
            'color' => '#FF0000',
            'icon' => 'heroicon-folder'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => 'Nueva Categoría',
                'slug' => 'nueva-categoria',
                'description' => 'Descripción de la categoría'
            ],
            'message' => 'Categoría creada exitosamente'
        ]);
        
        $this->assertDatabaseHas('categories', [
            'name' => 'Nueva Categoría',
            'slug' => 'nueva-categoria'
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_category()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/categories', []);

        $this->assertValidationErrors($response, ['name']);
    }

    #[Test]
    public function it_validates_color_format_when_creating_category()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Test Category',
            'color' => 'invalid-color'
        ]);

        $this->assertValidationErrors($response, ['color']);
    }

    #[Test]
    public function it_validates_parent_exists_when_creating_category()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Test Category',
            'parent_id' => 99999
        ]);

        $this->assertValidationErrors($response, ['parent_id']);
    }

    #[Test]
    public function it_auto_generates_slug_when_creating_category()
    {
        Sanctum::actingAs($this->adminUser);

        $categoryData = [
            'name' => 'Categoría con Caracteres Especiales',
            'description' => 'Test category'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(201);
        $category = Category::where('name', 'Categoría con Caracteres Especiales')->first();
        $this->assertEquals('categoria-con-caracteres-especiales', $category->slug);
    }

    #[Test]
    public function it_can_create_child_category()
    {
        Sanctum::actingAs($this->adminUser);
        
        $parentCategory = Category::factory()->active()->create();

        $childData = [
            'name' => 'Subcategoría',
            'parent_id' => $parentCategory->id
        ];

        $response = $this->postJson('/api/v1/categories', $childData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', [
            'name' => 'Subcategoría',
            'parent_id' => $parentCategory->id
        ]);
    }

    #[Test]
    public function it_can_show_active_category()
    {
        $category = Category::factory()->active()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertResourceStructure($response, [
            'id',
            'name',
            'slug',
            'description',
            'color',
            'icon',
            'parent_id',
            'sort_order',
            'is_active',
            'category_type',
            'language',
            'parent',
            'children',
            'organization'
        ]);
        
        $response->assertJson([
            'data' => [
                'id' => $category->id,
                'name' => $category->name
            ]
        ]);
    }

    #[Test]
    public function it_returns_404_for_inactive_category()
    {
        $category = Category::factory()->inactive()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Categoría no encontrada'
        ]);
    }

    #[Test]
    public function it_can_update_category()
    {
        Sanctum::actingAs($this->adminUser);
        
        $category = Category::factory()->active()->create([
            'name' => 'Nombre Original',
            'description' => 'Descripción original'
        ]);

        $updateData = [
            'name' => 'Nombre Actualizado',
            'description' => 'Descripción actualizada',
            'color' => '#00FF00'
        ];

        $response = $this->putJson("/api/v1/categories/{$category->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'name' => 'Nombre Actualizado',
                'description' => 'Descripción actualizada',
                'color' => '#00FF00'
            ],
            'message' => 'Categoría actualizada exitosamente'
        ]);
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Nombre Actualizado'
        ]);
    }

    #[Test]
    public function it_prevents_circular_reference_when_updating_parent()
    {
        Sanctum::actingAs($this->adminUser);
        
        $parentCategory = Category::factory()->active()->create();
        $childCategory = Category::factory()->active()->create([
            'parent_id' => $parentCategory->id
        ]);

        // Intentar hacer que el padre sea hijo de su propio hijo
        $response = $this->putJson("/api/v1/categories/{$parentCategory->id}", [
            'parent_id' => $childCategory->id
        ]);

        $this->assertValidationErrors($response, ['parent_id']);
    }

    #[Test]
    public function it_can_delete_empty_category()
    {
        Sanctum::actingAs($this->adminUser);
        
        $category = Category::factory()->active()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Categoría eliminada exitosamente'
        ]);
        
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    #[Test]
    public function it_prevents_deletion_of_category_with_children()
    {
        Sanctum::actingAs($this->adminUser);
        
        $parentCategory = Category::factory()->active()->create();
        $childCategory = Category::factory()->active()->create([
            'parent_id' => $parentCategory->id
        ]);

        $response = $this->deleteJson("/api/v1/categories/{$parentCategory->id}");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'No se puede eliminar una categoría que tiene subcategorías'
        ]);
        
        $this->assertDatabaseHas('categories', ['id' => $parentCategory->id]);
    }

    #[Test]
    public function it_prevents_deletion_of_category_with_articles()
    {
        Sanctum::actingAs($this->adminUser);
        
        $category = Category::factory()->active()->create();
        Article::factory()->create(['category_id' => $category->id]);

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'No se puede eliminar una categoría que tiene contenido asociado'
        ]);
        
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    #[Test]
    public function it_can_get_categories_tree()
    {
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        // Crear estructura jerárquica
        $parentCategory = Category::factory()->active()->create([
            'name' => 'Padre',
            'parent_id' => null,
            'sort_order' => 1
        ]);
        
        $childCategory1 = Category::factory()->active()->create([
            'name' => 'Hijo 1',
            'parent_id' => $parentCategory->id,
            'sort_order' => 1
        ]);
        
        $childCategory2 = Category::factory()->active()->create([
            'name' => 'Hijo 2',
            'parent_id' => $parentCategory->id,
            'sort_order' => 2
        ]);
        
        $grandchildCategory = Category::factory()->active()->create([
            'name' => 'Nieto',
            'parent_id' => $childCategory1->id,
            'sort_order' => 1
        ]);

        $response = $this->getJson('/api/v1/categories/tree');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Debe mostrar solo categorías raíz con sus hijos cargados
        $this->assertCount(1, $data);
        $this->assertEquals('Padre', $data[0]['name']);
        $this->assertCount(2, $data[0]['children']);
        
        // Verificar que el primer hijo tiene un nieto
        $firstChild = collect($data[0]['children'])->firstWhere('name', 'Hijo 1');
        $this->assertNotNull($firstChild);
        $this->assertCount(1, $firstChild['children']);
        $this->assertEquals('Nieto', $firstChild['children'][0]['name']);
    }

    #[Test]
    public function it_can_filter_tree_by_language()
    {
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        Category::factory()->active()->count(2)->create([
            'parent_id' => null,
            'language' => 'es'
        ]);
        
        Category::factory()->active()->count(1)->create([
            'parent_id' => null,
            'language' => 'en'
        ]);

        $response = $this->getJson('/api/v1/categories/tree?language=es');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $category) {
            $this->assertEquals('es', $category['language']);
        }
    }

    #[Test]
    public function it_can_filter_tree_by_type()
    {
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        Category::factory()->active()->count(2)->create([
            'parent_id' => null,
            'category_type' => 'article'
        ]);
        
        Category::factory()->active()->count(1)->create([
            'parent_id' => null,
            'category_type' => 'document'
        ]);

        $response = $this->getJson('/api/v1/categories/tree?type=article');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $category) {
            $this->assertEquals('article', $category['category_type']);
        }
    }

    #[Test]
    public function it_requires_authentication_for_create_update_delete()
    {
        $category = Category::factory()->active()->create();
        
        $endpoints = [
            'POST' => [
                '/api/v1/categories'
            ],
            'PUT' => [
                "/api/v1/categories/{$category->id}"
            ],
            'DELETE' => [
                "/api/v1/categories/{$category->id}"
            ]
        ];

        $this->assertEndpointsRequireAuth($endpoints);
    }

    #[Test]
    public function it_includes_relationships_in_responses()
    {
        $parentCategory = Category::factory()->active()->create();
        $childCategory = Category::factory()->active()->create([
            'parent_id' => $parentCategory->id
        ]);

        $response = $this->getJson("/api/v1/categories/{$childCategory->id}");

        $response->assertStatus(200);
        $this->assertIncludesRelationships($response, ['parent', 'children', 'organization']);
    }

    #[Test]
    public function it_handles_unique_slug_validation()
    {
        Sanctum::actingAs($this->adminUser);
        
        $existingCategory = Category::factory()->active()->create([
            'slug' => 'test-category'
        ]);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);

        $this->assertValidationErrors($response, ['slug']);
    }

    #[Test]
    public function it_allows_same_slug_when_updating_same_category()
    {
        Sanctum::actingAs($this->adminUser);
        
        $category = Category::factory()->active()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug'
        ]);

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Updated Name',
            'slug' => 'original-slug' // Mismo slug
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function it_can_handle_multiple_filters_simultaneously()
    {
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        $parentCategory = Category::factory()->active()->create([
            'language' => 'es',
            'category_type' => 'article'
        ]);
        
        Category::factory()->active()->create([
            'parent_id' => $parentCategory->id,
            'language' => 'es',
            'category_type' => 'article'
        ]);
        
        Category::factory()->active()->create([
            'parent_id' => $parentCategory->id,
            'language' => 'en',
            'category_type' => 'article'
        ]);
        
        Category::factory()->active()->create([
            'parent_id' => $parentCategory->id,
            'language' => 'es',
            'category_type' => 'document'
        ]);

        $response = $this->getJson("/api/v1/categories?parent_id={$parentCategory->id}&language=es&type=article");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    #[Test]
    public function it_returns_empty_result_for_invalid_filters()
    {
        Article::where('category_id', '>', 0)->delete();
        Category::query()->delete();
        
        Category::factory()->active()->count(3)->create();

        $response = $this->getJson('/api/v1/categories?parent_id=999999');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    public function it_validates_language_values()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Test Category',
            'language' => 'invalid-language'
        ]);

        $this->assertValidationErrors($response, ['language']);
    }

    #[Test]
    public function it_sets_default_values_when_creating_category()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Simple Category'
        ]);

        $response->assertStatus(201);
        $category = Category::where('name', 'Simple Category')->first();
        
        $this->assertEquals('es', $category->language);
        $this->assertTrue($category->is_active);
        $this->assertEquals(0, $category->sort_order);
    }
}
