<?php

namespace Tests\Unit\Models;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSettingTest extends TestCase
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
            'channel',
            'notification_type',
            'enabled',
        ];

        $this->assertEquals($fillable, (new NotificationSetting())->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'user_id' => 'int',
            'enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];

        $this->assertEquals($casts, (new NotificationSetting())->getCasts());
    }

    /** @test */
    public function it_has_correct_constants()
    {
        $this->assertEquals([
            'email',
            'push',
            'sms',
            'in_app',
        ], NotificationSetting::CHANNELS);

        $this->assertEquals([
            'wallet',
            'event',
            'message',
            'general',
        ], NotificationSetting::NOTIFICATION_TYPES);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $setting = NotificationSetting::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertInstanceOf(User::class, $setting->user);
        $this->assertEquals($this->user->id, $setting->user->id);
    }

    /** @test */
    public function it_has_enabled_scope()
    {
        NotificationSetting::factory()->create(['enabled' => true]);
        NotificationSetting::factory()->create(['enabled' => false]);

        $enabledCount = NotificationSetting::enabled()->count();
        $this->assertEquals(1, $enabledCount);
    }

    /** @test */
    public function it_has_disabled_scope()
    {
        NotificationSetting::factory()->create(['enabled' => true]);
        NotificationSetting::factory()->create(['enabled' => false]);

        $disabledCount = NotificationSetting::disabled()->count();
        $this->assertEquals(1, $disabledCount);
    }

    /** @test */
    public function it_has_by_channel_scope()
    {
        NotificationSetting::factory()->create(['channel' => 'email']);
        NotificationSetting::factory()->create(['channel' => 'push']);
        NotificationSetting::factory()->create(['channel' => 'email']);

        $emailCount = NotificationSetting::byChannel('email')->count();
        $this->assertEquals(2, $emailCount);
    }

    /** @test */
    public function it_has_by_notification_type_scope()
    {
        NotificationSetting::factory()->create(['notification_type' => 'wallet']);
        NotificationSetting::factory()->create(['notification_type' => 'event']);
        NotificationSetting::factory()->create(['notification_type' => 'wallet']);

        $walletCount = NotificationSetting::byNotificationType('wallet')->count();
        $this->assertEquals(2, $walletCount);
    }

    /** @test */
    public function it_has_by_user_scope()
    {
        $user2 = User::factory()->create();
        
        NotificationSetting::factory()->create(['user_id' => $this->user->id]);
        NotificationSetting::factory()->create(['user_id' => $this->user->id]);
        NotificationSetting::factory()->create(['user_id' => $user2->id]);

        $userSettings = NotificationSetting::byUser($this->user->id)->count();
        $this->assertEquals(2, $userSettings);
    }

    /** @test */
    public function it_can_enable()
    {
        $setting = NotificationSetting::factory()->create(['enabled' => false]);

        $this->assertFalse($setting->enabled);

        $setting->enable();
        $this->assertTrue($setting->enabled);
    }

    /** @test */
    public function it_can_disable()
    {
        $setting = NotificationSetting::factory()->create(['enabled' => true]);

        $this->assertTrue($setting->enabled);

        $setting->disable();
        $this->assertFalse($setting->enabled);
    }

    /** @test */
    public function it_can_toggle()
    {
        $setting = NotificationSetting::factory()->create(['enabled' => false]);

        $this->assertFalse($setting->enabled);

        $setting->toggle();
        $this->assertTrue($setting->enabled);

        $setting->toggle();
        $this->assertFalse($setting->enabled);
    }

    /** @test */
    public function it_has_channel_label()
    {
        $setting = NotificationSetting::factory()->create(['channel' => 'email']);
        $this->assertEquals('Email', $setting->getChannelLabelAttribute());

        $setting->update(['channel' => 'push']);
        $this->assertEquals('Push', $setting->getChannelLabelAttribute());

        $setting->update(['channel' => 'sms']);
        $this->assertEquals('SMS', $setting->getChannelLabelAttribute());

        $setting->update(['channel' => 'in_app']);
        $this->assertEquals('En la Aplicación', $setting->getChannelLabelAttribute());
    }

    /** @test */
    public function it_has_notification_type_label()
    {
        $setting = NotificationSetting::factory()->create(['notification_type' => 'wallet']);
        $this->assertEquals('Billetera', $setting->getNotificationTypeLabelAttribute());

        $setting->update(['notification_type' => 'event']);
        $this->assertEquals('Eventos', $setting->getNotificationTypeLabelAttribute());

        $setting->update(['notification_type' => 'message']);
        $this->assertEquals('Mensajes', $setting->getNotificationTypeLabelAttribute());

        $setting->update(['notification_type' => 'general']);
        $this->assertEquals('General', $setting->getNotificationTypeLabelAttribute());
    }

    /** @test */
    public function it_has_status_badge_class()
    {
        $setting = NotificationSetting::factory()->create(['enabled' => true]);
        $this->assertEquals('success', $setting->getStatusBadgeClassAttribute());

        $setting->update(['enabled' => false]);
        $this->assertEquals('danger', $setting->getStatusBadgeClassAttribute());
    }

    /** @test */
    public function it_has_channel_icon()
    {
        $setting = NotificationSetting::factory()->create(['channel' => 'email']);
        $this->assertEquals('heroicon-o-envelope', $setting->getChannelIconAttribute());

        $setting->update(['channel' => 'push']);
        $this->assertEquals('heroicon-o-device-phone-mobile', $setting->getChannelIconAttribute());

        $setting->update(['channel' => 'sms']);
        $this->assertEquals('heroicon-o-chat-bubble-left-right', $setting->getChannelIconAttribute());

        $setting->update(['channel' => 'in_app']);
        $this->assertEquals('heroicon-o-computer-desktop', $setting->getChannelIconAttribute());
    }

    /** @test */
    public function it_has_notification_type_icon()
    {
        $setting = NotificationSetting::factory()->create(['notification_type' => 'wallet']);
        $this->assertEquals('heroicon-o-credit-card', $setting->getNotificationTypeIconAttribute());

        $setting->update(['notification_type' => 'event']);
        $this->assertEquals('heroicon-o-calendar', $setting->getNotificationTypeIconAttribute());

        $setting->update(['notification_type' => 'message']);
        $this->assertEquals('heroicon-o-chat-bubble-left', $setting->getNotificationTypeIconAttribute());

        $setting->update(['notification_type' => 'general']);
        $this->assertEquals('heroicon-o-bell', $setting->getNotificationTypeIconAttribute());
    }

    /** @test */
    public function it_has_channel_color()
    {
        $setting = NotificationSetting::factory()->create(['channel' => 'email']);
        $this->assertEquals('blue', $setting->getChannelColorAttribute());

        $setting->update(['channel' => 'push']);
        $this->assertEquals('orange', $setting->getChannelColorAttribute());

        $setting->update(['channel' => 'sms']);
        $this->assertEquals('green', $setting->getChannelColorAttribute());

        $setting->update(['channel' => 'in_app']);
        $this->assertEquals('purple', $setting->getChannelColorAttribute());
    }

    /** @test */
    public function it_has_notification_type_color()
    {
        $setting = NotificationSetting::factory()->create(['notification_type' => 'wallet']);
        $this->assertEquals('green', $setting->getNotificationTypeColorAttribute());

        $setting->update(['notification_type' => 'event']);
        $this->assertEquals('orange', $setting->getNotificationTypeColorAttribute());

        $setting->update(['notification_type' => 'message']);
        $this->assertEquals('blue', $setting->getNotificationTypeColorAttribute());

        $setting->update(['notification_type' => 'general']);
        $this->assertEquals('gray', $setting->getNotificationTypeColorAttribute());
    }

    /** @test */
    public function it_can_get_user_stats()
    {
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

        $stats = NotificationSetting::getUserStats($this->user->id);

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['enabled']);
        $this->assertEquals(1, $stats['disabled']);
        $this->assertEquals(1, $stats['by_channel']['email']);
        $this->assertEquals(1, $stats['by_channel']['push']);
    }

    /** @test */
    public function it_can_get_general_stats()
    {
        NotificationSetting::factory()->create(['channel' => 'email']);
        NotificationSetting::factory()->create(['channel' => 'push']);
        NotificationSetting::factory()->create(['channel' => 'email']);

        $stats = NotificationSetting::getStats();

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['by_channel']['email']);
        $this->assertEquals(1, $stats['by_channel']['push']);
    }

    /** @test */
    public function it_can_create_default_settings()
    {
        $this->assertEquals(0, NotificationSetting::count());

        $created = NotificationSetting::createDefaultSettings($this->user->id);

        $this->assertEquals(16, $created); // 4 canales × 4 tipos
        $this->assertEquals(16, NotificationSetting::count());
    }

    /** @test */
    public function it_can_update_or_create_setting()
    {
        $this->assertEquals(0, NotificationSetting::count());

        $setting = NotificationSetting::updateOrCreateSetting(
            $this->user->id,
            'email',
            'wallet',
            true
        );

        $this->assertInstanceOf(NotificationSetting::class, $setting);
        $this->assertEquals($this->user->id, $setting->user_id);
        $this->assertEquals('email', $setting->channel);
        $this->assertEquals('wallet', $setting->notification_type);
        $this->assertTrue($setting->enabled);

        // Actualizar el mismo setting
        $updatedSetting = NotificationSetting::updateOrCreateSetting(
            $this->user->id,
            'email',
            'wallet',
            false
        );

        $this->assertEquals($setting->id, $updatedSetting->id);
        $this->assertFalse($updatedSetting->enabled);
    }

    /** @test */
    public function it_can_get_enabled_channels_for_user()
    {
        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'email',
            'notification_type' => 'wallet',
            'enabled' => true
        ]);

        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'push',
            'notification_type' => 'wallet',
            'enabled' => false
        ]);

        $enabledChannels = NotificationSetting::getEnabledChannels($this->user->id, 'wallet');

        $this->assertContains('email', $enabledChannels);
        $this->assertNotContains('push', $enabledChannels);
    }

    /** @test */
    public function it_has_unique_constraint()
    {
        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'email',
            'notification_type' => 'wallet'
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        NotificationSetting::factory()->create([
            'user_id' => $this->user->id,
            'channel' => 'email',
            'notification_type' => 'wallet'
        ]);
    }
}
