<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase, ApiTestHelpers;

    protected User $user;
    protected User $adminUser;
    protected Organization $organization;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->category = Category::factory()->create();
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
    }

    #[Test]
    public function it_can_list_published_articles()
    {
        // Limpiar datos existentes
        Article::truncate();
        
        // Crear artículos de prueba
        Article::factory()->published()->count(5)->create();
        Article::factory()->draft()->count(3)->create(); // No deben aparecer

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200);
        $this->assertResourceCollectionStructure($response, [
            'id',
            'title',
            'slug',
            'excerpt',
            'featured_image',
            'published_at',
            'category',
            'author',
            'organization'
        ]);
        
        // Solo deben aparecer los publicados
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_articles_by_category()
    {
        Article::truncate();
        
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        Article::factory()->published()->count(3)->create(['category_id' => $category1->id]);
        Article::factory()->published()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->getJson("/api/v1/articles?category_id={$category1->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertCollectionFiltered($response->json('data'), 'category.id', $category1->id);
    }

    #[Test]
    public function it_can_filter_featured_articles()
    {
        Article::truncate();
        
        Article::factory()->published()->featured()->count(2)->create();
        Article::factory()->published()->count(3)->create(['featured' => false]);

        $response = $this->getJson('/api/v1/articles?featured=true');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $article) {
            $this->assertTrue($article['featured']);
        }
    }

    #[Test]
    public function it_can_search_articles()
    {
        Article::truncate();
        
        Article::factory()->published()->create([
            'title' => 'Energía Solar en España',
            'excerpt' => 'Todo sobre energía solar'
        ]);
        
        Article::factory()->published()->create([
            'title' => 'Energía Eólica',
            'text' => 'Contenido sobre energía eólica'
        ]);
        
        Article::factory()->published()->create([
            'title' => 'Hidroeléctrica',
            'excerpt' => 'Artículo sobre agua'
        ]);

        $response = $this->getJson('/api/v1/articles?search=energía');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_articles_by_language()
    {
        Article::truncate();
        
        Article::factory()->published()->count(3)->create(['language' => 'es']);
        Article::factory()->published()->count(2)->create(['language' => 'en']);

        $response = $this->getJson('/api/v1/articles?language=es');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertCollectionFiltered($response->json('data'), 'language', 'es');
    }

    #[Test]
    public function it_orders_articles_correctly()
    {
        Article::truncate();
        
        // Crear artículos con diferentes fechas y featured status
        $featured = Article::factory()->published()->featured()->create([
            'published_at' => now()->subDays(1)
        ]);
        
        $recent = Article::factory()->published()->create([
            'featured' => false,
            'published_at' => now()
        ]);
        
        $old = Article::factory()->published()->create([
            'featured' => false,
            'published_at' => now()->subDays(2)
        ]);

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // El featured debe aparecer primero, luego por fecha desc
        $this->assertEquals($featured->id, $data[0]['id']);
        $this->assertEquals($recent->id, $data[1]['id']);
        $this->assertEquals($old->id, $data[2]['id']);
    }

    #[Test]
    public function it_can_create_article()
    {
        Sanctum::actingAs($this->adminUser);

        $articleData = [
            'title' => 'Nuevo Artículo de Prueba',
            'text' => 'Contenido del artículo de prueba',
            'excerpt' => 'Resumen del artículo',
            'category_id' => $this->category->id,
            'language' => 'es',
            'status' => 'draft'
        ];

        $response = $this->postJson('/api/v1/articles', $articleData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'title' => 'Nuevo Artículo de Prueba',
                'text' => 'Contenido del artículo de prueba'
            ],
            'message' => 'Artículo creado exitosamente'
        ]);
        
        $this->assertDatabaseHas('articles', [
            'title' => 'Nuevo Artículo de Prueba',
            'author_id' => $this->adminUser->id
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_article()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/articles', []);

        $this->assertValidationErrors($response, ['title', 'text']);
    }

    #[Test]
    public function it_auto_generates_slug_when_creating_article()
    {
        Sanctum::actingAs($this->adminUser);

        $articleData = [
            'title' => 'Artículo con Título Especial',
            'text' => 'Contenido del artículo',
            'category_id' => $this->category->id
        ];

        $response = $this->postJson('/api/v1/articles', $articleData);

        $response->assertStatus(201);
        $article = Article::where('title', 'Artículo con Título Especial')->first();
        $this->assertEquals('articulo-con-titulo-especial', $article->slug);
    }

    #[Test]
    public function it_can_show_article_by_id()
    {
        $article = Article::factory()->published()->create();

        $response = $this->getJson("/api/v1/articles/{$article->id}");

        $response->assertStatus(200);
        $this->assertResourceStructure($response, [
            'id',
            'title',
            'text',
            'slug',
            'excerpt',
            'featured_image',
            'published_at',
            'category',
            'author',
            'organization',
            'comments'
        ]);
        
        $response->assertJson([
            'data' => [
                'id' => $article->id,
                'title' => $article->title
            ]
        ]);
    }

    #[Test]
    public function it_can_show_article_by_slug()
    {
        $article = Article::factory()->published()->create(['slug' => 'articulo-de-prueba']);

        $response = $this->getJson('/api/v1/articles/articulo-de-prueba');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $article->id,
                'slug' => 'articulo-de-prueba'
            ]
        ]);
    }

    #[Test]
    public function it_increments_views_when_showing_article()
    {
        $article = Article::factory()->published()->create(['number_of_views' => 5]);

        $response = $this->getJson("/api/v1/articles/{$article->id}");

        $response->assertStatus(200);
        $this->assertEquals(6, $article->fresh()->number_of_views);
    }

    #[Test]
    public function it_returns_404_for_non_existent_article()
    {
        $response = $this->getJson('/api/v1/articles/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_for_draft_article_in_public_endpoint()
    {
        $article = Article::factory()->draft()->create();

        $response = $this->getJson("/api/v1/articles/{$article->id}");

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_article()
    {
        Sanctum::actingAs($this->adminUser);
        
        $article = Article::factory()->create([
            'title' => 'Título Original',
            'text' => 'Contenido original'
        ]);

        $updateData = [
            'title' => 'Título Actualizado',
            'text' => 'Contenido actualizado',
            'excerpt' => 'Nuevo resumen'
        ];

        $response = $this->putJson("/api/v1/articles/{$article->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'title' => 'Título Actualizado',
                'text' => 'Contenido actualizado'
            ],
            'message' => 'Artículo actualizado exitosamente'
        ]);
        
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Título Actualizado'
        ]);
    }

    #[Test]
    public function it_can_delete_article()
    {
        Sanctum::actingAs($this->adminUser);
        
        $article = Article::factory()->create();

        $response = $this->deleteJson("/api/v1/articles/{$article->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Artículo eliminado exitosamente'
        ]);
        
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    #[Test]
    public function it_can_get_featured_articles()
    {
        Article::truncate();
        
        Article::factory()->published()->featured()->count(3)->create();
        Article::factory()->published()->count(2)->create(['featured' => false]);

        $response = $this->getJson('/api/v1/articles/featured');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $article) {
            $this->assertTrue($article['featured']);
        }
    }

    #[Test]
    public function it_can_limit_featured_articles()
    {
        Article::truncate();
        
        Article::factory()->published()->featured()->count(10)->create();

        $response = $this->getJson('/api/v1/articles/featured?limit=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    #[Test]
    public function it_can_get_recent_articles()
    {
        Article::truncate();
        
        Article::factory()->published()->count(5)->create();

        $response = $this->getJson('/api/v1/articles/recent');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
        
        // Verificar orden por fecha descendente
        $data = $response->json('data');
        $this->assertCollectionOrdered($data, 'published_at', 'desc');
    }

    #[Test]
    public function it_can_get_popular_articles()
    {
        Article::truncate();
        
        Article::factory()->published()->create([
            'number_of_views' => 100,
            'published_at' => now()->subDays(1)
        ]);
        Article::factory()->published()->create([
            'number_of_views' => 50,
            'published_at' => now()->subDays(2)
        ]);
        Article::factory()->published()->create([
            'number_of_views' => 200,
            'published_at' => now()->subDays(3)
        ]);

        $response = $this->getJson('/api/v1/articles/popular');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        // Verificar orden por vistas descendente
        $data = $response->json('data');
        $this->assertCollectionOrdered($data, 'number_of_views', 'desc');
    }

    #[Test]
    public function it_can_filter_popular_articles_by_period()
    {
        Article::truncate();
        
        // Artículo popular reciente
        Article::factory()->published()->create([
            'number_of_views' => 100,
            'published_at' => now()->subDays(5)
        ]);
        
        // Artículo popular antiguo
        Article::factory()->published()->create([
            'number_of_views' => 200,
            'published_at' => now()->subMonths(2)
        ]);

        $response = $this->getJson('/api/v1/articles/popular?period=month');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    #[Test]
    public function it_respects_per_page_limit()
    {
        Article::truncate();
        
        Article::factory()->published()->count(20)->create();

        $response = $this->getJson('/api/v1/articles?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
        
        // Verificar que no se pueda exceder el límite máximo
        $response = $this->getJson('/api/v1/articles?per_page=100');
        $response->assertStatus(200);
        $this->assertLessThanOrEqual(50, count($response->json('data')));
    }

    #[Test]
    public function it_requires_authentication_for_create_update_delete()
    {
        $article = Article::factory()->create();
        
        $endpoints = [
            'POST' => [
                '/api/v1/articles'
            ],
            'PUT' => [
                "/api/v1/articles/{$article->id}"
            ],
            'DELETE' => [
                "/api/v1/articles/{$article->id}"
            ]
        ];

        $this->assertEndpointsRequireAuth($endpoints);
    }

    #[Test]
    public function it_includes_relationships_in_responses()
    {
        $article = Article::factory()->published()->create();

        $response = $this->getJson("/api/v1/articles/{$article->id}");

        $response->assertStatus(200);
        $this->assertIncludesRelationships($response, ['category', 'author', 'organization']);
    }

    #[Test]
    public function it_handles_pagination_correctly()
    {
        Article::truncate();
        
        Article::factory()->published()->count(25)->create();

        $response = $this->getJson('/api/v1/articles?per_page=10');

        $this->assertPaginatedResponse($response, 10, 10);
    }

    #[Test]
    public function it_can_handle_multiple_filters_simultaneously()
    {
        Article::truncate();
        
        $category = Category::factory()->create();
        
        Article::factory()->published()->featured()->create([
            'category_id' => $category->id,
            'language' => 'es',
            'title' => 'Artículo con energía'
        ]);
        
        Article::factory()->published()->create([
            'category_id' => $category->id,
            'language' => 'es',
            'featured' => false,
            'title' => 'Otro artículo'
        ]);
        
        Article::factory()->published()->featured()->create([
            'category_id' => $category->id,
            'language' => 'en',
            'title' => 'Article with energy'
        ]);

        $response = $this->getJson("/api/v1/articles?category_id={$category->id}&language=es&featured=true&search=energía");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    #[Test]
    public function it_returns_empty_result_for_invalid_filters()
    {
        Article::truncate();
        
        Article::factory()->published()->count(3)->create();

        $response = $this->getJson('/api/v1/articles?category_id=999');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    public function it_validates_limit_parameters_in_special_endpoints()
    {
        // Test limits are respected and capped
        Article::factory()->published()->featured()->count(25)->create();

        $response = $this->getJson('/api/v1/articles/featured?limit=25');
        $response->assertStatus(200);
        $this->assertLessThanOrEqual(20, count($response->json('data')));

        $response = $this->getJson('/api/v1/articles/recent?limit=25');
        $response->assertStatus(200);
        $this->assertLessThanOrEqual(20, count($response->json('data')));
    }
}
