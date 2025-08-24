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

class EventAttendanceControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Organization $organization;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->event = Event::factory()->create(['organization_id' => $this->organization->id]);
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_event_attendances()
    {
        EventAttendance::factory(3)->create(['event_id' => $this->event->id]);

        $response = $this->getJson('/api/v1/event-attendances');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'event_id',
                            'user_id',
                            'status',
                            'registered_at',
                            'checked_in_at',
                            'cancellation_reason',
                            'notes',
                            'checkin_token',
                            'status_label',
                            'status_badge_class',
                            'time_since_registration',
                            'event',
                            'user',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'links',
                    'meta'
                ]);
    }

    /** @test */
    public function it_can_filter_attendances_by_status()
    {
        EventAttendance::factory()->create(['event_id' => $this->event->id, 'status' => 'registered']);
        EventAttendance::factory()->create(['event_id' => $this->event->id, 'status' => 'attended']);
        EventAttendance::factory()->create(['event_id' => $this->event->id, 'status' => 'cancelled']);

        $response = $this->getJson('/api/v1/event-attendances?status=registered');

        $response->assertStatus(200);
        $attendances = $response->json('data');
        $this->assertCount(1, $attendances);
        $this->assertEquals('registered', $attendances[0]['status']);
    }

    /** @test */
    public function it_can_filter_attendances_by_event()
    {
        $otherEvent = Event::factory()->create(['organization_id' => $this->organization->id]);
        
        EventAttendance::factory()->create(['event_id' => $this->event->id]);
        EventAttendance::factory()->create(['event_id' => $otherEvent->id]);

        $response = $this->getJson("/api/v1/event-attendances?event_id={$this->event->id}");

        $response->assertStatus(200);
        $attendances = $response->json('data');
        $this->assertCount(1, $attendances);
        $this->assertEquals($this->event->id, $attendances[0]['event_id']);
    }

    /** @test */
    public function it_can_filter_attendances_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        EventAttendance::factory()->create(['event_id' => $this->event->id, 'user_id' => $user1->id]);
        EventAttendance::factory()->create(['event_id' => $this->event->id, 'user_id' => $user2->id]);

        $response = $this->getJson("/api/v1/event-attendances?user_id={$user1->id}");

        $response->assertStatus(200);
        $attendances = $response->json('data');
        $this->assertCount(1, $attendances);
        $this->assertEquals($user1->id, $attendances[0]['user_id']);
    }

    /** @test */
    public function it_can_filter_attendances_by_checkin_status()
    {
        EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'checked_in_at' => now(),
        ]);
        EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'checked_in_at' => null,
        ]);

        $response = $this->getJson('/api/v1/event-attendances?checkin_status=checked_in');

        $response->assertStatus(200);
        $attendances = $response->json('data');
        $this->assertCount(1, $attendances);
        $this->assertNotNull($attendances[0]['checked_in_at']);
    }

    /** @test */
    public function it_can_search_attendances()
    {
        EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'notes' => 'Usuario VIP con necesidades especiales',
        ]);
        EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'notes' => 'Usuario regular',
        ]);

        $response = $this->getJson('/api/v1/event-attendances?search=VIP');

        $response->assertStatus(200);
        $attendances = $response->json('data');
        $this->assertCount(1, $attendances);
        $this->assertStringContainsString('VIP', $attendances[0]['notes']);
    }

    /** @test */
    public function it_can_create_event_attendance()
    {
        $user = User::factory()->create();
        
        $attendanceData = [
            'event_id' => $this->event->id,
            'user_id' => $user->id,
            'status' => 'registered',
            'registered_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Usuario de prueba',
        ];

        $response = $this->postJson('/api/v1/event-attendances', $attendanceData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'event_id',
                        'user_id',
                        'status',
                        'registered_at',
                        'notes',
                        'checkin_token',
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('event_attendances', [
            'event_id' => $this->event->id,
            'user_id' => $user->id,
            'status' => 'registered',
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_user_registration()
    {
        $user = User::factory()->create();
        EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $user->id,
        ]);

        $attendanceData = [
            'event_id' => $this->event->id,
            'user_id' => $user->id,
            'status' => 'registered',
            'registered_at' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson('/api/v1/event-attendances', $attendanceData);

        $response->assertStatus(422)
                ->assertJson(['message' => 'El usuario ya está registrado en este evento']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_attendance()
    {
        $response = $this->postJson('/api/v1/event-attendances', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['event_id', 'user_id']);
    }

    /** @test */
    public function it_can_show_specific_attendance()
    {
        $attendance = EventAttendance::factory()->create(['event_id' => $this->event->id]);

        $response = $this->getJson("/api/v1/event-attendances/{$attendance->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'event_id',
                        'user_id',
                        'status',
                        'event',
                        'user',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'id' => $attendance->id,
                        'event_id' => $attendance->event_id,
                        'user_id' => $attendance->user_id,
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_attendance()
    {
        $response = $this->getJson('/api/v1/event-attendances/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_attendance()
    {
        $attendance = EventAttendance::factory()->create(['event_id' => $this->event->id]);
        
        $updateData = [
            'status' => 'attended',
            'checked_in_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Usuario confirmado',
        ];

        $response = $this->putJson("/api/v1/event-attendances/{$attendance->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'status',
                        'checked_in_at',
                        'notes',
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('event_attendances', [
            'id' => $attendance->id,
            'status' => 'attended',
            'notes' => 'Usuario confirmado',
        ]);
    }

    /** @test */
    public function it_can_delete_attendance()
    {
        $attendance = EventAttendance::factory()->create(['event_id' => $this->event->id]);

        $response = $this->deleteJson("/api/v1/event-attendances/{$attendance->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Asistencia al evento eliminada exitosamente']);

        $this->assertSoftDeleted('event_attendances', ['id' => $attendance->id]);
    }

    /** @test */
    public function it_can_get_attendance_statistics()
    {
        EventAttendance::factory(5)->create(['status' => 'registered']);
        EventAttendance::factory(3)->create(['status' => 'attended']);
        EventAttendance::factory(2)->create(['status' => 'cancelled']);

        $response = $this->getJson('/api/v1/event-attendances/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total',
                        'registered',
                        'attended',
                        'cancelled',
                        'no_show',
                        'by_status'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_available_statuses()
    {
        $response = $this->getJson('/api/v1/event-attendances/statuses');

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
    public function it_can_check_in_user()
    {
        $attendance = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'registered',
        ]);

        $response = $this->patchJson("/api/v1/event-attendances/{$attendance->id}/check-in");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'status',
                        'checked_in_at',
                        'message'
                    ]
                ]);

        $this->assertDatabaseHas('event_attendances', [
            'id' => $attendance->id,
            'status' => 'attended',
        ]);
    }

    /** @test */
    public function it_prevents_checking_in_already_attended_user()
    {
        $attendance = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'attended',
            'checked_in_at' => now(),
        ]);

        $response = $this->patchJson("/api/v1/event-attendances/{$attendance->id}/check-in");

        $response->assertStatus(422)
                ->assertJson(['message' => 'No se puede realizar el check-in en este momento']);
    }

    /** @test */
    public function it_can_cancel_attendance()
    {
        $attendance = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'registered',
        ]);

        $cancelData = [
            'cancellation_reason' => 'Conflicto de horarios'
        ];

        $response = $this->patchJson("/api/v1/event-attendances/{$attendance->id}/cancel", $cancelData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'status',
                        'cancellation_reason',
                        'message'
                    ]
                ]);

        $this->assertDatabaseHas('event_attendances', [
            'id' => $attendance->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Conflicto de horarios',
        ]);
    }

    /** @test */
    public function it_validates_cancellation_reason()
    {
        $attendance = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'registered',
        ]);

        $response = $this->patchJson("/api/v1/event-attendances/{$attendance->id}/cancel", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cancellation_reason']);
    }

    /** @test */
    public function it_can_mark_user_as_no_show()
    {
        $attendance = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'registered',
        ]);

        $response = $this->patchJson("/api/v1/event-attendances/{$attendance->id}/mark-no-show");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'status',
                        'message'
                    ]
                ]);

        $this->assertDatabaseHas('event_attendances', [
            'id' => $attendance->id,
            'status' => 'no_show',
        ]);
    }

    /** @test */
    public function it_prevents_marking_attended_user_as_no_show()
    {
        $attendance = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'attended',
        ]);

        $response = $this->patchJson("/api/v1/event-attendances/{$attendance->id}/mark-no-show");

        $response->assertStatus(422)
                ->assertJson(['message' => 'No se puede marcar como no asistió en este momento']);
    }

    /** @test */
    public function it_can_re_register_cancelled_user()
    {
        $attendance = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'cancelled',
        ]);

        $response = $this->patchJson("/api/v1/event-attendances/{$attendance->id}/re-register");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'status',
                        'message'
                    ]
                ]);

        $this->assertDatabaseHas('event_attendances', [
            'id' => $attendance->id,
            'status' => 'registered',
        ]);
    }

    /** @test */
    public function it_prevents_re_registering_non_cancelled_user()
    {
        $attendance = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'registered',
        ]);

        $response = $this->patchJson("/api/v1/event-attendances/{$attendance->id}/re-register");

        $response->assertStatus(422)
                ->assertJson(['message' => 'No se puede re-registrar al usuario en este momento']);
    }

    /** @test */
    public function it_can_find_attendance_by_token()
    {
        $token = bin2hex(random_bytes(32));
        $attendance = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'checkin_token' => $token,
        ]);

        $response = $this->getJson("/api/v1/event-attendances/find-by-token?token={$token}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'event_id',
                        'user_id',
                        'status',
                        'event',
                        'user'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'id' => $attendance->id,
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_invalid_token()
    {
        $response = $this->getJson('/api/v1/event-attendances/find-by-token?token=invalid_token');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Token de check-in no válido']);
    }

    /** @test */
    public function it_validates_token_parameter()
    {
        $response = $this->getJson('/api/v1/event-attendances/find-by-token');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['token']);
    }

    /** @test */
    public function it_can_generate_new_checkin_token()
    {
        $attendance = EventAttendance::factory()->create(['event_id' => $this->event->id]);

        $response = $this->patchJson("/api/v1/event-attendances/{$attendance->id}/generate-new-token");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'checkin_token',
                        'message'
                    ]
                ]);

        $attendance->refresh();
        $this->assertNotNull($attendance->checkin_token);
    }

    /** @test */
    public function it_can_get_event_specific_stats()
    {
        EventAttendance::factory(3)->create(['event_id' => $this->event->id, 'status' => 'registered']);
        EventAttendance::factory(2)->create(['event_id' => $this->event->id, 'status' => 'attended']);

        $response = $this->getJson("/api/v1/event-attendances/event/{$this->event->id}/stats");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_registered',
                        'total_attended',
                        'total_cancelled',
                        'total_no_show',
                        'attendance_rate'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_user_specific_stats()
    {
        $user = User::factory()->create();
        
        EventAttendance::factory(3)->create(['user_id' => $user->id, 'status' => 'attended']);
        EventAttendance::factory()->create(['user_id' => $user->id, 'status' => 'registered']);

        $response = $this->getJson("/api/v1/event-attendances/user/{$user->id}/stats");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_events',
                        'attended_events',
                        'registered_events',
                        'cancelled_events',
                        'no_show_events',
                        'attendance_rate'
                    ]
                ]);
    }

    /** @test */
    public function unauthorized_user_cannot_access_attendances()
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/event-attendances');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_paginate_attendances()
    {
        EventAttendance::factory(25)->create(['event_id' => $this->event->id]);

        $response = $this->getJson('/api/v1/event-attendances?per_page=10&page=1');

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
    public function it_can_sort_attendances()
    {
        $attendance1 = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'registered_at' => now()->subWeek(),
        ]);
        $attendance2 = EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'registered_at' => now()->subDay(),
        ]);

        // Ordenar por fecha de registro ascendente
        $response = $this->getJson('/api/v1/event-attendances?sort=registered_at&order=asc');
        $attendances = $response->json('data');
        $this->assertEquals($attendance1->id, $attendances[0]['id']);

        // Ordenar por fecha de registro descendente
        $response = $this->getJson('/api/v1/event-attendances?sort=registered_at&order=desc');
        $attendances = $response->json('data');
        $this->assertEquals($attendance2->id, $attendances[0]['id']);
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'registered_at' => $startDate->copy()->addDay(),
        ]);
        EventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'registered_at' => $startDate->copy()->subDay(),
        ]);

        $response = $this->getJson("/api/v1/event-attendances?from={$startDate->format('Y-m-d')}&until={$endDate->format('Y-m-d')}");

        $response->assertStatus(200);
        $attendances = $response->json('data');
        $this->assertCount(1, $attendances);
    }
}
