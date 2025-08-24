<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Balance\StoreBalanceRequest;
use App\Http\Requests\Api\V1\Balance\UpdateBalanceRequest;
use App\Http\Resources\Api\V1\BalanceResource;
use App\Models\Balance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Balances",
 *     description="API Endpoints para la gestión de balances y transacciones económicas"
 * )
 */
class BalanceController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/balances",
     *     summary="Listar balances",
     *     description="Retorna una lista paginada de balances y transacciones",
     *     operationId="getBalances",
     *     tags={"Balances"},
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
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo de transacción",
     *         required=false,
     *         @OA\Schema(type="string", enum={"deposit", "withdrawal", "yield", "investment", "fee", "refund"})
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Fecha inicio",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Fecha fin",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de balances",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BalanceResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Balance::query()->with(['user']);

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Ordenamiento por defecto: más recientes primero
        $query->orderBy('created_at', 'desc');

        $perPage = min($request->input('per_page', 15), 100);
        $balances = $query->paginate($perPage);

        return BalanceResource::collection($balances);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/balances/my-balance",
     *     summary="Obtener mi balance actual",
     *     description="Retorna el balance actual del usuario autenticado",
     *     operationId="getMyBalance",
     *     tags={"Balances"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Balance actual del usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_balance", type="number"),
     *             @OA\Property(property="pending_balance", type="number"),
     *             @OA\Property(property="available_balance", type="number"),
     *             @OA\Property(property="recent_transactions", type="array")
     *         )
     *     )
     * )
     */
    public function myBalance(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Calcular balance actual
        $currentBalance = Balance::where('user_id', $user->id)->sum('amount');
        
        // Balance pendiente (transacciones en proceso)
        $pendingBalance = Balance::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');
            
        $availableBalance = $currentBalance - $pendingBalance;

        // Transacciones recientes
        $recentTransactions = Balance::with(['user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'current_balance' => round($currentBalance, 2),
            'pending_balance' => round($pendingBalance, 2),
            'available_balance' => round($availableBalance, 2),
            'currency' => 'EUR',
            'recent_transactions' => BalanceResource::collection($recentTransactions),
            'updated_at' => now()->toISOString()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/balances/transaction-history",
     *     summary="Obtener historial de transacciones",
     *     description="Retorna el historial completo de transacciones del usuario",
     *     operationId="getTransactionHistory",
     *     tags={"Balances"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="months",
     *         in="query",
     *         description="Últimos N meses",
     *         required=false,
     *         @OA\Schema(type="integer", default=12)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historial de transacciones",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BalanceResource")),
     *             @OA\Property(property="summary", type="object")
     *         )
     *     )
     * )
     */
    public function transactionHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $months = $request->input('months', 12);
        
        $query = Balance::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMonths($months));

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->input('type'));
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Generar resumen
        $summary = [
            'total_transactions' => $transactions->count(),
            'total_deposits' => $transactions->where('transaction_type', 'deposit')->sum('amount'),
            'total_withdrawals' => abs($transactions->where('transaction_type', 'withdrawal')->sum('amount')),
            'total_yields' => $transactions->where('transaction_type', 'yield')->sum('amount'),
            'total_investments' => abs($transactions->where('transaction_type', 'investment')->sum('amount')),
            'net_flow' => $transactions->sum('amount'),
            'by_month' => $this->generateMonthlyBreakdown($transactions)
        ];

        return response()->json([
            'data' => BalanceResource::collection($transactions),
            'summary' => $summary,
            'period' => [
                'from' => now()->subMonths($months)->toDateString(),
                'to' => now()->toDateString(),
                'months' => $months
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/balances/deposit",
     *     summary="Realizar depósito",
     *     description="Procesa un depósito en la cuenta del usuario",
     *     operationId="deposit",
     *     tags={"Balances"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", minimum=1),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="payment_method", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Depósito procesado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/BalanceResource")
     *     ),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function deposit(Request $request): BalanceResource
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:50'
        ]);

        $balance = Balance::create([
            'user_id' => $request->user()->id,
            'amount' => $request->input('amount'),
            'transaction_type' => 'deposit',
            'description' => $request->input('description', 'Depósito'),
            'status' => 'completed',
            'reference_id' => 'DEP_' . now()->format('YmdHis') . '_' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'metadata' => [
                'payment_method' => $request->input('payment_method'),
                'processed_at' => now()->toISOString()
            ]
        ]);

        return new BalanceResource($balance->load(['user']));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/balances/withdraw",
     *     summary="Realizar retiro",
     *     description="Procesa un retiro de la cuenta del usuario",
     *     operationId="withdraw",
     *     tags={"Balances"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", minimum=1),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="withdrawal_method", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Retiro procesado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/BalanceResource")
     *     ),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=400, description="Fondos insuficientes")
     * )
     */
    public function withdraw(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'withdrawal_method' => 'nullable|string|max:50'
        ]);

        $user = $request->user();
        $amount = $request->input('amount');

        // Verificar fondos disponibles
        $currentBalance = Balance::where('user_id', $user->id)->sum('amount');
        
        if ($currentBalance < $amount) {
            return response()->json([
                'message' => 'Fondos insuficientes',
                'current_balance' => $currentBalance,
                'requested_amount' => $amount
            ], 400);
        }

        $balance = Balance::create([
            'user_id' => $user->id,
            'amount' => -$amount, // Negativo para retiro
            'transaction_type' => 'withdrawal',
            'description' => $request->input('description', 'Retiro'),
            'status' => 'pending', // Los retiros requieren procesamiento
            'reference_id' => 'WIT_' . now()->format('YmdHis') . '_' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'metadata' => [
                'withdrawal_method' => $request->input('withdrawal_method'),
                'requested_at' => now()->toISOString()
            ]
        ]);

        return response()->json(new BalanceResource($balance->load(['user'])), 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/balances/investment",
     *     summary="Registrar inversión",
     *     description="Registra una transacción de inversión en productos energéticos",
     *     operationId="investment",
     *     tags={"Balances"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", minimum=1),
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="user_asset_id", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inversión registrada exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/BalanceResource")
     *     )
     * )
     */
    public function investment(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'product_id' => 'nullable|exists:products,id',
            'user_asset_id' => 'nullable|exists:user_assets,id',
            'description' => 'nullable|string|max:255'
        ]);

        $user = $request->user();
        $amount = $request->input('amount');

        // Verificar fondos disponibles
        $currentBalance = Balance::where('user_id', $user->id)->sum('amount');
        
        if ($currentBalance < $amount) {
            return response()->json([
                'message' => 'Fondos insuficientes para la inversión',
                'current_balance' => $currentBalance,
                'investment_amount' => $amount
            ], 400);
        }

        $balance = Balance::create([
            'user_id' => $user->id,
            'amount' => -$amount, // Negativo para inversión
            'transaction_type' => 'investment',
            'description' => $request->input('description', 'Inversión en producto energético'),
            'status' => 'completed',
            'reference_id' => 'INV_' . now()->format('YmdHis') . '_' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'metadata' => [
                'product_id' => $request->input('product_id'),
                'user_asset_id' => $request->input('user_asset_id'),
                'invested_at' => now()->toISOString()
            ]
        ]);

        return response()->json(new BalanceResource($balance->load(['user'])), 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/balances/yield",
     *     summary="Registrar rendimiento",
     *     description="Registra el rendimiento generado por un activo",
     *     operationId="yield",
     *     tags={"Balances"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", minimum=0),
     *             @OA\Property(property="user_asset_id", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rendimiento registrado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/BalanceResource")
     *     )
     * )
     */
    public function yield(Request $request): BalanceResource
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'user_asset_id' => 'required|exists:user_assets,id',
            'description' => 'nullable|string|max:255'
        ]);

        $balance = Balance::create([
            'user_id' => $request->user()->id,
            'amount' => $request->input('amount'), // Positivo para rendimiento
            'transaction_type' => 'yield',
            'description' => $request->input('description', 'Rendimiento de activo energético'),
            'status' => 'completed',
            'reference_id' => 'YLD_' . now()->format('YmdHis') . '_' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'metadata' => [
                'user_asset_id' => $request->input('user_asset_id'),
                'generated_at' => now()->toISOString()
            ]
        ]);

        return new BalanceResource($balance->load(['user']));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/balances/analytics",
     *     summary="Obtener analytics financieros",
     *     description="Retorna métricas y análisis del comportamiento financiero",
     *     operationId="getBalanceAnalytics",
     *     tags={"Balances"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período de análisis",
     *         required=false,
     *         @OA\Schema(type="string", enum={"1m", "3m", "6m", "1y"}, default="3m")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Analytics financieros",
     *         @OA\JsonContent(
     *             @OA\Property(property="income_vs_expenses", type="object"),
     *             @OA\Property(property="yield_performance", type="object"),
     *             @OA\Property(property="monthly_trends", type="array")
     *         )
     *     )
     * )
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->input('period', '3m');
        
        $months = match($period) {
            '1m' => 1,
            '3m' => 3,
            '6m' => 6,
            '1y' => 12,
            default => 3
        };

        $transactions = Balance::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMonths($months))
            ->get();

        // Análisis de ingresos vs gastos
        $incomeVsExpenses = [
            'total_income' => $transactions->where('amount', '>', 0)->sum('amount'),
            'total_expenses' => abs($transactions->where('amount', '<', 0)->sum('amount')),
            'net_flow' => $transactions->sum('amount'),
            'income_sources' => $this->getIncomeBreakdown($transactions),
            'expense_categories' => $this->getExpenseBreakdown($transactions)
        ];

        // Rendimiento de yields
        $yieldPerformance = [
            'total_yield' => $transactions->where('transaction_type', 'yield')->sum('amount'),
            'average_monthly_yield' => $transactions->where('transaction_type', 'yield')->sum('amount') / max($months, 1),
            'yield_growth_rate' => $this->calculateYieldGrowthRate($transactions),
            'yield_consistency' => $this->calculateYieldConsistency($transactions)
        ];

        // Tendencias mensuales
        $monthlyTrends = $this->generateMonthlyTrends($transactions, $months);

        return response()->json([
            'period' => $period,
            'income_vs_expenses' => $incomeVsExpenses,
            'yield_performance' => $yieldPerformance,
            'monthly_trends' => $monthlyTrends,
            'performance_score' => $this->calculatePerformanceScore($incomeVsExpenses, $yieldPerformance),
            'recommendations' => $this->generateRecommendations($incomeVsExpenses, $yieldPerformance)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/balances/{balance}",
     *     summary="Obtener una transacción específica",
     *     description="Retorna los detalles de una transacción específica",
     *     operationId="showBalance",
     *     tags={"Balances"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="balance",
     *         in="path",
     *         description="ID de la transacción",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la transacción",
     *         @OA\JsonContent(ref="#/components/schemas/BalanceResource")
     *     ),
     *     @OA\Response(response=404, description="Transacción no encontrada")
     * )
     */
    public function show(Balance $balance): BalanceResource
    {
        return new BalanceResource($balance->load(['user']));
    }

    /**
     * Genera desglose mensual de transacciones
     */
    private function generateMonthlyBreakdown($transactions): array
    {
        return $transactions->groupBy(function ($transaction) {
            return $transaction->created_at->format('Y-m');
        })->map(function ($monthTransactions, $month) {
            return [
                'month' => $month,
                'total_amount' => $monthTransactions->sum('amount'),
                'transaction_count' => $monthTransactions->count(),
                'deposits' => $monthTransactions->where('transaction_type', 'deposit')->sum('amount'),
                'withdrawals' => abs($monthTransactions->where('transaction_type', 'withdrawal')->sum('amount')),
                'yields' => $monthTransactions->where('transaction_type', 'yield')->sum('amount'),
                'investments' => abs($monthTransactions->where('transaction_type', 'investment')->sum('amount'))
            ];
        })->values()->toArray();
    }

    /**
     * Obtiene desglose de fuentes de ingresos
     */
    private function getIncomeBreakdown($transactions): array
    {
        $income = $transactions->where('amount', '>', 0);
        
        return $income->groupBy('transaction_type')->map(function ($group, $type) {
            return [
                'type' => $type,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
                'percentage' => 0 // Se calculará después
            ];
        })->values()->toArray();
    }

    /**
     * Obtiene desglose de categorías de gastos
     */
    private function getExpenseBreakdown($transactions): array
    {
        $expenses = $transactions->where('amount', '<', 0);
        
        return $expenses->groupBy('transaction_type')->map(function ($group, $type) {
            return [
                'type' => $type,
                'total' => abs($group->sum('amount')),
                'count' => $group->count(),
                'percentage' => 0 // Se calculará después
            ];
        })->values()->toArray();
    }

    /**
     * Calcula la tasa de crecimiento de rendimientos
     */
    private function calculateYieldGrowthRate($transactions): float
    {
        // Implementación simplificada
        $yields = $transactions->where('transaction_type', 'yield')->sortBy('created_at');
        
        if ($yields->count() < 2) {
            return 0;
        }

        $firstHalf = $yields->take($yields->count() / 2)->sum('amount');
        $secondHalf = $yields->skip($yields->count() / 2)->sum('amount');

        return $firstHalf > 0 ? (($secondHalf - $firstHalf) / $firstHalf) * 100 : 0;
    }

    /**
     * Calcula la consistencia de rendimientos
     */
    private function calculateYieldConsistency($transactions): float
    {
        $yields = $transactions->where('transaction_type', 'yield');
        
        if ($yields->count() === 0) {
            return 0;
        }

        $amounts = $yields->pluck('amount')->toArray();
        $mean = array_sum($amounts) / count($amounts);
        
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $amounts)) / count($amounts);
        
        $stdDev = sqrt($variance);
        
        return $mean > 0 ? (1 - ($stdDev / $mean)) * 100 : 0;
    }

    /**
     * Genera tendencias mensuales
     */
    private function generateMonthlyTrends($transactions, int $months): array
    {
        $trends = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthTransactions = $transactions->filter(function ($transaction) use ($month) {
                return $transaction->created_at->format('Y-m') === $month->format('Y-m');
            });

            $trends[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->format('F Y'),
                'total_amount' => $monthTransactions->sum('amount'),
                'income' => $monthTransactions->where('amount', '>', 0)->sum('amount'),
                'expenses' => abs($monthTransactions->where('amount', '<', 0)->sum('amount')),
                'net_flow' => $monthTransactions->sum('amount'),
                'transaction_count' => $monthTransactions->count()
            ];
        }

        return $trends;
    }

    /**
     * Calcula puntuación de rendimiento general
     */
    private function calculatePerformanceScore($incomeVsExpenses, $yieldPerformance): int
    {
        $score = 0;
        
        // Flujo neto positivo (hasta 40 puntos)
        if ($incomeVsExpenses['net_flow'] > 0) {
            $score += min(40, ($incomeVsExpenses['net_flow'] / 1000) * 10);
        }
        
        // Rendimientos consistentes (hasta 30 puntos)
        $score += ($yieldPerformance['yield_consistency'] / 100) * 30;
        
        // Crecimiento de rendimientos (hasta 30 puntos)
        if ($yieldPerformance['yield_growth_rate'] > 0) {
            $score += min(30, $yieldPerformance['yield_growth_rate'] * 3);
        }
        
        return min(100, round($score));
    }

    /**
     * Genera recomendaciones basadas en el análisis
     */
    private function generateRecommendations($incomeVsExpenses, $yieldPerformance): array
    {
        $recommendations = [];
        
        if ($incomeVsExpenses['net_flow'] < 0) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Flujo negativo',
                'message' => 'Considera reducir gastos o aumentar inversiones rentables'
            ];
        }
        
        if ($yieldPerformance['yield_growth_rate'] < 5) {
            $recommendations[] = [
                'type' => 'suggestion',
                'title' => 'Optimizar rendimientos',
                'message' => 'Busca productos con mejor rendimiento para maximizar ganancias'
            ];
        }
        
        if ($yieldPerformance['yield_consistency'] < 70) {
            $recommendations[] = [
                'type' => 'tip',
                'title' => 'Diversificar portafolio',
                'message' => 'Considera diversificar para obtener rendimientos más consistentes'
            ];
        }
        
        return $recommendations;
    }
}
