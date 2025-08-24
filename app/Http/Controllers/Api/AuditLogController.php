<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AuditLog::with(['user']);

            // Filtros
            if ($request->has('action')) {
                $query->whereIn('action', $request->action);
            }

            if ($request->has('actor_type')) {
                $query->whereIn('actor_type', $request->actor_type);
            }

            if ($request->has('user_id')) {
                $query->whereIn('user_id', $request->user_id);
            }

            if ($request->has('auditable_type')) {
                $query->where('auditable_type', $request->auditable_type);
            }

            if ($request->has('method')) {
                $query->whereIn('method', $request->method);
            }

            if ($request->has('response_code')) {
                $query->whereIn('response_code', $request->response_code);
            }

            if ($request->has('has_changes')) {
                if ($request->boolean('has_changes')) {
                    $query->where(function ($q) {
                        $q->whereNotNull('old_values')
                          ->orWhereNotNull('new_values');
                    });
                }
            }

            if ($request->has('recent_logs')) {
                if ($request->boolean('recent_logs')) {
                    $query->where('created_at', '>=', now()->subDay());
                }
            }

            if ($request->has('today_logs')) {
                if ($request->boolean('today_logs')) {
                    $query->whereDate('created_at', today());
                }
            }

            if ($request->has('this_week_logs')) {
                if ($request->boolean('this_week_logs')) {
                    $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                }
            }

            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->date_to);
            }

            if ($request->has('ip_address')) {
                $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
            }

            if ($request->has('search')) {
                $query->search($request->search);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 25);
            $logs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ],
                'message' => 'Registros de auditoría obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener registros de auditoría: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener registros de auditoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created audit log.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'action' => ['required', Rule::in(array_keys(AuditLog::getAvailableActions()))],
                'description' => 'required|string',
                'user_id' => 'nullable|exists:users,id',
                'actor_type' => ['nullable', Rule::in(array_keys(AuditLog::getAvailableActorTypes()))],
                'actor_identifier' => 'nullable|string|max:255',
                'auditable_type' => 'nullable|string|max:255',
                'auditable_id' => 'nullable|integer',
                'old_values' => 'nullable|array',
                'new_values' => 'nullable|array',
                'ip_address' => 'nullable|ip',
                'user_agent' => 'nullable|string',
                'url' => 'nullable|url|max:255',
                'method' => 'nullable|string|max:10',
                'request_data' => 'nullable|array',
                'response_data' => 'nullable|array',
                'response_code' => 'nullable|integer',
                'session_id' => 'nullable|string|max:255',
                'request_id' => 'nullable|string|max:255',
                'metadata' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $log = AuditLog::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $log->load(['user']),
                'message' => 'Registro de auditoría creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear registro de auditoría: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear registro de auditoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        try {
            $auditLog->load(['user']);

            return response()->json([
                'success' => true,
                'data' => $auditLog,
                'message' => 'Registro de auditoría obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener registro de auditoría: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener registro de auditoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified audit log.
     */
    public function update(Request $request, AuditLog $auditLog): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'description' => 'sometimes|required|string',
                'old_values' => 'sometimes|nullable|array',
                'new_values' => 'sometimes|nullable|array',
                'metadata' => 'sometimes|nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $auditLog->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $auditLog->fresh()->load(['user']),
                'message' => 'Registro de auditoría actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar registro de auditoría: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar registro de auditoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified audit log.
     */
    public function destroy(AuditLog $auditLog): JsonResponse
    {
        try {
            DB::beginTransaction();

            $auditLog->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro de auditoría eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar registro de auditoría: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar registro de auditoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get audit log changes.
     */
    public function getChanges(AuditLog $auditLog): JsonResponse
    {
        try {
            $changes = $auditLog->getChangedFields();

            return response()->json([
                'success' => true,
                'data' => $changes,
                'message' => 'Cambios del registro obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener cambios del registro: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cambios del registro',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get audit log statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_logs' => AuditLog::count(),
                'logs_today' => AuditLog::whereDate('created_at', today())->count(),
                'logs_this_week' => AuditLog::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'logs_this_month' => AuditLog::whereMonth('created_at', now()->month)->count(),
                'actions_by_type' => AuditLog::selectRaw('action, COUNT(*) as count')
                    ->groupBy('action')
                    ->pluck('count', 'action'),
                'actor_types' => AuditLog::selectRaw('actor_type, COUNT(*) as count')
                    ->groupBy('actor_type')
                    ->pluck('count', 'actor_type'),
                'response_codes' => AuditLog::selectRaw('response_code, COUNT(*) as count')
                    ->whereNotNull('response_code')
                    ->groupBy('response_code')
                    ->pluck('count', 'response_code'),
                'methods' => AuditLog::selectRaw('method, COUNT(*) as count')
                    ->whereNotNull('method')
                    ->groupBy('method')
                    ->pluck('count', 'method'),
                'top_users' => AuditLog::selectRaw('user_id, COUNT(*) as count')
                    ->whereNotNull('user_id')
                    ->groupBy('user_id')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->with('user:id,name')
                    ->get(),
                'recent_activity' => AuditLog::where('created_at', '>=', now()->subHours(24))
                    ->count(),
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
     * Get available actions.
     */
    public function actions(): JsonResponse
    {
        try {
            $actions = AuditLog::getAvailableActions();

            return response()->json([
                'success' => true,
                'data' => $actions,
                'message' => 'Acciones disponibles obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener acciones disponibles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener acciones disponibles',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available actor types.
     */
    public function actorTypes(): JsonResponse
    {
        try {
            $actorTypes = AuditLog::getAvailableActorTypes();

            return response()->json([
                'success' => true,
                'data' => $actorTypes,
                'message' => 'Tipos de actores disponibles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de actores disponibles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de actores disponibles',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Export audit logs.
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'format' => 'required|in:csv,json,xml',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'actions' => 'nullable|array',
                'actor_types' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = AuditLog::with(['user']);

            // Aplicar filtros
            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->date_to);
            }

            if ($request->has('actions')) {
                $query->whereIn('action', $request->actions);
            }

            if ($request->has('actor_types')) {
                $query->whereIn('actor_type', $request->actor_types);
            }

            $logs = $query->orderBy('created_at', 'desc')->get();

            // Generar exportación según formato
            $exportData = $this->generateExport($logs, $request->format);

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'message' => 'Exportación generada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al exportar registros de auditoría: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar registros de auditoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get audit log summary.
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'period' => 'required|in:day,week,month,quarter,year',
                'group_by' => 'nullable|in:action,actor_type,user_id,method,response_code',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $period = $request->period;
            $groupBy = $request->group_by ?? 'action';

            $query = AuditLog::query();

            // Aplicar filtro de período
            switch ($period) {
                case 'day':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [
                        now()->startOfQuarter(),
                        now()->endOfQuarter()
                    ]);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }

            $summary = $query->selectRaw("{$groupBy}, COUNT(*) as count")
                ->groupBy($groupBy)
                ->orderBy('count', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'group_by' => $groupBy,
                    'summary' => $summary,
                    'total' => $summary->sum('count'),
                ],
                'message' => 'Resumen obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener resumen: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get audit log timeline.
     */
    public function timeline(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'days' => 'nullable|integer|min:1|max:30',
                'group_by' => 'nullable|in:hour,day',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $days = $request->get('days', 7);
            $groupBy = $request->get('group_by', 'day');

            $query = AuditLog::where('created_at', '>=', now()->subDays($days));

            if ($groupBy === 'hour') {
                $timeline = $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as period, COUNT(*) as count')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            } else {
                $timeline = $query->selectRaw('DATE(created_at) as period, COUNT(*) as count')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'days' => $days,
                    'group_by' => $groupBy,
                    'timeline' => $timeline,
                ],
                'message' => 'Línea de tiempo obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener línea de tiempo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener línea de tiempo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk delete audit logs.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'log_ids' => 'required|array|min:1',
                'log_ids.*' => 'exists:audit_logs,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $logIds = $request->log_ids;
            $logs = AuditLog::whereIn('id', $logIds)->get();
            $deletedCount = 0;

            foreach ($logs as $log) {
                $log->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'deleted_count' => $deletedCount,
                    'total_requested' => count($logIds)
                ],
                'message' => "{$deletedCount} registros de auditoría eliminados exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en eliminación masiva de registros de auditoría: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en eliminación masiva de registros de auditoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Generate export data.
     */
    private function generateExport($logs, $format): array
    {
        switch ($format) {
            case 'csv':
                return $this->generateCsvExport($logs);
            case 'xml':
                return $this->generateXmlExport($logs);
            case 'json':
            default:
                return $this->generateJsonExport($logs);
        }
    }

    /**
     * Generate CSV export.
     */
    private function generateCsvExport($logs): array
    {
        $headers = [
            'ID', 'Acción', 'Descripción', 'Usuario', 'Tipo de Actor', 'Objeto',
            'IP', 'Método', 'URL', 'Código de Respuesta', 'Fecha'
        ];

        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                $log->id,
                $log->action,
                $log->description,
                $log->user ? $log->user->name : 'N/A',
                $log->actor_type,
                $log->auditable_type ? class_basename($log->auditable_type) : 'N/A',
                $log->ip_address,
                $log->method,
                $log->url,
                $log->response_code,
                $log->created_at->format('Y-m-d H:i:s')
            ];
        }

        return [
            'format' => 'csv',
            'headers' => $headers,
            'rows' => $rows,
            'filename' => 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ];
    }

    /**
     * Generate XML export.
     */
    private function generateXmlExport($logs): array
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><audit_logs></audit_logs>');

        foreach ($logs as $log) {
            $logElement = $xml->addChild('log');
            $logElement->addChild('id', $log->id);
            $logElement->addChild('action', $log->action);
            $logElement->addChild('description', $log->description);
            $logElement->addChild('user', $log->user ? $log->user->name : 'N/A');
            $logElement->addChild('actor_type', $log->actor_type);
            $logElement->addChild('created_at', $log->created_at->format('Y-m-d H:i:s'));
        }

        return [
            'format' => 'xml',
            'content' => $xml->asXML(),
            'filename' => 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.xml'
        ];
    }

    /**
     * Generate JSON export.
     */
    private function generateJsonExport($logs): array
    {
        $data = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user ? $log->user->name : null,
                'actor_type' => $log->actor_type,
                'auditable_type' => $log->auditable_type ? class_basename($log->auditable_type) : null,
                'ip_address' => $log->ip_address,
                'method' => $log->method,
                'url' => $log->url,
                'response_code' => $log->response_code,
                'created_at' => $log->created_at->format('Y-m-d H:i:s')
            ];
        });

        return [
            'format' => 'json',
            'data' => $data,
            'filename' => 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.json'
        ];
    }
}
