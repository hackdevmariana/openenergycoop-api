<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnergyTransferControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $energyTransfer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->energyTransfer = EnergyTransfer::factory()->create();
    }

    public function test_index_returns_paginated_energy_transfers()
    {
        EnergyTransfer::factory()->count(15)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'transfer_number', 'name', 'transfer_type', 'status',
                        'priority', 'transfer_amount_kwh', 'scheduled_start_time',
                        'created_at', 'updated_at'
                    ]
                ],
                'links', 'meta'
            ]);
    }

    public function test_index_with_filters()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers?status=pending&transfer_type=generation&priority=high');

        $response->assertStatus(200);
    }

    public function test_index_with_search()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers?search=test');

        $response->assertStatus(200);
    }

    public function test_index_with_sorting()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers?sort=transfer_amount_kwh&order=desc');

        $response->assertStatus(200);
    }

    public function test_index_with_pagination_limit()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers?per_page=5');

        $response->assertStatus(200);
    }

    public function test_store_creates_new_energy_transfer()
    {
        $data = [
            'name' => 'Test Transfer',
            'description' => 'Test Description',
            'transfer_type' => 'generation',
            'status' => 'pending',
            'priority' => 'normal',
            'transfer_amount_kwh' => 1000.00,
            'scheduled_start_time' => now()->addHour(),
            'scheduled_end_time' => now()->addHours(5),
            'currency' => 'EUR',
            'is_automated' => false,
            'requires_approval' => true
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-transfers', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'transfer_number', 'name', 'description', 'transfer_type',
                    'status', 'priority', 'transfer_amount_kwh', 'scheduled_start_time',
                    'scheduled_end_time', 'currency', 'is_automated', 'requires_approval',
                    'created_at', 'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('energy_transfers', [
            'name' => 'Test Transfer',
            'transfer_type' => 'generation',
            'status' => 'pending'
        ]);
    }

    public function test_store_validates_unique_transfer_number()
    {
        $existingTransfer = EnergyTransfer::factory()->create(['transfer_number' => 'TRF-001']);

        $data = [
            'name' => 'Test Transfer',
            'transfer_type' => 'generation',
            'status' => 'pending',
            'priority' => 'normal',
            'transfer_amount_kwh' => 1000.00,
            'scheduled_start_time' => now()->addHour(),
            'scheduled_end_time' => now()->addHours(5),
            'currency' => 'EUR'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-transfers', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['transfer_number']);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-transfers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'transfer_type', 'status', 'priority', 'transfer_amount_kwh']);
    }

    public function test_show_returns_energy_transfer()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-transfers/{$this->energyTransfer->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'transfer_number', 'name', 'description', 'transfer_type',
                    'status', 'priority', 'transfer_amount_kwh', 'scheduled_start_time',
                    'scheduled_end_time', 'currency', 'is_automated', 'requires_approval',
                    'created_at', 'updated_at'
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_transfer()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/99999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_energy_transfer()
    {
        $data = [
            'name' => 'Updated Transfer',
            'description' => 'Updated Description',
            'priority' => 'high'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-transfers/{$this->energyTransfer->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Transfer',
                    'description' => 'Updated Description',
                    'priority' => 'high'
                ]
            ]);

        $this->assertDatabaseHas('energy_transfers', [
            'id' => $this->energyTransfer->id,
            'name' => 'Updated Transfer',
            'priority' => 'high'
        ]);
    }

    public function test_update_validates_unique_transfer_number()
    {
        $existingTransfer = EnergyTransfer::factory()->create(['transfer_number' => 'TRF-002']);

        $data = ['transfer_number' => 'TRF-002'];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-transfers/{$this->energyTransfer->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['transfer_number']);
    }

    public function test_destroy_deletes_energy_transfer()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/energy-transfers/{$this->energyTransfer->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('energy_transfers', [
            'id' => $this->energyTransfer->id
        ]);
    }

    public function test_statistics_returns_transfer_statistics()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_transfers', 'pending_transfers', 'in_progress_transfers',
                    'completed_transfers', 'cancelled_transfers', 'total_energy_transferred',
                    'average_efficiency', 'total_cost'
                ]
            ]);
    }

    public function test_types_returns_transfer_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_statuses_returns_transfer_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/statuses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_priorities_returns_transfer_priorities()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/priorities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'count']
                ]
            ]);
    }

    public function test_update_status_updates_transfer_status()
    {
        $data = ['status' => 'in_progress'];

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/energy-transfers/{$this->energyTransfer->id}/update-status", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['status' => 'in_progress']
            ]);

        $this->assertDatabaseHas('energy_transfers', [
            'id' => $this->energyTransfer->id,
            'status' => 'in_progress'
        ]);
    }

    public function test_update_status_validates_status()
    {
        $data = ['status' => 'invalid_status'];

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/energy-transfers/{$this->energyTransfer->id}/update-status", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_duplicate_creates_copy_of_transfer()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-transfers/{$this->energyTransfer->id}/duplicate");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'transfer_number', 'name', 'description', 'transfer_type',
                    'status', 'priority', 'transfer_amount_kwh'
                ]
            ]);

        $this->assertDatabaseCount('energy_transfers', 2);
    }

    public function test_overdue_returns_overdue_transfers()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/overdue');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'status', 'scheduled_end_time']
                ]
            ]);
    }

    public function test_due_soon_returns_due_soon_transfers()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/due-soon');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'status', 'scheduled_start_time']
                ]
            ]);
    }

    public function test_by_type_returns_transfers_by_type()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/by-type/generation');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'transfer_type']
                ]
            ]);
    }

    public function test_high_priority_returns_high_priority_transfers()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/high-priority');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'priority']
                ]
            ]);
    }

    public function test_automated_returns_automated_transfers()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/automated');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'is_automated']
                ]
            ]);
    }

    public function test_manual_returns_manual_transfers()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/manual');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'is_automated']
                ]
            ]);
    }

    public function test_requires_approval_returns_transfers_requiring_approval()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/requires-approval');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'requires_approval']
                ]
            ]);
    }

    public function test_approved_returns_approved_transfers()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/approved');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'is_approved']
                ]
            ]);
    }

    public function test_verified_returns_verified_transfers()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/verified');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'is_verified']
                ]
            ]);
    }

    public function test_by_entity_returns_transfers_by_entity()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/by-entity/App\\Models\\User/1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'source_id', 'source_type']
                ]
            ]);
    }

    public function test_by_currency_returns_transfers_by_currency()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/by-currency/EUR');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'currency']
                ]
            ]);
    }

    public function test_by_amount_range_returns_transfers_by_amount_range()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/by-amount-range?min=100&max=1000');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'transfer_amount_kwh']
                ]
            ]);
    }

    public function test_by_efficiency_range_returns_transfers_by_efficiency_range()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-transfers/by-efficiency-range?min=80&max=100');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'transfer_number', 'name', 'efficiency_percentage']
                ]
            ]);
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/energy-transfers');
        $response->assertStatus(401);
    }

    public function test_logs_activity_on_create()
    {
        $data = [
            'name' => 'Test Transfer',
            'transfer_type' => 'generation',
            'status' => 'pending',
            'priority' => 'normal',
            'transfer_amount_kwh' => 1000.00,
            'scheduled_start_time' => now()->addHour(),
            'scheduled_end_time' => now()->addHours(5),
            'currency' => 'EUR'
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/energy-transfers', $data);

        // Verificar que se registró la actividad (si tienes un sistema de logging)
        // $this->assertDatabaseHas('activity_log', [...]);
    }
}
