<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Geography - Regions",
 *     description="API Endpoints for Spanish regions (Comunidades Autónomas)"
 * )
 */
class RegionController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of regions.
     *
     * @OA\Get(
     *     path="/api/v1/regions",
     *     summary="List all Spanish regions",
     *     description="Returns a list of all Spanish autonomous communities",
     *     operationId="indexRegions",
     *     tags={"Geography - Regions"},
     *     @OA\Parameter(
     *         name="include_counts",
     *         in="query",
     *         description="Include province and municipality counts",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Parameter(
     *         name="with_weather",
     *         in="query",
     *         description="Only regions with weather data",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of regions",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Madrid"),
     *                 @OA\Property(property="slug", type="string", example="madrid"),
     *                 @OA\Property(property="provinces_count", type="integer", example=1),
     *                 @OA\Property(property="municipalities_count", type="integer", example=179),
     *                 @OA\Property(property="has_weather_data", type="boolean", example=true)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Region::query();

        if ($request->boolean('include_counts')) {
            $query->withCount(['provinces', 'municipalities']);
        }

        if ($request->boolean('with_weather')) {
            $query->whereHas('municipalities.weatherSnapshots');
        }

        $regions = $query->orderBy('name')->get();

        // Añadir información adicional si se solicita
        if ($request->boolean('include_counts')) {
            $regions->each(function ($region) {
                $region->has_weather_data = $region->hasWeatherData();
            });
        }

        return response()->json($regions);
    }

    /**
     * Display the specified region.
     *
     * @OA\Get(
     *     path="/api/v1/regions/{id}",
     *     summary="Get region by ID",
     *     description="Returns detailed information about a specific region",
     *     operationId="showRegion",
     *     tags={"Geography - Regions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Region ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include_provinces",
     *         in="query",
     *         description="Include provinces list",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Parameter(
     *         name="include_weather",
     *         in="query",
     *         description="Include weather statistics",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Region details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="provinces_count", type="integer"),
     *             @OA\Property(property="municipalities_count", type="integer"),
     *             @OA\Property(property="provinces", type="array", @OA\Items(ref="#/components/schemas/Province")),
     *             @OA\Property(property="weather_stats", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Region not found"
     *     )
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $query = Region::where('id', $id);

        if ($request->boolean('include_provinces')) {
            $query->with(['provinces' => function ($q) {
                $q->withCount('municipalities')->orderBy('name');
            }]);
        }

        $region = $query->firstOrFail();

        $response = $region->toArray();
        
        // Añadir conteos
        $response['provinces_count'] = $region->getProvincesCount();
        $response['municipalities_count'] = $region->getMunicipalitiesCount();

        // Añadir estadísticas meteorológicas si se solicita
        if ($request->boolean('include_weather')) {
            $response['weather_stats'] = $region->getAverageWeatherData();
            $response['latest_weather'] = $region->getLatestWeatherSnapshot();
        }

        return response()->json($response);
    }

    /**
     * Get region by slug.
     *
     * @OA\Get(
     *     path="/api/v1/regions/slug/{slug}",
     *     summary="Get region by slug",
     *     description="Returns region information using the slug identifier",
     *     operationId="showRegionBySlug",
     *     tags={"Geography - Regions"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Region slug",
     *         required=true,
     *         @OA\Schema(type="string", example="madrid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Region details",
     *         @OA\JsonContent(ref="#/components/schemas/Region")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Region not found"
     *     )
     * )
     */
    public function showBySlug(Request $request, string $slug): JsonResponse
    {
        $region = Region::bySlug($slug)->firstOrFail();
        
        return $this->show($request, $region->id);
    }

    /**
     * Get weather statistics for region.
     *
     * @OA\Get(
     *     path="/api/v1/regions/{id}/weather",
     *     summary="Get weather statistics for region",
     *     description="Returns aggregated weather data for all municipalities in the region",
     *     operationId="getRegionWeather",
     *     tags={"Geography - Regions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Region ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Start date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="End date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Weather statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="avg_temperature", type="number"),
     *             @OA\Property(property="avg_cloud_coverage", type="number"),
     *             @OA\Property(property="avg_solar_radiation", type="number"),
     *             @OA\Property(property="data_points", type="integer")
     *         )
     *     )
     * )
     */
    public function weather(Request $request, int $id): JsonResponse
    {
        $region = Region::findOrFail($id);
        
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->from) : null;
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->to) : null;
        
        $weatherStats = $region->getAverageWeatherData($from, $to);
        
        return response()->json([
            'region' => [
                'id' => $region->id,
                'name' => $region->name,
                'slug' => $region->slug,
            ],
            'period' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'weather_stats' => $weatherStats,
        ]);
    }
}