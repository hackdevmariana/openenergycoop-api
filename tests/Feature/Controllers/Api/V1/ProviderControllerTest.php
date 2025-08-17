<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Company;
use App\Models\Organization;
use App\Models\Provider;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProviderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        
        // Asignar rol de admin al usuario para que tenga permisos completos
        $this->user->assignRole('admin');
        
        // Crear un perfil para el usuario para que tenga una organización asociada
        \App\Models\CustomerProfile::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
        ]);
    }

    #[Test]
    public function it_can_list_providers()
    {
        Sanctum::actingAs($this->user);
        
        // Crear algunos proveedores de prueba
        Provider::factory()->count(3)->renewable()->create();
        Provider::factory()->count(2)->traditional()->create();

        $response = $this->getJson('/api/v1/providers');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'company',
                    'type',
                    'rating',
                    'total_products',
                    'sustainability_score',
                    'verification_status',
                    'is_active',
                    'certifications',
                    'contact_info',
                    'tags'
                ]
            ],
            'links',
            'meta'
        ]);
        
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_providers_by_type()
    {
        Sanctum::actingAs($this->user);
        
        Provider::factory()->count(3)->renewable()->create();
        Provider::factory()->count(2)->traditional()->create();

        $response = $this->getJson('/api/v1/providers?type=renewable');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $provider) {
            $this->assertEquals('renewable', $provider['type']);
        }
    }

    #[Test]
    public function it_can_filter_providers_by_rating()
    {
        Sanctum::actingAs($this->user);
        
        Provider::factory()->count(2)->create(['rating' => 4.5]);
        Provider::factory()->count(3)->create(['rating' => 3.0]);

        $response = $this->getJson('/api/v1/providers?rating_min=4.0');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $provider) {
            $this->assertGreaterThanOrEqual(4.0, $provider['rating']);
        }
    }

    #[Test]
    public function it_can_search_providers()
    {
        Sanctum::actingAs($this->user);
        
        Provider::factory()->create(['name' => 'EcoGreen Solar Energy']);
        Provider::factory()->create(['name' => 'Traditional Coal Power']);
        Provider::factory()->create(['description' => 'Clean solar solutions for homes']);

        $response = $this->getJson('/api/v1/providers?search=solar');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_get_top_rated_providers()
    {
        Sanctum::actingAs($this->user);
        
        Provider::factory()->count(3)->create(['rating' => 4.8]);
        Provider::factory()->count(2)->create(['rating' => 3.0]);

        $response = $this->getJson('/api/v1/providers/top-rated?limit=2');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $provider) {
            $this->assertGreaterThanOrEqual(4.0, $provider['rating']);
        }
    }

    #[Test]
    public function it_can_get_renewable_providers()
    {
        Sanctum::actingAs($this->user);
        
        Provider::factory()->count(3)->renewable()->create();
        Provider::factory()->count(2)->traditional()->create();

        $response = $this->getJson('/api/v1/providers/renewable');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $provider) {
            $this->assertEquals('renewable', $provider['type']);
            $this->assertTrue($provider['is_active']);
        }
    }

    #[Test]
    public function it_can_get_provider_certifications()
    {
        Sanctum::actingAs($this->user);
        
        $provider = Provider::factory()->renewable()->create([
            'certifications' => ['ISO 14001', 'Carbon Neutral Certified'],
            'verification_status' => 'verified',
            'last_verified_at' => now()
        ]);

        $response = $this->getJson("/api/v1/providers/{$provider->id}/certifications");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'certifications',
            'verification_status',
            'last_verified_at'
        ]);
        
        $this->assertCount(2, $response->json('certifications'));
        $this->assertEquals('verified', $response->json('verification_status'));
    }

    #[Test]
    public function it_can_get_provider_statistics()
    {
        Sanctum::actingAs($this->user);
        
        $provider = Provider::factory()->renewable()->create([
            'rating' => 4.5,
            'total_products' => 25,
            'sustainability_score' => 95
        ]);

        $response = $this->getJson("/api/v1/providers/{$provider->id}/statistics");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_products',
            'average_rating',
            'total_sales',
            'sustainability_score',
            'certification_count',
            'is_verified'
        ]);
        
        $this->assertEquals(25, $response->json('total_products'));
        $this->assertEquals(4.5, $response->json('average_rating'));
        $this->assertEquals(95, $response->json('sustainability_score'));
    }

    #[Test]
    public function it_can_create_a_provider()
    {
        Sanctum::actingAs($this->user);
        
        $company = Company::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $providerData = [
            'name' => 'Test Solar Provider',
            'description' => 'A test solar energy provider',
            'company_id' => $company->id,
            'type' => 'renewable',
            'rating' => 4.5,
            'sustainability_score' => 85,
            'certifications' => ['ISO 14001', 'Solar Certified'],
            'contact_info' => [
                'email' => 'contact@testsolar.com',
                'phone' => '+34 123 456 789',
                'website' => 'https://testsolar.com'
            ],
            'tag_ids' => $tags->pluck('id')->toArray()
        ];

        $response = $this->postJson('/api/v1/providers', $providerData);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => 'Test Solar Provider',
            'type' => 'renewable',
            'sustainability_score' => 85
        ]);

        $this->assertDatabaseHas('providers', [
            'name' => 'Test Solar Provider',
            'type' => 'renewable'
        ]);
    }

    #[Test]
    public function it_requires_authentication_for_creating_providers()
    {
        $company = Company::factory()->create();
        
        $providerData = [
            'name' => 'Test Provider',
            'company_id' => $company->id,
            'type' => 'renewable'
        ];

        $response = $this->postJson('/api/v1/providers', $providerData);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_validates_provider_creation_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/providers', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'company_id', 'type']);
    }

    #[Test]
    public function it_can_show_a_specific_provider()
    {
        Sanctum::actingAs($this->user);
        
        $provider = Provider::factory()->renewable()->create();

        $response = $this->getJson("/api/v1/providers/{$provider->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $provider->id,
            'name' => $provider->name,
            'type' => $provider->type
        ]);
    }

    #[Test]
    public function it_can_update_a_provider()
    {
        Sanctum::actingAs($this->user);
        
        $provider = Provider::factory()->create();

        $updateData = [
            'name' => 'Updated Provider Name',
            'sustainability_score' => 95,
            'verification_status' => 'verified'
        ];

        $response = $this->putJson("/api/v1/providers/{$provider->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Updated Provider Name',
            'sustainability_score' => 95,
            'verification_status' => 'verified'
        ]);

        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'name' => 'Updated Provider Name',
            'sustainability_score' => 95
        ]);
    }

    #[Test]
    public function it_can_delete_a_provider()
    {
        Sanctum::actingAs($this->user);
        
        $provider = Provider::factory()->create();

        $response = $this->deleteJson("/api/v1/providers/{$provider->id}");

        $response->assertStatus(204);
        $this->assertModelMissing($provider);
    }

    #[Test]
    public function public_endpoints_work_without_authentication()
    {
        Provider::factory()->count(3)->renewable()->create();

        // Test public listing
        $response = $this->getJson('/api/v1/providers');
        $response->assertStatus(200);

        // Test public top-rated
        $response = $this->getJson('/api/v1/providers/top-rated');
        $response->assertStatus(200);

        // Test public renewable
        $response = $this->getJson('/api/v1/providers/renewable');
        $response->assertStatus(200);

        // Test public show
        $provider = Provider::first();
        $response = $this->getJson("/api/v1/providers/{$provider->id}");
        $response->assertStatus(200);

        // Test public statistics
        $response = $this->getJson("/api/v1/providers/{$provider->id}/statistics");
        $response->assertStatus(200);
    }

    #[Test]
    public function it_returns_404_for_non_existent_provider()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/providers/99999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_paginates_provider_results()
    {
        Sanctum::actingAs($this->user);
        
        Provider::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/providers?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $response->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'last_page', 'per_page', 'total']
        ]);
    }
}
