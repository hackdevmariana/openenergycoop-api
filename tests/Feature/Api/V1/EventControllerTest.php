<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_events()
    {
        Event::factory(3)->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'date',
                            'location',
                            'public',
                            'language',
                            'organization_id',
                            'is_draft',
                            'status',
                            'time_until',
                            'language_label',
                            'status_badge_class',
                            'attendance_stats',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'links',
                    'meta'
                ]);
    }

    /** @test */
    public function it_can_filter_events_by_type()
    {
        // Crear eventos pasados y futuros
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'date' => now()->subWeek(),
        ]);
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'date' => now()->addWeek(),
        ]);

        $response = $this->getJson('/api/v1/events?type=upcoming');

        $response->assertStatus(200);
        $events = $response->json('data');
        $this->assertCount(1, $events);
    }

    /** @test */
    public function it_can_filter_events_by_public_status()
    {
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'public' => true,
        ]);
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'public' => false,
        ]);

        $response = $this->getJson('/api/v1/events?public=true');

        $response->assertStatus(200);
        $events = $response->json('data');
        $this->assertCount(1, $events);
        $this->assertTrue($events[0]['public']);
    }

    /** @test */
    public function it_can_filter_events_by_language()
    {
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'language' => 'es',
        ]);
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'language' => 'en',
        ]);

        $response = $this->getJson('/api/v1/events?language=es');

        $response->assertStatus(200);
        $events = $response->json('data');
        $this->assertCount(1, $events);
        $this->assertEquals('es', $events[0]['language']);
    }

    /** @test */
    public function it_can_search_events()
    {
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Conferencia de Energía Renovable',
        ]);
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Taller de Programación',
        ]);

        $response = $this->getJson('/api/v1/events?search=energía');

        $response->assertStatus(200);
        $events = $response->json('data');
        $this->assertCount(1, $events);
        $this->assertStringContainsString('Energía', $events[0]['title']);
    }

    /** @test */
    public function it_can_create_event()
    {
        $eventData = [
            'title' => 'Nuevo Evento de Prueba',
            'description' => 'Descripción del evento de prueba',
            'date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'location' => 'Sala de Conferencias',
            'public' => true,
            'language' => 'es',
            'organization_id' => $this->organization->id,
            'is_draft' => false,
        ];

        $response = $this->postJson('/api/v1/events', $eventData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'date',
                        'location',
                        'public',
                        'language',
                        'organization_id',
                        'is_draft',
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('events', [
            'title' => 'Nuevo Evento de Prueba',
            'organization_id' => $this->organization->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_event()
    {
        $response = $this->postJson('/api/v1/events', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'description', 'date', 'location', 'organization_id']);
    }

    /** @test */
    public function it_can_show_specific_event()
    {
        $event = Event::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'date',
                        'location',
                        'public',
                        'language',
                        'organization_id',
                        'is_draft',
                        'organization',
                        'attendances',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'id' => $event->id,
                        'title' => $event->title,
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_event()
    {
        $response = $this->getJson('/api/v1/events/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_event()
    {
        $event = Event::factory()->create(['organization_id' => $this->organization->id]);
        
        $updateData = [
            'title' => 'Título Actualizado',
            'description' => 'Descripción actualizada',
        ];

        $response = $this->putJson("/api/v1/events/{$event->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Título Actualizado',
        ]);
    }

    /** @test */
    public function it_can_delete_event()
    {
        $event = Event::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->deleteJson("/api/v1/events/{$event->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Evento eliminado exitosamente']);

        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    /** @test */
    public function it_can_get_event_statistics()
    {
        Event::factory(5)->create(['organization_id' => $this->organization->id, 'public' => true]);
        Event::factory(2)->create(['organization_id' => $this->organization->id, 'public' => false]);

        $response = $this->getJson('/api/v1/events/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total',
                        'public',
                        'private',
                        'upcoming',
                        'past',
                        'today',
                        'this_week',
                        'this_month',
                        'published',
                        'drafts',
                        'by_language'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_event_types()
    {
        $response = $this->getJson('/api/v1/events/types');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'value',
                            'label',
                            'description'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_available_languages()
    {
        $response = $this->getJson('/api/v1/events/languages');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'value',
                            'label',
                            'native'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_upcoming_events()
    {
        Event::factory(3)->create([
            'organization_id' => $this->organization->id,
            'date' => now()->addWeek(),
            'public' => true,
            'is_draft' => false,
        ]);
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'date' => now()->subWeek(),
            'public' => true,
            'is_draft' => false,
        ]);

        $response = $this->getJson('/api/v1/events/upcoming?limit=2');

        $response->assertStatus(200);
        $events = $response->json('data');
        $this->assertCount(2, $events);
    }

    /** @test */
    public function it_can_get_todays_events()
    {
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'date' => now(),
            'public' => true,
            'is_draft' => false,
        ]);
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'date' => now()->addDay(),
            'public' => true,
            'is_draft' => false,
        ]);

        $response = $this->getJson('/api/v1/events/today');

        $response->assertStatus(200);
        $events = $response->json('data');
        $this->assertCount(1, $events);
    }

    /** @test */
    public function it_can_get_events_by_date_range()
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'date' => $startDate->copy()->addDay(),
            'public' => true,
            'is_draft' => false,
        ]);
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'date' => $startDate->copy()->subDay(),
            'public' => true,
            'is_draft' => false,
        ]);

        $response = $this->getJson("/api/v1/events/by-date-range?from={$startDate->format('Y-m-d')}&to={$endDate->format('Y-m-d')}");

        $response->assertStatus(200);
        $events = $response->json('data');
        $this->assertCount(1, $events);
    }

    /** @test */
    public function it_validates_date_range_parameters()
    {
        $response = $this->getJson('/api/v1/events/by-date-range');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['from', 'to']);
    }

    /** @test */
    public function it_can_toggle_event_public_status()
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'public' => true,
        ]);

        $response = $this->patchJson("/api/v1/events/{$event->id}/toggle-public");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'public',
                        'message'
                    ]
                ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'public' => false,
        ]);
    }

    /** @test */
    public function it_can_toggle_event_draft_status()
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'is_draft' => true,
        ]);

        $response = $this->patchJson("/api/v1/events/{$event->id}/toggle-draft");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'is_draft',
                        'message'
                    ]
                ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'is_draft' => false,
        ]);
    }

    /** @test */
    public function it_can_get_event_attendance_stats()
    {
        $event = Event::factory()->create(['organization_id' => $this->organization->id]);
        
        // Crear algunas asistencias
        EventAttendance::factory(3)->create(['event_id' => $event->id, 'status' => 'registered']);
        EventAttendance::factory(2)->create(['event_id' => $event->id, 'status' => 'attended']);
        EventAttendance::factory()->create(['event_id' => $event->id, 'status' => 'cancelled']);

        $response = $this->getJson("/api/v1/events/{$event->id}/attendance-stats");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_registered',
                        'total_attended',
                        'total_cancelled',
                        'total_no_show',
                        'attendance_rate',
                        'cancellation_rate',
                        'no_show_rate'
                    ]
                ]);
    }

    /** @test */
    public function it_can_check_user_registration_for_event()
    {
        $event = Event::factory()->create(['organization_id' => $this->organization->id]);
        $user = User::factory()->create();
        
        EventAttendance::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
        ]);

        $response = $this->getJson("/api/v1/events/{$event->id}/check-user-registration?user_id={$user->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'is_registered',
                        'status',
                        'registered_at',
                        'checked_in_at'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'is_registered' => true,
                        'status' => 'registered',
                    ]
                ]);
    }

    /** @test */
    public function it_returns_not_registered_for_unregistered_user()
    {
        $event = Event::factory()->create(['organization_id' => $this->organization->id]);
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/events/{$event->id}/check-user-registration?user_id={$user->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'is_registered' => false,
                        'status' => null,
                        'registered_at' => null
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_recommended_events()
    {
        $user = User::factory()->create();
        Event::factory(3)->create([
            'organization_id' => $this->organization->id,
            'public' => true,
            'is_draft' => false,
            'date' => now()->addWeek(),
        ]);

        $response = $this->getJson("/api/v1/events/recommended?user_id={$user->id}&limit=2");

        $response->assertStatus(200);
        $events = $response->json('data');
        $this->assertCount(2, $events);
    }

    /** @test */
    public function it_validates_user_id_for_recommended_events()
    {
        $response = $this->getJson('/api/v1/events/recommended');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function unauthorized_user_cannot_access_events()
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_paginate_events()
    {
        Event::factory(25)->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson('/api/v1/events?per_page=10&page=1');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'links' => [
                        'first',
                        'last',
                        'prev',
                        'next'
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page'
                    ]
                ]);

        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_sort_events()
    {
        $event1 = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'A - Primer Evento',
            'date' => now()->addWeek(),
        ]);
        $event2 = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Z - Último Evento',
            'date' => now()->addDay(),
        ]);

        // Ordenar por título ascendente
        $response = $this->getJson('/api/v1/events?sort=title&order=asc');
        $events = $response->json('data');
        $this->assertEquals($event1->title, $events[0]['title']);

        // Ordenar por fecha ascendente
        $response = $this->getJson('/api/v1/events?sort=date&order=asc');
        $events = $response->json('data');
        $this->assertEquals($event2->title, $events[0]['title']);
    }
}
