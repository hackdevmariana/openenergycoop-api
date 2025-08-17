<?php

namespace Tests\Feature\Feature\Controllers\Api\V1;

use App\Models\EnergySharing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnergySharingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $consumer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->consumer = User::factory()->create();
    }

    /** @test */
    public function it_can_list_energy_sharings_for_user()
    {
        Sanctum::actingAs($this->user);

        EnergySharing::factory()->count(2)->create(['provider_user_id' => $this->user->id]);
        EnergySharing::factory()->count(1)->create(['consumer_user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/energy-sharings');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_energy_sharing()
    {
        Sanctum::actingAs($this->user);

        $sharingData = [
            'consumer_user_id' => $this->consumer->id,
            'sharing_code' => 'ES-123456',
            'title' => 'Excedente Solar Disponible',
            'sharing_type' => 'direct',
            'energy_amount_kwh' => 100.50,
            'is_renewable' => true,
            'sharing_start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'sharing_end_datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'proposal_expiry_datetime' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'duration_hours' => 8,
            'price_per_kwh' => 0.15,
            'payment_method' => 'credits',
            'allows_partial_delivery' => true,
            'certified_green_energy' => true,
            'real_time_tracking' => false,
        ];

        $response = $this->postJson('/api/v1/energy-sharings', $sharingData);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Propuesta de intercambio de energía creada exitosamente']);
    }

    /** @test */
    public function it_can_accept_energy_sharing()
    {
        Sanctum::actingAs($this->consumer);

        $sharing = EnergySharing::factory()->proposed()->create([
            'provider_user_id' => $this->user->id,
            'consumer_user_id' => $this->consumer->id,
        ]);

        $response = $this->postJson("/api/v1/energy-sharings/{$sharing->id}/accept");

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Intercambio de energía aceptado exitosamente']);
    }

    /** @test */
    public function it_can_complete_energy_sharing()
    {
        Sanctum::actingAs($this->user);

        $sharing = EnergySharing::factory()->active()->create([
            'provider_user_id' => $this->user->id,
            'consumer_user_id' => $this->consumer->id,
            'energy_amount_kwh' => 100,
        ]);

        $completionData = [
            'energy_delivered_kwh' => 95.5,
            'quality_score' => 4.5,
            'delivery_efficiency' => 95.5,
        ];

        $response = $this->postJson("/api/v1/energy-sharings/{$sharing->id}/complete", $completionData);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Intercambio de energía completado exitosamente']);
    }

    /** @test */
    public function it_can_get_my_sharings()
    {
        Sanctum::actingAs($this->user);

        EnergySharing::factory()->count(2)->create(['provider_user_id' => $this->user->id]);
        EnergySharing::factory()->count(1)->create(['consumer_user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/energy-sharings/my-sharings');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data', 'summary']);
    }

    /** @test */
    public function only_consumer_can_accept_sharing()
    {
        Sanctum::actingAs($this->user); // Provider trying to accept

        $sharing = EnergySharing::factory()->proposed()->create([
            'provider_user_id' => $this->user->id,
            'consumer_user_id' => $this->consumer->id,
        ]);

        $response = $this->postJson("/api/v1/energy-sharings/{$sharing->id}/accept");

        $response->assertStatus(403)
            ->assertJsonFragment(['error' => 'Solo el consumidor puede aceptar el intercambio']);
    }

    /** @test */
    public function it_can_rate_completed_sharing()
    {
        Sanctum::actingAs($this->consumer);

        $sharing = EnergySharing::factory()->completed()->create([
            'provider_user_id' => $this->user->id,
            'consumer_user_id' => $this->consumer->id,
        ]);

        $ratingData = [
            'rating' => 4.5,
            'feedback' => 'Excelente experiencia de intercambio',
            'would_repeat' => true,
        ];

        $response = $this->postJson("/api/v1/energy-sharings/{$sharing->id}/rate", $ratingData);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Calificación enviada exitosamente']);
    }
}
