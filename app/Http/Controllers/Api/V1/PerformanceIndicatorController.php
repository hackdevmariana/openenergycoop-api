<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PerformanceIndicator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Indicadores de Rendimiento", description: "Gestión de KPIs e indicadores de performance")]
class PerformanceIndicatorController extends \App\Http\Controllers\Controller
{
    #[OA\Get(
        path: "/api/v1/performance-indicators",
        description: "Obtener lista de indicadores de rendimiento",
        summary: "Listar indicadores de rendimiento",
        security: [["sanctum" => []]],
        tags: ["Indicadores de Rendimiento"]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = PerformanceIndicator::with(['energyCooperative', 'user', 'energyReport']);

        if ($request->filled('indicator_type')) {
            $query->where('indicator_type', $request->indicator_type);
        }

        if ($request->filled('criticality')) {
            $query->where('criticality', $request->criticality);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('show_in_dashboard')) {
            $query->where('show_in_dashboard', $request->boolean('show_in_dashboard'));
        }

        $indicators = $query->orderBy('priority', 'desc')
            ->orderBy('measurement_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $indicators->items(),
            'meta' => [
                'current_page' => $indicators->currentPage(),
                'last_page' => $indicators->lastPage(),
                'per_page' => $indicators->perPage(),
                'total' => $indicators->total(),
                'from' => $indicators->firstItem(),
                'to' => $indicators->lastItem(),
            ]
        ]);
    }

    #[OA\Post(
        path: "/api/v1/performance-indicators",
        description: "Crear un nuevo indicador de rendimiento",
        summary: "Crear indicador de rendimiento",
        security: [["sanctum" => []]],
        tags: ["Indicadores de Rendimiento"]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'indicator_name' => 'required|string|max:255',
            'indicator_code' => 'required|string|max:255|unique:performance_indicators,indicator_code',
            'indicator_type' => 'required|in:kpi,metric,target,benchmark,efficiency,utilization,quality,satisfaction',
            'category' => 'required|in:operational,financial,technical,customer,environmental,safety,quality,strategic',
            'criticality' => 'required|in:low,medium,high,critical',
            'current_value' => 'required|numeric',
            'unit' => 'nullable|string|max:50',
            'target_value' => 'nullable|numeric',
            'measurement_timestamp' => 'required|date',
            'measurement_date' => 'required|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'period_type' => 'required|in:instant,daily,weekly,monthly,quarterly,yearly',
            'alerts_enabled' => 'nullable|boolean',
            'energy_cooperative_id' => 'nullable|exists:energy_cooperatives,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['created_by_id'] = auth()->id();

        $indicator = PerformanceIndicator::create($data);
        $indicator->load(['energyCooperative', 'user']);

        return response()->json($indicator, 201);
    }

    #[OA\Get(
        path: "/api/v1/performance-indicators/{id}",
        description: "Obtener detalles de un indicador específico",
        summary: "Mostrar indicador de rendimiento",
        security: [["sanctum" => []]],
        tags: ["Indicadores de Rendimiento"]
    )]
    public function show(PerformanceIndicator $performanceIndicator): JsonResponse
    {
        $performanceIndicator->load(['energyCooperative', 'user', 'energyReport']);
        return response()->json($performanceIndicator);
    }

    #[OA\Put(
        path: "/api/v1/performance-indicators/{id}",
        description: "Actualizar un indicador existente",
        summary: "Actualizar indicador de rendimiento",
        security: [["sanctum" => []]],
        tags: ["Indicadores de Rendimiento"]
    )]
    public function update(Request $request, PerformanceIndicator $performanceIndicator): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'indicator_name' => 'string|max:255',
            'current_value' => 'numeric',
            'target_value' => 'nullable|numeric',
            'is_active' => 'boolean',
            'show_in_dashboard' => 'boolean',
            'alerts_enabled' => 'boolean',
            'criticality' => 'in:low,medium,high,critical',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $performanceIndicator->update($validator->validated());
        return response()->json($performanceIndicator);
    }

    #[OA\Delete(
        path: "/api/v1/performance-indicators/{id}",
        description: "Eliminar un indicador",
        summary: "Eliminar indicador de rendimiento",
        security: [["sanctum" => []]],
        tags: ["Indicadores de Rendimiento"]
    )]
    public function destroy(PerformanceIndicator $performanceIndicator): JsonResponse
    {
        $performanceIndicator->delete();
        return response()->json(['message' => 'Indicador eliminado exitosamente'], 204);
    }

    #[OA\Get(
        path: "/api/v1/performance-indicators/dashboard",
        description: "Obtener indicadores para el dashboard",
        summary: "Indicadores del dashboard",
        security: [["sanctum" => []]],
        tags: ["Indicadores de Rendimiento"]
    )]
    public function dashboard(): JsonResponse
    {
        $indicators = PerformanceIndicator::forDashboard()
            ->with(['energyCooperative'])
            ->orderBy('dashboard_order')
            ->get();

        return response()->json($indicators);
    }

    #[OA\Get(
        path: "/api/v1/performance-indicators/alerts",
        description: "Obtener indicadores con alertas activas",
        summary: "Indicadores con alertas",
        security: [["sanctum" => []]],
        tags: ["Indicadores de Rendimiento"]
    )]
    public function alerts(): JsonResponse
    {
        $alerts = PerformanceIndicator::withAlerts()
            ->with(['energyCooperative'])
            ->orderBy('criticality', 'desc')
            ->get();

        return response()->json($alerts);
    }
}
