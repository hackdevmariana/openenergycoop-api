<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EventAttendance\StoreEventAttendanceRequest;
use App\Http\Requests\Api\V1\EventAttendance\UpdateEventAttendanceRequest;
use App\Http\Resources\Api\V1\EventAttendance\EventAttendanceResource;
use App\Http\Resources\Api\V1\EventAttendance\EventAttendanceCollection;
use App\Models\EventAttendance;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Event Attendance Management
 *
 * APIs for managing event attendances and registrations
 */
class EventAttendanceController extends Controller
{
    /**
     * Display a listing of event attendances.
     *
     * @queryParam status string Filter by status (registered, attended, cancelled, no_show). Example: registered
     * @queryParam event_id integer Filter by event ID. Example: 1
     * @queryParam user_id integer Filter by user ID. Example: 1
     * @queryParam organization_id integer Filter by organization ID. Example: 1
     * @queryParam checkin_status string Filter by check-in status (checked_in, not_checked_in). Example: not_checked_in
     * @queryParam from string Filter by registration date from (Y-m-d). Example: 2024-01-01
     * @queryParam until string Filter by registration date until (Y-m-d). Example: 2024-01-31
     * @queryParam search string Search in notes or cancellation reason. Example: conferencia
     * @queryParam page integer Page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam sort string Sort field (registered_at, checked_in_at, created_at). Example: registered_at
     * @queryParam order string Sort order (asc, desc). Example: desc
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "event_id": 1,
     *       "user_id": 1,
     *       "status": "registered",
     *       "registered_at": "2024-01-10T10:00:00Z",
     *       "checked_in_at": null,
     *       "cancellation_reason": null,
     *       "notes": null,
     *       "checkin_token": "abc123...",
     *       "status_label": "Registrado",
     *       "status_badge_class": "info",
     *       "status_icon": "heroicon-o-clock",
     *       "status_color": "blue",
     *       "time_since_registration": "hace 5 días",
     *       "time_until_event": "en 2 días",
     *       "event": {...},
     *       "user": {...},
     *       "created_at": "2024-01-10T10:00:00Z",
     *       "updated_at": "2024-01-10T10:00:00Z"
     *     }
     *   ],
     *   "links": {...},
     *   "meta": {...}
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = EventAttendance::query();

        // Aplicar filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('event_id')) {
            $query->byEvent($request->event_id);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('organization_id')) {
            $query->byOrganization($request->organization_id);
        }

        if ($request->filled('checkin_status')) {
            $query = match ($request->checkin_status) {
                'checked_in' => $query->whereNotNull('checked_in_at'),
                'not_checked_in' => $query->whereNull('checked_in_at'),
                default => $query
            };
        }

        if ($request->filled('from') || $request->filled('until')) {
            if ($request->filled('from')) {
                $query->whereDate('registered_at', '>=', $request->from);
            }
            if ($request->filled('until')) {
                $query->whereDate('registered_at', '<=', $request->until);
            }
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Aplicar ordenamiento
        $sortField = $request->get('sort', 'registered_at');
        $sortOrder = $request->get('order', 'desc');
        
        if (in_array($sortField, ['registered_at', 'checked_in_at', 'created_at'])) {
            $query->orderBy($sortField, $sortOrder);
        }

        // Cargar relaciones necesarias
        $query->with(['event', 'user']);

        $attendances = $query->paginate($request->get('per_page', 15));

        return EventAttendanceCollection::make($attendances);
    }

    /**
     * Store a newly created event attendance.
     *
     * @bodyParam event_id integer required The ID of the event. Example: 1
     * @bodyParam user_id integer required The ID of the user. Example: 1
     * @bodyParam status string The status of the attendance. Example: registered
     * @bodyParam registered_at string The registration date and time (ISO 8601). Example: 2024-01-10T10:00:00Z
     * @bodyParam notes string Additional notes for administrators. Example: Usuario VIP
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "event_id": 1,
     *     "user_id": 1,
     *     "status": "registered",
     *     "registered_at": "2024-01-10T10:00:00Z",
     *     "checked_in_at": null,
     *     "cancellation_reason": null,
     *       "notes": "Usuario VIP",
     *     "checkin_token": "abc123...",
     *     "status_label": "Registrado",
     *     "status_badge_class": "info",
     *     "status_icon": "heroicon-o-clock",
     *     "status_color": "blue",
     *     "time_since_registration": "hace 5 días",
     *     "time_until_event": "en 2 días",
     *     "created_at": "2024-01-10T10:00:00Z",
     *     "updated_at": "2024-01-10T10:00:00Z"
     *   },
     *   "message": "Asistencia al evento registrada exitosamente"
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos.",
     *   "errors": {...}
     * }
     */
    public function store(StoreEventAttendanceRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Verificar que el usuario no esté ya registrado
            if (EventAttendance::isUserRegistered($request->event_id, $request->user_id)) {
                return response()->json([
                    'message' => 'El usuario ya está registrado en este evento'
                ], 422);
            }

            $attendance = EventAttendance::create($request->validated());

            Log::info('Asistencia al evento registrada', [
                'attendance_id' => $attendance->id,
                'event_id' => $attendance->event_id,
                'user_id' => $attendance->user_id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'data' => new EventAttendanceResource($attendance),
                'message' => 'Asistencia al evento registrada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar asistencia al evento', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al registrar la asistencia al evento',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified event attendance.
     *
     * @urlParam eventAttendance integer required The ID of the event attendance. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "event_id": 1,
     *     "user_id": 1,
     *     "status": "registered",
     *     "registered_at": "2024-01-10T10:00:00Z",
     *     "checked_in_at": null,
     *     "cancellation_reason": null,
     *     "notes": "Usuario VIP",
     *     "checkin_token": "abc123...",
     *     "status_label": "Registrado",
     *     "status_badge_class": "info",
     *     "status_icon": "heroicon-o-clock",
     *     "status_color": "blue",
     *     "time_since_registration": "hace 5 días",
     *     "time_until_event": "en 2 días",
     *     "event": {...},
     *     "user": {...},
     *     "created_at": "2024-01-10T10:00:00Z",
     *     "updated_at": "2024-01-10T10:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Asistencia al evento no encontrada"
     * }
     */
    public function show(EventAttendance $eventAttendance): JsonResponse
    {
        $eventAttendance->load(['event', 'user']);

        return response()->json([
            'data' => new EventAttendanceResource($eventAttendance)
        ]);
    }

    /**
     * Update the specified event attendance.
     *
     * @urlParam eventAttendance integer required The ID of the event attendance. Example: 1
     * @bodyParam status string The status of the attendance. Example: attended
     * @bodyParam checked_in_at string The check-in date and time (ISO 8601). Example: 2024-01-15T10:00:00Z
     * @bodyParam cancellation_reason string The reason for cancellation. Example: Conflicto de horarios
     * @bodyParam notes string Additional notes for administrators. Example: Usuario VIP confirmado
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "event_id": 1,
     *     "user_id": 1,
     *     "status": "attended",
     *     "registered_at": "2024-01-10T10:00:00Z",
     *     "checked_in_at": "2024-01-15T10:00:00Z",
     *     "cancellation_reason": null,
     *     "notes": "Usuario VIP confirmado",
     *     "checkin_token": "abc123...",
     *     "status_label": "Asistió",
     *     "status_badge_class": "success",
     *     "status_icon": "heroicon-o-check-circle",
     *     "status_color": "green",
     *     "time_since_registration": "hace 5 días",
     *     "time_since_checkin": "hace 2 horas",
     *     "created_at": "2024-01-10T10:00:00Z",
     *     "updated_at": "2024-01-15T10:00:00Z"
     *   },
     *   "message": "Asistencia al evento actualizada exitosamente"
     * }
     *
     * @response 404 {
     *   "message": "Asistencia al evento no encontrada"
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos.",
     *   "errors": {...}
     * }
     */
    public function update(UpdateEventAttendanceRequest $request, EventAttendance $eventAttendance): JsonResponse
    {
        try {
            DB::beginTransaction();

            $eventAttendance->update($request->validated());

            Log::info('Asistencia al evento actualizada', [
                'attendance_id' => $eventAttendance->id,
                'event_id' => $eventAttendance->event_id,
                'user_id' => $eventAttendance->user_id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'data' => new EventAttendanceResource($eventAttendance),
                'message' => 'Asistencia al evento actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar asistencia al evento', [
                'attendance_id' => $eventAttendance->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al actualizar la asistencia al evento',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified event attendance.
     *
     * @urlParam eventAttendance integer required The ID of the event attendance. Example: 1
     *
     * @response 200 {
     *   "message": "Asistencia al evento eliminada exitosamente"
     * }
     *
     * @response 404 {
     *   "message": "Asistencia al evento no encontrada"
     * }
     */
    public function destroy(EventAttendance $eventAttendance): JsonResponse
    {
        try {
            DB::beginTransaction();

            $attendanceId = $eventAttendance->id;
            $eventId = $eventAttendance->event_id;
            $userId = $eventAttendance->user_id;

            $eventAttendance->delete();

            Log::info('Asistencia al evento eliminada', [
                'attendance_id' => $attendanceId,
                'event_id' => $eventId,
                'user_id' => $userId,
                'admin_user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Asistencia al evento eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al eliminar asistencia al evento', [
                'attendance_id' => $eventAttendance->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al eliminar la asistencia al evento',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get attendance statistics.
     *
     * @queryParam event_id integer Filter by event ID. Example: 1
     * @queryParam user_id integer Filter by user ID. Example: 1
     * @queryParam status string Filter by status. Example: registered
     * @queryParam organization_id integer Filter by organization ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "total": 150,
     *     "registered": 120,
     *     "attended": 95,
     *     "cancelled": 20,
     *     "no_show": 15,
     *     "by_status": {
     *       "registered": 120,
     *       "attended": 95,
     *       "cancelled": 20,
     *       "no_show": 15
     *     }
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only(['event_id', 'user_id', 'status', 'organization_id']);
        $stats = EventAttendance::getStats($filters);

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Get available attendance statuses.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "value": "registered",
     *       "label": "Registrado",
     *       "description": "Usuario registrado pero no ha asistido"
     *     },
     *     {
     *       "value": "attended",
     *       "label": "Asistió",
     *       "description": "Usuario asistió al evento"
     *     },
     *     {
     *       "value": "cancelled",
     *       "label": "Cancelado",
     *       "description": "Usuario canceló su asistencia"
     *     },
     *     {
     *       "value": "no_show",
     *       "label": "No Asistió",
     *       "description": "Usuario no asistió al evento"
     *     }
     *   ]
     * }
     */
    public function statuses(): JsonResponse
    {
        $statuses = collect(EventAttendance::STATUSES)->map(function ($label, $value) {
            $descriptions = [
                'registered' => 'Usuario registrado pero no ha asistido',
                'attended' => 'Usuario asistió al evento',
                'cancelled' => 'Usuario canceló su asistencia',
                'no_show' => 'Usuario no asistió al evento'
            ];

            return [
                'value' => $value,
                'label' => $label,
                'description' => $descriptions[$value] ?? ''
            ];
        })->values();

        return response()->json([
            'data' => $statuses
        ]);
    }

    /**
     * Check in a user for an event.
     *
     * @urlParam eventAttendance integer required The ID of the event attendance. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "status": "attended",
     *     "checked_in_at": "2024-01-15T10:00:00Z",
     *     "message": "Check-in realizado exitosamente"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "No se puede realizar el check-in en este momento"
     * }
     */
    public function checkIn(EventAttendance $eventAttendance): JsonResponse
    {
        if (!$eventAttendance->canCheckIn()) {
            return response()->json([
                'message' => 'No se puede realizar el check-in en este momento'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $success = $eventAttendance->checkIn();

            if (!$success) {
                return response()->json([
                    'message' => 'Error al realizar el check-in'
                ], 500);
            }

            Log::info('Check-in realizado', [
                'attendance_id' => $eventAttendance->id,
                'event_id' => $eventAttendance->event_id,
                'user_id' => $eventAttendance->user_id,
                'admin_user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'data' => [
                    'id' => $eventAttendance->id,
                    'status' => $eventAttendance->status,
                    'checked_in_at' => $eventAttendance->checked_in_at,
                    'message' => 'Check-in realizado exitosamente'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al realizar check-in', [
                'attendance_id' => $eventAttendance->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al realizar el check-in',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cancel user attendance for an event.
     *
     * @urlParam eventAttendance integer required The ID of the event attendance. Example: 1
     * @bodyParam cancellation_reason string required The reason for cancellation. Example: Conflicto de horarios
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "status": "cancelled",
     *     "cancellation_reason": "Conflicto de horarios",
     *     "message": "Asistencia cancelada exitosamente"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "No se puede cancelar la asistencia en este momento"
     * }
     */
    public function cancel(Request $request, EventAttendance $eventAttendance): JsonResponse
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:1000'
        ]);

        if (!$eventAttendance->canCancel()) {
            return response()->json([
                'message' => 'No se puede cancelar la asistencia en este momento'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $success = $eventAttendance->cancel($request->cancellation_reason);

            if (!$success) {
                return response()->json([
                    'message' => 'Error al cancelar la asistencia'
                ], 500);
            }

            Log::info('Asistencia cancelada', [
                'attendance_id' => $eventAttendance->id,
                'event_id' => $eventAttendance->event_id,
                'user_id' => $eventAttendance->user_id,
                'reason' => $request->cancellation_reason,
                'admin_user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'data' => [
                    'id' => $eventAttendance->id,
                    'status' => $eventAttendance->status,
                    'cancellation_reason' => $eventAttendance->cancellation_reason,
                    'message' => 'Asistencia cancelada exitosamente'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al cancelar asistencia', [
                'attendance_id' => $eventAttendance->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al cancelar la asistencia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mark user as no show for an event.
     *
     * @urlParam eventAttendance integer required The ID of the event attendance. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "status": "no_show",
     *     "message": "Usuario marcado como no asistió"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "No se puede marcar como no asistió en este momento"
     * }
     */
    public function markAsNoShow(EventAttendance $eventAttendance): JsonResponse
    {
        if (!$eventAttendance->isRegistered()) {
            return response()->json([
                'message' => 'No se puede marcar como no asistió en este momento'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $success = $eventAttendance->markAsNoShow();

            if (!$success) {
                return response()->json([
                    'message' => 'Error al marcar como no asistió'
                ], 500);
            }

            Log::info('Usuario marcado como no asistió', [
                'attendance_id' => $eventAttendance->id,
                'event_id' => $eventAttendance->event_id,
                'user_id' => $eventAttendance->user_id,
                'admin_user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'data' => [
                    'id' => $eventAttendance->id,
                    'status' => $eventAttendance->status,
                    'message' => 'Usuario marcado como no asistió'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al marcar como no asistió', [
                'attendance_id' => $eventAttendance->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al marcar como no asistió',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Re-register a cancelled user.
     *
     * @urlParam eventAttendance integer required The ID of the event attendance. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "status": "registered",
     *     "message": "Usuario re-registrado exitosamente"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "No se puede re-registrar al usuario en este momento"
     * }
     */
    public function reRegister(EventAttendance $eventAttendance): JsonResponse
    {
        if (!$eventAttendance->isCancelled()) {
            return response()->json([
                'message' => 'No se puede re-registrar al usuario en este momento'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $success = $eventAttendance->reRegister();

            if (!$success) {
                return response()->json([
                    'message' => 'Error al re-registrar al usuario'
                ], 500);
            }

            Log::info('Usuario re-registrado', [
                'attendance_id' => $eventAttendance->id,
                'event_id' => $eventAttendance->event_id,
                'user_id' => $eventAttendance->user_id,
                'admin_user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'data' => [
                    'id' => $eventAttendance->id,
                    'status' => $eventAttendance->status,
                    'message' => 'Usuario re-registrado exitosamente'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al re-registrar usuario', [
                'attendance_id' => $eventAttendance->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al re-registrar al usuario',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get attendance by check-in token.
     *
     * @queryParam token string required The check-in token. Example: abc123...
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "event_id": 1,
     *     "user_id": 1,
     *     "status": "registered",
     *     "event": {...},
     *     "user": {...}
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Token de check-in no válido"
     * }
     */
    public function findByToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:64'
        ]);

        $attendance = EventAttendance::findByCheckinToken($request->token);

        if (!$attendance) {
            return response()->json([
                'message' => 'Token de check-in no válido'
            ], 404);
        }

        $attendance->load(['event', 'user']);

        return response()->json([
            'data' => new EventAttendanceResource($attendance)
        ]);
    }

    /**
     * Generate new check-in token.
     *
     * @urlParam eventAttendance integer required The ID of the event attendance. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "checkin_token": "def456...",
     *     "message": "Nuevo token de check-in generado"
     *   }
     * }
     */
    public function generateNewToken(EventAttendance $eventAttendance): JsonResponse
    {
        try {
            $newToken = $eventAttendance->generateNewCheckinToken();

            Log::info('Nuevo token de check-in generado', [
                'attendance_id' => $eventAttendance->id,
                'event_id' => $eventAttendance->event_id,
                'user_id' => $eventAttendance->user_id,
                'admin_user_id' => auth()->id(),
            ]);

            return response()->json([
                'data' => [
                    'id' => $eventAttendance->id,
                    'checkin_token' => $newToken,
                    'message' => 'Nuevo token de check-in generado'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al generar nuevo token de check-in', [
                'attendance_id' => $eventAttendance->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Error al generar nuevo token de check-in',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get event-specific attendance statistics.
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
    public function eventStats(Event $event): JsonResponse
    {
        $stats = EventAttendance::getEventStats($event->id);

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Get user-specific attendance statistics.
     *
     * @urlParam user integer required The ID of the user. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "total_events": 15,
     *     "attended_events": 12,
     *     "registered_events": 2,
     *     "cancelled_events": 1,
     *     "no_show_events": 0,
     *     "attendance_rate": 80
     *   }
     * }
     */
    public function userStats(int $user): JsonResponse
    {
        $stats = EventAttendance::getUserStats($user);

        return response()->json([
            'data' => $stats
        ]);
    }
}
