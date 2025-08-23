<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyForecast;
use App\Models\User;
use App\Models\EnergySource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyForecastControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_energy_forecasts()
    {
        EnergyForecast::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'forecast_number', 'name', 'forecast_type',
                        'forecast_horizon', 'forecast_method', 'forecast_status'
                    ]
                ],
                'meta', 'links'
            ]);
    }

    public function test_index_with_filters()
    {
        EnergyForecast::factory()->create(['forecast_type' => 'demand']);
        EnergyForecast::factory()->create(['forecast_type' => 'generation']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts?forecast_type=demand');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_index_with_search()
    {
        EnergyForecast::factory()->create(['name' => 'Solar Forecast']);
        EnergyForecast::factory()->create(['name' => 'Wind Forecast']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts?search=Solar');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_store_creates_energy_forecast()
    {
        $data = [
            'name' => 'Test Forecast',
            'forecast_type' => 'demand',
            'forecast_horizon' => 'daily',
            'forecast_method' => 'statistical',
            'forecast_start_time' => now(),
            'forecast_end_time' => now()->addDays(1)
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-forecasts', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'message', 'data' => [
                    'id', 'forecast_number', 'name', 'forecast_type'
                ]
            ]);

        $this->assertDatabaseHas('energy_forecasts', [
            'name' => 'Test Forecast',
            'forecast_type' => 'demand'
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-forecasts', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'forecast_type', 'forecast_horizon', 'forecast_method']);
    }

    public function test_store_validates_forecast_number_uniqueness()
    {
        EnergyForecast::factory()->create(['forecast_number' => 'FC-001']);

        $data = [
            'forecast_number' => 'FC-001',
            'name' => 'Test Forecast',
            'forecast_type' => 'demand',
            'forecast_horizon' => 'daily',
            'forecast_method' => 'statistical',
            'forecast_start_time' => now(),
            'forecast_end_time' => now()->addDays(1)
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-forecasts', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['forecast_number']);
    }

    public function test_show_returns_energy_forecast()
    {
        $forecast = EnergyForecast::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-forecasts/{$forecast->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'forecast_number', 'name', 'forecast_type'
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_forecast()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/999');

        $response->assertNotFound();
    }

    public function test_update_modifies_energy_forecast()
    {
        $forecast = EnergyForecast::factory()->create();
        $updateData = ['name' => 'Updated Forecast Name'];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-forecasts/{$forecast->id}", $updateData);

        $response->assertOk()
            ->assertJsonStructure([
                'message', 'data' => ['id', 'name']
            ]);

        $this->assertDatabaseHas('energy_forecasts', [
            'id' => $forecast->id,
            'name' => 'Updated Forecast Name'
        ]);
    }

    public function test_update_validates_forecast_number_uniqueness()
    {
        $forecast1 = EnergyForecast::factory()->create(['forecast_number' => 'FC-001']);
        $forecast2 = EnergyForecast::factory()->create(['forecast_number' => 'FC-002']);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-forecasts/{$forecast2->id}", [
                'forecast_number' => 'FC-001'
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['forecast_number']);
    }

    public function test_destroy_deletes_energy_forecast()
    {
        $forecast = EnergyForecast::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/energy-forecasts/{$forecast->id}");

        $response->assertOk();
        $this->assertSoftDeleted('energy_forecasts', ['id' => $forecast->id]);
    }

    public function test_statistics_returns_forecast_statistics()
    {
        EnergyForecast::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total', 'by_type', 'by_status', 'by_horizon', 'by_method', 'by_accuracy'
                ]
            ]);
    }

    public function test_types_returns_forecast_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/types');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_horizons_returns_forecast_horizons()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/horizons');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_methods_returns_forecast_methods()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/methods');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_statuses_returns_forecast_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/statuses');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_accuracy_levels_returns_accuracy_levels()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/accuracy-levels');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_update_status_modifies_forecast_status()
    {
        $forecast = EnergyForecast::factory()->create(['forecast_status' => 'draft']);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/energy-forecasts/{$forecast->id}/update-status", [
                'forecast_status' => 'active'
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('energy_forecasts', [
            'id' => $forecast->id,
            'forecast_status' => 'active'
        ]);
    }

    public function test_update_status_validates_status()
    {
        $forecast = EnergyForecast::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/energy-forecasts/{$forecast->id}/update-status", [
                'forecast_status' => 'invalid_status'
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['forecast_status']);
    }

    public function test_duplicate_creates_copy_of_forecast()
    {
        $forecast = EnergyForecast::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-forecasts/{$forecast->id}/duplicate");

        $response->assertOk()
            ->assertJsonStructure([
                'message', 'data' => ['id', 'name']
            ]);

        $this->assertDatabaseHas('energy_forecasts', [
            'name' => $forecast->name . ' (Copy)',
            'forecast_status' => 'draft'
        ]);
    }

    public function test_active_returns_active_forecasts()
    {
        EnergyForecast::factory()->create(['forecast_status' => 'active']);
        EnergyForecast::factory()->create(['forecast_status' => 'draft']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/active');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_validated_returns_validated_forecasts()
    {
        EnergyForecast::factory()->create(['forecast_status' => 'validated']);
        EnergyForecast::factory()->create(['forecast_status' => 'draft']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/validated');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_type_returns_forecasts_by_type()
    {
        EnergyForecast::factory()->create(['forecast_type' => 'demand']);
        EnergyForecast::factory()->create(['forecast_type' => 'generation']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/by-type/demand');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_horizon_returns_forecasts_by_horizon()
    {
        EnergyForecast::factory()->create(['forecast_horizon' => 'daily']);
        EnergyForecast::factory()->create(['forecast_horizon' => 'monthly']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/by-horizon/daily');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_high_accuracy_returns_high_accuracy_forecasts()
    {
        EnergyForecast::factory()->create(['accuracy_level' => 'high']);
        EnergyForecast::factory()->create(['accuracy_level' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/high-accuracy');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_expiring_returns_expired_forecasts()
    {
        EnergyForecast::factory()->create(['expiry_time' => now()->subDay()]);
        EnergyForecast::factory()->create(['expiry_time' => now()->addDay()]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/expiring');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_source_returns_forecasts_by_source()
    {
        $source = EnergySource::factory()->create();
        EnergyForecast::factory()->create(['source_id' => $source->id]);
        EnergyForecast::factory()->create(['source_id' => null]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-forecasts/by-source/{$source->id}");

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_target_returns_forecasts_by_target()
    {
        EnergyForecast::factory()->create(['target_id' => 1]);
        EnergyForecast::factory()->create(['target_id' => 2]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts/by-target/1');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_pagination_with_limit_parameter()
    {
        EnergyForecast::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-forecasts?limit=10');

        $response->assertOk();
        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(10, $response->json('meta.per_page'));
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/energy-forecasts');
        $response->assertUnauthorized();
    }

    public function test_logs_activity_on_create()
    {
        $data = [
            'name' => 'Test Forecast',
            'forecast_type' => 'demand',
            'forecast_horizon' => 'daily',
            'forecast_method' => 'statistical',
            'forecast_start_time' => now(),
            'forecast_end_time' => now()->addDays(1)
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/energy-forecasts', $data);

        // Verificar que se creó el registro en la base de datos
        $this->assertDatabaseHas('energy_forecasts', [
            'name' => 'Test Forecast',
            'created_by' => $this->user->id
        ]);
    }
}
