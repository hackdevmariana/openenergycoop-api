<?php

namespace Tests\Feature\Feature\Controllers\Api\V1;

use App\Models\UserSubscription;
use App\Models\User;
use App\Models\EnergyCooperative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserSubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_user_subscriptions()
    {
        Sanctum::actingAs($this->user);

        UserSubscription::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/user-subscriptions');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_subscription()
    {
        Sanctum::actingAs($this->user);

        $subscriptionData = [
            'subscription_type' => 'basic_renewable',
            'plan_name' => 'Plan Solar Básico',
            'service_category' => 'energy_supply',
            'start_date' => '2024-01-01',
            'billing_frequency' => 'monthly',
            'price' => 50.00,
        ];

        $response = $this->postJson('/api/v1/user-subscriptions', $subscriptionData);

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Suscripción creada exitosamente']);
    }

    /** @test */
    public function it_can_pause_subscription()
    {
        Sanctum::actingAs($this->user);

        $subscription = UserSubscription::factory()->active()->create(['user_id' => $this->user->id]);

        $response = $this->postJson("/api/v1/user-subscriptions/{$subscription->id}/pause");

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Suscripción pausada exitosamente']);
    }

    /** @test */
    public function it_can_get_my_subscriptions()
    {
        Sanctum::actingAs($this->user);

        UserSubscription::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/user-subscriptions/my-subscriptions');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data', 'summary']);
    }

    /** @test */
    public function users_can_only_see_their_own_subscriptions()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        UserSubscription::factory()->create(['user_id' => $otherUser->id]);
        UserSubscription::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/user-subscriptions');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
