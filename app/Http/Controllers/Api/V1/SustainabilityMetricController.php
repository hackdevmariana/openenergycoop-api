<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SustainabilityMetric;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Métricas de Sostenibilidad", description: "Gestión de métricas ambientales y sostenibilidad")]
class SustainabilityMetricController extends \App\Http\Controllers\Controller
{
    #[OA\Get(
        path: "/api/v1/sustainability-metrics",
        description: "Obtener lista de métricas de sostenibilidad",
        summary: "Listar métricas de sostenibilidad",
        security: [["sanctum" => []]],
        tags: ["Métricas de Sostenibilidad"]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = SustainabilityMetric::with(['energyCooperative', 'user', 'energyReport']);

        if ($request->filled('metric_type')) {
            $query->where('metric_type', $request->metric_type);
        }

        if ($request->filled('is_certified')) {
            $query->where('is_certified', $request->boolean('is_certified'));
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        $metrics = $query->orderBy('measurement_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $metrics->items(),
            'meta' => [
                'current_page' => $metrics->currentPage(),
                'last_page' => $metrics->lastPage(),
                'per_page' => $metrics->perPage(),
                'total' => $metrics->total(),
                'from' => $metrics->firstItem(),
                'to' => $metrics->lastItem(),
            ]
        ]);
    }

    #[OA\Post(
        path: "/api/v1/sustainability-metrics",
        description: "Crear una nueva métrica de sostenibilidad",
        summary: "Crear métrica de sostenibilidad",
        security: [["sanctum" => []]],
        tags: ["Métricas de Sostenibilidad"]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'metric_name' => 'required|string|max:255',
            'metric_code' => 'required|string|max:255|unique:sustainability_metrics,metric_code',
            'metric_type' => 'required|in:carbon_footprint,renewable_percentage,energy_efficiency,waste_reduction,water_usage',
            'value' => 'required|numeric',
            'unit' => 'required|string|max:50',
            'measurement_date' => 'required|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'period_type' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'is_certified' => 'nullable|boolean',
            'entity_type' => 'nullable|string',
            'entity_id' => 'nullable|integer',
            'energy_cooperative_id' => 'nullable|exists:energy_cooperatives,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $metric = SustainabilityMetric::create($validator->validated());
        $metric->load(['energyCooperative', 'user']);

        return response()->json($metric, 201);
    }

    #[OA\Get(
        path: "/api/v1/sustainability-metrics/{id}",
        description: "Obtener detalles de una métrica específica",
        summary: "Mostrar métrica de sostenibilidad",
        security: [["sanctum" => []]],
        tags: ["Métricas de Sostenibilidad"]
    )]
    public function show(SustainabilityMetric $sustainabilityMetric): JsonResponse
    {
        $sustainabilityMetric->load(['energyCooperative', 'user', 'energyReport']);
        return response()->json($sustainabilityMetric);
    }

    #[OA\Put(
        path: "/api/v1/sustainability-metrics/{id}",
        description: "Actualizar una métrica existente",
        summary: "Actualizar métrica de sostenibilidad",
        security: [["sanctum" => []]],
        tags: ["Métricas de Sostenibilidad"]
    )]
    public function update(Request $request, SustainabilityMetric $sustainabilityMetric): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'metric_name' => 'string|max:255',
            'value' => 'numeric',
            'unit' => 'string|max:50',
            'target_value' => 'nullable|numeric',
            'is_certified' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $sustainabilityMetric->update($validator->validated());
        return response()->json($sustainabilityMetric);
    }

    #[OA\Delete(
        path: "/api/v1/sustainability-metrics/{id}",
        description: "Eliminar una métrica",
        summary: "Eliminar métrica de sostenibilidad",
        security: [["sanctum" => []]],
        tags: ["Métricas de Sostenibilidad"]
    )]
    public function destroy(SustainabilityMetric $sustainabilityMetric): JsonResponse
    {
        $sustainabilityMetric->delete();
        return response()->json(['message' => 'Métrica eliminada exitosamente'], 204);
    }

    #[OA\Get(
        path: "/api/v1/sustainability-metrics/summary",
        description: "Obtener resumen de métricas por tipo",
        summary: "Resumen de métricas",
        security: [["sanctum" => []]],
        tags: ["Métricas de Sostenibilidad"]
    )]
    public function summary(): JsonResponse
    {
        $summary = SustainabilityMetric::selectRaw('
            metric_type,
            COUNT(*) as total_metrics,
            AVG(value) as average_value,
            SUM(CASE WHEN is_certified = 1 THEN 1 ELSE 0 END) as certified_count
        ')
        ->groupBy('metric_type')
        ->get();

        return response()->json($summary);
    }
}
