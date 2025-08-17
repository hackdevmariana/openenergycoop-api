<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyProduction;
use App\Models\UserAsset;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class EnergyProductionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_list_energy_productions()
    {
        Sanctum::actingAs($this->user);
        
        EnergyProduction::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/energy-productions');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id', 'production_datetime', 'energy_source',
                            'production_kwh', 'system_efficiency', 'operational_status'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_create_production_record()
    {
        Sanctum::actingAs($this->user);
        
        $userAsset = UserAsset::factory()->create();
        
        $productionData = [
            'user_id' => $this->user->id,
            'user_asset_id' => $userAsset->id,
            'production_datetime' => now()->format('Y-m-d H:i:s'),
            'period_type' => 'daily',
            'energy_source' => 'solar_pv',
            'production_kwh' => 45.5,
            'system_efficiency' => 92.5,
            'renewable_percentage' => 100.0,
        ];

        $response = $this->postJson('/api/v1/energy-productions', $productionData);

        $response->assertCreated()
            ->assertJsonFragment([
                'production_kwh' => 45.5,
                'energy_source' => 'solar_pv',
                'system_efficiency' => 92.5
            ]);
    }

    /** @test */
    public function user_can_get_their_productions()
    {
        Sanctum::actingAs($this->user);
        
        EnergyProduction::factory()->count(3)->create(['user_id' => $this->user->id]);
        EnergyProduction::factory()->count(2)->create(); // Otros usuarios

        $response = $this->getJson('/api/v1/energy-productions/my-productions');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_get_production_analytics()
    {
        Sanctum::actingAs($this->user);
        
        EnergyProduction::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/energy-productions/analytics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_production_kwh',
                    'avg_daily_production',
                    'avg_system_efficiency',
                    'renewable_percentage',
                    'total_revenue',
                    'co2_avoided_kg',
                    'production_by_source'
                ]
            ]);
    }

    /** @test */
    public function user_can_filter_productions_by_energy_source()
    {
        Sanctum::actingAs($this->user);
        
        EnergyProduction::factory()->solar()->count(3)->create();
        EnergyProduction::factory()->wind()->count(2)->create();

        $response = $this->getJson('/api/v1/energy-productions?energy_source=solar_pv');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_filter_productions_by_period_type()
    {
        Sanctum::actingAs($this->user);
        
        EnergyProduction::factory()->count(3)->create(['period_type' => 'daily']);
        EnergyProduction::factory()->count(2)->create(['period_type' => 'hourly']);

        $response = $this->getJson('/api/v1/energy-productions?period_type=daily');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_filter_productions_by_date_range()
    {
        Sanctum::actingAs($this->user);
        
        EnergyProduction::factory()->create(['production_datetime' => '2024-01-15 10:00:00']);
        EnergyProduction::factory()->create(['production_datetime' => '2024-01-20 14:00:00']);
        EnergyProduction::factory()->create(['production_datetime' => '2024-01-25 16:00:00']);

        $response = $this->getJson('/api/v1/energy-productions?date_from=2024-01-18&date_to=2024-01-22');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_view_specific_production()
    {
        Sanctum::actingAs($this->user);
        
        $production = EnergyProduction::factory()->create();

        $response = $this->getJson("/api/v1/energy-productions/{$production->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $production->id,
                'energy_source' => $production->energy_source,
                'production_kwh' => $production->production_kwh
            ]);
    }

    /** @test */
    public function user_can_update_production_record()
    {
        Sanctum::actingAs($this->user);
        
        $production = EnergyProduction::factory()->create();

        $response = $this->putJson("/api/v1/energy-productions/{$production->id}", [
            'production_kwh' => 55.0,
            'system_efficiency' => 95.0,
            'operational_status' => 'online'
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'production_kwh' => 55.0,
                'system_efficiency' => 95.0,
                'operational_status' => 'online'
            ]);
    }

    /** @test */
    public function user_can_delete_production_record()
    {
        Sanctum::actingAs($this->user);
        
        $production = EnergyProduction::factory()->create();

        $response = $this->deleteJson("/api/v1/energy-productions/{$production->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('energy_productions', ['id' => $production->id]);
    }

    /** @test */
    public function guest_cannot_access_productions()
    {
        $response = $this->getJson('/api/v1/energy-productions');
        $response->assertUnauthorized();
    }

    /** @test */
    public function production_creation_requires_valid_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/energy-productions', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'user_id', 'production_datetime', 'period_type',
                'energy_source', 'production_kwh'
            ]);
    }

    /** @test */
    public function energy_source_must_be_valid()
    {
        Sanctum::actingAs($this->user);
        
        $userAsset = UserAsset::factory()->create();

        $response = $this->postJson('/api/v1/energy-productions', [
            'user_id' => $this->user->id,
            'user_asset_id' => $userAsset->id,
            'production_datetime' => now()->format('Y-m-d H:i:s'),
            'period_type' => 'daily',
            'energy_source' => 'invalid_source',
            'production_kwh' => 25.0,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['energy_source']);
    }

    /** @test */
    public function production_kwh_must_be_positive()
    {
        Sanctum::actingAs($this->user);
        
        $userAsset = UserAsset::factory()->create();

        $response = $this->postJson('/api/v1/energy-productions', [
            'user_id' => $this->user->id,
            'user_asset_id' => $userAsset->id,
            'production_datetime' => now()->format('Y-m-d H:i:s'),
            'period_type' => 'daily',
            'energy_source' => 'solar_pv',
            'production_kwh' => -5.0,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['production_kwh']);
    }
}