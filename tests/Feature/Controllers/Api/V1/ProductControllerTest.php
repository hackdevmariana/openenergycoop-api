<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Organization;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;
    protected Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->provider = Provider::factory()->renewable()->create();
        
        // Asignar rol de admin al usuario para que tenga permisos completos
        $this->user->assignRole('admin');
        
        // Crear un perfil para el usuario para que tenga una organización asociada
        \App\Models\CustomerProfile::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
        ]);
    }

    #[Test]
    public function it_can_list_products()
    {
        Sanctum::actingAs($this->user);
        
        // Crear algunos productos de prueba
        Product::factory()->count(3)->solar()->for($this->provider)->create();
        Product::factory()->count(2)->for($this->provider)->create(['type' => 'wind']);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'provider',
                    'type',
                    'unit_price',
                    'currency',
                    'unit',
                    'minimum_investment',
                    'maximum_investment',
                    'expected_yield_percentage',
                    'risk_level',
                    'renewable_percentage',
                    'sustainability_score',
                    'is_active',
                    'tags'
                ]
            ],
            'links',
            'meta'
        ]);
        
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_products_by_type()
    {
        Sanctum::actingAs($this->user);
        
        Product::factory()->count(3)->solar()->for($this->provider)->create();
        Product::factory()->count(2)->for($this->provider)->create(['type' => 'wind']);

        $response = $this->getJson('/api/v1/products?type=solar');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $product) {
            $this->assertEquals('solar', $product['type']);
        }
    }

    #[Test]
    public function it_can_filter_products_by_provider()
    {
        Sanctum::actingAs($this->user);
        
        $anotherProvider = Provider::factory()->create();
        
        Product::factory()->count(3)->for($this->provider)->create();
        Product::factory()->count(2)->for($anotherProvider)->create();

        $response = $this->getJson("/api/v1/products?provider_id={$this->provider->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $product) {
            $this->assertEquals($this->provider->id, $product['provider']['id']);
        }
    }

    #[Test]
    public function it_can_filter_products_by_price_range()
    {
        Sanctum::actingAs($this->user);
        
        Product::factory()->for($this->provider)->create(['unit_price' => 100]);
        Product::factory()->for($this->provider)->create(['unit_price' => 500]);
        Product::factory()->for($this->provider)->create(['unit_price' => 1000]);

        $response = $this->getJson('/api/v1/products?price_min=200&price_max=800');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        
        $product = $response->json('data')[0];
        $this->assertEquals(500, $product['unit_price']);
    }

    #[Test]
    public function it_can_filter_products_by_sustainability()
    {
        Sanctum::actingAs($this->user);
        
        Product::factory()->for($this->provider)->create(['sustainability_score' => 95]);
        Product::factory()->for($this->provider)->create(['sustainability_score' => 75]);
        Product::factory()->for($this->provider)->create(['sustainability_score' => 60]);

        $response = $this->getJson('/api/v1/products?sustainability_min=80');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        
        $product = $response->json('data')[0];
        $this->assertEquals(95, $product['sustainability_score']);
    }

    #[Test]
    public function it_can_get_sustainable_products()
    {
        Sanctum::actingAs($this->user);
        
        Product::factory()->count(3)->for($this->provider)->create(['sustainability_score' => 90]);
        Product::factory()->count(2)->for($this->provider)->create(['sustainability_score' => 70]);

        $response = $this->getJson('/api/v1/products/sustainable');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $product) {
            $this->assertGreaterThanOrEqual(80, $product['sustainability_score']);
        }
    }

    #[Test]
    public function it_can_get_product_recommendations()
    {
        Sanctum::actingAs($this->user);
        
        Product::factory()->count(5)->for($this->provider)->create([
            'sustainability_score' => 85,
            'is_active' => true
        ]);

        $response = $this->getJson("/api/v1/products/recommendations/{$this->user->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'sustainability_score'
                ]
            ],
            'recommendation_reasons'
        ]);
        
        $this->assertIsArray($response->json('recommendation_reasons'));
    }

    #[Test]
    public function it_can_calculate_product_pricing()
    {
        Sanctum::actingAs($this->user);
        
        $product = Product::factory()->for($this->provider)->create([
            'unit_price' => 1000,
            'unit' => 'kWh',
            'currency' => 'EUR',
            'co2_reduction' => 200
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}/pricing?quantity=5&months=12");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'unit_price',
            'quantity',
            'total_price',
            'monthly_cost',
            'annual_cost',
            'currency',
            'unit',
            'savings_projection' => [
                'traditional_cost',
                'projected_savings',
                'payback_period_months',
                'co2_reduction_kg'
            ],
            'sustainability_impact' => [
                'score',
                'co2_reduction',
                'renewable_percentage'
            ]
        ]);
        
        $this->assertEquals(1000, $response->json('unit_price'));
        $this->assertEquals(5, $response->json('quantity'));
        $this->assertEquals(5000, $response->json('total_price'));
        $this->assertEquals(1000, $response->json('co2_reduction_kg'));
    }

    #[Test]
    public function it_can_get_product_sustainability_details()
    {
        Sanctum::actingAs($this->user);
        
        $product = Product::factory()->for($this->provider)->create([
            'sustainability_score' => 92,
            'renewable_percentage' => 100,
            'co2_reduction' => 300,
            'energy_efficiency' => 'A++',
            'carbon_footprint' => 0.05,
            'water_saving' => 500,
            'certifications' => ['ISO 14001', 'Energy Star']
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}/sustainability");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'sustainability_score',
            'environmental_impact' => [
                'co2_reduction_per_unit',
                'renewable_percentage',
                'energy_efficiency_rating',
                'lifecycle_carbon_footprint',
                'water_usage_reduction'
            ],
            'certifications',
            'sustainability_rating',
            'comparison_vs_traditional' => [
                'co2_reduction_percentage',
                'cost_efficiency',
                'environmental_benefit'
            ]
        ]);
        
        $this->assertEquals(92, $response->json('sustainability_score'));
        $this->assertEquals(100, $response->json('environmental_impact.renewable_percentage'));
        $this->assertCount(2, $response->json('certifications'));
    }

    #[Test]
    public function it_can_create_a_product()
    {
        Sanctum::actingAs($this->user);
        
        $tags = Tag::factory()->count(2)->create();

        $productData = [
            'name' => 'Test Solar Panel',
            'description' => 'A high-efficiency solar panel',
            'provider_id' => $this->provider->id,
            'type' => 'solar',
            'unit_price' => 1500,
            'unit' => 'panel',
            'minimum_investment' => 100,
            'maximum_investment' => 10000,
            'expected_yield_percentage' => 8.5,
            'risk_level' => 'low',
            'renewable_percentage' => 100,
            'co2_reduction' => 250,
            'energy_efficiency' => 'A++',
            'certifications' => ['ISO 14001', 'Energy Star'],
            'tag_ids' => $tags->pluck('id')->toArray()
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => 'Test Solar Panel',
            'type' => 'solar',
            'unit_price' => 1500
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Solar Panel',
            'type' => 'solar'
        ]);
    }

    #[Test]
    public function it_calculates_sustainability_score_on_creation()
    {
        Sanctum::actingAs($this->user);

        $productData = [
            'name' => 'High Efficiency Solar',
            'provider_id' => $this->provider->id,
            'type' => 'solar',
            'unit_price' => 2000,
            'unit' => 'panel',
            'renewable_percentage' => 100,
            'co2_reduction' => 500,
            'energy_efficiency' => 'A++',
            'certifications' => ['ISO 14001', 'Energy Star', 'LEED']
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);
        
        $product = $response->json('data');
        $this->assertGreaterThan(80, $product['sustainability_score']);
        $this->assertLessThanOrEqual(100, $product['sustainability_score']);
    }

    #[Test]
    public function it_validates_product_creation_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'provider_id', 'type', 'unit_price', 'unit']);
    }

    #[Test]
    public function it_validates_investment_range()
    {
        Sanctum::actingAs($this->user);

        $productData = [
            'name' => 'Test Product',
            'provider_id' => $this->provider->id,
            'type' => 'solar',
            'unit_price' => 1000,
            'unit' => 'panel',
            'minimum_investment' => 1000,
            'maximum_investment' => 500 // Menor que mínimo
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['maximum_investment']);
    }

    #[Test]
    public function it_can_update_a_product()
    {
        Sanctum::actingAs($this->user);
        
        $product = Product::factory()->for($this->provider)->create();

        $updateData = [
            'name' => 'Updated Product Name',
            'unit_price' => 2500,
            'renewable_percentage' => 95,
            'expected_yield_percentage' => 9.5
        ];

        $response = $this->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Updated Product Name',
            'unit_price' => 2500,
            'renewable_percentage' => 95
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'unit_price' => 2500
        ]);
    }

    #[Test]
    public function it_recalculates_sustainability_score_on_update()
    {
        Sanctum::actingAs($this->user);
        
        $product = Product::factory()->for($this->provider)->create([
            'sustainability_score' => 60
        ]);

        $updateData = [
            'renewable_percentage' => 100,
            'co2_reduction' => 500,
            'energy_efficiency' => 'A++'
        ];

        $response = $this->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200);
        
        $updatedProduct = $response->json('data');
        $this->assertGreaterThan(60, $updatedProduct['sustainability_score']);
    }

    #[Test]
    public function it_can_delete_a_product()
    {
        Sanctum::actingAs($this->user);
        
        $product = Product::factory()->for($this->provider)->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertModelMissing($product);
    }

    #[Test]
    public function public_endpoints_work_without_authentication()
    {
        Product::factory()->count(3)->for($this->provider)->create(['sustainability_score' => 85]);

        // Test public listing
        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(200);

        // Test public sustainable
        $response = $this->getJson('/api/v1/products/sustainable');
        $response->assertStatus(200);

        // Test public show
        $product = Product::first();
        $response = $this->getJson("/api/v1/products/{$product->id}");
        $response->assertStatus(200);

        // Test public pricing
        $response = $this->getJson("/api/v1/products/{$product->id}/pricing");
        $response->assertStatus(200);

        // Test public sustainability
        $response = $this->getJson("/api/v1/products/{$product->id}/sustainability");
        $response->assertStatus(200);
    }

    #[Test]
    public function it_only_shows_active_products_by_default()
    {
        Sanctum::actingAs($this->user);
        
        Product::factory()->count(3)->for($this->provider)->create(['is_active' => true]);
        Product::factory()->count(2)->for($this->provider)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $product) {
            $this->assertTrue($product['is_active']);
        }
    }

    #[Test]
    public function it_can_search_products()
    {
        Sanctum::actingAs($this->user);
        
        Product::factory()->for($this->provider)->create(['name' => 'Premium Solar Panel']);
        Product::factory()->for($this->provider)->create(['name' => 'Wind Turbine System']);
        Product::factory()->for($this->provider)->create(['description' => 'Advanced solar technology']);

        $response = $this->getJson('/api/v1/products?search=solar');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_paginates_product_results()
    {
        Sanctum::actingAs($this->user);
        
        Product::factory()->count(25)->for($this->provider)->create();

        $response = $this->getJson('/api/v1/products?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $response->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'last_page', 'per_page', 'total']
        ]);
    }
}
