<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyContract;
use App\Models\User;
use App\Models\Provider;
use App\Models\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class EnergyContractControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    /** @test */
    public function authenticated_user_can_list_energy_contracts()
    {
        Sanctum::actingAs($this->user);
        
        EnergyContract::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/energy-contracts');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'contract_number',
                            'name',
                            'type',
                            'status',
                            'total_value',
                            'start_date',
                            'end_date',
                            'user',
                            'provider',
                            'product'
                        ]
                    ],
                    'links',
                    'meta'
                ],
                'message'
            ]);
    }

    /** @test */
    public function user_can_filter_contracts_by_status()
    {
        Sanctum::actingAs($this->user);
        
        EnergyContract::factory()->active()->count(3)->create();
        EnergyContract::factory()->pending()->count(2)->create();

        $response = $this->getJson('/api/v1/energy-contracts?status=active');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_search_contracts_by_text()
    {
        Sanctum::actingAs($this->user);
        
        EnergyContract::factory()->create(['name' => 'Solar Energy Contract']);
        EnergyContract::factory()->create(['name' => 'Wind Power Contract']);
        EnergyContract::factory()->create(['contract_number' => 'SOL-123']);

        $response = $this->getJson('/api/v1/energy-contracts?search=solar');

        $response->assertOk();
        $this->assertEquals(2, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_create_energy_contract()
    {
        Sanctum::actingAs($this->user);
        
        $provider = Provider::factory()->create();
        $product = Product::factory()->create(['provider_id' => $provider->id]);
        
        $contractData = [
            'user_id' => $this->user->id,
            'provider_id' => $provider->id,
            'product_id' => $product->id,
            'contract_number' => 'CTR-TEST-001',
            'name' => 'Test Energy Contract',
            'type' => 'supply',
            'total_value' => 50000.00,
            'contracted_power' => 25.5,
            'start_date' => now()->addDays(30)->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
            'green_energy_percentage' => 85.5,
            'carbon_neutral' => true,
        ];

        $response = $this->postJson('/api/v1/energy-contracts', $contractData);

        $response->assertCreated()
            ->assertJsonFragment([
                'contract_number' => 'CTR-TEST-001',
                'name' => 'Test Energy Contract',
                'type' => 'supply',
                'carbon_neutral' => true
            ]);

        $this->assertDatabaseHas('energy_contracts', [
            'contract_number' => 'CTR-TEST-001',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function contract_creation_requires_valid_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/energy-contracts', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'user_id',
                'provider_id',
                'product_id',
                'contract_number',
                'name',
                'type',
                'total_value',
                'contracted_power',
                'start_date',
                'end_date'
            ]);
    }

    /** @test */
    public function contract_number_must_be_unique()
    {
        Sanctum::actingAs($this->user);
        
        EnergyContract::factory()->create(['contract_number' => 'CTR-DUPLICATE']);
        
        $provider = Provider::factory()->create();
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/energy-contracts', [
            'user_id' => $this->user->id,
            'provider_id' => $provider->id,
            'product_id' => $product->id,
            'contract_number' => 'CTR-DUPLICATE',
            'name' => 'Test Contract',
            'type' => 'supply',
            'total_value' => 10000,
            'contracted_power' => 10,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contract_number']);
    }

    /** @test */
    public function user_can_view_specific_contract()
    {
        Sanctum::actingAs($this->user);
        
        $contract = EnergyContract::factory()->create();

        $response = $this->getJson("/api/v1/energy-contracts/{$contract->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'name' => $contract->name
            ]);
    }

    /** @test */
    public function user_can_update_contract()
    {
        Sanctum::actingAs($this->user);
        
        $contract = EnergyContract::factory()->create();

        $updateData = [
            'name' => 'Updated Contract Name',
            'total_value' => 75000.00,
            'green_energy_percentage' => 95.0
        ];

        $response = $this->putJson("/api/v1/energy-contracts/{$contract->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Updated Contract Name',
                'total_value' => 75000.00,
                'green_energy_percentage' => 95.0
            ]);

        $this->assertDatabaseHas('energy_contracts', [
            'id' => $contract->id,
            'name' => 'Updated Contract Name',
            'total_value' => 75000.00
        ]);
    }

    /** @test */
    public function user_cannot_delete_active_contract()
    {
        Sanctum::actingAs($this->user);
        
        $contract = EnergyContract::factory()->active()->create();

        $response = $this->deleteJson("/api/v1/energy-contracts/{$contract->id}");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'success' => false,
                'message' => 'No se puede eliminar un contrato activo.'
            ]);

        $this->assertDatabaseHas('energy_contracts', ['id' => $contract->id]);
    }

    /** @test */
    public function user_can_delete_draft_contract()
    {
        Sanctum::actingAs($this->user);
        
        $contract = EnergyContract::factory()->draft()->create();

        $response = $this->deleteJson("/api/v1/energy-contracts/{$contract->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Contrato energético eliminado exitosamente'
            ]);

        $this->assertDatabaseMissing('energy_contracts', ['id' => $contract->id]);
    }

    /** @test */
    public function admin_can_approve_pending_contract()
    {
        Sanctum::actingAs($this->admin);
        
        $contract = EnergyContract::factory()->pending()->create();

        $response = $this->postJson("/api/v1/energy-contracts/{$contract->id}/approve");

        $response->assertOk()
            ->assertJsonFragment([
                'status' => 'active',
                'success' => true
            ]);

        $this->assertDatabaseHas('energy_contracts', [
            'id' => $contract->id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function cannot_approve_non_pending_contract()
    {
        Sanctum::actingAs($this->admin);
        
        $contract = EnergyContract::factory()->active()->create();

        $response = $this->postJson("/api/v1/energy-contracts/{$contract->id}/approve");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Solo se pueden aprobar contratos pendientes'
            ]);
    }

    /** @test */
    public function admin_can_suspend_active_contract()
    {
        Sanctum::actingAs($this->admin);
        
        $contract = EnergyContract::factory()->active()->create();

        $response = $this->postJson("/api/v1/energy-contracts/{$contract->id}/suspend", [
            'reason' => 'Payment issues'
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'status' => 'suspended',
                'success' => true
            ]);
    }

    /** @test */
    public function admin_can_terminate_contract()
    {
        Sanctum::actingAs($this->admin);
        
        $contract = EnergyContract::factory()->active()->create();

        $response = $this->postJson("/api/v1/energy-contracts/{$contract->id}/terminate", [
            'reason' => 'Contract breach'
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'status' => 'terminated',
                'success' => true
            ]);
    }

    /** @test */
    public function user_can_get_their_contracts()
    {
        Sanctum::actingAs($this->user);
        
        // Contratos del usuario
        EnergyContract::factory()->count(3)->create(['user_id' => $this->user->id]);
        
        // Contratos de otros usuarios
        EnergyContract::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/energy-contracts/my-contracts');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
        
        // Verificar que todos los contratos pertenecen al usuario
        foreach ($response->json('data.data') as $contract) {
            $this->assertEquals($this->user->id, $contract['user_id']);
        }
    }

    /** @test */
    public function user_can_get_contracts_analytics()
    {
        Sanctum::actingAs($this->user);
        
        EnergyContract::factory()->active()->count(5)->create();
        EnergyContract::factory()->pending()->count(2)->create();
        EnergyContract::factory()->carbonNeutral()->count(3)->create();

        $response = $this->getJson('/api/v1/energy-contracts/analytics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_contracts',
                    'contracts_by_status',
                    'contracts_by_type',
                    'total_value',
                    'carbon_neutral_percentage',
                    'new_contracts_this_month'
                ],
                'message'
            ]);

        $this->assertTrue($response->json('data.total_contracts') >= 10);
    }

    /** @test */
    public function guest_cannot_access_contracts()
    {
        $response = $this->getJson('/api/v1/energy-contracts');
        $response->assertUnauthorized();
    }

    /** @test */
    public function contract_date_validation_works()
    {
        Sanctum::actingAs($this->user);
        
        $provider = Provider::factory()->create();
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/energy-contracts', [
            'user_id' => $this->user->id,
            'provider_id' => $provider->id,
            'product_id' => $product->id,
            'contract_number' => 'CTR-DATE-TEST',
            'name' => 'Date Test Contract',
            'type' => 'supply',
            'total_value' => 10000,
            'contracted_power' => 10,
            'start_date' => '2024-12-31',
            'end_date' => '2024-01-01', // End date before start date
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }
}