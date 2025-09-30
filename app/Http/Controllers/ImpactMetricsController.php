<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ImpactMetrics;
use App\Models\User;
use App\Models\PlantGroup;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
            if ($request->has('user_id') && $request->user_id !== '') {
                $query->byUser($request->user_id);
            }

            if ($request->has('plant_group_id') && $request->plant_group_id !== '') {
                $query->byPlantGroup($request->plant_group_id);
            }

            if ($request->has('type') && $request->type !== '') {
                if ($request->type === 'individual') {
                    $query->individual();
                } elseif ($request->type === 'global') {
                    $query->global();
                }
            }

            if ($request->has('date_from') && $request->date_from !== '') {
                $query->byDateRange($request->date_from, $request->date_to ?? null);
            }

            if ($request->has('co2_min') || $request->has('co2_max')) {
                $query->byCo2Range($request->co2_min ?? 0, $request->co2_max ?? null);
            }

            if ($request->has('kwh_min') || $request->has('kwh_max')) {
                $query->byKwhRange($request->kwh_min ?? 0, $request->kwh_max ?? null);
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

            // Obtener datos para filtros
            $users = User::select('id', 'name', 'email')->get();
            $plantGroups = PlantGroup::select('id', 'name')->get();

            return view('impact-metrics.index', compact('metrics', 'users', 'plantGroups'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de impacto: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las métricas de impacto');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $users = User::select('id', 'name', 'email')->get();
        $plantGroups = PlantGroup::select('id', 'name')->get();
        
        return view('impact-metrics.create', compact('users', 'plantGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'plant_group_id' => 'nullable|exists:plant_groups,id',
            'co2_avoided_kg' => 'required|numeric|min:0',
            'kwh_produced' => 'required|numeric|min:0',
            'generated_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $impactMetric = ImpactMetrics::create($request->all());

            DB::commit();

            Log::info('Métrica de impacto creada', [
                'user_id' => auth()->id(),
                'impact_metric_id' => $impactMetric->id
            ]);

            return redirect()->route('impact-metrics.show', $impactMetric)
                ->with('success', 'Métrica de impacto creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear métrica de impacto: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al crear la métrica de impacto')
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
        $users = User::select('id', 'name', 'email')->get();
        $plantGroups = PlantGroup::select('id', 'name')->get();
        
        return view('impact-metrics.edit', compact('impactMetric', 'users', 'plantGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ImpactMetrics $impactMetric): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'plant_group_id' => 'nullable|exists:plant_groups,id',
            'co2_avoided_kg' => 'required|numeric|min:0',
            'kwh_produced' => 'required|numeric|min:0',
            'generated_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $impactMetric->update($request->all());

            DB::commit();

            Log::info('Métrica de impacto actualizada', [
                'user_id' => auth()->id(),
                'impact_metric_id' => $impactMetric->id
            ]);

            return redirect()->route('impact-metrics.show', $impactMetric)
                ->with('success', 'Métrica de impacto actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar métrica de impacto: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al actualizar la métrica de impacto')
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

            Log::info('Métrica de impacto eliminada', [
                'user_id' => auth()->id(),
                'impact_metric_id' => $impactMetric->id
            ]);

            return redirect()->route('impact-metrics.index')
                ->with('success', 'Métrica de impacto eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar métrica de impacto: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al eliminar la métrica de impacto');
        }
    }

    /**
     * Obtener métricas por usuario
     */
    public function byUser(Request $request, $userId): View
    {
        $user = User::findOrFail($userId);
        $metrics = ImpactMetrics::where('user_id', $userId)
            ->with('plantGroup')
            ->orderBy('generated_at', 'desc')
            ->paginate(15);

        return view('impact-metrics.by-user', compact('metrics', 'user'));
    }

    /**
     * Obtener métricas por grupo de plantas
     */
    public function byPlantGroup(Request $request, $plantGroupId): View
    {
        $plantGroup = PlantGroup::findOrFail($plantGroupId);
        $metrics = ImpactMetrics::where('plant_group_id', $plantGroupId)
            ->with('user')
            ->orderBy('generated_at', 'desc')
            ->paginate(15);

        return view('impact-metrics.by-plant-group', compact('metrics', 'plantGroup'));
    }

    /**
     * Obtener métricas globales
     */
    public function global(): View
    {
        $metrics = ImpactMetrics::global()
            ->with(['user', 'plantGroup'])
            ->orderBy('generated_at', 'desc')
            ->paginate(15);

        return view('impact-metrics.global', compact('metrics'));
    }

    /**
     * Obtener métricas individuales
     */
    public function individual(): View
    {
        $metrics = ImpactMetrics::individual()
            ->with(['user', 'plantGroup'])
            ->orderBy('generated_at', 'desc')
            ->paginate(15);

        return view('impact-metrics.individual', compact('metrics'));
    }

    /**
     * Obtener métricas recientes
     */
    public function recent(): View
    {
        $metrics = ImpactMetrics::recent()
            ->with(['user', 'plantGroup'])
            ->paginate(15);

        return view('impact-metrics.recent', compact('metrics'));
    }

    /**
     * Obtener métricas de este mes
     */
    public function thisMonth(): View
    {
        $metrics = ImpactMetrics::thisMonth()
            ->with(['user', 'plantGroup'])
            ->orderBy('generated_at', 'desc')
            ->paginate(15);

        return view('impact-metrics.this-month', compact('metrics'));
    }

    /**
     * Obtener métricas de este año
     */
    public function thisYear(): View
    {
        $metrics = ImpactMetrics::thisYear()
            ->with(['user', 'plantGroup'])
            ->orderBy('generated_at', 'desc')
            ->paginate(15);

        return view('impact-metrics.this-year', compact('metrics'));
    }

    /**
     * Obtener estadísticas
     */
    public function statistics(): View
    {
        $stats = [
            'total_metrics' => ImpactMetrics::count(),
            'total_co2_avoided' => ImpactMetrics::sum('co2_avoided_kg'),
            'total_kwh_produced' => ImpactMetrics::sum('kwh_produced'),
            'average_co2_per_metric' => ImpactMetrics::avg('co2_avoided_kg'),
            'average_kwh_per_metric' => ImpactMetrics::avg('kwh_produced'),
        ];

        return view('impact-metrics.statistics', compact('stats'));
    }

    /**
     * Actualizar métricas
     */
    public function updateMetrics(Request $request, ImpactMetrics $impactMetric): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'co2_avoided_kg' => 'required|numeric|min:0',
            'kwh_produced' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $impactMetric->update([
                'co2_avoided_kg' => $request->co2_avoided_kg,
                'kwh_produced' => $request->kwh_produced,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Métricas actualizadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar métricas: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error al actualizar las métricas');
        }
    }

    /**
     * Resetear métricas
     */
    public function resetMetrics(ImpactMetrics $impactMetric): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $impactMetric->update([
                'co2_avoided_kg' => 0,
                'kwh_produced' => 0,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Métricas reseteadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al resetear métricas: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error al resetear las métricas');
        }
    }
}
