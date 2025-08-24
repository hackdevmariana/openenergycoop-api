<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Event\StoreEventRequest;
use App\Http\Requests\Api\V1\Event\UpdateEventRequest;
use App\Http\Resources\Api\V1\Event\EventResource;
use App\Http\Resources\Api\V1\Event\EventCollection;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Event Management
 *
 * APIs for managing events and their configurations
 */
class EventController extends Controller
{
    /**
     * Display a listing of events.
     *
     * @queryParam type string Filter events by type (upcoming, past, today, this_week, this_month). Example: upcoming
     * @queryParam public boolean Filter by public status. Example: true
     * @queryParam language string Filter by language. Example: es
     * @queryParam organization_id integer Filter by organization ID. Example: 1
     * @queryParam is_draft boolean Filter by draft status. Example: false
     * @queryParam search string Search in title, description, or location. Example: conferencia
     * @queryParam location string Filter by location. Example: Madrid
     * @queryParam page integer Page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam sort string Sort field (date, title, created_at, attendances_count). Example: date
     * @queryParam order string Sort order (asc, desc). Example: desc
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Conferencia sobre Energía Renovable",
     *       "description": "Descripción del evento...",
     *       "date": "2024-02-15T10:00:00Z",
     *       "location": "Auditorio Principal",
     *       "public": true,
     *       "language": "es",
     *       "organization_id": 1,
     *       "is_draft": false,
     *       "status": "upcoming",
     *       "time_until": "en 2 días",
     *       "language_label": "Español",
     *       "status_badge_class": "info",
     *       "status_icon": "heroicon-o-calendar",
     *       "status_color": "blue",
     *       "attendance_stats": {
     *         "total_registered": 25,
     *         "total_attended": 0,
     *         "total_cancelled": 2,
     *         "total_no_show": 0,
     *         "attendance_rate": 0
     *       },
     *       "created_at": "2024-01-15T10:00:00Z",
     *       "updated_at": "2024-01-15T10:00:00Z"
     *     }
     *   ],
     *   "links": {...},
     *   "meta": {...}
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Event::query();

        // Aplicar filtros
        if ($request->filled('type')) {
            $query = match ($request->type) {
                'upcoming' => $query->upcoming(),
                'past' => $query->past(),
                'today' => $query->today(),
                'this_week' => $query->thisWeek(),
                'this_month' => $query->thisMonth(),
                default => $query
            };
        }

        if ($request->filled('public')) {
            $query->where('public', $request->boolean('public'));
        }

        if ($request->filled('language')) {
            $query->byLanguage($request->language);
        }

        if ($request->filled('organization_id')) {
            $query->byOrganization($request->organization_id);
        }

        if ($request->filled('is_draft')) {
            $query->where('is_draft', $request->boolean('is_draft'));
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('location')) {
            $query->byLocation($request->location);
        }

        // Aplicar ordenamiento
        $sortField = $request->get('sort', 'date');
        $sortOrder = $request->get('order', 'desc');
        
        if (in_array($sortField, ['date', 'title', 'created_at', 'attendances_count'])) {
            $query->orderBy($sortField, $sortOrder);
        }

        // Cargar relaciones necesarias
        $query->with(['organization', 'attendances']);

        $events = $query->paginate($request->get('per_page', 15));

        return EventCollection::make($events);
    }

    /**
     * Store a newly created event.
     *
     * @bodyParam title string required The title of the event. Example: Conferencia sobre Energía Renovable
     * @bodyParam description string required The description of the event. Example: Descripción detallada del evento...
     * @bodyParam date string required The date and time of the event (ISO 8601). Example: 2024-02-15T10:00:00Z
     * @bodyParam location string required The location of the event. Example: Auditorio Principal
     * @bodyParam public boolean The public status of the event. Example: true
     * @bodyParam language string The language of the event. Example: es
     * @bodyParam organization_id integer required The ID of the organization. Example: 1
     * @bodyParam is_draft boolean The draft status of the event. Example: false
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "title": "Conferencia sobre Energía Renovable",
     *     "description": "Descripción del evento...",
     *     "date": "2024-02-15T10:00:00Z",
     *     "location": "Auditorio Principal",
     *     "public": true,
     *     "language": "es",
     *     "organization_id": 1,
     *     "is_draft": false,
     *     "status": "upcoming",
     *     "time_until": "en 2 días",
     *     "language_label": "Español",
     *     "status_badge_class": "info",
     *     "status_icon": "heroicon-o-calendar",
     *       "status_color": "blue",
     *     "attendance_stats": {
     *       "total_registered": 0,
     *       "total_attended": 0,
     *       "total_cancelled": 0,
     *       "total_no_show": 0,
     *       "attendance_rate": 0
     *     },
     *     "created_at": "2024-01-15T10:00:00Z",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   },
     *   "message": "Evento creado exitosamente"
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos.",
     *   "errors": {...}
     * }
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $event = Event::create($request->validated());

            Log::info('Evento creado', [
                'event_id' => $event->id,
                'title' => $event->title,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'data' => new EventResource($event),
                'message' => 'Evento creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear evento', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al crear el evento',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified event.
     *
     * @urlParam event integer required The ID of the event. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "title": "Conferencia sobre Energía Renovable",
     *     "description": "Descripción del evento...",
     *     "date": "2024-02-15T10:00:00Z",
     *     "location": "Auditorio Principal",
     *     "public": true,
     *     "language": "es",
     *     "organization_id": 1,
     *     "is_draft": false,
     *     "status": "upcoming",
     *     "time_until": "en 2 días",
     *     "language_label": "Español",
     *     "status_badge_class": "info",
     *     "status_icon": "heroicon-o-calendar",
     *     "status_color": "blue",
     *     "attendance_stats": {
     *       "total_registered": 25,
     *       "total_attended": 0,
     *       "total_cancelled": 2,
     *       "total_no_show": 0,
     *       "attendance_rate": 0
     *     },
     *     "organization": {...},
     *     "attendances": [...],
     *     "created_at": "2024-01-15T10:00:00Z",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Evento no encontrado"
     * }
     */
    public function show(Event $event): JsonResponse
    {
        $event->load(['organization', 'attendances.user']);

        return response()->json([
            'data' => new EventResource($event)
        ]);
    }

