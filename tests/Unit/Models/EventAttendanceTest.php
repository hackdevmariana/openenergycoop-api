<?php

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'event_id',
            'user_id',
            'status',
            'registered_at',
            'checked_in_at',
            'cancellation_reason',
            'notes',
            'checkin_token'
        ];

        $attendance = new EventAttendance();
        $this->assertEquals($fillable, $attendance->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $attendance = new EventAttendance();
        
        $expectedCasts = [
            'id' => 'int',
            'registered_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];

        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertEquals($cast, $attendance->getCasts()[$attribute]);
        }
    }

    /** @test */
    public function it_has_correct_status_constants()
    {
        $expectedStatuses = [
            'registered' => 'Registrado',
            'attended' => 'Asistió',
            'cancelled' => 'Cancelado',
            'no_show' => 'No Asistió'
        ];

        $this->assertEquals($expectedStatuses, EventAttendance::STATUSES);
        $this->assertEquals(['registered', 'attended', 'cancelled', 'no_show'], EventAttendance::STATUS);
    }

    /** @test */
    public function it_belongs_to_event()
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $organization->id]);
        $attendance = EventAttendance::factory()->create(['event_id' => $event->id]);

        $this->assertInstanceOf(Event::class, $attendance->event);
        $this->assertEquals($event->id, $attendance->event->id);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $attendance = EventAttendance::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $attendance->user);
        $this->assertEquals($user->id, $attendance->user->id);
    }

    /** @test */
    public function by_status_scope_filters_by_status()
    {
        EventAttendance::factory()->create(['status' => 'registered']);
        EventAttendance::factory()->create(['status' => 'attended']);

        $registeredAttendances = EventAttendance::byStatus('registered')->get();

        $this->assertCount(1, $registeredAttendances);
        $this->assertEquals('registered', $registeredAttendances->first()->status);
    }

    /** @test */
    public function registered_scope_filters_registered_attendances()
    {
        EventAttendance::factory()->create(['status' => 'registered']);
        EventAttendance::factory()->create(['status' => 'attended']);

        $registeredAttendances = EventAttendance::registered()->get();

        $this->assertCount(1, $registeredAttendances);
        $this->assertEquals('registered', $registeredAttendances->first()->status);
    }

    /** @test */
    public function attended_scope_filters_attended_attendances()
    {
        EventAttendance::factory()->create(['status' => 'registered']);
        EventAttendance::factory()->create(['status' => 'attended']);

        $attendedAttendances = EventAttendance::attended()->get();

        $this->assertCount(1, $attendedAttendances);
        $this->assertEquals('attended', $attendedAttendances->first()->status);
    }

    /** @test */
    public function cancelled_scope_filters_cancelled_attendances()
    {
        EventAttendance::factory()->create(['status' => 'registered']);
        EventAttendance::factory()->create(['status' => 'cancelled']);

        $cancelledAttendances = EventAttendance::cancelled()->get();

        $this->assertCount(1, $cancelledAttendances);
        $this->assertEquals('cancelled', $cancelledAttendances->first()->status);
    }

    /** @test */
    public function no_show_scope_filters_no_show_attendances()
    {
        EventAttendance::factory()->create(['status' => 'registered']);
        EventAttendance::factory()->create(['status' => 'no_show']);

        $noShowAttendances = EventAttendance::noShow()->get();

        $this->assertCount(1, $noShowAttendances);
        $this->assertEquals('no_show', $noShowAttendances->first()->status);
    }

    /** @test */
    public function by_event_scope_filters_by_event()
    {
        $organization = Organization::factory()->create();
        $event1 = Event::factory()->create(['organization_id' => $organization->id]);
        $event2 = Event::factory()->create(['organization_id' => $organization->id]);
        
        EventAttendance::factory()->create(['event_id' => $event1->id]);
        EventAttendance::factory()->create(['event_id' => $event2->id]);

        $event1Attendances = EventAttendance::byEvent($event1->id)->get();

        $this->assertCount(1, $event1Attendances);
        $this->assertEquals($event1->id, $event1Attendances->first()->event_id);
    }

    /** @test */
    public function by_user_scope_filters_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        EventAttendance::factory()->create(['user_id' => $user1->id]);
        EventAttendance::factory()->create(['user_id' => $user2->id]);

        $user1Attendances = EventAttendance::byUser($user1->id)->get();

        $this->assertCount(1, $user1Attendances);
        $this->assertEquals($user1->id, $user1Attendances->first()->user_id);
    }

    /** @test */
    public function by_organization_scope_filters_by_organization()
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        $event1 = Event::factory()->create(['organization_id' => $org1->id]);
        $event2 = Event::factory()->create(['organization_id' => $org2->id]);
        
        EventAttendance::factory()->create(['event_id' => $event1->id]);
        EventAttendance::factory()->create(['event_id' => $event2->id]);

        $org1Attendances = EventAttendance::byOrganization($org1->id)->get();

        $this->assertCount(1, $org1Attendances);
    }

    /** @test */
    public function recent_scope_filters_recent_attendances()
    {
        EventAttendance::factory()->create(['registered_at' => now()->subDays(5)]);
        EventAttendance::factory()->create(['registered_at' => now()->subDays(10)]);

        $recentAttendances = EventAttendance::recent(7)->get();

        $this->assertCount(1, $recentAttendances);
    }

    /** @test */
    public function search_scope_searches_in_notes_and_cancellation_reason()
    {
        EventAttendance::factory()->create(['notes' => 'Usuario VIP especial']);
        EventAttendance::factory()->create(['cancellation_reason' => 'Problema de transporte']);
        EventAttendance::factory()->create(['notes' => 'Usuario regular']);

        $vipAttendances = EventAttendance::search('VIP')->get();
        $transportAttendances = EventAttendance::search('transporte')->get();

        $this->assertCount(1, $vipAttendances);
        $this->assertCount(1, $transportAttendances);
    }

    /** @test */
    public function is_registered_method_returns_correct_value()
    {
        $registeredAttendance = EventAttendance::factory()->create(['status' => 'registered']);
        $attendedAttendance = EventAttendance::factory()->create(['status' => 'attended']);

        $this->assertTrue($registeredAttendance->isRegistered());
        $this->assertFalse($attendedAttendance->isRegistered());
    }

    /** @test */
    public function is_attended_method_returns_correct_value()
    {
        $registeredAttendance = EventAttendance::factory()->create(['status' => 'registered']);
        $attendedAttendance = EventAttendance::factory()->create(['status' => 'attended']);

        $this->assertFalse($registeredAttendance->isAttended());
        $this->assertTrue($attendedAttendance->isAttended());
    }

    /** @test */
    public function is_cancelled_method_returns_correct_value()
    {
        $registeredAttendance = EventAttendance::factory()->create(['status' => 'registered']);
        $cancelledAttendance = EventAttendance::factory()->create(['status' => 'cancelled']);

        $this->assertFalse($registeredAttendance->isCancelled());
        $this->assertTrue($cancelledAttendance->isCancelled());
    }

    /** @test */
    public function is_no_show_method_returns_correct_value()
    {
        $registeredAttendance = EventAttendance::factory()->create(['status' => 'registered']);
        $noShowAttendance = EventAttendance::factory()->create(['status' => 'no_show']);

        $this->assertFalse($registeredAttendance->isNoShow());
        $this->assertTrue($noShowAttendance->isNoShow());
    }

    /** @test */
    public function can_check_in_method_returns_correct_value()
    {
        $registeredAttendance = EventAttendance::factory()->create(['status' => 'registered']);
        $attendedAttendance = EventAttendance::factory()->create(['status' => 'attended']);
        $cancelledAttendance = EventAttendance::factory()->create(['status' => 'cancelled']);

        $this->assertTrue($registeredAttendance->canCheckIn());
        $this->assertFalse($attendedAttendance->canCheckIn());
        $this->assertFalse($cancelledAttendance->canCheckIn());
    }

    /** @test */
    public function can_cancel_method_returns_correct_value()
    {
        $registeredAttendance = EventAttendance::factory()->create(['status' => 'registered']);
        $attendedAttendance = EventAttendance::factory()->create(['status' => 'attended']);
        $cancelledAttendance = EventAttendance::factory()->create(['status' => 'cancelled']);

        $this->assertTrue($registeredAttendance->canCancel());
        $this->assertFalse($attendedAttendance->canCancel());
        $this->assertFalse($cancelledAttendance->canCancel());
    }

    /** @test */
    public function check_in_method_updates_status_and_time()
    {
        $attendance = EventAttendance::factory()->create(['status' => 'registered']);

        $result = $attendance->checkIn();

        $this->assertTrue($result);
        $this->assertEquals('attended', $attendance->status);
        $this->assertNotNull($attendance->checked_in_at);
    }

    /** @test */
    public function check_in_method_fails_for_non_registered_user()
    {
        $attendance = EventAttendance::factory()->create(['status' => 'attended']);

        $result = $attendance->checkIn();

        $this->assertFalse($result);
    }

    /** @test */
    public function cancel_method_updates_status_and_reason()
    {
        $attendance = EventAttendance::factory()->create(['status' => 'registered']);
        $reason = 'Conflicto de horarios';

        $result = $attendance->cancel($reason);

        $this->assertTrue($result);
        $this->assertEquals('cancelled', $attendance->status);
        $this->assertEquals($reason, $attendance->cancellation_reason);
    }

    /** @test */
    public function cancel_method_fails_for_non_registered_user()
    {
        $attendance = EventAttendance::factory()->create(['status' => 'attended']);

        $result = $attendance->cancel('Cualquier razón');

        $this->assertFalse($result);
    }

    /** @test */
    public function mark_as_no_show_method_updates_status()
    {
        $attendance = EventAttendance::factory()->create(['status' => 'registered']);

        $result = $attendance->markAsNoShow();

        $this->assertTrue($result);
        $this->assertEquals('no_show', $attendance->status);
    }

    /** @test */
    public function re_register_method_updates_status()
    {
        $attendance = EventAttendance::factory()->create(['status' => 'cancelled']);

        $result = $attendance->reRegister();

        $this->assertTrue($result);
        $this->assertEquals('registered', $attendance->status);
        $this->assertNull($attendance->cancellation_reason);
    }

    /** @test */
    public function re_register_method_fails_for_non_cancelled_user()
    {
        $attendance = EventAttendance::factory()->create(['status' => 'registered']);

        $result = $attendance->reRegister();

        $this->assertFalse($result);
    }

    /** @test */
    public function status_label_attribute_returns_correct_label()
    {
        $registeredAttendance = EventAttendance::factory()->create(['status' => 'registered']);
        $attendedAttendance = EventAttendance::factory()->create(['status' => 'attended']);

        $this->assertEquals('Registrado', $registeredAttendance->status_label);
        $this->assertEquals('Asistió', $attendedAttendance->status_label);
    }

    /** @test */
    public function status_badge_class_attribute_returns_correct_class()
    {
        $registeredAttendance = EventAttendance::factory()->create(['status' => 'registered']);
        $attendedAttendance = EventAttendance::factory()->create(['status' => 'attended']);
        $cancelledAttendance = EventAttendance::factory()->create(['status' => 'cancelled']);
        $noShowAttendance = EventAttendance::factory()->create(['status' => 'no_show']);

        $this->assertEquals('info', $registeredAttendance->status_badge_class);
        $this->assertEquals('success', $attendedAttendance->status_badge_class);
        $this->assertEquals('warning', $cancelledAttendance->status_badge_class);
        $this->assertEquals('danger', $noShowAttendance->status_badge_class);
    }

    /** @test */
    public function time_since_registration_attribute_returns_human_readable_time()
    {
        $attendance = EventAttendance::factory()->create(['registered_at' => now()->subWeek()]);

        $this->assertStringContainsString('hace', $attendance->time_since_registration);
    }

    /** @test */
    public function generate_checkin_token_method_creates_unique_token()
    {
        $attendance = EventAttendance::factory()->create();

        $token = $attendance->generateCheckinToken();

        $this->assertNotNull($token);
        $this->assertEquals(64, strlen($token));
        $this->assertEquals($token, $attendance->checkin_token);
    }

    /** @test */
    public function generate_new_checkin_token_method_creates_new_token()
    {
        $attendance = EventAttendance::factory()->create(['checkin_token' => 'old_token']);
        $oldToken = $attendance->checkin_token;

        $newToken = $attendance->generateNewCheckinToken();

        $this->assertNotEquals($oldToken, $newToken);
        $this->assertEquals($newToken, $attendance->checkin_token);
    }

    /** @test */
    public function find_by_checkin_token_method_finds_attendance()
    {
        $token = bin2hex(random_bytes(32));
        $attendance = EventAttendance::factory()->create(['checkin_token' => $token]);

        $foundAttendance = EventAttendance::findByCheckinToken($token);

        $this->assertEquals($attendance->id, $foundAttendance->id);
    }

    /** @test */
    public function find_by_checkin_token_method_returns_null_for_invalid_token()
    {
        $foundAttendance = EventAttendance::findByCheckinToken('invalid_token');

        $this->assertNull($foundAttendance);
    }

    /** @test */
    public function is_user_registered_method_checks_registration()
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $organization->id]);
        $user = User::factory()->create();
        
        EventAttendance::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue(EventAttendance::isUserRegistered($event->id, $user->id));
        
        $otherUser = User::factory()->create();
        $this->assertFalse(EventAttendance::isUserRegistered($event->id, $otherUser->id));
    }

    /** @test */
    public function get_event_stats_method_returns_correct_statistics()
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $organization->id]);
        
        EventAttendance::factory(5)->create(['event_id' => $event->id, 'status' => 'registered']);
        EventAttendance::factory(3)->create(['event_id' => $event->id, 'status' => 'attended']);
        EventAttendance::factory(1)->create(['event_id' => $event->id, 'status' => 'cancelled']);
        EventAttendance::factory(1)->create(['event_id' => $event->id, 'status' => 'no_show']);

        $stats = EventAttendance::getEventStats($event->id);

        $this->assertEquals(10, $stats['total_registered']);
        $this->assertEquals(3, $stats['total_attended']);
        $this->assertEquals(1, $stats['total_cancelled']);
        $this->assertEquals(1, $stats['total_no_show']);
        $this->assertEquals(30, $stats['attendance_rate']); // 3/10 * 100
    }

    /** @test */
    public function get_user_stats_method_returns_correct_statistics()
    {
        $user = User::factory()->create();
        
        EventAttendance::factory(5)->create(['user_id' => $user->id, 'status' => 'attended']);
        EventAttendance::factory(2)->create(['user_id' => $user->id, 'status' => 'registered']);
        EventAttendance::factory(1)->create(['user_id' => $user->id, 'status' => 'cancelled']);

        $stats = EventAttendance::getUserStats($user->id);

        $this->assertEquals(8, $stats['total_events']);
        $this->assertEquals(5, $stats['attended_events']);
        $this->assertEquals(2, $stats['registered_events']);
        $this->assertEquals(1, $stats['cancelled_events']);
        $this->assertEquals(62.5, $stats['attendance_rate']); // 5/8 * 100
    }

    /** @test */
    public function get_stats_method_returns_global_statistics()
    {
        EventAttendance::factory(10)->create(['status' => 'registered']);
        EventAttendance::factory(5)->create(['status' => 'attended']);
        EventAttendance::factory(2)->create(['status' => 'cancelled']);
        EventAttendance::factory(1)->create(['status' => 'no_show']);

        $stats = EventAttendance::getStats();

        $this->assertEquals(18, $stats['total']);
        $this->assertEquals(10, $stats['registered']);
        $this->assertEquals(5, $stats['attended']);
        $this->assertEquals(2, $stats['cancelled']);
        $this->assertEquals(1, $stats['no_show']);
    }

    /** @test */
    public function get_stats_method_accepts_filters()
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $organization->id]);
        
        EventAttendance::factory(3)->create(['event_id' => $event->id, 'status' => 'registered']);
        EventAttendance::factory(2)->create(['status' => 'registered']); // Different event

        $stats = EventAttendance::getStats(['event_id' => $event->id]);

        $this->assertEquals(3, $stats['total']);
    }

    /** @test */
    public function uses_soft_deletes()
    {
        $attendance = EventAttendance::factory()->create();
        
        $attendance->delete();
        
        $this->assertSoftDeleted($attendance);
        $this->assertNotNull($attendance->fresh()->deleted_at);
    }

    /** @test */
    public function has_factory()
    {
        $attendance = EventAttendance::factory()->create();
        
        $this->assertInstanceOf(EventAttendance::class, $attendance);
        $this->assertNotNull($attendance->event_id);
        $this->assertNotNull($attendance->user_id);
        $this->assertNotNull($attendance->status);
        $this->assertNotNull($attendance->registered_at);
    }

    /** @test */
    public function automatically_generates_checkin_token_on_creation()
    {
        $attendance = EventAttendance::factory()->create();
        
        $this->assertNotNull($attendance->checkin_token);
        $this->assertEquals(64, strlen($attendance->checkin_token));
    }

    /** @test */
    public function sets_default_registered_at_on_creation()
    {
        $attendance = EventAttendance::factory()->create();
        
        $this->assertNotNull($attendance->registered_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $attendance->registered_at);
    }
}
