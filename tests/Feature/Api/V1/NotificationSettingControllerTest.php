<?php

namespace Tests\Feature\Api\V1;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationSettingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function it_can_list_notification_settings()
    {
        Sanctum::actingAs($this->user);

        NotificationSetting::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/notification-settings');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data',
                        'meta',
                        'summary',
                        'with'
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.meta.total'));
    }

    /** @test */
    public function it_can_filter_settings_by_channel()
    {
        Sanctum::actingAs($this->user);

        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'email'
        ]);
        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'push'
        ]);

        $response = $this->getJson('/api/v1/notification-settings?channel=email');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.meta.total'));
        $this->assertEquals('email', $response->json('data.data.0.channel'));
    }

    /** @test */
    public function it_can_filter_settings_by_notification_type()
    {
        Sanctum::actingAs($this->user);

        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'notification_type' => 'wallet'
        ]);
        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'notification_type' => 'event'
        ]);

        $response = $this->getJson('/api/v1/notification-settings?notification_type=wallet');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.meta.total'));
        $this->assertEquals('wallet', $response->json('data.data.0.notification_type'));
    }

    /** @test */
    public function it_can_filter_settings_by_enabled_status()
    {
        Sanctum::actingAs($this->user);

        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'enabled' => true
        ]);
        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'enabled' => false
        ]);

        $response = $this->getJson('/api/v1/notification-settings?enabled=true');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.meta.total'));
        $this->assertTrue($response->json('data.data.0.enabled'));
    }

    /** @test */
    public function it_can_create_notification_setting()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'user_id' => $this->user->id,
            'channel' => 'email',
            'notification_type' => 'wallet',
            'enabled' => true
        ];

        $response = $this->postJson('/api/v1/notification-settings', $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        $this->assertDatabaseHas('notification_settings', [
            'user_id' => $this->user->id,
            'channel' => 'email',
            'notification_type' => 'wallet',
            'enabled' => true
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/notification-settings', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id', 'channel', 'notification_type']);
    }

    /** @test */
    public function it_validates_unique_combination_of_user_channel_type()
    {
        Sanctum::actingAs($this->admin);

        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'email',
            'notification_type' => 'wallet'
        ]);

        $data = [
            'user_id' => $this->user->id,
            'channel' => 'email',
            'notification_type' => 'wallet',
            'enabled' => true
        ];

        $response = $this->postJson('/api/v1/notification-settings', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['channel']);
    }

    /** @test */
    public function it_can_show_notification_setting()
    {
        Sanctum::actingAs($this->user);

        $setting = NotificationSetting::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/notification-settings/{$setting->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $setting->id,
                        'channel' => $setting->channel
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_setting()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/notification-settings/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_notification_setting()
    {
        Sanctum::actingAs($this->admin);

        $setting = NotificationSetting::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'enabled' => false
        ];

        $response = $this->putJson("/api/v1/notification-settings/{$setting->id}", $data);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'enabled' => false
                    ]
                ]);

        $this->assertDatabaseHas('notification_settings', [
            'id' => $setting->id,
            'enabled' => false
        ]);
    }

    /** @test */
    public function it_can_delete_notification_setting()
    {
        Sanctum::actingAs($this->admin);

        $setting = NotificationSetting::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/notification-settings/{$setting->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('notification_settings', ['id' => $setting->id]);
    }

    /** @test */
    public function it_can_get_statistics()
    {
        Sanctum::actingAs($this->user);

        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'email',
            'enabled' => true
        ]);
        
        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'push',
            'enabled' => false
        ]);

        $response = $this->getJson('/api/v1/notification-settings/statistics?user_id=' . $this->user->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'total',
                        'enabled',
                        'disabled',
                        'by_channel',
                        'by_type'
                    ]
                ]);

        $this->assertEquals(2, $response->json('data.total'));
        $this->assertEquals(1, $response->json('data.enabled'));
        $this->assertEquals(1, $response->json('data.disabled'));
    }

    /** @test */
    public function it_can_get_channels()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/notification-settings/channels');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => ['email', 'push', 'sms', 'in_app']
                ]);
    }

    /** @test */
    public function it_can_get_notification_types()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/notification-settings/notification-types');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => ['wallet', 'event', 'message', 'general']
                ]);
    }

    /** @test */
    public function it_can_toggle_setting()
    {
        Sanctum::actingAs($this->user);

        $setting = NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'enabled' => false
        ]);

        $response = $this->patchJson("/api/v1/notification-settings/{$setting->id}/toggle");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('notification_settings', [
            'id' => $setting->id,
            'enabled' => true
        ]);
    }

    /** @test */
    public function it_can_get_user_settings()
    {
        Sanctum::actingAs($this->user);

        NotificationSetting::factory()->create(['user_id' => $this->user->id]);
        NotificationSetting::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/notification-settings/user/{$this->user->id}");

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function it_can_create_default_settings_for_user()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/v1/notification-settings/create-defaults/{$this->user->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'created',
                        'settings'
                    ]
                ]);

        $this->assertEquals(16, $response->json('data.created')); // 4 canales × 4 tipos
        $this->assertEquals(16, NotificationSetting::where('user_id', $this->user->id)->count());
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/notification-settings');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_paginate_settings()
    {
        Sanctum::actingAs($this->user);

        NotificationSetting::factory()->count(25)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/notification-settings?per_page=10');

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('data.meta.per_page'));
        $this->assertEquals(25, $response->json('data.meta.total'));
        $this->assertEquals(3, $response->json('data.meta.last_page'));
    }

    /** @test */
    public function it_can_sort_settings()
    {
        Sanctum::actingAs($this->user);

        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'email',
            'created_at' => now()->subDays(1)
        ]);
        
        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'push',
            'created_at' => now()
        ]);

        $response = $this->getJson('/api/v1/notification-settings?sort_by=channel&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('email', $response->json('data.data.0.channel'));
        $this->assertEquals('push', $response->json('data.data.1.channel'));
    }

    /** @test */
    public function it_validates_channel_values()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'user_id' => $this->user->id,
            'channel' => 'invalid_channel',
            'notification_type' => 'wallet'
        ];

        $response = $this->postJson('/api/v1/notification-settings', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['channel']);
    }

    /** @test */
    public function it_validates_notification_type_values()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'user_id' => $this->user->id,
            'channel' => 'email',
            'notification_type' => 'invalid_type'
        ];

        $response = $this->postJson('/api/v1/notification-settings', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['notification_type']);
    }

    /** @test */
    public function it_validates_user_exists()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'user_id' => 99999,
            'channel' => 'email',
            'notification_type' => 'wallet'
        ];

        $response = $this->postJson('/api/v1/notification-settings', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }
}
