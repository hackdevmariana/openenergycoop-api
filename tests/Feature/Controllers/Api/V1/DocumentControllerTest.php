<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Document;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class DocumentControllerTest extends TestCase
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
    public function it_can_list_published_visible_documents()
    {
        // Limpiar datos existentes
        Document::query()->delete();
        
        // Crear documentos de prueba
        Document::factory()->published()->visible()->count(4)->create();
        Document::factory()->draft()->count(2)->create(); // No deben aparecer
        Document::factory()->published()->hidden()->count(1)->create(); // No debe aparecer

        $response = $this->getJson('/api/v1/documents');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'file_path',
                    'file_type',
                    'file_size',
                    'mime_type',
                    'visible',
                    'download_count',
                    'number_of_views',
                    'version',
                    'expires_at',
                    'requires_auth',
                    'allowed_roles',
                    'language',
                    'is_draft',
                    'published_at',
                    'uploaded_at',
                    'category',
                    'organization',
                    'uploaded_by',
                    'is_published',
                    'is_expired',
                    'download_url',
                    'file_type_label',
                    'formatted_file_size'
                ]
            ],
            'links',
            'meta'
        ]);
        
        // Solo deben aparecer los documentos publicados y visibles
        $this->assertCount(4, $response->json('data'));
        
        foreach ($response->json('data') as $document) {
            $this->assertTrue((bool)$document['is_published']);
            $this->assertTrue((bool)$document['visible']);
        }
    }

    #[Test]
    public function it_orders_documents_by_uploaded_at_desc()
    {
        Document::query()->delete();
        
        // Crear documentos con diferentes fechas de subida
        $document1 = Document::factory()->published()->visible()->create([
            'uploaded_at' => now()->subDays(3),
            'title' => 'Documento Antiguo'
        ]);
        
        $document2 = Document::factory()->published()->visible()->create([
            'uploaded_at' => now()->subDays(1),
            'title' => 'Documento Reciente'
        ]);
        
        $document3 = Document::factory()->published()->visible()->create([
            'uploaded_at' => now()->subHours(1),
            'title' => 'Documento Muy Reciente'
        ]);

        $response = $this->getJson('/api/v1/documents');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Verificar orden: uploaded_at desc
        $this->assertEquals($document3->id, $data[0]['id']); // Más reciente
        $this->assertEquals($document2->id, $data[1]['id']); // Medio
        $this->assertEquals($document1->id, $data[2]['id']); // Más antiguo
    }

    #[Test]
    public function it_can_filter_documents_by_category()
    {
        Document::query()->delete();
        
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        Document::factory()->published()->visible()->create(['category_id' => $category1->id]);
        Document::factory()->published()->visible()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->getJson("/api/v1/documents?category_id={$category2->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $document) {
            $this->assertEquals($category2->id, $document['category']['id']);
        }
    }

    #[Test]
    public function it_can_search_documents_by_title_and_description()
    {
        Document::query()->delete();
        
        Document::factory()->published()->visible()->create([
            'title' => 'Manual de Usuario Completo',
            'description' => 'Guía detallada para usuarios'
        ]);
        
        Document::factory()->published()->visible()->create([
            'title' => 'Reporte Financiero',
            'description' => 'Análisis económico anual'
        ]);

        // Buscar por título
        $response = $this->getJson('/api/v1/documents?search=Manual');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('Manual', $response->json('data.0.title'));

        // Buscar por descripción
        $response = $this->getJson('/api/v1/documents?search=económico');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('Reporte', $response->json('data.0.title'));
    }

    #[Test]
    public function it_can_filter_documents_by_file_type()
    {
        Document::query()->delete();
        
        Document::factory()->published()->visible()->pdf()->count(3)->create();
        Document::factory()->published()->visible()->word()->count(2)->create();

        $response = $this->getJson('/api/v1/documents?type=pdf');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $document) {
            $this->assertEquals('pdf', $document['file_type']);
        }
    }

    #[Test]
    public function it_supports_pagination()
    {
        Document::query()->delete();
        
        Document::factory()->published()->visible()->count(25)->create();

        // Test paginación por defecto
        $response = $this->getJson('/api/v1/documents');
        $response->assertStatus(200);
        $this->assertCount(15, $response->json('data')); // Per page por defecto

        // Test paginación personalizada
        $response = $this->getJson('/api/v1/documents?per_page=10');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));

        // Test límite máximo
        $response = $this->getJson('/api/v1/documents?per_page=100');
        $response->assertStatus(200);
        $this->assertCount(25, $response->json('data')); // Máximo 50, pero solo hay 25
    }

    #[Test]
    public function it_can_create_document()
    {
        Sanctum::actingAs($this->adminUser);

        $documentData = [
            'title' => 'Nuevo Documento',
            'description' => 'Descripción del nuevo documento',
            'file_path' => 'documents/nuevo-documento.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'category_id' => $this->category->id
        ];

        $response = $this->postJson('/api/v1/documents', $documentData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'title' => 'Nuevo Documento',
                'description' => 'Descripción del nuevo documento',
                'file_path' => 'documents/nuevo-documento.pdf',
                'file_type' => 'pdf'
            ],
            'message' => 'Documento creado exitosamente'
        ]);
        
        $this->assertDatabaseHas('documents', [
            'title' => 'Nuevo Documento',
            'uploaded_by' => $this->adminUser->id
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_document()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/documents', []);

        $this->assertValidationErrors($response, ['title']);
    }

    #[Test]
    public function it_validates_expiration_date_when_creating_document()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/documents', [
            'title' => 'Test Document',
            'expires_at' => now()->subDay()->toDateString() // Fecha pasada
        ]);

        $this->assertValidationErrors($response, ['expires_at']);
    }

    #[Test]
    public function it_can_show_published_visible_document()
    {
        $document = Document::factory()->published()->visible()->create([
            'download_count' => 0 // Empezar con contador en 0
        ]);

        $response = $this->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $document->id,
                'title' => $document->title,
                'file_type' => $document->file_type
            ]
        ]);
        
        // Verificar que se incrementó el contador de descargas
        $document->refresh();
        $this->assertEquals(1, $document->download_count);
    }

    #[Test]
    public function it_returns_404_for_draft_document()
    {
        $document = Document::factory()->draft()->create();

        $response = $this->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Documento no encontrado'
        ]);
    }

    #[Test]
    public function it_returns_404_for_hidden_document()
    {
        $document = Document::factory()->published()->hidden()->create();

        $response = $this->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Documento no encontrado'
        ]);
    }

    #[Test]
    public function it_can_update_document()
    {
        Sanctum::actingAs($this->adminUser);
        
        $document = Document::factory()->published()->create([
            'title' => 'Título Original',
            'description' => 'Descripción original'
        ]);

        $updateData = [
            'title' => 'Título Actualizado',
            'description' => 'Descripción actualizada',
            'visible' => false
        ];

        $response = $this->putJson("/api/v1/documents/{$document->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'title' => 'Título Actualizado',
                'description' => 'Descripción actualizada',
                'visible' => false
            ],
            'message' => 'Documento actualizado exitosamente'
        ]);
        
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Título Actualizado'
        ]);
    }

    #[Test]
    public function it_can_delete_document()
    {
        Sanctum::actingAs($this->adminUser);
        
        $document = Document::factory()->published()->create();

        $response = $this->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Documento eliminado exitosamente'
        ]);
        
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    #[Test]
    public function it_can_download_document()
    {
        $document = Document::factory()->published()->visible()->create([
            'download_count' => 5
        ]);

        $response = $this->getJson("/api/v1/documents/{$document->id}/download");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'download_url',
            'filename',
            'file_size',
            'file_type',
            'message'
        ]);
        
        // Verificar que se incrementó el contador de descargas
        $document->refresh();
        $this->assertEquals(6, $document->download_count);
    }

    #[Test]
    public function it_can_get_most_downloaded_documents()
    {
        Document::query()->delete();
        
        Document::factory()->published()->visible()->withDownloads(100)->count(2)->create();
        Document::factory()->published()->visible()->withDownloads(50)->count(1)->create();
        Document::factory()->published()->visible()->withDownloads(0)->count(1)->create(); // No debe aparecer

        $response = $this->getJson('/api/v1/documents/most-downloaded');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'total'
        ]);
        
        $this->assertEquals(3, $response->json('total'));
        
        $data = $response->json('data');
        // Verificar que están ordenados por download_count desc
        $this->assertGreaterThanOrEqual($data[1]['download_count'], $data[0]['download_count']);
    }

    #[Test]
    public function it_can_get_recent_documents()
    {
        Document::query()->delete();
        
        Document::factory()->published()->visible()->recent()->count(3)->create();
        Document::factory()->published()->visible()->count(2)->create(['uploaded_at' => now()->subMonths(2)]);

        $response = $this->getJson('/api/v1/documents/recent');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'total'
        ]);
        
        $this->assertEquals(5, $response->json('total')); // Todos aparecen, pero ordenados por fecha
        
        // Verificar que están ordenados por uploaded_at desc
        $data = $response->json('data');
        $firstDate = $data[0]['uploaded_at'];
        $lastDate = end($data)['uploaded_at'];
        $this->assertGreaterThanOrEqual($lastDate, $firstDate);
    }

    #[Test]
    public function it_can_get_popular_documents()
    {
        Document::query()->delete();
        
        Document::factory()->published()->visible()->popular()->count(4)->create();
        Document::factory()->published()->visible()->withDownloads(0)->count(2)->create();

        $response = $this->getJson('/api/v1/documents/popular');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'total'
        ]);
        
        $this->assertEquals(6, $response->json('total'));
        
        // Verificar que están ordenados por download_count desc
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual($data[1]['download_count'], $data[0]['download_count']);
    }

    #[Test]
    public function it_limits_special_endpoints_results()
    {
        Document::query()->delete();
        
        Document::factory()->published()->visible()->count(15)->create();

        // Test límite por defecto en recent
        $response = $this->getJson('/api/v1/documents/recent');
        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('total')); // Límite por defecto

        // Test límite personalizado
        $response = $this->getJson('/api/v1/documents/recent?limit=5');
        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('total'));

        // Test límite máximo
        $response = $this->getJson('/api/v1/documents/recent?limit=50');
        $response->assertStatus(200);
        $this->assertEquals(15, $response->json('total')); // Máximo 20, pero solo hay 15
    }

    #[Test]
    public function it_requires_authentication_for_create_update_delete()
    {
        $document = Document::factory()->published()->create();
        
        $endpoints = [
            'POST' => [
                '/api/v1/documents'
            ],
            'PUT' => [
                "/api/v1/documents/{$document->id}"
            ],
            'DELETE' => [
                "/api/v1/documents/{$document->id}"
            ]
        ];

        $this->assertEndpointsRequireAuth($endpoints);
    }

    #[Test]
    public function it_includes_relationships_in_responses()
    {
        $document = Document::factory()->published()->visible()->create();

        $response = $this->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200);
        $this->assertIncludesRelationships($response, ['category', 'organization', 'uploaded_by']);
    }

    #[Test]
    public function it_includes_computed_fields_in_responses()
    {
        $document = Document::factory()->published()->visible()->pdf()->create([
            'file_size' => 1024000,
            'download_count' => 50,
            'expires_at' => now()->addDays(5)
        ]);

        $response = $this->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertArrayHasKey('is_published', $data);
        $this->assertArrayHasKey('is_expired', $data);
        $this->assertArrayHasKey('is_expiring_soon', $data);
        $this->assertArrayHasKey('download_url', $data);
        $this->assertArrayHasKey('file_type_label', $data);
        $this->assertArrayHasKey('formatted_file_size', $data);
        
        $this->assertTrue($data['is_published']);
        $this->assertFalse($data['is_expired']);
        $this->assertTrue($data['is_expiring_soon']); // 5 días < 30 días por defecto
        $this->assertEquals('PDF', $data['file_type_label']);
        $this->assertEquals('1000 KB', $data['formatted_file_size']);
    }

    #[Test]
    public function it_can_handle_multiple_filters_simultaneously()
    {
        Document::query()->delete();
        
        $category = Category::factory()->create();
        
        Document::factory()->published()->visible()->pdf()->create(['category_id' => $category->id]);
        Document::factory()->published()->visible()->word()->create(['category_id' => $category->id]);
        Document::factory()->published()->visible()->pdf()->count(2)->create(); // Diferente categoría

        $response = $this->getJson("/api/v1/documents?category_id={$category->id}&type=pdf");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        
        $document = $response->json('data.0');
        $this->assertEquals($category->id, $document['category']['id']);
        $this->assertEquals('pdf', $document['file_type']);
    }

    #[Test]
    public function it_returns_empty_result_for_invalid_filters()
    {
        Document::query()->delete();
        
        Document::factory()->published()->visible()->count(3)->create();

        $response = $this->getJson('/api/v1/documents?type=nonexistent');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    public function it_sets_default_values_when_creating_document()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/documents', [
            'title' => 'Simple Document'
        ]);

        $response->assertStatus(201);
        $document = Document::where('title', 'Simple Document')->first();
        
        $this->assertTrue($document->visible);
        $this->assertTrue($document->is_draft);
        $this->assertFalse($document->requires_auth);
        $this->assertEquals('es', $document->language);
        $this->assertEquals('1.0', $document->version);
        $this->assertEquals($this->adminUser->id, $document->uploaded_by);
        $this->assertNotNull($document->uploaded_at);
    }

    #[Test]
    public function it_returns_404_for_non_existent_document()
    {
        $response = $this->getJson('/api/v1/documents/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_validates_file_size_is_numeric()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/documents', [
            'title' => 'Test Document',
            'file_size' => 'invalid'
        ]);

        $this->assertValidationErrors($response, ['file_size']);
    }

    #[Test]
    public function it_validates_language_format()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/documents', [
            'title' => 'Test Document',
            'language' => 'invalid-lang'
        ]);

        $this->assertValidationErrors($response, ['language']);
    }

    #[Test]
    public function it_validates_category_exists()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/documents', [
            'title' => 'Test Document',
            'category_id' => 999999 // ID que no existe
        ]);

        $this->assertValidationErrors($response, ['category_id']);
    }
}
