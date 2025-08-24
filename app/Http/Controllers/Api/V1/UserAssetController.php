<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserAsset\StoreUserAssetRequest;
use App\Http\Requests\Api\V1\UserAsset\UpdateUserAssetRequest;
use App\Http\Resources\Api\V1\UserAssetResource;
use App\Models\UserAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="User Assets",
 *     description="API Endpoints para la gestión de activos energéticos de usuarios"
 * )
 */
class UserAssetController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user-assets",
     *     summary="Listar activos de usuarios",
     *     description="Retorna una lista paginada de activos energéticos de usuarios",
     *     operationId="getUserAssets",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filtrar por usuario",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Filtrar por producto",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive", "pending", "completed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de activos",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserAssetResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = UserAsset::query()->with(['user', 'product', 'product.provider']);

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Ordenamiento por defecto: más recientes primero
        $query->orderBy('created_at', 'desc');

        $perPage = min($request->input('per_page', 15), 100);
        $assets = $query->paginate($perPage);

        return UserAssetResource::collection($assets);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-assets/my-assets",
     *     summary="Obtener mis activos",
     *     description="Retorna los activos del usuario autenticado",
     *     operationId="getMyUserAssets",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Mis activos",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserAssetResource")),
     *             @OA\Property(property="summary", type="object")
     *         )
     *     )
     * )
     */
    public function myAssets(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $assets = UserAsset::with(['product', 'product.provider'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_assets' => $assets->count(),
            'total_investment' => $assets->sum('total_investment'),
            'total_current_value' => $assets->sum('current_value'),
            'total_daily_yield' => $assets->sum('daily_yield'),
            'average_roi' => $assets->avg('roi_percentage'),
            'active_assets' => $assets->where('status', 'active')->count(),
        ];

        return response()->json([
            'data' => UserAssetResource::collection($assets),
            'summary' => $summary
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-assets/{userAsset}/performance",
     *     summary="Obtener rendimiento de un activo",
     *     description="Retorna métricas detalladas de rendimiento del activo",
     *     operationId="getUserAssetPerformance",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="userAsset",
     *         in="path",
     *         description="ID del activo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período de análisis",
     *         required=false,
     *         @OA\Schema(type="string", enum={"7d", "30d", "90d", "1y"}, default="30d")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Métricas de rendimiento",
     *         @OA\JsonContent(
     *             @OA\Property(property="roi_percentage", type="number"),
     *             @OA\Property(property="daily_yield", type="number"),
     *             @OA\Property(property="projected_annual_return", type="number"),
     *             @OA\Property(property="performance_chart", type="array")
     *         )
     *     )
     * )
     */
    public function performance(Request $request, UserAsset $userAsset): JsonResponse
    {
        $period = $request->input('period', '30d');
        
        // Calcular métricas básicas
        $roi = $this->calculateROI($userAsset);
        $dailyYield = $userAsset->daily_yield;
        $projectedAnnualReturn = $dailyYield * 365;
        
        // Generar datos del gráfico de rendimiento (simulado)
        $performanceChart = $this->generatePerformanceChart($userAsset, $period);
        
        return response()->json([
            'asset_id' => $userAsset->id,
            'roi_percentage' => round($roi, 2),
            'daily_yield' => $dailyYield,
            'projected_annual_return' => round($projectedAnnualReturn, 2),
            'total_return_to_date' => round($userAsset->current_value - $userAsset->total_investment, 2),
            'performance_chart' => $performanceChart,
            'next_reinvestment_date' => $userAsset->next_reinvestment_date?->toDateString(),
            'auto_reinvest_enabled' => $userAsset->auto_reinvest,
            'sustainability_impact' => [
                'co2_saved_kg' => $userAsset->quantity * ($userAsset->product->co2_reduction ?? 100),
                'renewable_energy_kwh' => $userAsset->quantity * 1000, // Estimación
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user-assets/{userAsset}/toggle-auto-reinvest",
     *     summary="Activar/desactivar auto-reinversión",
     *     description="Cambia el estado de auto-reinversión del activo",
     *     operationId="toggleAutoReinvest",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="userAsset",
     *         in="path",
     *         description="ID del activo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado de auto-reinversión actualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="auto_reinvest", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function toggleAutoReinvest(UserAsset $userAsset): JsonResponse
    {
        $userAsset->auto_reinvest = !$userAsset->auto_reinvest;
        $userAsset->save();

        $message = $userAsset->auto_reinvest 
            ? 'Auto-reinversión activada' 
            : 'Auto-reinversión desactivada';

        return response()->json([
            'auto_reinvest' => $userAsset->auto_reinvest,
            'message' => $message
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user-assets/{userAsset}/process-yield",
     *     summary="Procesar rendimiento del activo",
     *     description="Calcula y aplica el rendimiento diario del activo",
     *     operationId="processAssetYield",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="userAsset",
     *         in="path",
     *         description="ID del activo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rendimiento procesado",
     *         @OA\JsonContent(
     *             @OA\Property(property="yield_amount", type="number"),
     *             @OA\Property(property="new_current_value", type="number"),
     *             @OA\Property(property="reinvested", type="boolean")
     *         )
     *     )
     * )
     */
    public function processYield(UserAsset $userAsset): JsonResponse
    {
        $yieldAmount = $userAsset->daily_yield;
        $oldValue = $userAsset->current_value;
        
        // Aplicar rendimiento
        $userAsset->current_value += $yieldAmount;
        
        // Auto-reinversión si está habilitada y se alcanza el umbral
        $reinvested = false;
        if ($userAsset->auto_reinvest && $this->shouldReinvest($userAsset)) {
            $this->processReinvestment($userAsset);
            $reinvested = true;
        }
        
        $userAsset->last_yield_date = now();
        $userAsset->save();

        return response()->json([
            'yield_amount' => $yieldAmount,
            'old_value' => $oldValue,
            'new_current_value' => $userAsset->current_value,
            'reinvested' => $reinvested,
            'processed_at' => now()->toISOString()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-assets/portfolio-summary",
     *     summary="Resumen del portafolio",
     *     description="Retorna un resumen consolidado del portafolio del usuario",
     *     operationId="getPortfolioSummary",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Resumen del portafolio",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_investment", type="number"),
     *             @OA\Property(property="current_value", type="number"),
     *             @OA\Property(property="total_yield", type="number"),
     *             @OA\Property(property="asset_distribution", type="array")
     *         )
     *     )
     * )
     */
    public function portfolioSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $assets = UserAsset::with(['product', 'product.provider'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $totalInvestment = $assets->sum('total_investment');
        $currentValue = $assets->sum('current_value');
        $totalYield = $currentValue - $totalInvestment;
        $overallROI = $totalInvestment > 0 ? ($totalYield / $totalInvestment) * 100 : 0;

        // Distribución por tipo de producto
        $assetDistribution = $assets->groupBy('product.type')->map(function ($group, $type) {
            return [
                'type' => $type,
                'count' => $group->count(),
                'investment' => $group->sum('total_investment'),
                'current_value' => $group->sum('current_value'),
                'percentage' => 0 // Se calculará después
            ];
        })->values();

        // Calcular porcentajes
        foreach ($assetDistribution as &$distribution) {
            $distribution['percentage'] = $totalInvestment > 0 
                ? round(($distribution['investment'] / $totalInvestment) * 100, 1) 
                : 0;
        }

        return response()->json([
            'total_assets' => $assets->count(),
            'total_investment' => $totalInvestment,
            'current_value' => $currentValue,
            'total_yield' => $totalYield,
            'overall_roi_percentage' => round($overallROI, 2),
            'daily_yield_total' => $assets->sum('daily_yield'),
            'asset_distribution' => $assetDistribution,
            'top_performers' => $this->getTopPerformers($assets),
            'sustainability_impact' => [
                'total_co2_saved' => $assets->sum(function ($asset) {
                    return $asset->quantity * ($asset->product->co2_reduction ?? 100);
                }),
                'renewable_energy_total' => $assets->sum('quantity') * 1000
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user-assets",
     *     summary="Crear un nuevo activo",
     *     description="Crea un nuevo activo energético para el usuario",
     *     operationId="storeUserAsset",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreUserAssetRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Activo creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/UserAssetResource")
     *     ),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(StoreUserAssetRequest $request): UserAssetResource
    {
        $validated = $request->validated();
        
        // Calcular valores iniciales
        $validated['current_value'] = $validated['total_investment'];
        $validated['status'] = 'active';
        
        $asset = UserAsset::create($validated);

        return new UserAssetResource($asset->load(['user', 'product', 'product.provider']));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-assets/{userAsset}",
     *     summary="Obtener un activo específico",
     *     description="Retorna los detalles de un activo específico",
     *     operationId="showUserAsset",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="userAsset",
     *         in="path",
     *         description="ID del activo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del activo",
     *         @OA\JsonContent(ref="#/components/schemas/UserAssetResource")
     *     ),
     *     @OA\Response(response=404, description="Activo no encontrado")
     * )
     */
    public function show(UserAsset $userAsset): UserAssetResource
    {
        return new UserAssetResource($userAsset->load(['user', 'product', 'product.provider']));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user-assets/{userAsset}",
     *     summary="Actualizar un activo",
     *     description="Actualiza los datos de un activo existente",
     *     operationId="updateUserAsset",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="userAsset",
     *         in="path",
     *         description="ID del activo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserAssetRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activo actualizado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/UserAssetResource")
     *     ),
     *     @OA\Response(response=404, description="Activo no encontrado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(UpdateUserAssetRequest $request, UserAsset $userAsset): UserAssetResource
    {
        $userAsset->update($request->validated());

        return new UserAssetResource($userAsset->load(['user', 'product', 'product.provider']));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/user-assets/{userAsset}",
     *     summary="Eliminar un activo",
     *     description="Elimina un activo específico",
     *     operationId="destroyUserAsset",
     *     tags={"User Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="userAsset",
     *         in="path",
     *         description="ID del activo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Activo eliminado exitosamente"
     *     ),
     *     @OA\Response(response=404, description="Activo no encontrado")
     * )
     */
    public function destroy(UserAsset $userAsset): JsonResponse
    {
        $userAsset->delete();

        return response()->json(null, 204);
    }

    /**
     * Calcula el ROI de un activo
     */
    private function calculateROI(UserAsset $userAsset): float
    {
        if ($userAsset->total_investment <= 0) {
            return 0;
        }

        $gain = $userAsset->current_value - $userAsset->total_investment;
        return ($gain / $userAsset->total_investment) * 100;
    }

    /**
     * Genera datos del gráfico de rendimiento
     */
    private function generatePerformanceChart(UserAsset $userAsset, string $period): array
    {
        $days = match($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '1y' => 365,
            default => 30
        };

        $chart = [];
        $baseValue = $userAsset->total_investment;
        $dailyGrowth = $userAsset->daily_yield;

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($days - $i - 1);
            $value = $baseValue + ($dailyGrowth * $i);
            
            $chart[] = [
                'date' => $date->toDateString(),
                'value' => round($value, 2),
                'yield' => round($dailyGrowth, 2)
            ];
        }

        return $chart;
    }

    /**
     * Determina si se debe hacer reinversión automática
     */
    private function shouldReinvest(UserAsset $userAsset): bool
    {
        // Lógica para determinar si reinvertir (ejemplo: cada 30 días)
        return $userAsset->last_yield_date === null || 
               $userAsset->last_yield_date->diffInDays(now()) >= 30;
    }

    /**
     * Procesa la reinversión automática
     */
    private function processReinvestment(UserAsset $userAsset): void
    {
        $yieldAccumulated = $userAsset->current_value - $userAsset->total_investment;
        
        if ($yieldAccumulated > 0) {
            $userAsset->total_investment = $userAsset->current_value;
            $userAsset->next_reinvestment_date = now()->addDays(30);
        }
    }

    /**
     * Obtiene los activos con mejor rendimiento
     */
    private function getTopPerformers($assets): array
    {
        return $assets->sortByDesc(function ($asset) {
            return $this->calculateROI($asset);
        })->take(3)->map(function ($asset) {
            return [
                'id' => $asset->id,
                'product_name' => $asset->product->name,
                'roi_percentage' => round($this->calculateROI($asset), 2),
                'current_value' => $asset->current_value
            ];
        })->values()->toArray();
    }
}
