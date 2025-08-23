<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyReading;
use App\Models\EnergyMeter;
use App\Models\EnergyInstallation;
use App\Models\ConsumptionPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnergyReadingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $meter;
    protected $installation;
    protected $consumptionPoint;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->meter = EnergyMeter::factory()->create();
        $this->installation = EnergyInstallation::factory()->create();
        $this->consumptionPoint = ConsumptionPoint::factory()->create();
        $this->customer = User::factory()->create();
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_energy_readings()
    {
        EnergyReading::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/energy-readings');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'reading_number',
                            'reading_type',
                            'reading_status',
                            'reading_value',
                            'reading_unit',
                            'reading_timestamp'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page',
                        'last_page'
                    ]
                ]);
    }

    /** @test */
    public function it_can_filter_readings_by_type()
    {
        EnergyReading::factory()->create(['reading_type' => 'instantaneous']);
        EnergyReading::factory()->create(['reading_type' => 'cumulative']);

        $response = $this->getJson('/api/v1/energy-readings?reading_type=instantaneous');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('instantaneous', $response->json('data.0.reading_type'));
    }

    /** @test */
    public function it_can_filter_readings_by_source()
    {
        EnergyReading::factory()->create(['reading_source' => 'automatic']);
        EnergyReading::factory()->create(['reading_source' => 'manual']);

        $response = $this->getJson('/api/v1/energy-readings?reading_source=automatic');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('automatic', $response->json('data.0.reading_source'));
    }

    /** @test */
    public function it_can_filter_readings_by_status()
    {
        EnergyReading::factory()->create(['reading_status' => 'valid']);
        EnergyReading::factory()->create(['reading_status' => 'invalid']);

        $response = $this->getJson('/api/v1/energy-readings?reading_status=valid');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('valid', $response->json('data.0.reading_status'));
    }

    /** @test */
    public function it_can_filter_readings_by_meter()
    {
        $meter2 = EnergyMeter::factory()->create();
        EnergyReading::factory()->create(['meter_id' => $this->meter->id]);
        EnergyReading::factory()->create(['meter_id' => $meter2->id]);

        $response = $this->getJson("/api/v1/energy-readings?meter_id={$this->meter->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals($this->meter->id, $response->json('data.0.meter_id'));
    }

    /** @test */
    public function it_can_filter_readings_by_customer()
    {
        $customer2 = User::factory()->create();
        EnergyReading::factory()->create(['customer_id' => $this->customer->id]);
        EnergyReading::factory()->create(['customer_id' => $customer2->id]);

        $response = $this->getJson("/api/v1/energy-readings?customer_id={$this->customer->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals($this->customer->id, $response->json('data.0.customer_id'));
    }

    /** @test */
    public function it_can_search_readings()
    {
        EnergyReading::factory()->create(['reading_number' => 'RDG-001']);
        EnergyReading::factory()->create(['reading_number' => 'RDG-002']);
        EnergyReading::factory()->create(['notes' => 'Special reading']);

        $response = $this->getJson('/api/v1/energy-readings?search=RDG-001');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('RDG-001', $response->json('data.0.reading_number'));
    }

    /** @test */
    public function it_can_sort_readings()
    {
        EnergyReading::factory()->create(['reading_value' => 100.0]);
        EnergyReading::factory()->create(['reading_value' => 200.0]);
        EnergyReading::factory()->create(['reading_value' => 150.0]);

        $response = $this->getJson('/api/v1/energy-readings?sort_by=reading_value&sort_direction=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(200.0, $data[0]['reading_value']);
        $this->assertEquals(150.0, $data[1]['reading_value']);
        $this->assertEquals(100.0, $data[2]['reading_value']);
    }

    /** @test */
    public function it_can_paginate_readings()
    {
        EnergyReading::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/energy-readings?per_page=10');

        $response->assertStatus(200);
        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /** @test */
    public function it_can_create_energy_reading()
    {
        $data = [
            'reading_number' => 'RDG-001',
            'meter_id' => $this->meter->id,
            'customer_id' => $this->customer->id,
            'reading_type' => 'instantaneous',
            'reading_source' => 'automatic',
            'reading_status' => 'pending',
            'reading_timestamp' => now()->format('Y-m-d H:i:s'),
            'reading_value' => 150.5,
            'reading_unit' => 'kWh',
            'quality_score' => 95.0
        ];

        $response = $this->postJson('/api/v1/energy-readings', $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'reading_number',
                        'reading_type',
                        'reading_status',
                        'reading_value',
                        'reading_unit',
                        'reading_timestamp'
                    ]
                ]);

        $this->assertDatabaseHas('energy_readings', [
            'reading_number' => 'RDG-001',
            'meter_id' => $this->meter->id,
            'reading_value' => 150.5
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/v1/energy-readings', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'reading_number',
                    'meter_id',
                    'customer_id',
                    'reading_type',
                    'reading_source',
                    'reading_status',
                    'reading_timestamp',
                    'reading_value',
                    'reading_unit'
                ]);
    }

    /** @test */
    public function it_validates_unique_reading_number_on_create()
    {
        EnergyReading::factory()->create(['reading_number' => 'RDG-001']);

        $data = [
            'reading_number' => 'RDG-001',
            'meter_id' => $this->meter->id,
            'customer_id' => $this->customer->id,
            'reading_type' => 'instantaneous',
            'reading_source' => 'automatic',
            'reading_status' => 'pending',
            'reading_timestamp' => now()->format('Y-m-d H:i:s'),
            'reading_value' => 150.5,
            'reading_unit' => 'kWh'
        ];

        $response = $this->postJson('/api/v1/energy-readings', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['reading_number']);
    }

    /** @test */
    public function it_can_show_energy_reading()
    {
        $reading = EnergyReading::factory()->create();

        $response = $this->getJson("/api/v1/energy-readings/{$reading->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $reading->id,
                        'reading_number' => $reading->reading_number
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_reading()
    {
        $response = $this->getJson('/api/v1/energy-readings/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_energy_reading()
    {
        $reading = EnergyReading::factory()->create();
        $updateData = [
            'reading_value' => 200.0,
            'quality_score' => 98.0,
            'notes' => 'Updated reading'
        ];

        $response = $this->putJson("/api/v1/energy-readings/{$reading->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'reading_value' => 200.0,
                        'quality_score' => 98.0,
                        'notes' => 'Updated reading'
                    ]
                ]);

        $this->assertDatabaseHas('energy_readings', [
            'id' => $reading->id,
            'reading_value' => 200.0,
            'quality_score' => 98.0
        ]);
    }

    /** @test */
    public function it_validates_unique_reading_number_on_update()
    {
        $reading1 = EnergyReading::factory()->create(['reading_number' => 'RDG-001']);
        $reading2 = EnergyReading::factory()->create(['reading_number' => 'RDG-002']);

        $response = $this->putJson("/api/v1/energy-readings/{$reading2->id}", [
            'reading_number' => 'RDG-001'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['reading_number']);
    }

    /** @test */
    public function it_can_delete_energy_reading()
    {
        $reading = EnergyReading::factory()->create();

        $response = $this->deleteJson("/api/v1/energy-readings/{$reading->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('energy_readings', ['id' => $reading->id]);
    }

    /** @test */
    public function it_can_get_statistics()
    {
        EnergyReading::factory()->create(['reading_value' => 100.0, 'quality_score' => 90.0]);
        EnergyReading::factory()->create(['reading_value' => 200.0, 'quality_score' => 95.0]);

        $response = $this->getJson('/api/v1/energy-readings/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'total_readings',
                    'total_value',
                    'average_value',
                    'average_quality',
                    'readings_by_type',
                    'readings_by_status',
                    'readings_by_source'
                ]);
    }

    /** @test */
    public function it_can_get_reading_types()
    {
        $response = $this->getJson('/api/v1/energy-readings/types');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'types' => [
                        'instantaneous',
                        'cumulative',
                        'demand',
                        'energy',
                        'power'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_reading_sources()
    {
        $response = $this->getJson('/api/v1/energy-readings/sources');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'sources' => [
                        'automatic',
                        'manual',
                        'estimated',
                        'calculated',
                        'imported'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_reading_statuses()
    {
        $response = $this->getJson('/api/v1/energy-readings/statuses');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'statuses' => [
                        'pending',
                        'valid',
                        'invalid',
                        'corrected',
                        'flagged'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_reading_status()
    {
        $reading = EnergyReading::factory()->create(['reading_status' => 'pending']);

        $response = $this->postJson("/api/v1/energy-readings/{$reading->id}/update-status", [
            'reading_status' => 'valid',
            'validation_notes' => 'Validated by system'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'reading_status' => 'valid',
                        'validation_notes' => 'Validated by system'
                    ]
                ]);

        $this->assertDatabaseHas('energy_readings', [
            'id' => $reading->id,
            'reading_status' => 'valid'
        ]);
    }

    /** @test */
    public function it_validates_status_on_update()
    {
        $reading = EnergyReading::factory()->create();

        $response = $this->postJson("/api/v1/energy-readings/{$reading->id}/update-status", [
            'reading_status' => 'invalid_status'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['reading_status']);
    }

    /** @test */
    public function it_can_validate_reading()
    {
        $reading = EnergyReading::factory()->create(['reading_status' => 'pending']);

        $response = $this->postJson("/api/v1/energy-readings/{$reading->id}/validate", [
            'reading_status' => 'valid',
            'validation_notes' => 'Manually validated'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'reading_status' => 'valid',
                        'validated_by' => $this->user->id
                    ]
                ]);

        $this->assertDatabaseHas('energy_readings', [
            'id' => $reading->id,
            'reading_status' => 'valid',
            'validated_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_can_get_valid_readings()
    {
        EnergyReading::factory()->create(['reading_status' => 'valid']);
        EnergyReading::factory()->create(['reading_status' => 'invalid']);
        EnergyReading::factory()->create(['reading_status' => 'valid']);

        $response = $this->getJson('/api/v1/energy-readings/valid');

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
        $this->assertEquals('valid', $response->json('data.0.reading_status'));
    }

    /** @test */
    public function it_can_get_readings_by_type()
    {
        EnergyReading::factory()->create(['reading_type' => 'instantaneous']);
        EnergyReading::factory()->create(['reading_type' => 'cumulative']);
        EnergyReading::factory()->create(['reading_type' => 'instantaneous']);

        $response = $this->getJson('/api/v1/energy-readings/by-type/instantaneous');

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
        $this->assertEquals('instantaneous', $response->json('data.0.reading_type'));
    }

    /** @test */
    public function it_can_get_readings_by_meter()
    {
        $meter2 = EnergyMeter::factory()->create();
        EnergyReading::factory()->create(['meter_id' => $this->meter->id]);
        EnergyReading::factory()->create(['meter_id' => $meter2->id]);
        EnergyReading::factory()->create(['meter_id' => $this->meter->id]);

        $response = $this->getJson("/api/v1/energy-readings/by-meter/{$this->meter->id}");

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
        $this->assertEquals($this->meter->id, $response->json('data.0.meter_id'));
    }

    /** @test */
    public function it_can_get_readings_by_customer()
    {
        $customer2 = User::factory()->create();
        EnergyReading::factory()->create(['customer_id' => $this->customer->id]);
        EnergyReading::factory()->create(['customer_id' => $customer2->id]);
        EnergyReading::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->getJson("/api/v1/energy-readings/by-customer/{$this->customer->id}");

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
        $this->assertEquals($this->customer->id, $response->json('data.0.customer_id'));
    }

    /** @test */
    public function it_can_get_high_quality_readings()
    {
        EnergyReading::factory()->create(['quality_score' => 95.0]);
        EnergyReading::factory()->create(['quality_score' => 85.0]);
        EnergyReading::factory()->create(['quality_score' => 75.0]);

        $response = $this->getJson('/api/v1/energy-readings/high-quality');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals(95.0, $response->json('data.0.quality_score'));
    }

    /** @test */
    public function it_can_get_today_readings()
    {
        EnergyReading::factory()->create(['reading_timestamp' => now()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subDay()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subDays(2)]);

        $response = $this->getJson('/api/v1/energy-readings/today');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_this_month_readings()
    {
        EnergyReading::factory()->create(['reading_timestamp' => now()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subMonth()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subMonths(2)]);

        $response = $this->getJson('/api/v1/energy-readings/this-month');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_requires_authentication()
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/energy-readings');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_logs_activity_on_create()
    {
        $data = [
            'reading_number' => 'RDG-001',
            'meter_id' => $this->meter->id,
            'customer_id' => $this->customer->id,
            'reading_type' => 'instantaneous',
            'reading_source' => 'automatic',
            'reading_status' => 'pending',
            'reading_timestamp' => now()->format('Y-m-d H:i:s'),
            'reading_value' => 150.5,
            'reading_unit' => 'kWh'
        ];

        $response = $this->postJson('/api/v1/energy-readings', $data);

        $response->assertStatus(201);
        // Aquí se verificaría que se haya registrado la actividad en el log
    }
}
