<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MarketPrice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MarketPriceController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MarketPrice::query();

        if ($request->filled('market_name')) {
            $query->where('market_name', $request->market_name);
        }

        if ($request->filled('commodity_type')) {
            $query->where('commodity_type', $request->commodity_type);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('price_datetime', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('price_datetime', '<=', $request->date_to);
        }

        $prices = $query->orderBy('price_datetime', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $prices,
            'message' => 'Precios de mercado obtenidos exitosamente'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'market_name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'commodity_type' => 'required|in:electricity,natural_gas,carbon_credits,renewable_certificates,capacity,balancing',
            'product_name' => 'required|string|max:255',
            'price_datetime' => 'required|date',
            'period_type' => 'required|in:real_time,hourly,daily,weekly,monthly,quarterly,annual',
            'delivery_start_date' => 'required|date',
            'delivery_end_date' => 'required|date|after:delivery_start_date',
            'delivery_period' => 'required|in:spot,next_day,current_week,next_week,current_month,next_month,current_quarter,next_quarter,current_year,next_year',
            'price' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'unit' => 'required|string|max:255',
            'data_source' => 'required|string|max:255',
        ]);

        $price = MarketPrice::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => $price,
            'message' => 'Precio de mercado registrado exitosamente'
        ], 201);
    }

    public function show(MarketPrice $marketPrice): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $marketPrice,
            'message' => 'Precio de mercado obtenido exitosamente'
        ]);
    }

    public function update(Request $request, MarketPrice $marketPrice): JsonResponse
    {
        $validatedData = $request->validate([
            'price' => 'sometimes|numeric',
            'volume' => 'nullable|numeric|min:0',
            'price_change_percentage' => 'nullable|numeric',
            'market_status' => 'sometimes|in:open,closed,pre_opening,auction,suspended,maintenance',
        ]);

        $marketPrice->update($validatedData);

        return response()->json([
            'success' => true,
            'data' => $marketPrice,
            'message' => 'Precio de mercado actualizado exitosamente'
        ]);
    }

    public function destroy(MarketPrice $marketPrice): JsonResponse
    {
        $marketPrice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Precio de mercado eliminado exitosamente'
        ]);
    }

    public function latest(Request $request): JsonResponse
    {
        $query = MarketPrice::query();

        if ($request->filled('commodity_type')) {
            $query->where('commodity_type', $request->commodity_type);
        }

        if ($request->filled('market_name')) {
            $query->where('market_name', $request->market_name);
        }

        $latestPrices = $query->orderBy('price_datetime', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $latestPrices,
            'message' => 'Últimos precios obtenidos exitosamente'
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $commodityType = $request->get('commodity_type', 'electricity');
        $period = $request->get('period', 'day'); // day, week, month
        
        $baseQuery = MarketPrice::where('commodity_type', $commodityType);
        
        if ($period === 'day') {
            $baseQuery->whereDate('price_datetime', today());
        } elseif ($period === 'week') {
            $baseQuery->whereBetween('price_datetime', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $baseQuery->whereMonth('price_datetime', now()->month)
                     ->whereYear('price_datetime', now()->year);
        }
        
        $analytics = [
            'avg_price' => $baseQuery->avg('price'),
            'min_price' => $baseQuery->min('price'),
            'max_price' => $baseQuery->max('price'),
            'total_volume' => $baseQuery->sum('volume'),
            'price_volatility' => $baseQuery->avg('volatility'),
            'markets_count' => $baseQuery->distinct('market_name')->count(),
            'latest_update' => $baseQuery->max('price_datetime'),
            'price_trends' => $baseQuery->select(
                DB::raw('DATE(price_datetime) as date'),
                DB::raw('AVG(price) as avg_price'),
                DB::raw('MAX(price) as max_price'),
                DB::raw('MIN(price) as min_price')
            )->groupBy('date')
            ->orderBy('date')
            ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
            'message' => 'Análisis de precios obtenido exitosamente'
        ]);
    }

    public function markets(): JsonResponse
    {
        $markets = MarketPrice::select('market_name', 'country', 'commodity_type')
            ->distinct()
            ->orderBy('market_name')
            ->get()
            ->groupBy('country');

        return response()->json([
            'success' => true,
            'data' => $markets,
            'message' => 'Mercados disponibles obtenidos exitosamente'
        ]);
    }
}
