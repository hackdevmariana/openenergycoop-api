<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EnergyForecast\StoreEnergyForecastRequest;
use App\Http\Requests\Api\V1\EnergyForecast\UpdateEnergyForecastRequest;
use App\Http\Resources\Api\V1\EnergyForecast\EnergyForecastCollection;
use App\Http\Resources\Api\V1\EnergyForecast\EnergyForecastResource;
use App\Models\EnergyForecast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * @group Energy Forecast Management
 *
 * APIs for managing energy forecasts
 */
class EnergyForecastController extends Controller
{
    /**
     * Display a listing of energy forecasts.
     *
     * @queryParam forecast_type string Filter by forecast type (demand, generation, consumption, price, weather, load, renewable, storage, transmission, other). Example: demand
     * @queryParam forecast_horizon string Filter by forecast horizon (hourly, daily, weekly, monthly, quarterly, yearly, long_term). Example: daily
     * @queryParam forecast_method string Filter by forecast method (statistical, machine_learning, physical_model, hybrid, expert_judgment, other). Example: machine_learning
     * @queryParam forecast_status string Filter by forecast status (draft, active, validated, expired, superseded, archived). Example: active
     * @queryParam accuracy_level string Filter by accuracy level (low, medium, high, very_high). Example: high
     * @queryParam source_id integer Filter by source ID. Example: 1
     * @queryParam target_id integer Filter by target ID. Example: 1
     * @queryParam target_type string Filter by target type. Example: App\Models\EnergyInstallation
     * @queryParam search string Search in name, description, and forecast_number. Example: solar
     * @queryParam sort string Sort by field (name, forecast_type, forecast_horizon, accuracy_score, confidence_level, created_at). Example: -created_at
     * @queryParam limit integer Number of items per page. Example: 15
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergyForecast::query()
                ->with(['source', 'target', 'createdBy', 'approvedBy', 'validatedBy']);

            // Filtros
            if ($request->filled('forecast_type')) {
                $query->byForecastType($request->forecast_type);
            }

            if ($request->filled('forecast_horizon')) {
                $query->byForecastHorizon($request->forecast_horizon);
            }

            if ($request->filled('forecast_method')) {
                $query->byForecastMethod($request->forecast_method);
            }

            if ($request->filled('forecast_status')) {
                $query->byForecastStatus($request->forecast_status);
            }

            if ($request->filled('accuracy_level')) {
                $query->byAccuracyLevel($request->accuracy_level);
            }

            if ($request->filled('source_id')) {
                $query->bySource($request->source_id);
            }

            if ($request->filled('target_id')) {
                $query->byTarget($request->target_id, $request->target_type);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('forecast_number', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortField = $request->get('sort', '-created_at');
            if (str_starts_with($sortField, '-')) {
                $field = substr($sortField, 1);
                $direction = 'desc';
            } else {
                $field = $sortField;
                $direction = 'asc';
            }

            $allowedSortFields = ['name', 'forecast_type', 'forecast_horizon', 'accuracy_score', 'confidence_level', 'created_at'];
            if (in_array($field, $allowedSortFields)) {
                $query->orderBy($field, $direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Paginación
            $limit = min($request->get('limit', 15), 100);
            $forecasts = $query->paginate($limit);

            return response()->json(new EnergyForecastCollection($forecasts));

        } catch (\Exception $e) {
            Log::error('Error fetching energy forecasts: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching energy forecasts'], 500);
        }
    }

    /**
     * Store a newly created energy forecast.
     *
     * @bodyParam name string required The name of the forecast. Example: Solar Generation Forecast Q1 2024
     * @bodyParam description string The description of the forecast. Example: Quarterly forecast for solar energy generation
     * @bodyParam forecast_type string required The type of forecast. Example: generation
     * @bodyParam forecast_horizon string required The horizon of the forecast. Example: quarterly
     * @bodyParam forecast_method string required The method used for forecasting. Example: machine_learning
     * @bodyParam accuracy_level string The accuracy level. Example: high
     * @bodyParam accuracy_score numeric The accuracy score (0-100). Example: 85.5
     * @bodyParam confidence_level numeric The confidence level (0-100). Example: 90.0
     * @bodyParam source_id integer The source ID. Example: 1
     * @bodyParam target_id integer The target ID. Example: 1
     * @bodyParam target_type string The target type. Example: App\Models\EnergyInstallation
     * @bodyParam forecast_start_time datetime required The start time of the forecast. Example: 2024-01-01 00:00:00
     * @bodyParam forecast_end_time datetime required The end time of the forecast. Example: 2024-03-31 23:59:59
     * @bodyParam valid_from datetime The validity start date. Example: 2024-01-01 00:00:00
     * @bodyParam valid_until datetime The validity end date. Example: 2024-04-30 23:59:59
     * @bodyParam expiry_time datetime The expiry time. Example: 2024-05-01 00:00:00
     * @bodyParam time_zone string The time zone. Example: UTC
     * @bodyParam time_resolution string The time resolution. Example: 1h
     * @bodyParam forecast_unit string The unit of the forecast. Example: kWh
     * @bodyParam tags array The tags for the forecast. Example: ["solar", "quarterly", "high-accuracy"]
     */
    public function store(StoreEnergyForecastRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $forecast = EnergyForecast::create($request->validated());

            DB::commit();

            Log::info('Energy forecast created', ['forecast_id' => $forecast->id, 'user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Energy forecast created successfully',
                'data' => new EnergyForecastResource($forecast)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating energy forecast: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating energy forecast'], 500);
        }
    }

    /**
     * Display the specified energy forecast.
     *
     * @urlParam id integer required The ID of the forecast. Example: 1
     */
    public function show(EnergyForecast $forecast): JsonResponse
    {
        try {
            $forecast->load(['source', 'target', 'createdBy', 'approvedBy', 'validatedBy']);

            return response()->json([
                'data' => new EnergyForecastResource($forecast)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy forecast: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching energy forecast'], 500);
        }
    }

    /**
     * Update the specified energy forecast.
     *
     * @urlParam id integer required The ID of the forecast. Example: 1
     * @bodyParam name string The name of the forecast. Example: Updated Solar Generation Forecast
     * @bodyParam description string The description of the forecast. Example: Updated quarterly forecast
     * @bodyParam forecast_type string The type of forecast. Example: generation
     * @bodyParam forecast_horizon string The horizon of the forecast. Example: quarterly
     * @bodyParam forecast_method string The method used for forecasting. Example: machine_learning
     * @bodyParam accuracy_level string The accuracy level. Example: high
     * @bodyParam accuracy_score numeric The accuracy score (0-100). Example: 87.5
     * @bodyParam confidence_level numeric The confidence level (0-100). Example: 92.0
     * @bodyParam forecast_start_time datetime The start time of the forecast. Example: 2024-01-01 00:00:00
     * @bodyParam forecast_end_time datetime The end time of the forecast. Example: 2024-03-31 23:59:59
     * @bodyParam valid_from datetime The validity start date. Example: 2024-01-01 00:00:00
     * @bodyParam valid_until datetime The validity end date. Example: 2024-04-30 23:59:59
     * @bodyParam expiry_time datetime The expiry time. Example: 2024-05-01 00:00:00
     * @bodyParam tags array The tags for the forecast. Example: ["solar", "quarterly", "high-accuracy", "updated"]
     */
    public function update(UpdateEnergyForecastRequest $request, EnergyForecast $forecast): JsonResponse
    {
        try {
            DB::beginTransaction();

            $forecast->update($request->validated());

            DB::commit();

            Log::info('Energy forecast updated', ['forecast_id' => $forecast->id, 'user_id' => auth()->id()]);

            return response()->json([
                'message' => 'Energy forecast updated successfully',
                'data' => new EnergyForecastResource($forecast)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating energy forecast: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating energy forecast'], 500);
        }
    }

    /**
     * Remove the specified energy forecast.
     *
     * @urlParam id integer required The ID of the forecast. Example: 1
     */
    public function destroy(EnergyForecast $forecast): JsonResponse
    {
        try {
            DB::beginTransaction();

            $forecast->delete();

            DB::commit();

            Log::info('Energy forecast deleted', ['forecast_id' => $forecast->id, 'user_id' => auth()->id()]);

            return response()->json(['message' => 'Energy forecast deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting energy forecast: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting energy forecast'], 500);
        }
    }

    /**
     * Get statistics for energy forecasts.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total' => EnergyForecast::count(),
                'by_type' => EnergyForecast::selectRaw('forecast_type, COUNT(*) as count')
                    ->groupBy('forecast_type')
                    ->pluck('count', 'forecast_type'),
                'by_status' => EnergyForecast::selectRaw('forecast_status, COUNT(*) as count')
                    ->groupBy('forecast_status')
                    ->pluck('count', 'forecast_status'),
                'by_horizon' => EnergyForecast::selectRaw('forecast_horizon, COUNT(*) as count')
                    ->groupBy('forecast_horizon')
                    ->pluck('count', 'forecast_horizon'),
                'by_method' => EnergyForecast::selectRaw('forecast_method, COUNT(*) as count')
                    ->groupBy('forecast_method')
                    ->pluck('count', 'forecast_method'),
                'by_accuracy' => EnergyForecast::selectRaw('accuracy_level, COUNT(*) as count')
                    ->groupBy('accuracy_level')
                    ->pluck('count', 'accuracy_level'),
                'recent' => EnergyForecast::where('created_at', '>=', now()->subDays(30))->count(),
                'expiring_soon' => EnergyForecast::expired()->count(),
            ];

            return response()->json(['data' => $stats]);

        } catch (\Exception $e) {
            Log::error('Error fetching energy forecast statistics: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching statistics'], 500);
        }
    }

    /**
     * Get forecast types.
     */
    public function types(): JsonResponse
    {
        return response()->json(['data' => EnergyForecast::getForecastTypes()]);
    }

    /**
     * Get forecast horizons.
     */
    public function horizons(): JsonResponse
    {
        return response()->json(['data' => EnergyForecast::getForecastHorizons()]);
    }

    /**
     * Get forecast methods.
     */
    public function methods(): JsonResponse
    {
        return response()->json(['data' => EnergyForecast::getForecastMethods()]);
    }

    /**
     * Get forecast statuses.
     */
    public function statuses(): JsonResponse
    {
        return response()->json(['data' => EnergyForecast::getForecastStatuses()]);
    }

    /**
     * Get accuracy levels.
     */
    public function accuracyLevels(): JsonResponse
    {
        return response()->json(['data' => EnergyForecast::getAccuracyLevels()]);
    }

    /**
     * Update forecast status.
     *
     * @urlParam id integer required The ID of the forecast. Example: 1
     * @bodyParam forecast_status string required The new status. Example: validated
     */
    public function updateStatus(Request $request, EnergyForecast $forecast): JsonResponse
    {
        try {
            $request->validate([
                'forecast_status' => ['required', Rule::in(array_keys(EnergyForecast::getForecastStatuses()))],
            ]);

            DB::beginTransaction();

            $forecast->update(['forecast_status' => $request->forecast_status]);

            DB::commit();

            Log::info('Energy forecast status updated', [
                'forecast_id' => $forecast->id, 
                'status' => $request->forecast_status,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Forecast status updated successfully',
                'data' => new EnergyForecastResource($forecast)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating forecast status: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating forecast status'], 500);
        }
    }

    /**
     * Duplicate an energy forecast.
     *
     * @urlParam id integer required The ID of the forecast to duplicate. Example: 1
     */
    public function duplicate(EnergyForecast $forecast): JsonResponse
    {
        try {
            DB::beginTransaction();

            $newForecast = $forecast->replicate();
            $newForecast->name = $forecast->name . ' (Copy)';
            $newForecast->forecast_status = 'draft';
            $newForecast->forecast_number = 'FC-' . uniqid();
            $newForecast->created_by = auth()->id();
            $newForecast->approved_by = null;
            $newForecast->approved_at = null;
            $newForecast->validated_by = null;
            $newForecast->validated_at = null;
            $newForecast->save();

            DB::commit();

            Log::info('Energy forecast duplicated', [
                'original_id' => $forecast->id, 
                'new_id' => $newForecast->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Energy forecast duplicated successfully',
                'data' => new EnergyForecastResource($newForecast)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating energy forecast: ' . $e->getMessage());
            return response()->json(['message' => 'Error duplicating energy forecast'], 500);
        }
    }

    /**
     * Get active forecasts.
     */
    public function active(): JsonResponse
    {
        try {
            $forecasts = EnergyForecast::active()
                ->with(['source', 'target', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new EnergyForecastCollection($forecasts));

        } catch (\Exception $e) {
            Log::error('Error fetching active forecasts: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching active forecasts'], 500);
        }
    }

    /**
     * Get validated forecasts.
     */
    public function validated(): JsonResponse
    {
        try {
            $forecasts = EnergyForecast::validatedStatus()
                ->with(['source', 'target', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new EnergyForecastCollection($forecasts));

        } catch (\Exception $e) {
            Log::error('Error fetching validated forecasts: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching validated forecasts'], 500);
        }
    }

    /**
     * Get forecasts by type.
     *
     * @queryParam type string required The forecast type. Example: generation
     */
    public function byType(Request $request): JsonResponse
    {
        try {
            $request->validate(['type' => 'required|string']);

            $forecasts = EnergyForecast::byForecastType($request->type)
                ->with(['source', 'target', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new EnergyForecastCollection($forecasts));

        } catch (\Exception $e) {
            Log::error('Error fetching forecasts by type: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching forecasts by type'], 500);
        }
    }

    /**
     * Get forecasts by horizon.
     *
     * @queryParam horizon string required The forecast horizon. Example: daily
     */
    public function byHorizon(Request $request): JsonResponse
    {
        try {
            $request->validate(['horizon' => 'required|string']);

            $forecasts = EnergyForecast::byForecastHorizon($request->horizon)
                ->with(['source', 'target', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new EnergyForecastCollection($forecasts));

        } catch (\Exception $e) {
            Log::error('Error fetching forecasts by horizon: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching forecasts by horizon'], 500);
        }
    }

    /**
     * Get high accuracy forecasts.
     */
    public function highAccuracy(): JsonResponse
    {
        try {
            $forecasts = EnergyForecast::highAccuracy()
                ->with(['source', 'target', 'createdBy'])
                ->orderBy('accuracy_score', 'desc')
                ->paginate(15);

            return response()->json(new EnergyForecastCollection($forecasts));

        } catch (\Exception $e) {
            Log::error('Error fetching high accuracy forecasts: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching high accuracy forecasts'], 500);
        }
    }

    /**
     * Get expiring forecasts.
     */
    public function expiring(): JsonResponse
    {
        try {
            $forecasts = EnergyForecast::expired()
                ->with(['source', 'target', 'createdBy'])
                ->orderBy('expiry_time', 'asc')
                ->paginate(15);

            return response()->json(new EnergyForecastCollection($forecasts));

        } catch (\Exception $e) {
            Log::error('Error fetching expiring forecasts: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching expiring forecasts'], 500);
        }
    }

    /**
     * Get forecasts by source.
     *
     * @queryParam source_id integer required The source ID. Example: 1
     */
    public function bySource(Request $request): JsonResponse
    {
        try {
            $request->validate(['source_id' => 'required|integer']);

            $forecasts = EnergyForecast::bySource($request->source_id)
                ->with(['source', 'target', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new EnergyForecastCollection($forecasts));

        } catch (\Exception $e) {
            Log::error('Error fetching forecasts by source: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching forecasts by source'], 500);
        }
    }

    /**
     * Get forecasts by target.
     *
     * @queryParam target_id integer required The target ID. Example: 1
     * @queryParam target_type string The target type. Example: App\Models\EnergyInstallation
     */
    public function byTarget(Request $request): JsonResponse
    {
        try {
            $request->validate(['target_id' => 'required|integer']);

            $forecasts = EnergyForecast::byTarget($request->target_id, $request->target_type)
                ->with(['source', 'target', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json(new EnergyForecastCollection($forecasts));

        } catch (\Exception $e) {
            Log::error('Error fetching forecasts by target: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching forecasts by target'], 500);
        }
    }
}
