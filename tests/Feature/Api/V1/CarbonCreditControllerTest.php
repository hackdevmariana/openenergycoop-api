<?php

namespace Tests\Feature\Api\V1;

use App\Models\CarbonCredit;
use App\Models\User;
use App\Models\Provider;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class CarbonCreditControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    /** @test */
    public function user_can_list_carbon_credits()
    {
        Sanctum::actingAs($this->user);
        
        CarbonCredit::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/carbon-credits');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id', 'credit_id', 'project_name', 'credit_type',
                            'status', 'total_credits', 'available_credits'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_create_carbon_credit()
    {
        Sanctum::actingAs($this->user);
        
        $provider = Provider::factory()->create();
        
        $creditData = [
            'user_id' => $this->user->id,
            'provider_id' => $provider->id,
            'credit_id' => 'CC-TEST-001',
            'credit_type' => 'vcs',
            'project_name' => 'Test Forest Conservation',
            'project_type' => 'Reforestación',
            'project_country' => 'Brasil',
            'project_location' => 'Amazonas, Brasil',
            'total_credits' => 1000.0,
            'available_credits' => 1000.0,
            'vintage_year' => '2023-01-01',
            'credit_period_start' => '2023-01-01',
            'credit_period_end' => '2025-12-31',
        ];

        $response = $this->postJson('/api/v1/carbon-credits', $creditData);

        $response->assertCreated()
            ->assertJsonFragment([
                'credit_id' => 'CC-TEST-001',
                'project_name' => 'Test Forest Conservation',
                'total_credits' => 1000.0
            ]);
    }

    /** @test */
    public function admin_can_verify_carbon_credit()
    {
        Sanctum::actingAs($this->admin);
        
        $credit = CarbonCredit::factory()->create(['status' => 'pending']);

        $response = $this->postJson("/api/v1/carbon-credits/{$credit->id}/verify");

        $response->assertOk()
            ->assertJsonFragment([
                'status' => 'verified',
                'success' => true
            ]);

        $this->assertDatabaseHas('carbon_credits', [
            'id' => $credit->id,
            'status' => 'verified'
        ]);
    }

    /** @test */
    public function cannot_verify_non_pending_credit()
    {
        Sanctum::actingAs($this->admin);
        
        $credit = CarbonCredit::factory()->verified()->create();

        $response = $this->postJson("/api/v1/carbon-credits/{$credit->id}/verify");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Solo se pueden verificar créditos pendientes'
            ]);
    }

    /** @test */
    public function user_can_retire_their_credits()
    {
        Sanctum::actingAs($this->user);
        
        $credit = CarbonCredit::factory()->available()->create([
            'user_id' => $this->user->id,
            'available_credits' => 500.0
        ]);

        $response = $this->postJson("/api/v1/carbon-credits/{$credit->id}/retire", [
            'credits_to_retire' => 100.0,
            'retirement_reason' => 'Carbon offset for business operations'
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'available_credits' => 400.0,
                'retired_credits' => 100.0
            ]);
    }

    /** @test */
    public function cannot_retire_more_credits_than_available()
    {
        Sanctum::actingAs($this->user);
        
        $credit = CarbonCredit::factory()->create([
            'user_id' => $this->user->id,
            'available_credits' => 50.0
        ]);

        $response = $this->postJson("/api/v1/carbon-credits/{$credit->id}/retire", [
            'credits_to_retire' => 100.0,
            'retirement_reason' => 'Test retirement'
        ]);

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'success' => false,
                'message' => 'No hay suficientes créditos disponibles para retirar'
            ]);
    }

    /** @test */
    public function user_can_transfer_credits_to_another_user()
    {
        Sanctum::actingAs($this->user);
        
        $credit = CarbonCredit::factory()->create([
            'user_id' => $this->user->id,
            'available_credits' => 1000.0,
            'transferred_credits' => 0.0
        ]);

        $response = $this->postJson("/api/v1/carbon-credits/{$credit->id}/transfer", [
            'recipient_user_id' => $this->otherUser->id,
            'credits_to_transfer' => 200.0,
            'transfer_price' => 25.50,
            'transfer_notes' => 'Business transaction'
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'original_credit',
                    'new_credit'
                ]
            ]);

        // Verificar que el crédito original se actualizó
        $this->assertDatabaseHas('carbon_credits', [
            'id' => $credit->id,
            'available_credits' => 800.0,
            'transferred_credits' => 200.0
        ]);

        // Verificar que se creó un nuevo crédito para el destinatario
        $this->assertDatabaseHas('carbon_credits', [
            'user_id' => $this->otherUser->id,
            'available_credits' => 200.0,
            'original_owner_id' => $this->user->id
        ]);
    }

    /** @test */
    public function user_can_access_marketplace()
    {
        // Crear créditos disponibles
        CarbonCredit::factory()->available()->count(3)->create();
        CarbonCredit::factory()->create(['status' => 'retired']);

        $response = $this->getJson('/api/v1/carbon-credits/marketplace');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
        
        // Verificar que solo se muestran créditos disponibles
        foreach ($response->json('data.data') as $credit) {
            $this->assertEquals('available', $credit['status']);
            $this->assertGreaterThan(0, $credit['available_credits']);
        }
    }

    /** @test */
    public function user_can_filter_marketplace_by_type()
    {
        CarbonCredit::factory()->available()->create(['credit_type' => 'vcs']);
        CarbonCredit::factory()->available()->create(['credit_type' => 'gold_standard']);
        CarbonCredit::factory()->available()->create(['credit_type' => 'vcs']);

        $response = $this->getJson('/api/v1/carbon-credits/marketplace?credit_type=vcs');

        $response->assertOk();
        $this->assertEquals(2, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_get_their_credits()
    {
        Sanctum::actingAs($this->user);
        
        CarbonCredit::factory()->count(3)->create(['user_id' => $this->user->id]);
        CarbonCredit::factory()->count(2)->create(); // Créditos de otros usuarios

        $response = $this->getJson('/api/v1/carbon-credits/my-credits');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_view_credit_traceability()
    {
        $credit = CarbonCredit::factory()->verified()->create();

        $response = $this->getJson("/api/v1/carbon-credits/{$credit->id}/traceability");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'credit_info' => [
                        'credit_id', 'registry_id', 'serial_number'
                    ],
                    'project_info' => [
                        'project_name', 'project_type', 'project_location'
                    ],
                    'verification_info' => [
                        'verifier_name', 'verification_date'
                    ],
                    'ownership_chain',
                    'impact_metrics'
                ]
            ]);
    }

    /** @test */
    public function user_can_get_analytics()
    {
        CarbonCredit::factory()->verified()->count(5)->create();
        CarbonCredit::factory()->available()->count(3)->create();
        CarbonCredit::factory()->goldStandard()->count(2)->create();

        $response = $this->getJson('/api/v1/carbon-credits/analytics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_credits',
                    'credits_by_status',
                    'credits_by_type',
                    'total_available_credits',
                    'total_retired_credits',
                    'verified_credits_percentage'
                ]
            ]);
    }

    /** @test */
    public function credit_id_must_be_unique()
    {
        Sanctum::actingAs($this->user);
        
        CarbonCredit::factory()->create(['credit_id' => 'CC-UNIQUE']);
        
        $provider = Provider::factory()->create();

        $response = $this->postJson('/api/v1/carbon-credits', [
            'user_id' => $this->user->id,
            'provider_id' => $provider->id,
            'credit_id' => 'CC-UNIQUE',
            'credit_type' => 'vcs',
            'project_name' => 'Test Project',
            'project_type' => 'Solar',
            'project_country' => 'España',
            'project_location' => 'Madrid',
            'total_credits' => 100,
            'available_credits' => 100,
            'vintage_year' => '2023-01-01',
            'credit_period_start' => '2023-01-01',
            'credit_period_end' => '2024-12-31',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['credit_id']);
    }

    /** @test */
    public function cannot_delete_verified_credit()
    {
        Sanctum::actingAs($this->user);
        
        $credit = CarbonCredit::factory()->verified()->create();

        $response = $this->deleteJson("/api/v1/carbon-credits/{$credit->id}");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'success' => false,
                'message' => 'No se puede eliminar un crédito verificado o disponible'
            ]);
    }

    /** @test */
    public function can_delete_pending_credit()
    {
        Sanctum::actingAs($this->user);
        
        $credit = CarbonCredit::factory()->create(['status' => 'pending']);

        $response = $this->deleteJson("/api/v1/carbon-credits/{$credit->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('carbon_credits', ['id' => $credit->id]);
    }

    /** @test */
    public function guest_can_access_public_marketplace()
    {
        CarbonCredit::factory()->available()->count(3)->create();

        $response = $this->getJson('/api/v1/carbon-credits/marketplace');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function guest_can_access_public_analytics()
    {
        CarbonCredit::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/carbon-credits/analytics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['total_credits', 'credits_by_status']
            ]);
    }
}