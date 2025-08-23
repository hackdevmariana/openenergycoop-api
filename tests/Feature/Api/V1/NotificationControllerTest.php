<?php

namespace Tests\Feature\Api\V1;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function it_can_list_notifications()
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/notifications');

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
    public function it_can_filter_notifications_by_type()
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->create(['type' => 'info', 'user_id' => $this->user->id]);
        Notification::factory()->create(['type' => 'success', 'user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/notifications?type=info');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.meta.total'));
        $this->assertEquals('info', $response->json('data.data.0.type'));
    }

    /** @test */
    public function it_can_filter_notifications_by_user()
    {
        Sanctum::actingAs($this->admin);

        $user2 = User::factory()->create();
        Notification::factory()->create(['user_id' => $this->user->id]);
        Notification::factory()->create(['user_id' => $user2->id]);

        $response = $this->getJson('/api/v1/notifications?user_id=' . $this->user->id);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.meta.total'));
        $this->assertEquals($this->user->id, $response->json('data.data.0.user_id'));
    }

    /** @test */
    public function it_can_search_notifications()
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->create([
            'title' => 'Test notification',
            'user_id' => $this->user->id
        ]);
        Notification::factory()->create([
            'title' => 'Another notification',
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/v1/notifications?search=Test');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.meta.total'));
        $this->assertStringContainsString('Test', $response->json('data.data.0.title'));
    }

    /** @test */
    public function it_can_create_notification()
    {
        Sanctum::actingAs($this->admin);

        $data = [
            'user_id' => $this->user->id,
            'title' => 'Test notification',
            'message' => 'This is a test notification',
            'type' => 'info'
        ];

        $response = $this->postJson('/api/v1/notifications', $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Test notification',
            'type' => 'info'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/notifications', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id', 'title', 'message', 'type']);
    }

    /** @test */
    public function it_can_show_notification()
    {
        Sanctum::actingAs($this->user);

        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/notifications/{$notification->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $notification->id,
                        'title' => $notification->title
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_notification()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/notifications/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_notification()
    {
        Sanctum::actingAs($this->admin);

        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'title' => 'Updated notification',
            'type' => 'success'
        ];

        $response = $this->putJson("/api/v1/notifications/{$notification->id}", $data);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'title' => 'Updated notification',
                        'type' => 'success'
                    ]
                ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'title' => 'Updated notification',
            'type' => 'success'
        ]);
    }

    /** @test */
    public function it_can_delete_notification()
    {
        Sanctum::actingAs($this->admin);

        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/notifications/{$notification->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);
    }

    /** @test */
    public function it_can_get_statistics()
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'info',
            'read_at' => null
        ]);
        
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'success',
            'read_at' => now()
        ]);

        $response = $this->getJson('/api/v1/notifications/statistics?user_id=' . $this->user->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'total',
                        'unread',
                        'read',
                        'by_type'
                    ]
                ]);

        $this->assertEquals(2, $response->json('data.total'));
        $this->assertEquals(1, $response->json('data.unread'));
        $this->assertEquals(1, $response->json('data.read'));
    }

    /** @test */
    public function it_can_get_types()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/notifications/types');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => ['info', 'alert', 'success', 'warning', 'error']
                ]);
    }

    /** @test */
    public function it_can_mark_notification_as_read()
    {
        Sanctum::actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null
        ]);

        $response = $this->patchJson("/api/v1/notifications/{$notification->id}/mark-read");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => now()
        ]);
    }

    /** @test */
    public function it_can_mark_notification_as_delivered()
    {
        Sanctum::actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'delivered_at' => null
        ]);

        $response = $this->patchJson("/api/v1/notifications/{$notification->id}/mark-delivered");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'delivered_at' => now()
        ]);
    }

    /** @test */
    public function it_can_get_unread_notifications()
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null
        ]);
        
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => now()
        ]);

        $response = $this->getJson('/api/v1/notifications/unread');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.meta.total'));
    }

    /** @test */
    public function it_can_get_recent_notifications()
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(2)
        ]);
        
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subHours(12)
        ]);

        $response = $this->getJson('/api/v1/notifications/recent?days=1');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.meta.total'));
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_paginate_notifications()
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->count(25)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/notifications?per_page=10');

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('data.meta.per_page'));
        $this->assertEquals(25, $response->json('data.meta.total'));
        $this->assertEquals(3, $response->json('data.meta.last_page'));
    }

    /** @test */
    public function it_can_sort_notifications()
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'A notification',
            'created_at' => now()->subDays(1)
        ]);
        
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Z notification',
            'created_at' => now()
        ]);

        $response = $this->getJson('/api/v1/notifications?sort_by=title&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('A notification', $response->json('data.data.0.title'));
        $this->assertEquals('Z notification', $response->json('data.data.1.title'));
    }
}
