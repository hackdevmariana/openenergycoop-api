<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyProduction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnergyProductionController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EnergyProduction::with(['user', 'userAsset']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('energy_source')) {
            $query->where('energy_source', $request->energy_source);
        }

        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('production_datetime', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('production_datetime', '<=', $request->date_to);
        }

        $productions = $query->orderBy('production_datetime', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $productions,
            'message' => 'Producciones energéticas obtenidas exitosamente'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'production_datetime' => 'required|date',
            'period_type' => 'required|in:instant,hourly,daily,monthly,annual',
            'energy_source' => 'required|in:solar_pv,solar_thermal,wind,hydro,biomass,geothermal,biogas,combined',
            'production_kwh' => 'required|numeric|min:0',
            'system_efficiency' => 'nullable|numeric|min:0|max:100',
            'renewable_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $production = EnergyProduction::create($validatedData);
        $production->load(['user', 'userAsset']);

        return response()->json([
            'success' => true,
            'data' => $production,
            'message' => 'Producción energética registrada exitosamente'
        ], 201);
    }

    public function show(EnergyProduction $energyProduction): JsonResponse
    {
        $energyProduction->load(['user', 'userAsset']);

        return response()->json([
            'success' => true,
            'data' => $energyProduction,
            'message' => 'Producción energética obtenida exitosamente'
        ]);
    }

    public function update(Request $request, EnergyProduction $energyProduction): JsonResponse
    {
        $validatedData = $request->validate([
            'production_kwh' => 'sometimes|numeric|min:0',
            'system_efficiency' => 'nullable|numeric|min:0|max:100',
            'renewable_percentage' => 'nullable|numeric|min:0|max:100',
            'operational_status' => 'sometimes|in:online,offline,maintenance,error,curtailed,standby',
        ]);

        $energyProduction->update($validatedData);
        $energyProduction->load(['user', 'userAsset']);

        return response()->json([
            'success' => true,
            'data' => $energyProduction,
            'message' => 'Producción energética actualizada exitosamente'
        ]);
    }

    public function destroy(EnergyProduction $energyProduction): JsonResponse
    {
        $energyProduction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producción energética eliminada exitosamente'
        ]);
    }

    public function myProductions(Request $request): JsonResponse
    {
        $productions = EnergyProduction::where('user_id', Auth::id())
            ->orderBy('production_datetime', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $productions,
            'message' => 'Producciones del usuario obtenidas exitosamente'
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $userId = $request->user_id ?? Auth::id();
        
        $analytics = [
            'total_production_kwh' => EnergyProduction::where('user_id', $userId)->sum('production_kwh'),
            'avg_daily_production' => EnergyProduction::where('user_id', $userId)
                ->where('period_type', 'daily')
                ->avg('production_kwh'),
            'avg_system_efficiency' => EnergyProduction::where('user_id', $userId)
                ->whereNotNull('system_efficiency')
                ->avg('system_efficiency'),
            'renewable_percentage' => EnergyProduction::where('user_id', $userId)
                ->avg('renewable_percentage') ?? 100,
            'total_revenue' => EnergyProduction::where('user_id', $userId)->sum('revenue_eur'),
            'co2_avoided_kg' => EnergyProduction::where('user_id', $userId)->sum('co2_avoided_kg'),
            'production_by_source' => EnergyProduction::where('user_id', $userId)
                ->select('energy_source', DB::raw('SUM(production_kwh) as total'))
                ->groupBy('energy_source')
                ->pluck('total', 'energy_source')
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
            'message' => 'Análisis de producción obtenido exitosamente'
        ]);
    }
}
