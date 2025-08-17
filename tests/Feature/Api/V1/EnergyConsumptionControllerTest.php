<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyConsumption;
use App\Models\EnergyContract;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class EnergyConsumptionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_list_energy_consumptions()
    {
        Sanctum::actingAs($this->user);
        
        EnergyConsumption::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/energy-consumptions');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id', 'measurement_datetime', 'period_type',
                            'consumption_kwh', 'renewable_percentage', 'total_cost_eur'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_create_consumption_record()
    {
        Sanctum::actingAs($this->user);
        
        $contract = EnergyContract::factory()->create();
        
        $consumptionData = [
            'user_id' => $this->user->id,
            'energy_contract_id' => $contract->id,
            'measurement_datetime' => now()->format('Y-m-d H:i:s'),
            'period_type' => 'daily',
            'consumption_kwh' => 25.5,
            'renewable_percentage' => 85.0,
            'total_cost_eur' => 12.75
        ];

        $response = $this->postJson('/api/v1/energy-consumptions', $consumptionData);

        $response->assertCreated()
            ->assertJsonFragment([
                'consumption_kwh' => 25.5,
                'renewable_percentage' => 85.0
            ]);
    }

    /** @test */
    public function user_can_get_their_consumptions()
    {
        Sanctum::actingAs($this->user);
        
        EnergyConsumption::factory()->count(3)->create(['user_id' => $this->user->id]);
        EnergyConsumption::factory()->count(2)->create(); // Otros usuarios

        $response = $this->getJson('/api/v1/energy-consumptions/my-consumptions');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_get_consumption_analytics()
    {
        Sanctum::actingAs($this->user);
        
        EnergyConsumption::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/energy-consumptions/analytics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_consumption_kwh',
                    'avg_daily_consumption',
                    'renewable_percentage',
                    'total_cost',
                    'efficiency_score'
                ]
            ]);
    }

    /** @test */
    public function user_can_filter_consumptions_by_period_type()
    {
        Sanctum::actingAs($this->user);
        
        EnergyConsumption::factory()->daily()->count(3)->create();
        EnergyConsumption::factory()->count(2)->create(['period_type' => 'hourly']);

        $response = $this->getJson('/api/v1/energy-consumptions?period_type=daily');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_filter_consumptions_by_date_range()
    {
        Sanctum::actingAs($this->user);
        
        EnergyConsumption::factory()->create(['measurement_datetime' => '2024-01-15 10:00:00']);
        EnergyConsumption::factory()->create(['measurement_datetime' => '2024-01-20 14:00:00']);
        EnergyConsumption::factory()->create(['measurement_datetime' => '2024-01-25 16:00:00']);

        $response = $this->getJson('/api/v1/energy-consumptions?date_from=2024-01-18&date_to=2024-01-22');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.meta.total'));
    }

    /** @test */
    public function guest_cannot_access_consumptions()
    {
        $response = $this->getJson('/api/v1/energy-consumptions');
        $response->assertUnauthorized();
    }

    /** @test */
    public function consumption_creation_requires_valid_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/energy-consumptions', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'user_id', 'measurement_datetime', 'period_type', 'consumption_kwh'
            ]);
    }
}