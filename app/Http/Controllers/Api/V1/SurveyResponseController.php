<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

/**
 * @group Survey Response Management
 *
 * APIs for managing survey responses and their data
 */
class SurveyResponseController extends Controller
{
    /**
     * Display a listing of survey responses.
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam survey_id integer Filter by survey ID. Example: 1
     * @queryParam user_id integer Filter by user ID. Example: 1
     * @queryParam response_type string Filter by response type (anonymous/identified). Example: identified
     * @queryParam date_from string Filter responses from date. Example: 2024-01-01
     * @queryParam date_to string Filter responses until date. Example: 2024-12-31
     * @queryParam sort_by string Sort field. Example: created_at
     * @queryParam sort_order string Sort direction (asc/desc). Example: desc
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "survey_id": 1,
     *       "user_id": 1,
     *       "response_data": {
     *         "rating": 5,
     *         "comment": "Excelente servicio"
     *       },
     *       "response_type": "identified",
     *       "respondent_name": "Juan Pérez",
     *       "response_field_count": 2,
     *       "time_since_response": "hace 2 horas",
     *       "created_at": "2024-01-01T10:00:00.000000Z"
     *     }
     *   ],
     *   "links": {
     *     "first": "http://localhost:8000/api/v1/survey-responses?page=1",
     *     "last": "http://localhost:8000/api/v1/survey-responses?page=3",
     *     "prev": null,
     *     "next": "http://localhost:8000/api/v1/survey-responses?page=2"
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
            $query = SurveyResponse::with(['survey', 'user']);

            // Filtro por encuesta
            if ($request->filled('survey_id')) {
                $query->bySurvey($request->survey_id);
            }

            // Filtro por usuario
            if ($request->filled('user_id')) {
                $query->byUser($request->user_id);
            }

            // Filtro por tipo de respuesta
            if ($request->filled('response_type')) {
                switch ($request->response_type) {
                    case 'anonymous':
                        $query->anonymous();
                        break;
                    case 'identified':
                        $query->identified();
                        break;
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
            $responses = $query->paginate($perPage);

            Log::info('Survey responses retrieved successfully', [
                'user_id' => auth()->id(),
                'filters' => $request->only(['survey_id', 'user_id', 'response_type', 'date_from', 'date_to']),
                'count' => $responses->total()
            ]);

            return response()->json($responses);

        } catch (\Exception $e) {
            Log::error('Error retrieving survey responses', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al obtener las respuestas de encuestas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created survey response.
     *
     * @bodyParam survey_id integer required The ID of the survey. Example: 1
     * @bodyParam user_id integer The ID of the user (optional for anonymous responses). Example: 1
     * @bodyParam response_data object required The response data as JSON object. Example: {"rating": 5, "comment": "Excelente"}
     *
     * @response 201 {
     *   "message": "Respuesta de encuesta creada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "survey_id": 1,
     *     "user_id": 1,
     *     "response_data": {
     *       "rating": 5,
     *       "comment": "Excelente"
     *     },
     *     "response_type": "identified",
     *     "created_at": "2024-01-01T10:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos",
     *   "errors": {
     *     "survey_id": ["La encuesta es obligatoria"],
     *     "response_data": ["Los datos de respuesta son obligatorios"]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'survey_id' => 'required|exists:surveys,id',
                'user_id' => 'nullable|exists:users,id',
                'response_data' => 'required|array',
            ], [
                'survey_id.required' => 'La encuesta es obligatoria',
                'survey_id.exists' => 'La encuesta especificada no existe',
                'user_id.exists' => 'El usuario especificado no existe',
                'response_data.required' => 'Los datos de respuesta son obligatorios',
                'response_data.array' => 'Los datos de respuesta deben ser un objeto válido',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Verificar que la encuesta esté activa
            $survey = Survey::findOrFail($request->survey_id);
            if (!$survey->isActive()) {
                return response()->json([
                    'message' => 'La encuesta no está activa'
                ], 422);
            }

            // Verificar permisos de anonimato
            if (!$survey->allowsAnonymous() && !$request->user_id) {
                return response()->json([
                    'message' => 'Esta encuesta no permite respuestas anónimas'
                ], 422);
            }

            // Verificar que el usuario no haya respondido ya (si está autenticado)
            if ($request->user_id && $survey->hasUserResponded($request->user_id)) {
                return response()->json([
                    'message' => 'El usuario ya ha respondido esta encuesta'
                ], 422);
            }

            $response = SurveyResponse::create($request->all());

            Log::info('Survey response created successfully', [
                'user_id' => auth()->id(),
                'response_id' => $response->id,
                'survey_id' => $response->survey_id,
                'is_anonymous' => $response->isAnonymous()
            ]);

            return response()->json([
                'message' => 'Respuesta de encuesta creada exitosamente',
                'data' => $response->fresh()
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Survey response creation validation failed', [
                'user_id' => auth()->id(),
                'errors' => $e->errors()
            ]);

            return response()->json([
                'message' => 'Los datos proporcionados no son válidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating survey response', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al crear la respuesta de encuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified survey response.
     *
     * @urlParam id integer required The ID of the response. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "survey_id": 1,
     *     "user_id": 1,
     *     "response_data": {
     *       "rating": 5,
     *       "comment": "Excelente servicio"
     *     },
     *     "response_type": "identified",
     *     "respondent_name": "Juan Pérez",
     *     "respondent_email": "juan@example.com",
     *     "response_field_count": 2,
     *     "time_since_response": "hace 2 horas",
     *     "created_at": "2024-01-01T10:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Respuesta de encuesta no encontrada"
     * }
     */
    public function show(int $id): JsonResponse
    {
        try {
            $response = SurveyResponse::with(['survey', 'user'])->findOrFail($id);

            Log::info('Survey response retrieved successfully', [
                'user_id' => auth()->id(),
                'response_id' => $id
            ]);

            return response()->json([
                'data' => $response
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning('Survey response not found', [
                'user_id' => auth()->id(),
                'response_id' => $id
            ]);

            return response()->json([
                'message' => 'Respuesta de encuesta no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error retrieving survey response', [
                'user_id' => auth()->id(),
                'response_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al obtener la respuesta de encuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified survey response.
     *
     * @urlParam id integer required The ID of the response. Example: 1
     * @bodyParam response_data object The updated response data. Example: {"rating": 4, "comment": "Muy bueno"}
     *
     * @response 200 {
     *   "message": "Respuesta de encuesta actualizada exitosamente",
     *   "data": {
     *     "id": 1,
     *     "response_data": {
     *       "rating": 4,
     *       "comment": "Muy bueno"
     *     },
     *     "updated_at": "2024-01-01T10:00:00.000000Z"
     *   }
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $response = SurveyResponse::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'response_data' => 'sometimes|required|array',
            ], [
                'response_data.required' => 'Los datos de respuesta son obligatorios',
                'response_data.array' => 'Los datos de respuesta deben ser un objeto válido',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $response->update($request->all());

            Log::info('Survey response updated successfully', [
                'user_id' => auth()->id(),
                'response_id' => $id,
                'changes' => $request->all()
            ]);

            return response()->json([
                'message' => 'Respuesta de encuesta actualizada exitosamente',
                'data' => $response->fresh()
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning('Survey response not found for update', [
                'user_id' => auth()->id(),
                'response_id' => $id
            ]);

            return response()->json([
                'message' => 'Respuesta de encuesta no encontrada'
            ], 404);

        } catch (ValidationException $e) {
            Log::warning('Survey response update validation failed', [
                'user_id' => auth()->id(),
                'response_id' => $id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'message' => 'Los datos proporcionados no son válidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating survey response', [
                'user_id' => auth()->id(),
                'response_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al actualizar la respuesta de encuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified survey response.
     *
     * @urlParam id integer required The ID of the response. Example: 1
     *
     * @response 200 {
     *   "message": "Respuesta de encuesta eliminada exitosamente"
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $response = SurveyResponse::findOrFail($id);
            $response->delete();

            Log::info('Survey response deleted successfully', [
                'user_id' => auth()->id(),
                'response_id' => $id
            ]);

            return response()->json([
                'message' => 'Respuesta de encuesta eliminada exitosamente'
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning('Survey response not found for deletion', [
                'user_id' => auth()->id(),
                'response_id' => $id
            ]);

            return response()->json([
                'message' => 'Respuesta de encuesta no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting survey response', [
                'user_id' => auth()->id(),
                'response_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al eliminar la respuesta de encuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get response statistics.
     *
     * @queryParam survey_id integer Filter by survey ID. Example: 1
     * @queryParam user_id integer Filter by user ID. Example: 1
     * @queryParam response_type string Filter by response type. Example: anonymous
     * @queryParam date_from string Filter responses from date. Example: 2024-01-01
     * @queryParam date_to string Filter responses until date. Example: 2024-12-31
     *
     * @response 200 {
     *   "data": {
     *     "total": 150,
     *     "anonymous": 45,
     *     "identified": 105,
     *     "today": 12,
     *     "this_week": 67,
     *     "this_month": 134,
     *     "anonymous_percentage": 30.0,
     *     "identified_percentage": 70.0
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['survey_id', 'user_id', 'response_type', 'date_from', 'date_to']);
            
            // Aplicar filtros de fecha si se proporcionan
            if (isset($filters['date_from']) || isset($filters['date_to'])) {
                $from = $filters['date_from'] ?? '1900-01-01';
                $to = $filters['date_to'] ?? '2100-12-31';
                $filters['date_range'] = ['from' => $from, 'to' => $to];
            }

            $stats = SurveyResponse::getStats($filters);

            Log::info('Survey response statistics retrieved', [
                'user_id' => auth()->id(),
                'filters' => $filters
            ]);

            return response()->json([
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving survey response statistics', [
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
     * Get responses by survey.
     *
     * @urlParam survey_id integer required The ID of the survey. Example: 1
     * @queryParam limit integer Number of responses to return. Example: 50
     * @queryParam response_type string Filter by response type. Example: anonymous
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "response_data": {
     *         "rating": 5,
     *         "comment": "Excelente"
     *       },
     *       "response_type": "identified",
     *       "respondent_name": "Juan Pérez",
     *       "created_at": "2024-01-01T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function getBySurvey(int $surveyId, Request $request): JsonResponse
    {
        try {
            // Verificar que la encuesta existe
            $survey = Survey::findOrFail($surveyId);
            
            $limit = $request->get('limit', 50);
            $responseType = $request->get('response_type');

            $query = SurveyResponse::bySurvey($surveyId)->with(['user']);

            if ($responseType) {
                if ($responseType === 'anonymous') {
                    $query->anonymous();
                } elseif ($responseType === 'identified') {
                    $query->identified();
                }
            }

            $responses = $query->orderBy('created_at', 'desc')->limit($limit)->get();

            Log::info('Survey responses retrieved by survey', [
                'user_id' => auth()->id(),
                'survey_id' => $surveyId,
                'response_type' => $responseType,
                'count' => $responses->count()
            ]);

            return response()->json([
                'data' => $responses,
                'survey' => [
                    'id' => $survey->id,
                    'title' => $survey->title,
                    'total_responses' => $survey->responses()->count()
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning('Survey not found for responses', [
                'user_id' => auth()->id(),
                'survey_id' => $surveyId
            ]);

            return response()->json([
                'message' => 'Encuesta no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error retrieving survey responses by survey', [
                'user_id' => auth()->id(),
                'survey_id' => $surveyId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las respuestas de la encuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get responses by user.
     *
     * @urlParam user_id integer required The ID of the user. Example: 1
     * @queryParam limit integer Number of responses to return. Example: 50
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "survey_id": 1,
     *       "response_data": {
     *         "rating": 5,
     *         "comment": "Excelente"
     *       },
     *       "survey": {
     *         "id": 1,
     *         "title": "Encuesta de Satisfacción"
     *       },
     *       "created_at": "2024-01-01T10:00:00.000000Z"
     *   ]
     * }
     */
    public function getByUser(int $userId, Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            $responses = SurveyResponse::byUser($userId)
                ->with(['survey'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            Log::info('Survey responses retrieved by user', [
                'user_id' => auth()->id(),
                'target_user_id' => $userId,
                'count' => $responses->count()
            ]);

            return response()->json([
                'data' => $responses,
                'user_stats' => SurveyResponse::getUserStats($userId)
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving survey responses by user', [
                'user_id' => auth()->id(),
                'target_user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las respuestas del usuario',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get popular responses.
     *
     * @queryParam limit integer Number of responses to return. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "survey_id": 1,
     *       "response_field_count": 8,
     *       "survey": {
     *         "id": 1,
     *         "title": "Encuesta Detallada"
     *       }
     *     }
     *   ]
     * }
     */
    public function popular(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $responses = SurveyResponse::getPopularResponses($limit);

            Log::info('Popular survey responses retrieved', [
                'user_id' => auth()->id(),
                'limit' => $limit,
                'count' => $responses->count()
            ]);

            return response()->json([
                'data' => $responses
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving popular survey responses', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las respuestas populares',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get recent responses.
     *
     * @queryParam limit integer Number of responses to return. Example: 20
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "survey_id": 1,
     *       "user_id": 1,
     *       "response_data": {
     *         "rating": 5,
     *         "comment": "Excelente"
     *       },
     *       "survey": {
     *         "id": 1,
     *         "title": "Encuesta de Satisfacción"
     *       },
     *       "user": {
     *         "id": 1,
     *         "name": "Juan Pérez"
     *       },
     *       "created_at": "2024-01-01T10:00:00.000000Z"
     *   ]
     * }
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 20);
            $responses = SurveyResponse::getRecentResponses($limit);

            Log::info('Recent survey responses retrieved', [
                'user_id' => auth()->id(),
                'limit' => $limit,
                'count' => $responses->count()
            ]);

            return response()->json([
                'data' => $responses
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving recent survey responses', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las respuestas recientes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Check if user has responded to a survey.
     *
     * @urlParam survey_id integer required The ID of the survey. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "has_responded": true,
     *     "response_id": 1,
     *     "response_type": "identified",
     *     "created_at": "2024-01-01T10:00:00.000000Z"
     *   }
     * }
     *
     * @response 200 {
     *   "data": {
     *     "has_responded": false
     *   }
     * }
     */
    public function checkUserResponse(int $surveyId): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $hasResponded = SurveyResponse::hasUserRespondedToSurvey($user->id, $surveyId);
            $response = null;

            if ($hasResponded) {
                $response = SurveyResponse::getUserSurveyResponse($user->id, $surveyId);
            }

            Log::info('User response check performed', [
                'user_id' => $user->id,
                'survey_id' => $surveyId,
                'has_responded' => $hasResponded
            ]);

            return response()->json([
                'data' => [
                    'has_responded' => $hasResponded,
                    'response_id' => $response?->id,
                    'response_type' => $response?->response_type,
                    'created_at' => $response?->created_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking user response', [
                'user_id' => auth()->id(),
                'survey_id' => $surveyId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al verificar la respuesta del usuario',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Search in response data.
     *
     * @queryParam q string required Search query. Example: excelente
     * @queryParam survey_id integer Filter by survey ID. Example: 1
     * @queryParam limit integer Number of responses to return. Example: 15
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "survey_id": 1,
     *       "response_data": {
     *         "rating": 5,
     *         "comment": "Excelente servicio"
     *       },
     *       "survey": {
     *         "id": 1,
     *         "title": "Encuesta de Satisfacción"
     *       }
     *     }
     *   ],
     *   "query": "excelente",
     *   "total_results": 5
     * }
     */
    public function searchInResponses(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2',
                'survey_id' => 'integer|exists:surveys,id',
                'limit' => 'integer|min:1|max:100'
            ]);

            $query = $request->get('q');
            $surveyId = $request->get('survey_id');
            $limit = $request->get('limit', 15);

            $responsesQuery = SurveyResponse::searchInResponse($query);

            if ($surveyId) {
                $responsesQuery->bySurvey($surveyId);
            }

            $responses = $responsesQuery
                ->with(['survey'])
                ->limit($limit)
                ->get();

            Log::info('Survey response search performed', [
                'user_id' => auth()->id(),
                'query' => $query,
                'survey_id' => $surveyId,
                'results_count' => $responses->count()
            ]);

            return response()->json([
                'data' => $responses,
                'query' => $query,
                'survey_id' => $surveyId,
                'total_results' => $responses->count()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Consulta de búsqueda no válida',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error searching in survey responses', [
                'user_id' => auth()->id(),
                'query' => $request->get('q'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al buscar en las respuestas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
