<?php

namespace App\Http\Controllers;

use App\Models\ImpactMetrics;
use App\Models\User;
use App\Models\PlantGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ImpactMetricsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        try {
            $query = ImpactMetrics::with(['user', 'plantGroup']);

            // Aplicar filtros
            if ($request->has('user_id') && $request->user_id) {
                $query->byUser($request->user_id);
            }

            if ($request->has('plant_group_id') && $request->plant_group_id) {
                $query->byPlantGroup($request->plant_group_id);
            }

            if ($request->has('type')) {
                if ($request->type === 'individual') {
                    $query->individual();
                } elseif ($request->type === 'global') {
                    $query->global();
                }
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->byDateRange($request->date_from, $request->date_to ?? null);
            }

            if ($request->has('co2_min') || $request->has('co2_max')) {
                $minCo2 = $request->co2_min ?? 0;
                $maxCo2 = $request->co2_max ?? null;
                $query->byCo2Range($minCo2, $maxCo2);
            }

            if ($request->has('kwh_min') || $request->has('kwh_max')) {
                $minKwh = $request->kwh_min ?? 0;
                $maxKwh = $request->kwh_max ?? null;
                $query->byKwhRange($minKwh, $maxKwh);
            }

            // Aplicar ordenamiento
            $sortBy = $request->get('sort_by', 'generated_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['impact', 'production', 'date'])) {
                switch ($sortBy) {
                    case 'impact':
                        $query->orderByImpact($sortDirection);
                        break;
                    case 'production':
                        $query->orderByProduction($sortDirection);
                        break;
                    case 'date':
                        $query->orderByDate($sortDirection);
                        break;
                }
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Aplicar paginación
            $perPage = $request->get('per_page', 15);
            $metrics = $query->paginate($perPage);

            // Datos para filtros
            $users = User::orderBy('name')->get();
            $plantGroups = PlantGroup::orderBy('name')->get();

            return view('impact-metrics.index', compact('metrics', 'users', 'plantGroups'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de impacto: ' . $e->getMessage());
            return view('impact-metrics.index')->with('error', 'Error al cargar las métricas de impacto');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $users = User::orderBy('name')->get();
        $plantGroups = PlantGroup::orderBy('name')->get();
        
        return view('impact-metrics.create', compact('users', 'plantGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:users,id',
                'total_kwh_produced' => 'required|numeric|min:0',
                'total_co2_avoided_kg' => 'required|numeric|min:0',
                'plant_group_id' => 'nullable|exists:plant_groups,id',
                'generated_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $metrics = ImpactMetrics::create($request->all());

            DB::commit();

            return redirect()->route('impact-metrics.index')
                ->with('success', 'Métricas de impacto creadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear métricas de impacto: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al crear métricas de impacto')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ImpactMetrics $impactMetric): View
    {
        $impactMetric->load(['user', 'plantGroup']);
        
        return view('impact-metrics.show', compact('impactMetric'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ImpactMetrics $impactMetric): View
    {
        $users = User::orderBy('name')->get();
        $plantGroups = PlantGroup::orderBy('name')->get();
        
        return view('impact-metrics.edit', compact('impactMetric', 'users', 'plantGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ImpactMetrics $impactMetric): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:users,id',
                'total_kwh_produced' => 'sometimes|required|numeric|min:0',
                'total_co2_avoided_kg' => 'sometimes|required|numeric|min:0',
                'plant_group_id' => 'nullable|exists:plant_groups,id',
                'generated_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $impactMetric->update($request->all());

            DB::commit();

            return redirect()->route('impact-metrics.show', $impactMetric)
                ->with('success', 'Métricas de impacto actualizadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar métricas de impacto: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al actualizar métricas de impacto')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImpactMetrics $impactMetric): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $impactMetric->delete();

            DB::commit();

            return redirect()->route('impact-metrics.index')
                ->with('success', 'Métricas de impacto eliminadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar métricas de impacto: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al eliminar métricas de impacto');
        }
    }

    /**
     * Mostrar métricas por usuario
     */
    public function byUser($userId): View
    {
        try {
            $user = User::findOrFail($userId);
            $metrics = ImpactMetrics::byUser($userId)
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->paginate(15);

            return view('impact-metrics.by-user', compact('user', 'metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas por usuario: ' . $e->getMessage());
            return redirect()->route('impact-metrics.index')
                ->with('error', 'Error al obtener métricas del usuario');
        }
    }

    /**
     * Mostrar métricas por grupo de plantas
     */
    public function byPlantGroup($plantGroupId): View
    {
        try {
            $plantGroup = PlantGroup::findOrFail($plantGroupId);
            $metrics = ImpactMetrics::byPlantGroup($plantGroupId)
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->paginate(15);

            return view('impact-metrics.by-plant-group', compact('plantGroup', 'metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas por grupo de plantas: ' . $e->getMessage());
            return redirect()->route('impact-metrics.index')
                ->with('error', 'Error al obtener métricas del grupo de plantas');
        }
    }

    /**
     * Mostrar métricas globales
     */
    public function global(): View
    {
        try {
            $metrics = ImpactMetrics::global()
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->paginate(15);

            return view('impact-metrics.global', compact('metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas globales: ' . $e->getMessage());
            return redirect()->route('impact-metrics.index')
                ->with('error', 'Error al obtener métricas globales');
        }
    }

    /**
     * Mostrar métricas individuales
     */
    public function individual(): View
    {
        try {
            $metrics = ImpactMetrics::individual()
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->paginate(15);

            return view('impact-metrics.individual', compact('metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas individuales: ' . $e->getMessage());
            return redirect()->route('impact-metrics.index')
                ->with('error', 'Error al obtener métricas individuales');
        }
    }

    /**
     * Mostrar métricas recientes
     */
    public function recent(Request $request): View
    {
        try {
            $days = $request->get('days', 30);
            
            $metrics = ImpactMetrics::recent($days)
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->paginate(15);

            return view('impact-metrics.recent', compact('metrics', 'days'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas recientes: ' . $e->getMessage());
            return redirect()->route('impact-metrics.index')
                ->with('error', 'Error al obtener métricas recientes');
        }
    }

    /**
     * Mostrar métricas de este mes
     */
    public function thisMonth(): View
    {
        try {
            $metrics = ImpactMetrics::thisMonth()
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->paginate(15);

            return view('impact-metrics.this-month', compact('metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de este mes: ' . $e->getMessage());
            return redirect()->route('impact-metrics.index')
                ->with('error', 'Error al obtener métricas de este mes');
        }
    }

    /**
     * Mostrar métricas de este año
     */
    public function thisYear(): View
    {
        try {
            $metrics = ImpactMetrics::thisYear()
                ->with(['user', 'plantGroup'])
                ->orderBy('generated_at', 'desc')
                ->paginate(15);

            return view('impact-metrics.this-year', compact('metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de este año: ' . $e->getMessage());
            return redirect()->route('impact-metrics.index')
                ->with('error', 'Error al obtener métricas de este año');
        }
    }

    /**
     * Mostrar estadísticas generales
     */
    public function statistics(): View
    {
        try {
            $stats = [
                'total_impact' => ImpactMetrics::getTotalGlobalImpact(),
                'total_production' => ImpactMetrics::getTotalGlobalProduction(),
                'top_users_by_impact' => ImpactMetrics::getTopUsersByImpact(10),
                'top_users_by_production' => ImpactMetrics::getTopUsersByProduction(10),
                'community_impact' => ImpactMetrics::getCommunityImpact(),
                'recent_metrics' => ImpactMetrics::recent(7)->count(),
                'this_month_metrics' => ImpactMetrics::thisMonth()->count(),
                'this_year_metrics' => ImpactMetrics::thisYear()->count(),
            ];

            return view('impact-metrics.statistics', compact('stats'));

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return redirect()->route('impact-metrics.index')
                ->with('error', 'Error al obtener estadísticas');
        }
    }

    /**
     * Actualizar métricas existentes
     */
    public function updateMetrics(Request $request, ImpactMetrics $impactMetric): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'kwh_produced' => 'required|numeric|min:0',
                'co2_factor' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $impactMetric->addKwhProduction($request->kwh_produced, $request->co2_factor);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Métricas actualizadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar métricas: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al actualizar métricas');
        }
    }

    /**
     * Reiniciar métricas
     */
    public function resetMetrics(ImpactMetrics $impactMetric): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $impactMetric->resetMetrics();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Métricas reiniciadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reiniciar métricas: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al reiniciar métricas');
        }
    }
}
