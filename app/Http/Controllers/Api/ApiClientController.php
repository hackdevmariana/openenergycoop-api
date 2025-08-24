<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApiClientController extends Controller
{
    /**
     * Display a listing of API clients.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ApiClient::with(['organization']);

            // Filtros
            if ($request->has('status')) {
                $query->whereIn('status', $request->status);
            }

            if ($request->has('organization_id')) {
                $query->whereIn('organization_id', $request->organization_id);
            }

            if ($request->has('version')) {
                $query->whereIn('version', $request->version);
            }

            if ($request->has('active_clients')) {
                if ($request->boolean('active_clients')) {
                    $query->where('status', ApiClient::STATUS_ACTIVE);
                }
            }

            if ($request->has('expired_clients')) {
                if ($request->boolean('expired_clients')) {
                    $query->whereNotNull('expires_at')
                          ->where('expires_at', '<', now());
                }
            }

            if ($request->has('recent_usage')) {
                if ($request->boolean('recent_usage')) {
                    $query->where('last_used_at', '>=', now()->subDays(7));
                }
            }

            if ($request->has('no_recent_usage')) {
                if ($request->boolean('no_recent_usage')) {
                    $query->where(function ($q) {
                        $q->whereNull('last_used_at')
                          ->orWhere('last_used_at', '<', now()->subDays(30));
                    });
                }
            }

            if ($request->has('search')) {
                $query->search($request->search);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $clients = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $clients->items(),
                'pagination' => [
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'per_page' => $clients->perPage(),
                    'total' => $clients->total(),
                    'from' => $clients->firstItem(),
                    'to' => $clients->lastItem(),
                ],
                'message' => 'Clientes API obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener clientes API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created API client.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,id',
                'name' => 'required|string|max:255',
                'token' => 'nullable|string|max:64|unique:api_clients',
                'scopes' => 'nullable|array',
                'permissions' => 'nullable|array',
                'endpoints' => 'nullable|array',
                'actions' => 'nullable|array',
                'allowed_ips' => 'nullable|array',
                'callback_url' => 'nullable|url|max:255',
                'expires_at' => 'nullable|date|after:now',
                'max_requests_per_hour' => 'nullable|integer|min:1|max:10000',
                'max_requests_per_day' => 'nullable|integer|min:1|max:100000',
                'webhooks_enabled' => 'boolean',
                'webhook_config' => 'nullable|array',
                'webhook_secret' => 'nullable|string',
                'webhook_timeout' => 'nullable|integer|min:5|max:60',
                'rate_limiting_enabled' => 'boolean',
                'ip_restriction_enabled' => 'boolean',
                'logging_enabled' => 'boolean',
                'analytics_enabled' => 'boolean',
                'notifications_enabled' => 'boolean',
                'auto_rotation' => 'boolean',
                'version' => 'nullable|string|max:10',
                'description' => 'nullable|string',
                'metadata' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generar token si no se proporciona
            if (empty($request->token)) {
                $request->merge(['token' => Str::random(64)]);
            }

            // Establecer valores por defecto
            $data = array_merge([
                'status' => ApiClient::STATUS_ACTIVE,
                'scopes' => ['read'],
                'rate_limiting_enabled' => true,
                'ip_restriction_enabled' => false,
                'logging_enabled' => true,
                'analytics_enabled' => true,
                'notifications_enabled' => true,
                'auto_rotation' => false,
                'webhooks_enabled' => false,
                'version' => '1.0',
            ], $request->all());

            $client = ApiClient::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $client->load(['organization']),
                'message' => 'Cliente API creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified API client.
     */
    public function show(ApiClient $apiClient): JsonResponse
    {
        try {
            $apiClient->load(['organization']);

            return response()->json([
                'success' => true,
                'data' => $apiClient,
                'message' => 'Cliente API obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified API client.
     */
    public function update(Request $request, ApiClient $apiClient): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'scopes' => 'sometimes|nullable|array',
                'permissions' => 'sometimes|nullable|array',
                'endpoints' => 'sometimes|nullable|array',
                'actions' => 'sometimes|nullable|array',
                'allowed_ips' => 'sometimes|nullable|array',
                'callback_url' => 'sometimes|nullable|url|max:255',
                'expires_at' => 'sometimes|nullable|date|after:now',
                'max_requests_per_hour' => 'sometimes|nullable|integer|min:1|max:10000',
                'max_requests_per_day' => 'sometimes|nullable|integer|min:1|max:100000',
                'webhooks_enabled' => 'sometimes|boolean',
                'webhook_config' => 'sometimes|nullable|array',
                'webhook_secret' => 'sometimes|nullable|string',
                'webhook_timeout' => 'sometimes|nullable|integer|min:5|max:60',
                'rate_limiting_enabled' => 'sometimes|boolean',
                'ip_restriction_enabled' => 'sometimes|boolean',
                'logging_enabled' => 'sometimes|boolean',
                'analytics_enabled' => 'sometimes|boolean',
                'notifications_enabled' => 'sometimes|boolean',
                'auto_rotation' => 'sometimes|boolean',
                'version' => 'sometimes|nullable|string|max:10',
                'description' => 'sometimes|nullable|string',
                'metadata' => 'sometimes|nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $apiClient->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $apiClient->fresh()->load(['organization']),
                'message' => 'Cliente API actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified API client.
     */
    public function destroy(ApiClient $apiClient): JsonResponse
    {
        try {
            DB::beginTransaction();

            $apiClient->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cliente API eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Regenerate API client token.
     */
    public function regenerateToken(ApiClient $apiClient): JsonResponse
    {
        try {
            $apiClient->regenerateToken();

            return response()->json([
                'success' => true,
                'data' => $apiClient->fresh(),
                'message' => 'Token del cliente API regenerado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al regenerar token: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al regenerar token',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Suspend API client.
     */
    public function suspend(ApiClient $apiClient): JsonResponse
    {
        try {
            $apiClient->suspend();

            return response()->json([
                'success' => true,
                'data' => $apiClient->fresh(),
                'message' => 'Cliente API suspendido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al suspender cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al suspender cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Activate API client.
     */
    public function activate(ApiClient $apiClient): JsonResponse
    {
        try {
            $apiClient->activate();

            return response()->json([
                'success' => true,
                'data' => $apiClient->fresh(),
                'message' => 'Cliente API activado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al activar cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al activar cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Revoke API client.
     */
    public function revoke(ApiClient $apiClient): JsonResponse
    {
        try {
            $apiClient->revoke();

            return response()->json([
                'success' => true,
                'data' => $apiClient->fresh(),
                'message' => 'Cliente API revocado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al revocar cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al revocar cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update API client usage.
     */
    public function updateUsage(ApiClient $apiClient): JsonResponse
    {
        try {
            $apiClient->updateUsage();

            return response()->json([
                'success' => true,
                'data' => $apiClient->fresh(),
                'message' => 'Uso del cliente API actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar uso del cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar uso del cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Validate API client token.
     */
    public function validateToken(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string|max:64',
                'ip_address' => 'nullable|ip',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $client = ApiClient::where('token', $request->token)->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido'
                ], 401);
            }

            if ($client->status !== ApiClient::STATUS_ACTIVE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente API inactivo'
                ], 403);
            }

            if ($client->expires_at && $client->expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token expirado'
                ], 401);
            }

            // Validar IP si está habilitada la restricción
            if ($client->ip_restriction_enabled && !empty($client->allowed_ips)) {
                $clientIp = $request->ip_address ?? request()->ip();
                if (!in_array($clientIp, array_keys($client->allowed_ips))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'IP no permitida'
                    ], 403);
                }
            }

            // Actualizar último uso
            $client->updateUsage();

            return response()->json([
                'success' => true,
                'data' => [
                    'client_id' => $client->id,
                    'name' => $client->name,
                    'scopes' => $client->scopes,
                    'permissions' => $client->permissions,
                    'organization_id' => $client->organization_id,
                ],
                'message' => 'Token válido'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al validar token: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al validar token',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get API client scopes.
     */
    public function getScopes(ApiClient $apiClient): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'scopes' => $apiClient->scopes,
                    'permissions' => $apiClient->permissions,
                    'endpoints' => $apiClient->endpoints,
                    'actions' => $apiClient->actions,
                ],
                'message' => 'Scopes del cliente API obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener scopes del cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener scopes del cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update API client scopes.
     */
    public function updateScopes(Request $request, ApiClient $apiClient): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'scopes' => 'sometimes|array',
                'permissions' => 'sometimes|array',
                'endpoints' => 'sometimes|array',
                'actions' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $apiClient->updateScopes($request->all());

            return response()->json([
                'success' => true,
                'data' => $apiClient->fresh(),
                'message' => 'Scopes del cliente API actualizados exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar scopes del cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar scopes del cliente API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get API client rate limit info.
     */
    public function getRateLimitInfo(ApiClient $apiClient): JsonResponse
    {
        try {
            $rateLimitInfo = $apiClient->getRateLimitInfo();

            return response()->json([
                'success' => true,
                'data' => $rateLimitInfo,
                'message' => 'Información de límites de tasa obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener información de límites de tasa: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de límites de tasa',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get API client statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_clients' => ApiClient::count(),
                'active_clients' => ApiClient::where('status', ApiClient::STATUS_ACTIVE)->count(),
                'suspended_clients' => ApiClient::where('status', ApiClient::STATUS_SUSPENDED)->count(),
                'revoked_clients' => ApiClient::where('status', ApiClient::STATUS_REVOKED)->count(),
                'clients_by_organization' => ApiClient::selectRaw('organization_id, COUNT(*) as count')
                    ->groupBy('organization_id')
                    ->with('organization:id,name')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
                'recent_usage' => ApiClient::where('last_used_at', '>=', now()->subDays(7))->count(),
                'expired_clients' => ApiClient::whereNotNull('expires_at')
                    ->where('expires_at', '<', now())
                    ->count(),
                'clients_by_version' => ApiClient::selectRaw('version, COUNT(*) as count')
                    ->groupBy('version')
                    ->pluck('count', 'version'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available statuses.
     */
    public function statuses(): JsonResponse
    {
        try {
            $statuses = ApiClient::getAvailableStatuses();

            return response()->json([
                'success' => true,
                'data' => $statuses,
                'message' => 'Estados disponibles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estados disponibles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estados disponibles',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk update API clients.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'client_ids' => 'required|array|min:1',
                'client_ids.*' => 'exists:api_clients,id',
                'updates' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $clientIds = $request->client_ids;
            $updates = $request->updates;

            $clients = ApiClient::whereIn('id', $clientIds)->get();
            $updatedCount = 0;

            foreach ($clients as $client) {
                $client->update($updates);
                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'updated_count' => $updatedCount,
                    'total_requested' => count($clientIds)
                ],
                'message' => "{$updatedCount} clientes API actualizados exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en actualización masiva de clientes API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en actualización masiva de clientes API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk delete API clients.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'client_ids' => 'required|array|min:1',
                'client_ids.*' => 'exists:api_clients,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $clientIds = $request->client_ids;
            $clients = ApiClient::whereIn('id', $clientIds)->get();
            $deletedCount = 0;

            foreach ($clients as $client) {
                $client->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'deleted_count' => $deletedCount,
                    'total_requested' => count($clientIds)
                ],
                'message' => "{$deletedCount} clientes API eliminados exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en eliminación masiva de clientes API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en eliminación masiva de clientes API',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk regenerate tokens.
     */
    public function bulkRegenerateTokens(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'client_ids' => 'required|array|min:1',
                'client_ids.*' => 'exists:api_clients,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $clientIds = $request->client_ids;
            $clients = ApiClient::whereIn('id', $clientIds)->get();
            $regeneratedCount = 0;

            foreach ($clients as $client) {
                $client->regenerateToken();
                $regeneratedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'regenerated_count' => $regeneratedCount,
                    'total_requested' => count($clientIds)
                ],
                'message' => "{$regeneratedCount} tokens regenerados exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en regeneración masiva de tokens: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en regeneración masiva de tokens',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
