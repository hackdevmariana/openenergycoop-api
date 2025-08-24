<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyConsumption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnergyConsumptionController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EnergyConsumption::with(['user', 'energyContract']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('measurement_datetime', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('measurement_datetime', '<=', $request->date_to);
        }

        $consumptions = $query->orderBy('measurement_datetime', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $consumptions,
            'message' => 'Consumos energéticos obtenidos exitosamente'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'measurement_datetime' => 'required|date',
            'period_type' => 'required|in:instant,hourly,daily,monthly,billing_period',
            'consumption_kwh' => 'required|numeric|min:0',
            'renewable_percentage' => 'nullable|numeric|min:0|max:100',
            'total_cost_eur' => 'nullable|numeric|min:0',
        ]);

        $consumption = EnergyConsumption::create($validatedData);
        $consumption->load(['user', 'energyContract']);

        return response()->json([
            'success' => true,
            'data' => $consumption,
            'message' => 'Consumo energético registrado exitosamente'
        ], 201);
    }

    public function show(EnergyConsumption $energyConsumption): JsonResponse
    {
        $energyConsumption->load(['user', 'energyContract']);

        return response()->json([
            'success' => true,
            'data' => $energyConsumption,
            'message' => 'Consumo energético obtenido exitosamente'
        ]);
    }

    public function update(Request $request, EnergyConsumption $energyConsumption): JsonResponse
    {
        $validatedData = $request->validate([
            'consumption_kwh' => 'sometimes|numeric|min:0',
            'renewable_percentage' => 'nullable|numeric|min:0|max:100',
            'total_cost_eur' => 'nullable|numeric|min:0',
            'efficiency_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $energyConsumption->update($validatedData);
        $energyConsumption->load(['user', 'energyContract']);

        return response()->json([
            'success' => true,
            'data' => $energyConsumption,
            'message' => 'Consumo energético actualizado exitosamente'
        ]);
    }

    public function destroy(EnergyConsumption $energyConsumption): JsonResponse
    {
        $energyConsumption->delete();

        return response()->json([
            'success' => true,
            'message' => 'Consumo energético eliminado exitosamente'
        ]);
    }

    public function myConsumptions(Request $request): JsonResponse
    {
        $consumptions = EnergyConsumption::where('user_id', Auth::id())
            ->orderBy('measurement_datetime', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $consumptions,
            'message' => 'Consumos del usuario obtenidos exitosamente'
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $userId = $request->user_id ?? Auth::id();
        $period = $request->get('period', 'month'); // day, week, month, year
        
        $analytics = [
            'total_consumption_kwh' => EnergyConsumption::where('user_id', $userId)->sum('consumption_kwh'),
            'avg_daily_consumption' => EnergyConsumption::where('user_id', $userId)
                ->where('period_type', 'daily')
                ->avg('consumption_kwh'),
            'renewable_percentage' => EnergyConsumption::where('user_id', $userId)
                ->avg('renewable_percentage') ?? 0,
            'total_cost' => EnergyConsumption::where('user_id', $userId)->sum('total_cost_eur'),
            'efficiency_score' => EnergyConsumption::where('user_id', $userId)
                ->whereNotNull('efficiency_score')
                ->avg('efficiency_score')
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
            'message' => 'Análisis de consumo obtenido exitosamente'
        ]);
    }
}
