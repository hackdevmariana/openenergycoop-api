<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Geography - Municipalities",
 *     description="API Endpoints for Spanish municipalities"
 * )
 */
class MunicipalityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Municipality::with(['province.region']);

        if ($request->filled('province_id')) {
            $query->inProvince($request->province_id);
        }

        if ($request->filled('region_id')) {
            $query->inRegion($request->region_id);
        }

        if ($request->boolean('operating_only')) {
            $query->operating();
        }

        if ($request->boolean('with_weather')) {
            $query->withWeatherData();
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $limit = min($request->get('limit', 50), 100);
        $municipalities = $query->orderBy('name')->limit($limit)->get();

        $municipalities->each(function ($municipality) {
            $municipality->is_operating = $municipality->isOperating();
            $municipality->full_name = $municipality->full_name;
            
            if ($municipality->hasRecentWeatherData()) {
                $municipality->solar_potential = $municipality->getSolarEnergyPotential();
            }
        });

        return response()->json($municipalities);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $municipality = Municipality::with(['province.region'])->findOrFail($id);

        $response = $municipality->toArray();
        $response['is_operating'] = $municipality->isOperating();
        $response['full_name'] = $municipality->full_name;
        $response['solar_potential'] = $municipality->getSolarEnergyPotential();
        $response['peak_solar_hours'] = $municipality->getPeakSolarHours();

        if ($request->boolean('include_weather')) {
            $response['weather_summary'] = $municipality->getWeatherSummary();
        }

        return response()->json($response);
    }

    public function weather(Request $request, int $id): JsonResponse
    {
        $municipality = Municipality::findOrFail($id);
        
        $days = min($request->get('days', 30), 365);
        $from = now()->subDays($days);
        
        $conditions = $municipality->getAverageWeatherConditions($from);
        $latest = $municipality->getLatestWeather();
        
        return response()->json([
            'municipality' => [
                'id' => $municipality->id,
                'name' => $municipality->name,
                'full_name' => $municipality->full_name,
            ],
            'current_weather' => $latest,
            'average_conditions' => $conditions,
            'solar_potential' => $municipality->getSolarEnergyPotential(),
        ]);
    }
}