<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Reportes de Energía", description: "Gestión de reportes energéticos y analytics")]
class EnergyReportController extends Controller
{
    #[OA\Get(
        path: "/api/v1/energy-reports",
        description: "Obtener lista de reportes de energía con filtros y paginación",
        summary: "Listar reportes de energía",
        security: [["sanctum" => []]],
        tags: ["Reportes de Energía"]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = EnergyReport::with(['user', 'energyCooperative', 'createdBy']);

        // Aplicar filtros
        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scope')) {
            $query->where('scope', $request->scope);
        }

        // Búsqueda por texto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('report_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reports = $query->paginate($request->get('per_page', 15));

        return response()->json($reports);
    }

    #[OA\Post(
        path: "/api/v1/energy-reports",
        description: "Crear un nuevo reporte de energía",
        summary: "Crear reporte de energía",
        security: [["sanctum" => []]],
        tags: ["Reportes de Energía"]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'report_code' => 'required|string|max:255|unique:energy_reports,report_code',
            'description' => 'nullable|string',
            'report_type' => 'required|in:consumption,production,trading,savings,cooperative,user,system,custom',
            'report_category' => 'required|in:energy,financial,environmental,operational,performance',
            'scope' => 'required|in:user,cooperative,provider,system,custom',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'period_type' => 'required|in:daily,weekly,monthly,quarterly,yearly,custom',
            'auto_generate' => 'boolean',
            'is_public' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'energy_cooperative_id' => 'nullable|exists:energy_cooperatives,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['created_by_id'] = Auth::id();
        $data['status'] = 'draft';

        $report = EnergyReport::create($data);
        $report->load(['user', 'energyCooperative', 'createdBy']);

        return response()->json($report, 201);
    }

    #[OA\Get(
        path: "/api/v1/energy-reports/{id}",
        description: "Obtener detalles de un reporte específico",
        summary: "Mostrar reporte de energía",
        security: [["sanctum" => []]],
        tags: ["Reportes de Energía"]
    )]
    public function show(EnergyReport $energyReport): JsonResponse
    {
        $energyReport->load([
            'user', 
            'energyCooperative', 
            'createdBy',
            'sustainabilityMetrics',
            'performanceIndicators'
        ]);

        // Incrementar contador de visualizaciones
        $energyReport->incrementViewCount();

        return response()->json($energyReport);
    }

    #[OA\Put(
        path: "/api/v1/energy-reports/{id}",
        description: "Actualizar un reporte existente",
        summary: "Actualizar reporte de energía",
        security: [["sanctum" => []]],
        tags: ["Reportes de Energía"]
    )]
    public function update(Request $request, EnergyReport $energyReport): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:draft,generating,completed,failed,scheduled,cancelled',
            'auto_generate' => 'boolean',
            'is_public' => 'boolean',
            'priority' => 'integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $energyReport->update($validator->validated());
        $energyReport->load(['user', 'energyCooperative', 'createdBy']);

        return response()->json($energyReport);
    }

    #[OA\Delete(
        path: "/api/v1/energy-reports/{id}",
        description: "Eliminar un reporte",
        summary: "Eliminar reporte de energía",
        security: [["sanctum" => []]],
        tags: ["Reportes de Energía"]
    )]
    public function destroy(EnergyReport $energyReport): JsonResponse
    {
        $energyReport->delete();

        return response()->json(['message' => 'Reporte eliminado exitosamente'], 204);
    }

    #[OA\Post(
        path: "/api/v1/energy-reports/{id}/generate",
        description: "Generar un reporte específico",
        summary: "Generar reporte",
        security: [["sanctum" => []]],
        tags: ["Reportes de Energía"]
    )]
    public function generate(EnergyReport $energyReport): JsonResponse
    {
        if (!$energyReport->canGenerate()) {
            return response()->json([
                'message' => 'El reporte no puede ser generado en su estado actual',
                'current_status' => $energyReport->status
            ], 400);
        }

        $energyReport->update(['status' => 'generating']);

        return response()->json([
            'message' => 'Generación del reporte iniciada',
            'report' => $energyReport
        ]);
    }

    #[OA\Get(
        path: "/api/v1/energy-reports/my-reports",
        description: "Obtener reportes del usuario autenticado",
        summary: "Mis reportes",
        security: [["sanctum" => []]],
        tags: ["Reportes de Energía"]
    )]
    public function myReports(Request $request): JsonResponse
    {
        $reports = EnergyReport::where('user_id', Auth::id())
            ->orWhere('created_by_id', Auth::id())
            ->with(['energyCooperative'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($reports);
    }
}
