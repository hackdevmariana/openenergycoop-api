<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Organization;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use App\Models\UserAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserAssetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;
    protected Provider $provider;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->provider = Provider::factory()->renewable()->create();
        $this->product = Product::factory()->solar()->for($this->provider)->create();
        
        // Asignar rol de admin al usuario para que tenga permisos completos
        $this->user->assignRole('admin');
        
        // Crear un perfil para el usuario para que tenga una organización asociada
        \App\Models\CustomerProfile::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
        ]);
    }

    #[Test]
    public function it_can_list_user_assets()
    {
        Sanctum::actingAs($this->user);
        
        UserAsset::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);

        $response = $this->getJson('/api/v1/user-assets');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user',
                    'product',
                    'quantity',
                    'total_investment',
                    'current_value',
                    'daily_yield',
                    'auto_reinvest',
                    'status'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_can_get_my_assets()
    {
        Sanctum::actingAs($this->user);
        
        $otherUser = User::factory()->create();
        
        // Assets del usuario actual
        UserAsset::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'total_investment' => 1000,
            'current_value' => 1100,
            'daily_yield' => 5
        ]);
        
        // Assets de otro usuario (no deben aparecer)
        UserAsset::factory()->create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id
        ]);

        $response = $this->getJson('/api/v1/user-assets/my-assets');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'summary' => [
                'total_assets',
                'total_investment',
                'total_current_value',
                'total_daily_yield',
                'average_roi',
                'active_assets'
            ]
        ]);
        
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(2000, $response->json('summary.total_investment'));
        $this->assertEquals(2200, $response->json('summary.total_current_value'));
    }

    #[Test]
    public function it_can_get_asset_performance()
    {
        Sanctum::actingAs($this->user);
        
        $asset = UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'total_investment' => 1000,
            'current_value' => 1150,
            'daily_yield' => 3
        ]);

        $response = $this->getJson("/api/v1/user-assets/{$asset->id}/performance");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'asset_id',
            'roi_percentage',
            'daily_yield',
            'projected_annual_return',
            'total_return_to_date',
            'performance_chart',
            'auto_reinvest_enabled',
            'sustainability_impact' => [
                'co2_saved_kg',
                'renewable_energy_kwh'
            ]
        ]);
        
        $this->assertEquals(15, $response->json('roi_percentage'));
        $this->assertEquals(3, $response->json('daily_yield'));
        $this->assertEquals(150, $response->json('total_return_to_date'));
    }

    #[Test]
    public function it_can_toggle_auto_reinvest()
    {
        Sanctum::actingAs($this->user);
        
        $asset = UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'auto_reinvest' => false
        ]);

        $response = $this->postJson("/api/v1/user-assets/{$asset->id}/toggle-auto-reinvest");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'auto_reinvest' => true,
            'message' => 'Auto-reinversión activada'
        ]);

        $this->assertDatabaseHas('user_assets', [
            'id' => $asset->id,
            'auto_reinvest' => true
        ]);
    }

    #[Test]
    public function it_can_process_asset_yield()
    {
        Sanctum::actingAs($this->user);
        
        $asset = UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'current_value' => 1000,
            'daily_yield' => 5,
            'auto_reinvest' => false
        ]);

        $response = $this->postJson("/api/v1/user-assets/{$asset->id}/process-yield");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'yield_amount',
            'old_value',
            'new_current_value',
            'reinvested',
            'processed_at'
        ]);
        
        $this->assertEquals(5, $response->json('yield_amount'));
        $this->assertEquals(1000, $response->json('old_value'));
        $this->assertEquals(1005, $response->json('new_current_value'));
        $this->assertFalse($response->json('reinvested'));
    }

    #[Test]
    public function it_can_get_portfolio_summary()
    {
        Sanctum::actingAs($this->user);
        
        $solarProduct = Product::factory()->solar()->for($this->provider)->create();
        $windProduct = Product::factory()->for($this->provider)->create(['type' => 'wind']);
        
        UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $solarProduct->id,
            'total_investment' => 1000,
            'current_value' => 1100,
            'daily_yield' => 3,
            'status' => 'active'
        ]);
        
        UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $windProduct->id,
            'total_investment' => 2000,
            'current_value' => 2200,
            'daily_yield' => 5,
            'status' => 'active'
        ]);

        $response = $this->getJson('/api/v1/user-assets/portfolio-summary');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_assets',
            'total_investment',
            'current_value',
            'total_yield',
            'overall_roi_percentage',
            'daily_yield_total',
            'asset_distribution',
            'top_performers',
            'sustainability_impact' => [
                'total_co2_saved',
                'renewable_energy_total'
            ]
        ]);
        
        $this->assertEquals(2, $response->json('total_assets'));
        $this->assertEquals(3000, $response->json('total_investment'));
        $this->assertEquals(3300, $response->json('current_value'));
        $this->assertEquals(300, $response->json('total_yield'));
        $this->assertEquals(10, $response->json('overall_roi_percentage'));
    }

    #[Test]
    public function it_can_create_a_user_asset()
    {
        Sanctum::actingAs($this->user);

        $assetData = [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2.5,
            'total_investment' => 2500,
            'daily_yield' => 8.5,
            'auto_reinvest' => true,
            'risk_tolerance' => 'medium',
            'investment_strategy' => 'balanced'
        ];

        $response = $this->postJson('/api/v1/user-assets', $assetData);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'quantity' => 2.5,
            'total_investment' => 2500,
            'daily_yield' => 8.5,
            'auto_reinvest' => true,
            'status' => 'active'
        ]);

        $this->assertDatabaseHas('user_assets', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2.5,
            'total_investment' => 2500
        ]);
    }

    #[Test]
    public function it_validates_asset_creation_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/user-assets', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'product_id', 'quantity', 'total_investment']);
    }

    #[Test]
    public function it_can_update_a_user_asset()
    {
        Sanctum::actingAs($this->user);
        
        $asset = UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);

        $updateData = [
            'daily_yield' => 10,
            'auto_reinvest' => true,
            'risk_tolerance' => 'high',
            'notes' => 'Updated investment notes'
        ];

        $response = $this->putJson("/api/v1/user-assets/{$asset->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'daily_yield' => 10,
            'auto_reinvest' => true,
            'risk_tolerance' => 'high'
        ]);

        $this->assertDatabaseHas('user_assets', [
            'id' => $asset->id,
            'daily_yield' => 10,
            'auto_reinvest' => true
        ]);
    }

    #[Test]
    public function it_can_delete_a_user_asset()
    {
        Sanctum::actingAs($this->user);
        
        $asset = UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);

        $response = $this->deleteJson("/api/v1/user-assets/{$asset->id}");

        $response->assertStatus(204);
        $this->assertModelMissing($asset);
    }

    #[Test]
    public function it_requires_authentication_for_user_assets()
    {
        $response = $this->getJson('/api/v1/user-assets');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/user-assets/my-assets');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/user-assets/portfolio-summary');
        $response->assertStatus(401);
    }

    #[Test]
    public function it_filters_assets_by_user()
    {
        Sanctum::actingAs($this->user);
        
        $otherUser = User::factory()->create();
        
        UserAsset::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
        
        UserAsset::factory()->create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id
        ]);

        $response = $this->getJson("/api/v1/user-assets?user_id={$this->user->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $asset) {
            $this->assertEquals($this->user->id, $asset['user']['id']);
        }
    }

    #[Test]
    public function it_filters_assets_by_status()
    {
        Sanctum::actingAs($this->user);
        
        UserAsset::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'status' => 'active'
        ]);
        
        UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'status' => 'inactive'
        ]);

        $response = $this->getJson('/api/v1/user-assets?status=active');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $asset) {
            $this->assertEquals('active', $asset['status']);
        }
    }
}
