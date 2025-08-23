<?php

namespace Tests\Unit\Models;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'title',
            'message',
            'type',
            'read_at',
            'delivered_at',
        ];

        $this->assertEquals($fillable, (new Notification())->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'user_id' => 'int',
            'read_at' => 'datetime',
            'delivered_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];

        $this->assertEquals($casts, (new Notification())->getCasts());
    }

    /** @test */
    public function it_has_correct_constants()
    {
        $this->assertEquals([
            'info',
            'alert',
            'success',
            'warning',
            'error',
        ], Notification::TYPES);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($this->user->id, $notification->user->id);
    }

    /** @test */
    public function it_can_check_if_read()
    {
        $notification = Notification::factory()->create([
            'read_at' => null
        ]);

        $this->assertFalse($notification->isRead());

        $notification->update(['read_at' => now()]);
        $this->assertTrue($notification->isRead());
    }

    /** @test */
    public function it_can_check_if_delivered()
    {
        $notification = Notification::factory()->create([
            'delivered_at' => null
        ]);

        $this->assertFalse($notification->isDelivered());

        $notification->update(['delivered_at' => now()]);
        $this->assertTrue($notification->isDelivered());
    }

    /** @test */
    public function it_can_mark_as_read()
    {
        $notification = Notification::factory()->create([
            'read_at' => null
        ]);

        $this->assertFalse($notification->isRead());

        $notification->markAsRead();
        $this->assertTrue($notification->isRead());
        $this->assertNotNull($notification->read_at);
    }

    /** @test */
    public function it_can_mark_as_delivered()
    {
        $notification = Notification::factory()->create([
            'delivered_at' => null
        ]);

        $this->assertFalse($notification->isDelivered());

        $notification->markAsDelivered();
        $this->assertTrue($notification->isDelivered());
        $this->assertNotNull($notification->delivered_at);
    }

    /** @test */
    public function it_has_time_ago_attribute()
    {
        $notification = Notification::factory()->create([
            'created_at' => now()->subHours(2)
        ]);

        $timeAgo = $notification->getTimeAgoAttribute();
        $this->assertStringContainsString('2 horas', $timeAgo);
    }

    /** @test */
    public function it_has_type_badge_class()
    {
        $notification = Notification::factory()->create(['type' => 'success']);
        $this->assertEquals('success', $notification->getTypeBadgeClassAttribute());

        $notification->update(['type' => 'error']);
        $this->assertEquals('danger', $notification->getTypeBadgeClassAttribute());

        $notification->update(['type' => 'warning']);
        $this->assertEquals('warning', $notification->getTypeBadgeClassAttribute());

        $notification->update(['type' => 'alert']);
        $this->assertEquals('warning', $notification->getTypeBadgeClassAttribute());

        $notification->update(['type' => 'info']);
        $this->assertEquals('info', $notification->getTypeBadgeClassAttribute());
    }

    /** @test */
    public function it_has_type_icon()
    {
        $notification = Notification::factory()->create(['type' => 'success']);
        $this->assertEquals('heroicon-o-check-circle', $notification->getTypeIconAttribute());

        $notification->update(['type' => 'error']);
        $this->assertEquals('heroicon-o-x-circle', $notification->getTypeIconAttribute());

        $notification->update(['type' => 'warning']);
        $this->assertEquals('heroicon-o-exclamation-triangle', $notification->getTypeIconAttribute());

        $notification->update(['type' => 'alert']);
        $this->assertEquals('heroicon-o-bell-alert', $notification->getTypeIconAttribute());

        $notification->update(['type' => 'info']);
        $this->assertEquals('heroicon-o-information-circle', $notification->getTypeIconAttribute());
    }

    /** @test */
    public function it_has_type_color()
    {
        $notification = Notification::factory()->create(['type' => 'success']);
        $this->assertEquals('green', $notification->getTypeColorAttribute());

        $notification->update(['type' => 'error']);
        $this->assertEquals('red', $notification->getTypeColorAttribute());

        $notification->update(['type' => 'warning']);
        $this->assertEquals('yellow', $notification->getTypeColorAttribute());

        $notification->update(['type' => 'alert']);
        $this->assertEquals('orange', $notification->getTypeColorAttribute());

        $notification->update(['type' => 'info']);
        $this->assertEquals('blue', $notification->getTypeColorAttribute());
    }

    /** @test */
    public function it_has_unread_scope()
    {
        Notification::factory()->create(['read_at' => null]);
        Notification::factory()->create(['read_at' => now()]);

        $unreadCount = Notification::unread()->count();
        $this->assertEquals(1, $unreadCount);
    }

    /** @test */
    public function it_has_read_scope()
    {
        Notification::factory()->create(['read_at' => null]);
        Notification::factory()->create(['read_at' => now()]);

        $readCount = Notification::read()->count();
        $this->assertEquals(1, $readCount);
    }

    /** @test */
    public function it_has_by_type_scope()
    {
        Notification::factory()->create(['type' => 'info']);
        Notification::factory()->create(['type' => 'success']);
        Notification::factory()->create(['type' => 'info']);

        $infoCount = Notification::byType('info')->count();
        $this->assertEquals(2, $infoCount);
    }

    /** @test */
    public function it_has_by_user_scope()
    {
        $user2 = User::factory()->create();
        
        Notification::factory()->create(['user_id' => $this->user->id]);
        Notification::factory()->create(['user_id' => $this->user->id]);
        Notification::factory()->create(['user_id' => $user2->id]);

        $userNotifications = Notification::byUser($this->user->id)->count();
        $this->assertEquals(2, $userNotifications);
    }

    /** @test */
    public function it_has_recent_scope()
    {
        Notification::factory()->create(['created_at' => now()->subDays(2)]);
        Notification::factory()->create(['created_at' => now()->subHours(12)]);

        $recentCount = Notification::recent(1)->count();
        $this->assertEquals(1, $recentCount);
    }

    /** @test */
    public function it_has_delivered_scope()
    {
        Notification::factory()->create(['delivered_at' => null]);
        Notification::factory()->create(['delivered_at' => now()]);

        $deliveredCount = Notification::delivered()->count();
        $this->assertEquals(1, $deliveredCount);
    }

    /** @test */
    public function it_has_not_delivered_scope()
    {
        Notification::factory()->create(['delivered_at' => null]);
        Notification::factory()->create(['delivered_at' => now()]);

        $notDeliveredCount = Notification::notDelivered()->count();
        $this->assertEquals(1, $notDeliveredCount);
    }

    /** @test */
    public function it_can_get_user_stats()
    {
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

        $stats = Notification::getUserStats($this->user->id);

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['unread']);
        $this->assertEquals(1, $stats['read']);
        $this->assertEquals(1, $stats['by_type']['info']);
        $this->assertEquals(1, $stats['by_type']['success']);
    }

    /** @test */
    public function it_can_get_general_stats()
    {
        Notification::factory()->create(['type' => 'info']);
        Notification::factory()->create(['type' => 'success']);
        Notification::factory()->create(['type' => 'info']);

        $stats = Notification::getStats();

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['by_type']['info']);
        $this->assertEquals(1, $stats['by_type']['success']);
    }

    /** @test */
    public function it_can_mark_all_as_read_for_user()
    {
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null
        ]);
        
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null
        ]);

        $this->assertEquals(2, Notification::unread()->count());

        Notification::markAllAsRead($this->user->id);

        $this->assertEquals(0, Notification::unread()->count());
    }

    /** @test */
    public function it_can_cleanup_old_notifications()
    {
        Notification::factory()->create(['created_at' => now()->subDays(40)]);
        Notification::factory()->create(['created_at' => now()->subDays(20)]);
        Notification::factory()->create(['created_at' => now()->subDays(10)]);

        $this->assertEquals(3, Notification::count());

        $deleted = Notification::cleanupOld(30);

        $this->assertEquals(1, $deleted);
        $this->assertEquals(2, Notification::count());
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $notification = Notification::factory()->create();
        
        $this->assertEquals(1, Notification::count());
        
        $notification->delete();
        
        $this->assertEquals(0, Notification::count());
        $this->assertEquals(1, Notification::withTrashed()->count());
    }

    /** @test */
    public function it_can_be_restored()
    {
        $notification = Notification::factory()->create();
        $notification->delete();
        
        $this->assertEquals(0, Notification::count());
        
        $notification->restore();
        
        $this->assertEquals(1, Notification::count());
    }

    /** @test */
    public function it_can_be_force_deleted()
    {
        $notification = Notification::factory()->create();
        $notification->delete();
        
        $this->assertEquals(1, Notification::withTrashed()->count());
        
        $notification->forceDelete();
        
        $this->assertEquals(0, Notification::withTrashed()->count());
    }
}
