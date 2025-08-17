<?php

namespace Tests\Feature\Feature\Controllers\Api\V1;

use App\Models\EnergyCooperative;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnergyCooperativeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
    public function it_can_list_energy_cooperatives_for_authenticated_users()
    {
        Sanctum::actingAs($this->user);

        EnergyCooperative::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/energy-cooperatives');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'description',
                        'status',
                        'city',
                        'country'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_create_energy_cooperative()
    {
        Sanctum::actingAs($this->user);

        $cooperativeData = [
            'name' => 'Test Solar Cooperative',
            'code' => 'TSC001',
            'description' => 'A test cooperative for solar energy sharing',
            'status' => 'pending',
            'city' => 'Madrid',
            'country' => 'España',
            'max_members' => 500,
            'open_enrollment' => true,
            'allows_energy_sharing' => true,
            'allows_trading' => true,
        ];

        $response = $this->postJson('/api/v1/energy-cooperatives', $cooperativeData);

        $response->assertCreated()
            ->assertJsonFragment([
                'message' => 'Cooperativa energética creada exitosamente'
            ]);
    }

    /** @test */
    public function it_can_show_specific_cooperative()
    {
        Sanctum::actingAs($this->user);

        $cooperative = EnergyCooperative::factory()->create();

        $response = $this->getJson("/api/v1/energy-cooperatives/{$cooperative->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $cooperative->id);
    }

    /** @test */
    public function it_can_delete_cooperative_without_active_members()
    {
        Sanctum::actingAs($this->user);

        $cooperative = EnergyCooperative::factory()->create();

        $response = $this->deleteJson("/api/v1/energy-cooperatives/{$cooperative->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Cooperativa energética eliminada exitosamente'
            ]);
    }

    /** @test */
    public function it_cannot_delete_cooperative_with_active_members()
    {
        Sanctum::actingAs($this->user);

        $cooperative = EnergyCooperative::factory()->create();
        
        UserSubscription::factory()->count(2)->create([
            'energy_cooperative_id' => $cooperative->id,
            'status' => 'active',
        ]);

        $response = $this->deleteJson("/api/v1/energy-cooperatives/{$cooperative->id}");

        $response->assertStatus(409)
            ->assertJsonFragment([
                'error' => 'No se puede eliminar la cooperativa: tiene miembros activos'
            ]);
    }

    /** @test */
    public function it_can_join_cooperative()
    {
        Sanctum::actingAs($this->user);

        $cooperative = EnergyCooperative::factory()->active()->create([
            'open_enrollment' => true,
            'max_members' => 100,
            'current_members' => 50,
        ]);

        $joinData = [
            'subscription_type' => 'community_membership',
            'plan_name' => 'Basic Community Plan',
            'billing_frequency' => 'monthly',
        ];

        $response = $this->postJson("/api/v1/energy-cooperatives/{$cooperative->id}/join", $joinData);

        $response->assertCreated()
            ->assertJsonFragment([
                'message' => 'Solicitud de membresía enviada exitosamente'
            ]);
    }

    /** @test */
    public function unauthenticated_users_cannot_create_cooperatives()
    {
        $response = $this->postJson('/api/v1/energy-cooperatives', [
            'name' => 'Test Cooperative',
            'code' => 'TEST001',
        ]);

        $response->assertUnauthorized();
    }
}
