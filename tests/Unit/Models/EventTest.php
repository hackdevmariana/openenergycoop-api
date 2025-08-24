<?php

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'title',
            'description',
            'date',
            'location',
            'public',
            'language',
            'organization_id',
            'is_draft'
        ];

        $event = new Event();
        $this->assertEquals($fillable, $event->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $event = new Event();
        
        $expectedCasts = [
            'id' => 'int',
            'date' => 'datetime',
            'public' => 'boolean',
            'is_draft' => 'boolean',
            'deleted_at' => 'datetime'
        ];

        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertEquals($cast, $event->getCasts()[$attribute]);
        }
    }

    /** @test */
    public function it_has_correct_languages_constant()
    {
        $expectedLanguages = [
            'es' => 'Español',
            'en' => 'English',
            'ca' => 'Català',
            'eu' => 'Euskera',
            'gl' => 'Galego'
        ];

        $this->assertEquals($expectedLanguages, Event::LANGUAGES);
    }

    /** @test */
    public function it_has_correct_statuses_constant()
    {
        $expectedStatuses = [
            'upcoming' => 'Próximo',
            'ongoing' => 'En Curso',
            'completed' => 'Finalizado',
            'cancelled' => 'Cancelado'
        ];

        $this->assertEquals($expectedStatuses, Event::STATUS);
    }

    /** @test */
    public function it_belongs_to_organization()
    {
        $organization = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Organization::class, $event->organization);
        $this->assertEquals($organization->id, $event->organization->id);
    }

    /** @test */
    public function it_has_many_attendances()
    {
        $event = Event::factory()->create();
        EventAttendance::factory(3)->create(['event_id' => $event->id]);

        $this->assertCount(3, $event->attendances);
        $this->assertInstanceOf(EventAttendance::class, $event->attendances->first());
    }

    /** @test */
    public function it_has_many_registered_users()
    {
        $event = Event::factory()->create();
        EventAttendance::factory(2)->create(['event_id' => $event->id, 'status' => 'registered']);
        EventAttendance::factory()->create(['event_id' => $event->id, 'status' => 'attended']);

        $this->assertCount(2, $event->registeredUsers);
    }

    /** @test */
    public function it_has_many_attended_users()
    {
        $event = Event::factory()->create();
        EventAttendance::factory()->create(['event_id' => $event->id, 'status' => 'registered']);
        EventAttendance::factory(2)->create(['event_id' => $event->id, 'status' => 'attended']);

        $this->assertCount(2, $event->attendedUsers);
    }

    /** @test */
    public function it_has_many_cancelled_users()
    {
        $event = Event::factory()->create();
        EventAttendance::factory()->create(['event_id' => $event->id, 'status' => 'registered']);
        EventAttendance::factory(2)->create(['event_id' => $event->id, 'status' => 'cancelled']);

        $this->assertCount(2, $event->cancelledUsers);
    }

    /** @test */
    public function it_has_many_no_show_users()
    {
        $event = Event::factory()->create();
        EventAttendance::factory()->create(['event_id' => $event->id, 'status' => 'registered']);
        EventAttendance::factory(2)->create(['event_id' => $event->id, 'status' => 'no_show']);

        $this->assertCount(2, $event->noShowUsers);
    }

    /** @test */
    public function public_scope_filters_public_events()
    {
        Event::factory()->create(['public' => true]);
        Event::factory()->create(['public' => false]);

        $publicEvents = Event::public()->get();

        $this->assertCount(1, $publicEvents);
        $this->assertTrue($publicEvents->first()->public);
    }

    /** @test */
    public function private_scope_filters_private_events()
    {
        Event::factory()->create(['public' => true]);
        Event::factory()->create(['public' => false]);

        $privateEvents = Event::private()->get();

        $this->assertCount(1, $privateEvents);
        $this->assertFalse($privateEvents->first()->public);
    }

    /** @test */
    public function published_scope_filters_published_events()
    {
        Event::factory()->create(['is_draft' => false]);
        Event::factory()->create(['is_draft' => true]);

        $publishedEvents = Event::published()->get();

        $this->assertCount(1, $publishedEvents);
        $this->assertFalse($publishedEvents->first()->is_draft);
    }

    /** @test */
    public function drafts_scope_filters_draft_events()
    {
        Event::factory()->create(['is_draft' => false]);
        Event::factory()->create(['is_draft' => true]);

        $draftEvents = Event::drafts()->get();

        $this->assertCount(1, $draftEvents);
        $this->assertTrue($draftEvents->first()->is_draft);
    }

    /** @test */
    public function by_language_scope_filters_by_language()
    {
        Event::factory()->create(['language' => 'es']);
        Event::factory()->create(['language' => 'en']);

        $spanishEvents = Event::byLanguage('es')->get();

        $this->assertCount(1, $spanishEvents);
        $this->assertEquals('es', $spanishEvents->first()->language);
    }

    /** @test */
    public function by_organization_scope_filters_by_organization()
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        Event::factory()->create(['organization_id' => $org1->id]);
        Event::factory()->create(['organization_id' => $org2->id]);

        $org1Events = Event::byOrganization($org1->id)->get();

        $this->assertCount(1, $org1Events);
        $this->assertEquals($org1->id, $org1Events->first()->organization_id);
    }

    /** @test */
    public function upcoming_scope_filters_future_events()
    {
        Event::factory()->create(['date' => now()->addWeek()]);
        Event::factory()->create(['date' => now()->subWeek()]);

        $upcomingEvents = Event::upcoming()->get();

        $this->assertCount(1, $upcomingEvents);
        $this->assertTrue($upcomingEvents->first()->date->isFuture());
    }

    /** @test */
    public function past_scope_filters_past_events()
    {
        Event::factory()->create(['date' => now()->addWeek()]);
        Event::factory()->create(['date' => now()->subWeek()]);

        $pastEvents = Event::past()->get();

        $this->assertCount(1, $pastEvents);
        $this->assertTrue($pastEvents->first()->date->isPast());
    }

    /** @test */
    public function today_scope_filters_todays_events()
    {
        Event::factory()->create(['date' => now()]);
        Event::factory()->create(['date' => now()->addDay()]);

        $todayEvents = Event::today()->get();

        $this->assertCount(1, $todayEvents);
        $this->assertTrue($todayEvents->first()->date->isToday());
    }

    /** @test */
    public function this_week_scope_filters_this_weeks_events()
    {
        Event::factory()->create(['date' => now()->startOfWeek()->addDay()]);
        Event::factory()->create(['date' => now()->addWeek()]);

        $thisWeekEvents = Event::thisWeek()->get();

        $this->assertCount(1, $thisWeekEvents);
    }

    /** @test */
    public function this_month_scope_filters_this_months_events()
    {
        Event::factory()->create(['date' => now()->startOfMonth()->addDay()]);
        Event::factory()->create(['date' => now()->addMonth()]);

        $thisMonthEvents = Event::thisMonth()->get();

        $this->assertCount(1, $thisMonthEvents);
    }

    /** @test */
    public function by_location_scope_filters_by_location()
    {
        Event::factory()->create(['location' => 'Madrid']);
        Event::factory()->create(['location' => 'Barcelona']);

        $madridEvents = Event::byLocation('Madrid')->get();

        $this->assertCount(1, $madridEvents);
        $this->assertEquals('Madrid', $madridEvents->first()->location);
    }

    /** @test */
    public function search_scope_searches_in_multiple_fields()
    {
        Event::factory()->create(['title' => 'Conferencia de Energía']);
        Event::factory()->create(['description' => 'Evento sobre energía renovable']);
        Event::factory()->create(['location' => 'Centro de Energía']);
        Event::factory()->create(['title' => 'Taller de Programación']);

        $energyEvents = Event::search('energía')->get();

        $this->assertCount(3, $energyEvents);
    }

    /** @test */
    public function is_public_method_returns_correct_value()
    {
        $publicEvent = Event::factory()->create(['public' => true]);
        $privateEvent = Event::factory()->create(['public' => false]);

        $this->assertTrue($publicEvent->isPublic());
        $this->assertFalse($privateEvent->isPublic());
    }

    /** @test */
    public function is_upcoming_method_returns_correct_value()
    {
        $upcomingEvent = Event::factory()->create(['date' => now()->addWeek()]);
        $pastEvent = Event::factory()->create(['date' => now()->subWeek()]);

        $this->assertTrue($upcomingEvent->isUpcoming());
        $this->assertFalse($pastEvent->isUpcoming());
    }

    /** @test */
    public function status_attribute_returns_correct_status()
    {
        $upcomingEvent = Event::factory()->create(['date' => now()->addWeek()]);
        $pastEvent = Event::factory()->create(['date' => now()->subWeek()]);
        $todayEvent = Event::factory()->create(['date' => now()]);

        $this->assertEquals('upcoming', $upcomingEvent->status);
        $this->assertEquals('completed', $pastEvent->status);
        $this->assertEquals('ongoing', $todayEvent->status);
    }

    /** @test */
    public function time_until_attribute_returns_human_readable_time()
    {
        $event = Event::factory()->create(['date' => now()->addWeek()]);

        $this->assertStringContainsString('en', $event->time_until);
    }

    /** @test */
    public function language_label_attribute_returns_correct_label()
    {
        $spanishEvent = Event::factory()->create(['language' => 'es']);
        $englishEvent = Event::factory()->create(['language' => 'en']);

        $this->assertEquals('Español', $spanishEvent->language_label);
        $this->assertEquals('English', $englishEvent->language_label);
    }

    /** @test */
    public function status_badge_class_attribute_returns_correct_class()
    {
        $upcomingEvent = Event::factory()->create(['date' => now()->addWeek()]);
        $pastEvent = Event::factory()->create(['date' => now()->subWeek()]);

        $this->assertEquals('info', $upcomingEvent->status_badge_class);
        $this->assertEquals('success', $pastEvent->status_badge_class);
    }

    /** @test */
    public function attendance_stats_attribute_returns_correct_stats()
    {
        $event = Event::factory()->create();
        
        EventAttendance::factory(5)->create(['event_id' => $event->id, 'status' => 'registered']);
        EventAttendance::factory(3)->create(['event_id' => $event->id, 'status' => 'attended']);
        EventAttendance::factory(1)->create(['event_id' => $event->id, 'status' => 'cancelled']);
        EventAttendance::factory(1)->create(['event_id' => $event->id, 'status' => 'no_show']);

        $stats = $event->attendance_stats;

        $this->assertEquals(10, $stats['total_registered']);
        $this->assertEquals(3, $stats['total_attended']);
        $this->assertEquals(1, $stats['total_cancelled']);
        $this->assertEquals(1, $stats['total_no_show']);
        $this->assertEquals(30, $stats['attendance_rate']); // 3/10 * 100
    }

    /** @test */
    public function register_user_method_creates_attendance()
    {
        $event = Event::factory()->create();
        $user = User::factory()->create();

        $attendance = $event->registerUser($user->id);

        $this->assertInstanceOf(EventAttendance::class, $attendance);
        $this->assertEquals($event->id, $attendance->event_id);
        $this->assertEquals($user->id, $attendance->user_id);
        $this->assertEquals('registered', $attendance->status);
    }

    /** @test */
    public function register_user_method_prevents_duplicate_registration()
    {
        $event = Event::factory()->create();
        $user = User::factory()->create();
        
        EventAttendance::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $attendance = $event->registerUser($user->id);

        $this->assertNull($attendance);
    }

    /** @test */
    public function get_user_attendance_method_returns_attendance()
    {
        $event = Event::factory()->create();
        $user = User::factory()->create();
        
        $expectedAttendance = EventAttendance::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $attendance = $event->getUserAttendance($user->id);

        $this->assertEquals($expectedAttendance->id, $attendance->id);
    }

    /** @test */
    public function get_user_attendance_method_returns_null_for_unregistered_user()
    {
        $event = Event::factory()->create();
        $user = User::factory()->create();

        $attendance = $event->getUserAttendance($user->id);

        $this->assertNull($attendance);
    }

    /** @test */
    public function get_stats_method_returns_correct_statistics()
    {
        Event::factory(5)->create(['public' => true, 'is_draft' => false]);
        Event::factory(2)->create(['public' => false, 'is_draft' => false]);
        Event::factory(1)->create(['public' => true, 'is_draft' => true]);

        $stats = Event::getStats();

        $this->assertEquals(8, $stats['total']);
        $this->assertEquals(6, $stats['public']);
        $this->assertEquals(2, $stats['private']);
        $this->assertEquals(7, $stats['published']);
        $this->assertEquals(1, $stats['drafts']);
    }

    /** @test */
    public function get_stats_method_accepts_filters()
    {
        $organization = Organization::factory()->create();
        
        Event::factory(3)->create(['organization_id' => $organization->id, 'public' => true]);
        Event::factory(2)->create(['public' => true]); // Different organization

        $stats = Event::getStats(['organization_id' => $organization->id]);

        $this->assertEquals(3, $stats['total']);
    }

    /** @test */
    public function get_recommended_for_user_method_returns_events()
    {
        $user = User::factory()->create();
        
        Event::factory(5)->create([
            'public' => true,
            'is_draft' => false,
            'date' => now()->addWeek(),
        ]);

        $recommendedEvents = Event::getRecommendedForUser($user->id, 3);

        $this->assertCount(3, $recommendedEvents);
        $this->assertInstanceOf(Event::class, $recommendedEvents->first());
    }

    /** @test */
    public function uses_soft_deletes()
    {
        $event = Event::factory()->create();
        
        $event->delete();
        
        $this->assertSoftDeleted($event);
        $this->assertNotNull($event->fresh()->deleted_at);
    }

    /** @test */
    public function has_factory()
    {
        $event = Event::factory()->create();
        
        $this->assertInstanceOf(Event::class, $event);
        $this->assertNotNull($event->title);
        $this->assertNotNull($event->description);
        $this->assertNotNull($event->date);
        $this->assertNotNull($event->location);
    }
}
