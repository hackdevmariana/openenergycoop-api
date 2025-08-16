<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Banner;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class BannerControllerTest extends TestCase
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
    public function it_can_list_published_banners()
    {
        // Limpiar datos existentes
        Banner::query()->delete();
        
        // Crear banners de prueba
        Banner::factory()->published()->active()->count(3)->create();
        Banner::factory()->draft()->count(2)->create(); // No deben aparecer
        Banner::factory()->published()->inactive()->count(1)->create(); // Aparece porque no filtra por activo por defecto

        $response = $this->getJson('/api/v1/banners');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'image',
                    'mobile_image',
                    'internal_link',
                    'url',
                    'position',
                    'active',
                    'alt_text',
                    'title',
                    'description',
                    'exhibition_beginning',
                    'exhibition_end',
                    'banner_type',
                    'display_rules',
                    'click_count',
                    'impression_count',
                    'is_draft',
                    'published_at',
                    'organization',
                    'created_by',
                    'is_published',
                    'is_currently_displaying',
                    'click_through_rate',
                    'type_label'
                ]
            ]
        ]);
        
        // Solo deben aparecer los banners publicados (no drafts)
        $this->assertCount(4, $response->json('data')); // 3 activos + 1 inactivo publicado
        
        foreach ($response->json('data') as $banner) {
            $this->assertTrue((bool)$banner['is_published']);
        }
    }

    #[Test]
    public function it_orders_banners_by_position_and_created_at()
    {
        Banner::query()->delete();
        
        // Crear banners con diferentes posiciones
        $banner1 = Banner::factory()->published()->create([
            'position' => 5,
            'created_at' => now()->subDays(2)
        ]);
        
        $banner2 = Banner::factory()->published()->create([
            'position' => 10,
            'created_at' => now()->subDays(1)
        ]);
        
        $banner3 = Banner::factory()->published()->create([
            'position' => 5,
            'created_at' => now()->subDays(1)
        ]);

        $response = $this->getJson('/api/v1/banners');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Verificar orden: position desc, luego created_at desc
        $this->assertEquals($banner2->id, $data[0]['id']); // position 10, más reciente
        $this->assertEquals($banner3->id, $data[1]['id']); // position 5, más reciente
        $this->assertEquals($banner1->id, $data[2]['id']); // position 5, más antiguo
    }

    #[Test]
    public function it_can_filter_banners_by_active_status()
    {
        Banner::query()->delete();
        
        Banner::factory()->published()->active()->count(3)->create();
        Banner::factory()->published()->inactive()->count(2)->create();

        $response = $this->getJson('/api/v1/banners?active=true');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $banner) {
            $this->assertTrue((bool)$banner['active']);
        }
    }

    #[Test]
    public function it_can_filter_banners_by_type()
    {
        Banner::query()->delete();
        
        Banner::factory()->published()->ofType('header')->count(3)->create();
        Banner::factory()->published()->ofType('sidebar')->count(2)->create();

        $response = $this->getJson('/api/v1/banners?type=header');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $banner) {
            $this->assertEquals('header', $banner['banner_type']);
        }
    }

    #[Test]
    public function it_can_filter_banners_by_position()
    {
        Banner::query()->delete();
        
        Banner::factory()->published()->atPosition(1)->count(2)->create();
        Banner::factory()->published()->atPosition(2)->count(3)->create();

        $response = $this->getJson('/api/v1/banners?position=1');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $banner) {
            $this->assertEquals(1, $banner['position']);
        }
    }

    #[Test]
    public function it_can_create_banner()
    {
        Sanctum::actingAs($this->adminUser);

        $bannerData = [
            'image' => 'banners/test-banner.jpg',
            'title' => 'Nuevo Banner',
            'description' => 'Descripción del banner',
            'banner_type' => 'header',
            'position' => 5,
            'url' => 'https://example.com'
        ];

        $response = $this->postJson('/api/v1/banners', $bannerData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'image' => 'banners/test-banner.jpg',
                'title' => 'Nuevo Banner',
                'description' => 'Descripción del banner',
                'banner_type' => 'header',
                'position' => 5,
                'url' => 'https://example.com'
            ],
            'message' => 'Banner creado exitosamente'
        ]);
        
        $this->assertDatabaseHas('banners', [
            'image' => 'banners/test-banner.jpg',
            'title' => 'Nuevo Banner',
            'created_by_user_id' => $this->adminUser->id
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_banner()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/banners', []);

        $this->assertValidationErrors($response, ['image']);
    }

    #[Test]
    public function it_validates_url_format_when_creating_banner()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/banners', [
            'image' => 'banners/test.jpg',
            'url' => 'invalid-url'
        ]);

        $this->assertValidationErrors($response, ['url']);
    }

    #[Test]
    public function it_validates_banner_type_when_creating_banner()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/banners', [
            'image' => 'banners/test.jpg',
            'banner_type' => 'invalid-type'
        ]);

        $this->assertValidationErrors($response, ['banner_type']);
    }

    #[Test]
    public function it_validates_exhibition_dates_when_creating_banner()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/banners', [
            'image' => 'banners/test.jpg',
            'exhibition_beginning' => now()->addDays(5)->toDateString(),
            'exhibition_end' => now()->addDays(2)->toDateString() // Fin antes que inicio
        ]);

        $this->assertValidationErrors($response, ['exhibition_end']);
    }

    #[Test]
    public function it_can_show_published_active_banner()
    {
        $banner = Banner::factory()->published()->active()->create();

        $response = $this->getJson("/api/v1/banners/{$banner->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $banner->id,
                'image' => $banner->image,
                'title' => $banner->title
            ]
        ]);
    }

    #[Test]
    public function it_returns_404_for_draft_banner()
    {
        $banner = Banner::factory()->draft()->create();

        $response = $this->getJson("/api/v1/banners/{$banner->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Banner no encontrado'
        ]);
    }

    #[Test]
    public function it_returns_404_for_inactive_banner()
    {
        $banner = Banner::factory()->published()->inactive()->create();

        $response = $this->getJson("/api/v1/banners/{$banner->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Banner no encontrado'
        ]);
    }

    #[Test]
    public function it_can_update_banner()
    {
        Sanctum::actingAs($this->adminUser);
        
        $banner = Banner::factory()->published()->create([
            'title' => 'Título Original',
            'position' => 1
        ]);

        $updateData = [
            'title' => 'Título Actualizado',
            'position' => 5,
            'active' => false
        ];

        $response = $this->putJson("/api/v1/banners/{$banner->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'title' => 'Título Actualizado',
                'position' => 5,
                'active' => false
            ],
            'message' => 'Banner actualizado exitosamente'
        ]);
        
        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'title' => 'Título Actualizado',
            'updated_by_user_id' => $this->adminUser->id
        ]);
    }

    #[Test]
    public function it_can_delete_banner()
    {
        Sanctum::actingAs($this->adminUser);
        
        $banner = Banner::factory()->published()->create();

        $response = $this->deleteJson("/api/v1/banners/{$banner->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Banner eliminado exitosamente'
        ]);
        
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }

    #[Test]
    public function it_can_get_banners_by_position()
    {
        Banner::query()->delete();
        
        Banner::factory()->published()->active()->atPosition(1)->count(3)->create();
        Banner::factory()->published()->active()->atPosition(2)->count(2)->create();
        Banner::factory()->published()->inactive()->atPosition(1)->count(1)->create(); // No debe aparecer

        $response = $this->getJson('/api/v1/banners/by-position/1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'position',
            'total'
        ]);
        
        $this->assertEquals('1', $response->json('position'));
        $this->assertEquals(3, $response->json('total'));
        
        foreach ($response->json('data') as $banner) {
            $this->assertEquals(1, $banner['position']);
            $this->assertTrue((bool)$banner['active']);
            $this->assertTrue((bool)$banner['is_published']);
        }
    }

    #[Test]
    public function it_can_get_active_banners()
    {
        Banner::query()->delete();
        
        Banner::factory()->published()->active()->count(4)->create();
        Banner::factory()->published()->inactive()->count(2)->create(); // No deben aparecer
        Banner::factory()->draft()->active()->count(1)->create(); // No debe aparecer

        $response = $this->getJson('/api/v1/banners/active');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'total'
        ]);
        
        $this->assertEquals(4, $response->json('total'));
        
        foreach ($response->json('data') as $banner) {
            $this->assertTrue((bool)$banner['active']);
            $this->assertTrue((bool)$banner['is_published']);
        }
    }

    #[Test]
    public function it_can_filter_active_banners_by_type()
    {
        Banner::query()->delete();
        
        Banner::factory()->published()->active()->ofType('header')->count(3)->create();
        Banner::factory()->published()->active()->ofType('sidebar')->count(2)->create();

        $response = $this->getJson('/api/v1/banners/active?type=header');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
        
        foreach ($response->json('data') as $banner) {
            $this->assertEquals('header', $banner['banner_type']);
            $this->assertTrue((bool)$banner['active']);
        }
    }

    #[Test]
    public function it_requires_authentication_for_create_update_delete()
    {
        $banner = Banner::factory()->published()->create();
        
        $endpoints = [
            'POST' => [
                '/api/v1/banners'
            ],
            'PUT' => [
                "/api/v1/banners/{$banner->id}"
            ],
            'DELETE' => [
                "/api/v1/banners/{$banner->id}"
            ]
        ];

        $this->assertEndpointsRequireAuth($endpoints);
    }

    #[Test]
    public function it_includes_relationships_in_responses()
    {
        $banner = Banner::factory()->published()->active()->create();

        $response = $this->getJson("/api/v1/banners/{$banner->id}");

        $response->assertStatus(200);
        $this->assertIncludesRelationships($response, ['organization', 'created_by']);
    }

    #[Test]
    public function it_includes_computed_fields_in_responses()
    {
        $banner = Banner::factory()->published()->active()->create([
            'click_count' => 50,
            'impression_count' => 1000
        ]);

        $response = $this->getJson("/api/v1/banners/{$banner->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertArrayHasKey('is_published', $data);
        $this->assertArrayHasKey('is_currently_displaying', $data);
        $this->assertArrayHasKey('click_through_rate', $data);
        $this->assertArrayHasKey('type_label', $data);
        
        $this->assertTrue($data['is_published']);
        $this->assertEquals(5.0, $data['click_through_rate']); // 50/1000 * 100
    }

    #[Test]
    public function it_can_handle_multiple_filters_simultaneously()
    {
        Banner::query()->delete();
        
        Banner::factory()->published()->active()->ofType('header')->atPosition(1)->count(2)->create();
        Banner::factory()->published()->active()->ofType('sidebar')->atPosition(1)->count(1)->create();
        Banner::factory()->published()->inactive()->ofType('header')->atPosition(1)->count(1)->create();

        $response = $this->getJson('/api/v1/banners?active=true&type=header&position=1');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $banner) {
            $this->assertTrue((bool)$banner['active']);
            $this->assertEquals('header', $banner['banner_type']);
            $this->assertEquals(1, $banner['position']);
        }
    }

    #[Test]
    public function it_returns_empty_result_for_invalid_filters()
    {
        Banner::query()->delete();
        
        Banner::factory()->published()->active()->count(3)->create();

        $response = $this->getJson('/api/v1/banners?type=nonexistent');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    public function it_sets_default_values_when_creating_banner()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/banners', [
            'image' => 'banners/simple-banner.jpg'
        ]);

        $response->assertStatus(201);
        $banner = Banner::where('image', 'banners/simple-banner.jpg')->first();
        
        $this->assertTrue($banner->active);
        $this->assertTrue($banner->is_draft);
        $this->assertEquals(0, $banner->position);
        $this->assertEquals('header', $banner->banner_type);
        $this->assertEquals($this->adminUser->id, $banner->created_by_user_id);
    }

    #[Test]
    public function it_returns_404_for_non_existent_banner()
    {
        $response = $this->getJson('/api/v1/banners/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_validates_position_is_numeric()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/banners', [
            'image' => 'banners/test.jpg',
            'position' => 'invalid'
        ]);

        $this->assertValidationErrors($response, ['position']);
    }

    #[Test]
    public function it_validates_display_rules_is_array()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/banners', [
            'image' => 'banners/test.jpg',
            'display_rules' => 'not-an-array'
        ]);

        $this->assertValidationErrors($response, ['display_rules']);
    }
}