    /**
     * Update the specified event.
     *
     * @urlParam event integer required The ID of the event. Example: 1
     * @bodyParam title string The title of the event. Example: Conferencia sobre Energía Renovable
     * @bodyParam description string The description of the event. Example: Descripción detallada del evento...
     * @bodyParam date string The date and time of the event (ISO 8601). Example: 2024-02-15T10:00:00Z
     * @bodyParam location string The location of the event. Example: Auditorio Principal
     * @bodyParam public boolean The public status of the event. Example: true
     * @bodyParam language string The language of the event. Example: es
     * @bodyParam organization_id integer The ID of the organization. Example: 1
     * @bodyParam is_draft boolean The draft status of the event. Example: false
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "title": "Conferencia sobre Energía Renovable",
     *     "description": "Descripción actualizada del evento...",
     *     "date": "2024-02-15T10:00:00Z",
     *     "location": "Auditorio Principal",
     *     "public": true,
     *     "language": "es",
     *     "organization_id": 1,
     *     "is_draft": false,
     *     "status": "upcoming",
     *     "time_until": "en 2 días",
     *     "language_label": "Español",
     *     "status_badge_class": "info",
     *     "status_icon": "heroicon-o-calendar",
     *     "status_color": "blue",
     *     "attendance_stats": {
     *       "total_registered": 25,
     *       "total_attended": 0,
     *       "total_cancelled": 2,
     *       "total_no_show": 0,
     *       "attendance_rate": 0
     *     },
     *     "created_at": "2024-01-15T10:00:00Z",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   },
     *   "message": "Evento actualizado exitosamente"
     * }
     *
     * @response 404 {
     *   "message": "Evento no encontrado"
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos.",
     *   "errors": {...}
     * }
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        try {
            DB::beginTransaction();

            $event->update($request->validated());

            Log::info('Evento actualizado', [
                'event_id' => $event->id,
                'title' => $event->title,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'data' => new EventResource($event),
                'message' => 'Evento actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar evento', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al actualizar el evento',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified event.
     *
     * @urlParam event integer required The ID of the event. Example: 1
     *
     * @response 200 {
     *   "message": "Evento eliminado exitosamente"
     * }
     *
     * @response 404 {
     *   "message": "Evento no encontrado"
     * }
     */
    public function destroy(Event $event): JsonResponse
    {
        try {
            DB::beginTransaction();

            $eventId = $event->id;
            $eventTitle = $event->title;

            $event->delete();

            Log::info('Evento eliminado', [
                'event_id' => $eventId,
                'title' => $eventTitle,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Evento eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar evento', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al eliminar el evento',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get event statistics.
     *
     * @queryParam organization_id integer Filter by organization ID. Example: 1
     * @queryParam public boolean Filter by public status. Example: true
     * @queryParam language string Filter by language. Example: es
     *
     * @response 200 {
     *   "data": {
     *     "total": 28,
     *     "public": 26,
     *     "private": 2,
     *     "upcoming": 24,
     *     "past": 3,
     *     "today": 1,
     *     "this_week": 5,
     *     "this_month": 12,
     *     "published": 22,
     *     "drafts": 6,
     *     "by_language": {
     *       "es": 25,
     *       "en": 2,
     *       "ca": 1
     *     }
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only(['organization_id', 'public', 'language']);
        $stats = Event::getStats($filters);

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Get available event types.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "value": "upcoming",
     *       "label": "Próximo",
     *       "description": "Eventos futuros"
     *     },
     *     {
     *       "value": "ongoing",
     *       "label": "En Curso",
     *       "description": "Eventos en desarrollo"
     *     },
     *     {
     *       "value": "completed",
     *       "label": "Finalizado",
     *       "description": "Eventos pasados"
     *     },
     *     {
     *       "value": "cancelled",
     *       "label": "Cancelado",
     *       "description": "Eventos cancelados"
     *     }
     *   ]
     * }
     */
    public function types(): JsonResponse
    {
        $types = [
            [
                'value' => 'upcoming',
                'label' => 'Próximo',
                'description' => 'Eventos futuros'
            ],
            [
                'value' => 'ongoing',
                'label' => 'En Curso',
                'description' => 'Eventos en desarrollo'
            ],
            [
                'value' => 'completed',
                'label' => 'Finalizado',
                'description' => 'Eventos pasados'
            ],
            [
                'value' => 'cancelled',
                'label' => 'Cancelado',
                'description' => 'Eventos cancelados'
            ]
        ];

        return response()->json([
            'data' => $types
        ]);
    }

    /**
     * Get available languages.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "value": "es",
     *       "label": "Español",
     *       "native": "Español"
     *     },
     *     {
     *       "value": "en",
     *       "label": "English",
     *       "native": "English"
     *     },
     *     {
     *       "value": "ca",
     *       "label": "Català",
     *       "native": "Català"
     *     },
     *     {
     *       "value": "eu",
     *       "label": "Euskara",
     *       "native": "Euskara"
     *     },
     *     {
     *       "value": "gl",
     *       "label": "Galego",
     *       "native": "Galego"
     *     }
     *   ]
     * }
     */
    public function languages(): JsonResponse
    {
        $languages = collect(Event::LANGUAGES)->map(function ($label, $value) {
            return [
                'value' => $value,
                'label' => $label,
                'native' => $label
            ];
        })->values();

        return response()->json([
            'data' => $languages
        ]);
    }

    /**
     * Get upcoming events.
     *
     * @queryParam limit integer Number of events to return. Example: 5
     * @queryParam organization_id integer Filter by organization ID. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Conferencia sobre Energía Renovable",
     *       "date": "2024-02-15T10:00:00Z",
     *       "location": "Auditorio Principal",
     *       "time_until": "en 2 días"
     *     }
     *   ]
     * }
     */
    public function upcoming(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $query = Event::public()->published()->upcoming();

        if ($request->filled('organization_id')) {
            $query->byOrganization($request->organization_id);
        }

        $events = $query->orderBy('date')->limit($limit)->get();

        return response()->json([
            'data' => EventResource::collection($events)
        ]);
    }

    /**
     * Get today's events.
     *
     * @queryParam organization_id integer Filter by organization ID. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Taller de Energía Solar",
     *       "date": "2024-01-15T14:00:00Z",
     *       "location": "Sala de Talleres",
     *       "time_until": "en 2 horas"
     *     }
     *   ]
     * }
     */
    public function today(Request $request): JsonResponse
    {
        $query = Event::public()->published()->today();

        if ($request->filled('organization_id')) {
            $query->byOrganization($request->organization_id);
        }

        $events = $query->orderBy('date')->get();

        return response()->json([
            'data' => EventResource::collection($events)
        ]);
    }

    /**
     * Get events by date range.
     *
     * @queryParam from string Start date (Y-m-d). Example: 2024-01-01
     * @queryParam to string End date (Y-m-d). Example: 2024-01-31
     * @queryParam organization_id integer Filter by organization ID. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Conferencia sobre Energía Renovable",
     *       "date": "2024-01-15T10:00:00Z",
     *       "location": "Auditorio Principal"
     *     }
     *   ]
     * }
     */
    public function byDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $query = Event::public()->published();

        if ($request->filled('organization_id')) {
            $query->byOrganization($request->organization_id);
        }

        $events = $query->whereBetween('date', [
            $request->from . ' 00:00:00',
            $request->to . ' 23:59:59'
        ])->orderBy('date')->get();

        return response()->json([
            'data' => EventResource::collection($events)
        ]);
    }

    /**
     * Get recommended events for a user.
     *
     * @queryParam user_id integer required The ID of the user. Example: 1
     * @queryParam limit integer Number of events to return. Example: 5
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Conferencia sobre Energía Renovable",
     *       "date": "2024-02-15T10:00:00Z",
     *       "location": "Auditorio Principal",
     *       "time_until": "en 2 días"
     *     }
     *   ]
     * }
     */
    public function recommended(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $limit = $request->get('limit', 5);
        $events = Event::getRecommendedForUser($request->user_id, $limit);

        return response()->json([
            'data' => EventResource::collection($events)
        ]);
    }

    /**
     * Toggle event public status.
     *
     * @urlParam event integer required The ID of the event. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "public": false,
     *     "message": "Evento marcado como privado"
     *   }
     * }
     */
    public function togglePublic(Event $event): JsonResponse
    {
        $event->update(['public' => !$event->public]);

        $message = $event->public ? 'Evento marcado como público' : 'Evento marcado como privado';

        Log::info('Estado público del evento cambiado', [
            'event_id' => $event->id,
            'public' => $event->public,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'data' => [
                'id' => $event->id,
                'public' => $event->public,
                'message' => $message
            ]
        ]);
    }

    /**
     * Toggle event draft status.
     *
     * @urlParam event integer required The ID of the event. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "is_draft": false,
     *     "message": "Evento publicado"
     *   }
     * }
     */
    public function toggleDraft(Event $event): JsonResponse
    {
        $event->update(['is_draft' => !$event->is_draft]);

        $message = $event->is_draft ? 'Evento marcado como borrador' : 'Evento publicado';

        Log::info('Estado de borrador del evento cambiado', [
            'event_id' => $event->id,
            'is_draft' => $event->is_draft,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'data' => [
                'id' => $event->id,
                'is_draft' => $event->is_draft,
                'message' => $message
            ]
        ]);
    }

    /**
     * Get event attendance statistics.
     *
     * @urlParam event integer required The ID of the event. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "total_registered": 25,
     *     "total_attended": 20,
     *     "total_cancelled": 2,
     *     "total_no_show": 3,
     *     "attendance_rate": 80,
     *     "cancellation_rate": 8,
     *     "no_show_rate": 12
     *   }
     * }
     */
    public function attendanceStats(Event $event): JsonResponse
    {
        $stats = $event->getAttendanceStatsAttribute();

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Check if a user is registered for an event.
     *
     * @urlParam event integer required The ID of the event. Example: 1
     * @queryParam user_id integer required The ID of the user. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "is_registered": true,
     *     "status": "registered",
     *     "registered_at": "2024-01-10T10:00:00Z"
     *   }
     * }
     */
    public function checkUserRegistration(Event $event, Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $attendance = $event->getUserAttendance($request->user_id);

        if (!$attendance) {
            return response()->json([
                'data' => [
                    'is_registered' => false,
                    'status' => null,
                    'registered_at' => null
                ]
            ]);
        }

        return response()->json([
            'data' => [
                'is_registered' => true,
                'status' => $attendance->status,
                'registered_at' => $attendance->registered_at,
                'checked_in_at' => $attendance->checked_in_at
            ]
        ]);
    }
}
