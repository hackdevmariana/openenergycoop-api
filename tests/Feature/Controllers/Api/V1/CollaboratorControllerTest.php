<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Collaborator;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class CollaboratorControllerTest extends TestCase
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
    public function it_can_list_published_active_collaborators()
    {
        // Limpiar datos existentes
        Collaborator::query()->delete();
        
        // Crear colaboradores de prueba
        Collaborator::factory()->published()->active()->count(4)->create();
        Collaborator::factory()->draft()->count(2)->create(); // No deben aparecer
        Collaborator::factory()->published()->inactive()->count(1)->create(); // No debe aparecer

        $response = $this->getJson('/api/v1/collaborators');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'logo',
                    'url',
                    'description',
                    'order',
                    'is_active',
                    'collaborator_type',
                    'is_draft',
                    'published_at',
                    'organization',
                    'created_by',
                    'is_published',
                    'type_label'
                ]
            ]
        ]);
        
        // Solo deben aparecer los colaboradores publicados y activos
        $this->assertCount(4, $response->json('data'));
        
        foreach ($response->json('data') as $collaborator) {
            $this->assertTrue((bool)$collaborator['is_published']);
            $this->assertTrue((bool)$collaborator['is_active']);
        }
    }

    #[Test]
    public function it_orders_collaborators_by_order_and_name()
    {
        Collaborator::query()->delete();
        
        // Crear colaboradores con diferentes órdenes
        $collaborator1 = Collaborator::factory()->published()->active()->create([
            'order' => 5,
            'name' => 'Zebra Corp'
        ]);
        
        $collaborator2 = Collaborator::factory()->published()->active()->create([
            'order' => 1,
            'name' => 'Alpha Inc'
        ]);
        
        $collaborator3 = Collaborator::factory()->published()->active()->create([
            'order' => 5,
            'name' => 'Beta Ltd'
        ]);

        $response = $this->getJson('/api/v1/collaborators');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Verificar orden: order asc, luego name asc
        $this->assertEquals($collaborator2->id, $data[0]['id']); // order 1
        $this->assertEquals($collaborator3->id, $data[1]['id']); // order 5, "Beta" (alfabético)
        $this->assertEquals($collaborator1->id, $data[2]['id']); // order 5, "Zebra" (alfabético)
    }

    #[Test]
    public function it_can_filter_collaborators_by_type()
    {
        Collaborator::query()->delete();
        
        Collaborator::factory()->published()->active()->partner()->count(3)->create();
        Collaborator::factory()->published()->active()->sponsor()->count(2)->create();

        $response = $this->getJson('/api/v1/collaborators?type=partner');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $collaborator) {
            $this->assertEquals('partner', $collaborator['collaborator_type']);
        }
    }

    #[Test]
    public function it_can_filter_collaborators_by_active_status()
    {
        Collaborator::query()->delete();
        
        Collaborator::factory()->published()->active()->count(3)->create();
        Collaborator::factory()->published()->inactive()->count(2)->create();

        // Test filtrando activos
        $response = $this->getJson('/api/v1/collaborators?active=true');
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));

        // Test filtrando inactivos (pero publicados)
        $response = $this->getJson('/api/v1/collaborators?active=false');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $collaborator) {
            $this->assertFalse((bool)$collaborator['is_active']);
        }
    }

    #[Test]
    public function it_can_create_collaborator()
    {
        Sanctum::actingAs($this->adminUser);

        $collaboratorData = [
            'name' => 'Nueva Empresa Colaboradora',
            'logo' => 'collaborators/nueva-empresa.png',
            'url' => 'https://nueva-empresa.com',
            'description' => 'Descripción de la nueva empresa colaboradora',
            'collaborator_type' => 'partner',
            'order' => 10
        ];

        $response = $this->postJson('/api/v1/collaborators', $collaboratorData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => 'Nueva Empresa Colaboradora',
                'logo' => 'collaborators/nueva-empresa.png',
                'url' => 'https://nueva-empresa.com',
                'description' => 'Descripción de la nueva empresa colaboradora',
                'collaborator_type' => 'partner',
                'order' => 10
            ],
            'message' => 'Colaborador creado exitosamente'
        ]);
        
        $this->assertDatabaseHas('collaborators', [
            'name' => 'Nueva Empresa Colaboradora',
            'logo' => 'collaborators/nueva-empresa.png',
            'created_by_user_id' => $this->adminUser->id
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_collaborator()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/collaborators', []);

        $this->assertValidationErrors($response, ['name', 'logo']);
    }

    #[Test]
    public function it_validates_url_format_when_creating_collaborator()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/collaborators', [
            'name' => 'Test Company',
            'logo' => 'test.png',
            'url' => 'invalid-url'
        ]);

        $this->assertValidationErrors($response, ['url']);
    }

    #[Test]
    public function it_validates_collaborator_type_when_creating_collaborator()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/collaborators', [
            'name' => 'Test Company',
            'logo' => 'test.png',
            'collaborator_type' => 'invalid-type'
        ]);

        $this->assertValidationErrors($response, ['collaborator_type']);
    }

    #[Test]
    public function it_can_show_published_active_collaborator()
    {
        $collaborator = Collaborator::factory()->published()->active()->create();

        $response = $this->getJson("/api/v1/collaborators/{$collaborator->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $collaborator->id,
                'name' => $collaborator->name,
                'logo' => $collaborator->logo
            ]
        ]);
    }

    #[Test]
    public function it_returns_404_for_draft_collaborator()
    {
        $collaborator = Collaborator::factory()->draft()->create();

        $response = $this->getJson("/api/v1/collaborators/{$collaborator->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Colaborador no encontrado'
        ]);
    }

    #[Test]
    public function it_returns_404_for_inactive_collaborator()
    {
        $collaborator = Collaborator::factory()->published()->inactive()->create();

        $response = $this->getJson("/api/v1/collaborators/{$collaborator->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Colaborador no encontrado'
        ]);
    }

    #[Test]
    public function it_can_update_collaborator()
    {
        Sanctum::actingAs($this->adminUser);
        
        $collaborator = Collaborator::factory()->published()->create([
            'name' => 'Nombre Original',
            'order' => 1
        ]);

        $updateData = [
            'name' => 'Nombre Actualizado',
            'order' => 5,
            'is_active' => false
        ];

        $response = $this->putJson("/api/v1/collaborators/{$collaborator->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'name' => 'Nombre Actualizado',
                'order' => 5,
                'is_active' => false
            ],
            'message' => 'Colaborador actualizado exitosamente'
        ]);
        
        $this->assertDatabaseHas('collaborators', [
            'id' => $collaborator->id,
            'name' => 'Nombre Actualizado'
        ]);
    }

    #[Test]
    public function it_can_delete_collaborator()
    {
        Sanctum::actingAs($this->adminUser);
        
        $collaborator = Collaborator::factory()->published()->create();

        $response = $this->deleteJson("/api/v1/collaborators/{$collaborator->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Colaborador eliminado exitosamente'
        ]);
        
        $this->assertDatabaseMissing('collaborators', ['id' => $collaborator->id]);
    }

    #[Test]
    public function it_can_get_collaborators_by_type()
    {
        Collaborator::query()->delete();
        
        Collaborator::factory()->published()->active()->partner()->count(3)->create();
        Collaborator::factory()->published()->active()->sponsor()->count(2)->create();
        Collaborator::factory()->published()->inactive()->partner()->count(1)->create(); // No debe aparecer

        $response = $this->getJson('/api/v1/collaborators/by-type/partner');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'type',
            'total'
        ]);
        
        $this->assertEquals('partner', $response->json('type'));
        $this->assertEquals(3, $response->json('total'));
        
        foreach ($response->json('data') as $collaborator) {
            $this->assertEquals('partner', $collaborator['collaborator_type']);
            $this->assertTrue((bool)$collaborator['is_active']);
            $this->assertTrue((bool)$collaborator['is_published']);
        }
    }

    #[Test]
    public function it_can_get_active_collaborators()
    {
        Collaborator::query()->delete();
        
        Collaborator::factory()->published()->active()->count(5)->create();
        Collaborator::factory()->published()->inactive()->count(2)->create(); // No deben aparecer
        Collaborator::factory()->draft()->active()->count(1)->create(); // No debe aparecer

        $response = $this->getJson('/api/v1/collaborators/active');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'total'
        ]);
        
        $this->assertEquals(5, $response->json('total'));
        
        foreach ($response->json('data') as $collaborator) {
            $this->assertTrue((bool)$collaborator['is_active']);
            $this->assertTrue((bool)$collaborator['is_published']);
        }
    }

    #[Test]
    public function it_can_filter_active_collaborators_by_type()
    {
        Collaborator::query()->delete();
        
        Collaborator::factory()->published()->active()->sponsor()->count(3)->create();
        Collaborator::factory()->published()->active()->partner()->count(2)->create();

        $response = $this->getJson('/api/v1/collaborators/active?type=sponsor');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
        
        foreach ($response->json('data') as $collaborator) {
            $this->assertEquals('sponsor', $collaborator['collaborator_type']);
            $this->assertTrue((bool)$collaborator['is_active']);
        }
    }

    #[Test]
    public function it_limits_active_collaborators_results()
    {
        Collaborator::query()->delete();
        
        Collaborator::factory()->published()->active()->count(15)->create();

        // Test con límite por defecto
        $response = $this->getJson('/api/v1/collaborators/active');
        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('total')); // Límite por defecto

        // Test con límite personalizado
        $response = $this->getJson('/api/v1/collaborators/active?limit=5');
        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('total'));

        // Test con límite máximo
        $response = $this->getJson('/api/v1/collaborators/active?limit=100');
        $response->assertStatus(200);
        $this->assertEquals(15, $response->json('total')); // Máximo 50, pero solo hay 15
    }

    #[Test]
    public function it_requires_authentication_for_create_update_delete()
    {
        $collaborator = Collaborator::factory()->published()->create();
        
        $endpoints = [
            'POST' => [
                '/api/v1/collaborators'
            ],
            'PUT' => [
                "/api/v1/collaborators/{$collaborator->id}"
            ],
            'DELETE' => [
                "/api/v1/collaborators/{$collaborator->id}"
            ]
        ];

        $this->assertEndpointsRequireAuth($endpoints);
    }

    #[Test]
    public function it_includes_relationships_in_responses()
    {
        $collaborator = Collaborator::factory()->published()->active()->create();

        $response = $this->getJson("/api/v1/collaborators/{$collaborator->id}");

        $response->assertStatus(200);
        $this->assertIncludesRelationships($response, ['organization', 'created_by']);
    }

    #[Test]
    public function it_includes_computed_fields_in_responses()
    {
        $collaborator = Collaborator::factory()->published()->active()->partner()->create();

        $response = $this->getJson("/api/v1/collaborators/{$collaborator->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertArrayHasKey('is_published', $data);
        $this->assertArrayHasKey('type_label', $data);
        
        $this->assertTrue($data['is_published']);
        $this->assertEquals('Partner', $data['type_label']);
    }

    #[Test]
    public function it_can_handle_multiple_filters_simultaneously()
    {
        Collaborator::query()->delete();
        
        Collaborator::factory()->published()->active()->partner()->count(2)->create();
        Collaborator::factory()->published()->active()->sponsor()->count(1)->create();
        Collaborator::factory()->published()->inactive()->partner()->count(1)->create();

        $response = $this->getJson('/api/v1/collaborators?active=true&type=partner');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $collaborator) {
            $this->assertTrue((bool)$collaborator['is_active']);
            $this->assertEquals('partner', $collaborator['collaborator_type']);
        }
    }

    #[Test]
    public function it_returns_empty_result_for_invalid_filters()
    {
        Collaborator::query()->delete();
        
        Collaborator::factory()->published()->active()->count(3)->create();

        $response = $this->getJson('/api/v1/collaborators?type=nonexistent');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    public function it_sets_default_values_when_creating_collaborator()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/collaborators', [
            'name' => 'Simple Company',
            'logo' => 'simple-logo.png'
        ]);

        $response->assertStatus(201);
        $collaborator = Collaborator::where('name', 'Simple Company')->first();
        
        $this->assertTrue($collaborator->is_active);
        $this->assertTrue($collaborator->is_draft);
        $this->assertEquals(0, $collaborator->order);
        $this->assertEquals('partner', $collaborator->collaborator_type);
        $this->assertEquals($this->adminUser->id, $collaborator->created_by_user_id);
    }

    #[Test]
    public function it_returns_404_for_non_existent_collaborator()
    {
        $response = $this->getJson('/api/v1/collaborators/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_validates_order_is_numeric()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/collaborators', [
            'name' => 'Test Company',
            'logo' => 'test.png',
            'order' => 'invalid'
        ]);

        $this->assertValidationErrors($response, ['order']);
    }

    #[Test]
    public function it_validates_organization_exists()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/collaborators', [
            'name' => 'Test Company',
            'logo' => 'test.png',
            'organization_id' => 999999 // ID que no existe
        ]);

        $this->assertValidationErrors($response, ['organization_id']);
    }

    #[Test]
    public function it_can_get_all_collaborator_types()
    {
        Collaborator::query()->delete();
        
        Collaborator::factory()->published()->active()->partner()->create();
        Collaborator::factory()->published()->active()->sponsor()->create();
        Collaborator::factory()->published()->active()->member()->create();
        Collaborator::factory()->published()->active()->supporter()->create();

        $response = $this->getJson('/api/v1/collaborators');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $types = array_column($data, 'collaborator_type');
        $this->assertContains('partner', $types);
        $this->assertContains('sponsor', $types);
        $this->assertContains('member', $types);
        $this->assertContains('supporter', $types);
    }
}
