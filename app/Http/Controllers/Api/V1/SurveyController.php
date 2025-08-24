<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * @group Survey Management
 *
 * APIs for managing surveys and their configurations
 */
class SurveyController extends Controller
{
    /**
     * Display a listing of surveys.
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search term for title or description. Example: satisfacción
     * @queryParam status string Filter by survey status. Example: active
     * @queryParam anonymous_allowed boolean Filter by anonymous responses allowed. Example: true
     * @queryParam visible_results boolean Filter by results visibility. Example: true
     * @queryParam date_from string Filter surveys starting from date. Example: 2024-01-01
     * @queryParam date_to string Filter surveys ending before date. Example: 2024-12-31
     * @queryParam sort_by string Sort field. Example: created_at
     * @queryParam sort_order string Sort direction (asc/desc). Example: desc
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Encuesta de Satisfacción del Cliente",
     *       "description": "Tu opinión es muy importante para nosotros...",
     *       "starts_at": "2024-01-01T00:00:00.000000Z",
     *       "ends_at": "2024-01-31T23:59:59.000000Z",
     *       "anonymous_allowed": true,
     *       "visible_results": true,
     *       "status": "active",
     *       "status_label": "Activa",
     *       "duration": "30 días",
     *       "response_stats": {
     *         "total_responses": 45,
     *         "anonymous_responses": 12,
     *         "user_responses": 33,
     *         "unique_users": 28,
     *         "response_rate": 160.71
     *       },
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ],
     *   "links": {
     *     "first": "http://localhost:8000/api/v1/surveys?page=1",
     *     "last": "http://localhost:8000/api/v1/surveys?page=3",
     *     "prev": null,
     *     "next": "http://localhost:8000/api/v1/surveys?page=2"
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 3,
     *     "per_page": 15,
     *     "to": 15,
     *     "total": 45
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Survey::query();

            // Aplicar filtros de búsqueda
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Filtro por estado
            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'active':
                        $query->active();
                        break;
                    case 'upcoming':
                        $query->upcoming();
                        break;
                    case 'past':
                        $query->past();
                        break;
                    case 'expiring_soon':
                        $query->expiringSoon();
                        break;
                }
            }

            // Filtro por permisos de anonimato
            if ($request->has('anonymous_allowed')) {
                if ($request->boolean('anonymous_allowed')) {
                    $query->anonymousAllowed();
                } else {
                    $query->anonymousNotAllowed();
                }
            }

            // Filtro por visibilidad de resultados
            if ($request->has('visible_results')) {
                if ($request->boolean('visible_results')) {
                    $query->resultsVisible();
                } else {
                    $query->resultsHidden();
                }
            }

            // Filtro por rango de fechas
            if ($request->filled('date_from') || $request->filled('date_to')) {
                $from = $request->date_from ?? '1900-01-01';
                $to = $request->date_to ?? '2100-12-31';
                $query->byDateRange($from, $to);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $surveys = $query->withCount('responses')->paginate($perPage);

            // Agregar estadísticas de respuesta a cada encuesta
            $surveys->getCollection()->transform(function ($survey) {
                $survey->response_stats = $survey->response_stats;
                return $survey;
            });

            Log::info('Surveys retrieved successfully', [
                'user_id' => auth()->id(),
                'filters' => $request->only(['search', 'status', 'anonymous_allowed', 'visible_results', 'date_from', 'date_to']),
                'count' => $surveys->total()
            ]);

            return response()->json($surveys);

        } catch (\Exception $e) {
            Log::error('Error retrieving surveys', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al obtener las encuestas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created survey.
     *
     * @bodyParam title string required The title of the survey. Example: Encuesta de Satisfacción
     * @bodyParam description string required The description of the survey. Example: Tu opinión es importante
     * @bodyParam starts_at string required Start date and time. Example: 2024-01-01 00:00:00
     * @bodyParam ends_at string required End date and time. Example: 2024-01-31 23:59:59
     * @bodyParam anonymous_allowed boolean Whether anonymous responses are allowed. Example: true
     * @bodyParam visible_results boolean Whether results are visible to users. Example: true
     *
     * @response 201 {
     *   "message": "Encuesta creada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "title": "Encuesta de Satisfacción",
     *     "description": "Tu opinión es importante",
     *     "starts_at": "2024-01-01T00:00:00.000000Z",
     *     "ends_at": "2024-01-31T23:59:59.000000Z",
     *     "anonymous_allowed": true,
     *     "visible_results": true,
     *     "status": "draft",
     *     "created_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "title": ["El título es obligatorio"],
     *     "ends_at": ["La fecha de fin debe ser posterior a la fecha de inicio"]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'starts_at' => 'required|date',
                'ends_at' => 'required|date|after:starts_at',
                'anonymous_allowed' => 'boolean',
                'visible_results' => 'boolean',
            ], [
                'title.required' => 'El título es obligatorio',
                'title.max' => 'El título no puede tener más de 255 caracteres',
                'description.required' => 'La descripción es obligatoria',
                'starts_at.required' => 'La fecha de inicio es obligatoria',
                'starts_at.date' => 'La fecha de inicio debe ser una fecha válida',
                'ends_at.required' => 'La fecha de fin es obligatoria',
                'ends_at.date' => 'La fecha de fin debe ser una fecha válida',
                'ends_at.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
                'anonymous_allowed.boolean' => 'El campo de respuestas anónimas debe ser verdadero o falso',
                'visible_results.boolean' => 'El campo de resultados visibles debe ser verdadero o falso',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $survey = Survey::create($request->all());

            Log::info('Survey created successfully', [
                'user_id' => auth()->id(),
                'survey_id' => $survey->id,
                'title' => $survey->title
            ]);

            return response()->json([
                'message' => 'Encuesta creada exitosamente',
                'data' => $survey->fresh()
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Survey creation validation failed', [
                'user_id' => auth()->id(),
                'errors' => $e->errors()
            ]);

            return response()->json([
                'message' => 'Los datos proporcionados no son válidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating survey', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al crear la encuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified survey.
     *
     * @urlParam id integer required The ID of the survey. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "title": "Encuesta de Satisfacción del Cliente",
     *     "description": "Tu opinión es muy importante para nosotros...",
     *     "starts_at": "2024-01-01T00:00:00.000000Z",
     *     "ends_at": "2024-01-31T23:59:59.000000Z",
     *     "anonymous_allowed": true,
     *     "visible_results": true,
     *     "status": "active",
     *     "status_label": "Activa",
     *     "duration": "30 días",
     *     "time_until_start": "Ya comenzó",
     *     "time_until_end": "Termina en 15 días",
     *     "response_stats": {
     *       "total_responses": 45,
     *       "anonymous_responses": 12,
     *       "user_responses": 33,
     *       "unique_users": 28,
     *       "response_rate": 160.71
     *       },
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Encuesta no encontrada"
     * }
     */
    public function show(int $id): JsonResponse
    {
        try {
            $survey = Survey::withCount('responses')->findOrFail($id);
            
            // Agregar estadísticas de respuesta
            $survey->response_stats = $survey->response_stats;

            Log::info('Survey retrieved successfully', [
                'user_id' => auth()->id(),
                'survey_id' => $id
            ]);

            return response()->json([
                'data' => $survey
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning('Survey not found', [
                'user_id' => auth()->id(),
                'survey_id' => $id
            ]);

            return response()->json([
                'message' => 'Encuesta no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error retrieving survey', [
                'user_id' => auth()->id(),
                'survey_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al obtener la encuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified survey.
     *
     * @urlParam id integer required The ID of the survey. Example: 1
     * @bodyParam title string The title of the survey. Example: Encuesta de Satisfacción Actualizada
     * @bodyParam description string The description of the survey. Example: Descripción actualizada
     * @bodyParam starts_at string Start date and time. Example: 2024-01-01 00:00:00
     * @bodyParam ends_at string End date and time. Example: 2024-01-31 23:59:59
     * @bodyParam anonymous_allowed boolean Whether anonymous responses are allowed. Example: false
     * @bodyParam visible_results boolean Whether results are visible to users. Example: false
     *
     * @response 200 {
     *   "message": "Encuesta actualizada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "title": "Encuesta de Satisfacción Actualizada",
     *     "description": "Descripción actualizada",
     *     "starts_at": "2024-01-01T00:00:00.000000Z",
     *     "ends_at": "2024-01-31T23:59:59.000000Z",
     *     "anonymous_allowed": false,
     *     "visible_results": false,
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $survey = Survey::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'starts_at' => 'sometimes|required|date',
                'ends_at' => 'sometimes|required|date|after:starts_at',
                'anonymous_allowed' => 'sometimes|boolean',
                'visible_results' => 'sometimes|boolean',
            ], [
                'title.required' => 'El título es obligatorio',
                'title.max' => 'El título no puede tener más de 255 caracteres',
                'description.required' => 'La descripción es obligatoria',
                'starts_at.required' => 'La fecha de inicio es obligatoria',
                'starts_at.date' => 'La fecha de inicio debe ser una fecha válida',
                'ends_at.required' => 'La fecha de fin es obligatoria',
                'ends_at.date' => 'La fecha de fin debe ser una fecha válida',
                'ends_at.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
                'anonymous_allowed.boolean' => 'El campo de respuestas anónimas debe ser verdadero o falso',
                'visible_results.boolean' => 'El campo de resultados visibles debe ser verdadero o falso',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $survey->update($request->all());

            Log::info('Survey updated successfully', [
                'user_id' => auth()->id(),
                'survey_id' => $id,
                'changes' => $request->all()
            ]);

            return response()->json([
                'message' => 'Encuesta actualizada exitosamente',
                'data' => $survey->fresh()
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning('Survey not found for update', [
                'user_id' => auth()->id(),
                'survey_id' => $id
            ]);

            return response()->json([
                'message' => 'Encuesta no encontrada'
            ], 404);

        } catch (ValidationException $e) {
            Log::warning('Survey update validation failed', [
                'user_id' => auth()->id(),
                'survey_id' => $id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'message' => 'Los datos proporcionados no son válidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating survey', [
                'user_id' => auth()->id(),
                'survey_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al actualizar la encuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified survey.
     *
     * @urlParam id integer required The ID of the survey. Example: 1
     *
     * @response 200 {
     *   "message": "Encuesta eliminada exitosamente"
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $survey = Survey::findOrFail($id);
            
            // Verificar si hay respuestas antes de eliminar
            if ($survey->responses()->count() > 0) {
                Log::warning('Attempted to delete survey with responses', [
                    'user_id' => auth()->id(),
                    'survey_id' => $id,
                    'response_count' => $survey->responses()->count()
                ]);

                return response()->json([
                    'message' => 'No se puede eliminar una encuesta que tiene respuestas'
                ], 422);
            }

            $survey->delete();

            Log::info('Survey deleted successfully', [
                'user_id' => auth()->id(),
                'survey_id' => $id,
                'title' => $survey->title
            ]);

            return response()->json([
                'message' => 'Encuesta eliminada exitosamente'
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning('Survey not found for deletion', [
                'user_id' => auth()->id(),
                'survey_id' => $id
            ]);

            return response()->json([
                'message' => 'Encuesta no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting survey', [
                'user_id' => auth()->id(),
                'survey_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al eliminar la encuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get survey statistics.
     *
     * @queryParam status string Filter by survey status. Example: active
     * @queryParam anonymous_allowed boolean Filter by anonymous responses allowed. Example: true
     * @queryParam visible_results boolean Filter by results visibility. Example: true
     *
     * @response 200 {
     *   "data": {
     *     "total": 45,
     *     "active": 12,
     *     "upcoming": 8,
     *     "past": 25,
     *     "anonymous_allowed": 32,
     *     "anonymous_not_allowed": 13,
     *     "results_visible": 38,
     *     "results_hidden": 7
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'anonymous_allowed', 'visible_results']);
            $stats = Survey::getStats($filters);

            Log::info('Survey statistics retrieved', [
                'user_id' => auth()->id(),
                'filters' => $filters
            ]);

            return response()->json([
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving survey statistics', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al obtener las estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get surveys by status.
     *
     * @queryParam status string required The status to filter by. Example: active
     * @queryParam limit integer Number of surveys to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Encuesta Activa",
     *       "status": "active",
     *       "starts_at": "2024-01-01T00:00:00.000000Z",
     *       "ends_at": "2024-01-31T23:59:59.000000Z"
     *     }
     *   ]
     * }
     */
    public function getByStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:active,upcoming,past,expiring_soon',
                'limit' => 'integer|min:1|max:100'
            ]);

            $limit = $request->get('limit', 10);
            $status = $request->status;

            $query = Survey::query();

            switch ($status) {
                case 'active':
                    $surveys = Survey::active()->limit($limit)->get();
                    break;
                case 'upcoming':
                    $surveys = Survey::upcoming()->limit($limit)->get();
                    break;
                case 'past':
                    $surveys = Survey::past()->limit($limit)->get();
                    break;
                case 'expiring_soon':
                    $surveys = Survey::expiringSoon()->limit($limit)->get();
                    break;
                default:
                    $surveys = collect();
            }

            Log::info('Surveys retrieved by status', [
                'user_id' => auth()->id(),
                'status' => $status,
                'count' => $surveys->count()
            ]);

            return response()->json([
                'data' => $surveys
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Estado de encuesta no válido',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error retrieving surveys by status', [
                'user_id' => auth()->id(),
                'status' => $request->status,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las encuestas por estado',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get popular surveys.
     *
     * @queryParam limit integer Number of surveys to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Encuesta Popular",
     *       "responses_count": 150,
     *       "starts_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function popular(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $surveys = Survey::getPopularSurveys($limit);

            Log::info('Popular surveys retrieved', [
                'user_id' => auth()->id(),
                'limit' => $limit,
                'count' => $surveys->count()
            ]);

            return response()->json([
                'data' => $surveys
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving popular surveys', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las encuestas populares',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get surveys expiring soon.
     *
     * @queryParam days integer Number of days to consider "soon". Example: 7
     * @queryParam limit integer Number of surveys to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Encuesta que Expira Pronto",
     *       "ends_at": "2024-01-07T23:59:59.000000Z",
     *       "days_remaining": 3
     *     }
     *   ]
     * }
     */
    public function expiringSoon(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 7);
            $limit = $request->get('limit', 10);
            
            $surveys = Survey::getExpiringSoon($days, $limit);

            // Agregar días restantes a cada encuesta
            $surveys->transform(function ($survey) {
                $survey->days_remaining = now()->diffInDays($survey->ends_at, false);
                return $survey;
            });

            Log::info('Expiring soon surveys retrieved', [
                'user_id' => auth()->id(),
                'days' => $days,
                'count' => $surveys->count()
            ]);

            return response()->json([
                'data' => $surveys
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving expiring soon surveys', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las encuestas que expiran pronto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get surveys active today.
     *
     * @queryParam limit integer Number of surveys to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Encuesta de Hoy",
     *       "starts_at": "2024-01-01T00:00:00.000000Z",
     *       "ends_at": "2024-01-31T23:59:59.000000Z"
     *     }
     *   ]
     * }
     */
    public function activeToday(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $surveys = Survey::getActiveToday($limit);

            Log::info('Active today surveys retrieved', [
                'user_id' => auth()->id(),
                'count' => $surveys->count()
            ]);

            return response()->json([
                'data' => $surveys
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving active today surveys', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las encuestas activas hoy',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get recommended surveys for a user.
     *
     * @queryParam limit integer Number of surveys to return. Example: 5
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Encuesta Recomendada",
     *       "starts_at": "2024-01-01T00:00:00.000000Z",
     *       "ends_at": "2024-01-31T23:59:59.000000Z"
     *     }
     *   ]
     * }
     */
    public function recommended(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $limit = $request->get('limit', 5);
            $surveys = Survey::getRecommendedForUser($user, $limit);

            Log::info('Recommended surveys retrieved', [
                'user_id' => $user->id,
                'limit' => $limit,
                'count' => $surveys->count()
            ]);

            return response()->json([
                'data' => $surveys
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving recommended surveys', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las encuestas recomendadas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Search surveys.
     *
     * @queryParam q string required Search query. Example: satisfacción
     * @queryParam limit integer Number of surveys to return. Example: 15
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Encuesta de Satisfacción",
     *       "description": "Tu opinión es muy importante...",
     *       "starts_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2',
                'limit' => 'integer|min:1|max:100'
            ]);

            $query = $request->get('q');
            $limit = $request->get('limit', 15);

            $surveys = Survey::search($query)
                ->withCount('responses')
                ->limit($limit)
                ->get();

            Log::info('Survey search performed', [
                'user_id' => auth()->id(),
                'query' => $query,
                'results_count' => $surveys->count()
            ]);

            return response()->json([
                'data' => $surveys,
                'query' => $query,
                'total_results' => $surveys->count()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Consulta de búsqueda no válida',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error searching surveys', [
                'user_id' => auth()->id(),
                'query' => $request->get('q'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al buscar encuestas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
