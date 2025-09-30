<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CommunityMetrics;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CommunityMetricsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        try {
            $query = CommunityMetrics::with(['organization']);

            // Aplicar filtros
            if ($request->has('organization_id') && $request->organization_id !== '') {
                $query->byOrganization($request->organization_id);
            }

            if ($request->has('status') && $request->status !== '') {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'inactive') {
                    $query->inactive();
                }
            }

            if ($request->has('users_min') || $request->has('users_max')) {
                $query->byUserCount($request->users_min ?? 0, $request->users_max ?? null);
            }

            if ($request->has('co2_min') || $request->has('co2_max')) {
                $query->byCo2Range($request->co2_min ?? 0, $request->co2_max ?? null);
            }

            if ($request->has('kwh_min') || $request->has('kwh_max')) {
                $query->byKwhRange($request->kwh_min ?? 0, $request->kwh_max ?? null);
            }

            // Aplicar ordenamiento
            $sortBy = $request->get('sort_by', 'total_co2_avoided');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['users', 'impact', 'production', 'date'])) {
                switch ($sortBy) {
                    case 'users':
                        $query->orderByUsers($sortDirection);
                        break;
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
            $organizations = Organization::select('id', 'name')->get();

            return view('community-metrics.index', compact('metrics', 'organizations'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas comunitarias: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las métricas comunitarias');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $organizations = Organization::select('id', 'name')->get();
        
        return view('community-metrics.create', compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'total_users' => 'required|integer|min:0',
            'total_co2_avoided' => 'required|numeric|min:0',
            'total_kwh_produced' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $communityMetric = CommunityMetrics::create($request->all());

            DB::commit();

            Log::info('Métrica comunitaria creada', [
                'user_id' => auth()->id(),
                'community_metric_id' => $communityMetric->id
            ]);

            return redirect()->route('community-metrics.show', $communityMetric)
                ->with('success', 'Métrica comunitaria creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear métrica comunitaria: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al crear la métrica comunitaria')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CommunityMetrics $communityMetric): View
    {
        $communityMetric->load(['organization']);
        return view('community-metrics.show', compact('communityMetric'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommunityMetrics $communityMetric): View
    {
        $organizations = Organization::select('id', 'name')->get();
        
        return view('community-metrics.edit', compact('communityMetric', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommunityMetrics $communityMetric): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'total_users' => 'required|integer|min:0',
            'total_co2_avoided' => 'required|numeric|min:0',
            'total_kwh_produced' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $communityMetric->update($request->all());

            DB::commit();

            Log::info('Métrica comunitaria actualizada', [
                'user_id' => auth()->id(),
                'community_metric_id' => $communityMetric->id
            ]);

            return redirect()->route('community-metrics.show', $communityMetric)
                ->with('success', 'Métrica comunitaria actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar métrica comunitaria: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al actualizar la métrica comunitaria')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommunityMetrics $communityMetric): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $communityMetric->delete();

            DB::commit();

            Log::info('Métrica comunitaria eliminada', [
                'user_id' => auth()->id(),
                'community_metric_id' => $communityMetric->id
            ]);

            return redirect()->route('community-metrics.index')
                ->with('success', 'Métrica comunitaria eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar métrica comunitaria: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al eliminar la métrica comunitaria');
        }
    }

    /**
     * Obtener métricas por organización
     */
    public function byOrganization(Request $request, $organizationId): View
    {
        $organization = Organization::findOrFail($organizationId);
        $metrics = CommunityMetrics::where('organization_id', $organizationId)
            ->orderBy('period_start', 'desc')
            ->paginate(15);

        return view('community-metrics.by-organization', compact('metrics', 'organization'));
    }

    /**
     * Obtener métricas activas
     */
    public function active(): View
    {
        $metrics = CommunityMetrics::active()
            ->with('organization')
            ->orderBy('period_start', 'desc')
            ->paginate(15);

        return view('community-metrics.active', compact('metrics'));
    }

    /**
     * Obtener métricas inactivas
     */
    public function inactive(): View
    {
        $metrics = CommunityMetrics::inactive()
            ->with('organization')
            ->orderBy('period_start', 'desc')
            ->paginate(15);

        return view('community-metrics.inactive', compact('metrics'));
    }

    /**
     * Obtener métricas recientes
     */
    public function recent(): View
    {
        $metrics = CommunityMetrics::recent()
            ->with('organization')
            ->paginate(15);

        return view('community-metrics.recent', compact('metrics'));
    }

    /**
     * Obtener métricas de este mes
     */
    public function thisMonth(): View
    {
        $metrics = CommunityMetrics::thisMonth()
            ->with('organization')
            ->orderBy('period_start', 'desc')
            ->paginate(15);

        return view('community-metrics.this-month', compact('metrics'));
    }

    /**
     * Obtener métricas de este año
     */
    public function thisYear(): View
    {
        $metrics = CommunityMetrics::thisYear()
            ->with('organization')
            ->orderBy('period_start', 'desc')
            ->paginate(15);

        return view('community-metrics.this-year', compact('metrics'));
    }

    /**
     * Obtener estadísticas
     */
    public function statistics(): View
    {
        $stats = [
            'total_metrics' => CommunityMetrics::count(),
            'active_metrics' => CommunityMetrics::active()->count(),
            'total_users' => CommunityMetrics::sum('total_users'),
            'total_co2_avoided' => CommunityMetrics::sum('total_co2_avoided'),
            'total_kwh_produced' => CommunityMetrics::sum('total_kwh_produced'),
            'average_users_per_metric' => CommunityMetrics::avg('total_users'),
            'average_co2_per_metric' => CommunityMetrics::avg('total_co2_avoided'),
            'average_kwh_per_metric' => CommunityMetrics::avg('total_kwh_produced'),
        ];

        return view('community-metrics.statistics', compact('stats'));
    }

    /**
     * Agregar usuario
     */
    public function addUser(Request $request, CommunityMetrics $communityMetric): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $communityMetric->increment('total_users');

            DB::commit();

            return redirect()->back()->with('success', 'Usuario agregado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar usuario: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error al agregar el usuario');
        }
    }

    /**
     * Remover usuario
     */
    public function removeUser(Request $request, CommunityMetrics $communityMetric): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $communityMetric->decrement('total_users');

            DB::commit();

            return redirect()->back()->with('success', 'Usuario removido exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al remover usuario: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error al remover el usuario');
        }
    }

    /**
     * Agregar producción KWh
     */
    public function addKwhProduction(Request $request, CommunityMetrics $communityMetric): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'kwh_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $communityMetric->increment('total_kwh_produced', $request->kwh_amount);

            DB::commit();

            return redirect()->back()->with('success', 'Producción KWh agregada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar producción KWh: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error al agregar la producción KWh');
        }
    }

    /**
     * Agregar CO2 evitado
     */
    public function addCo2Avoided(Request $request, CommunityMetrics $communityMetric): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'co2_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $communityMetric->increment('total_co2_avoided', $request->co2_amount);

            DB::commit();

            return redirect()->back()->with('success', 'CO2 evitado agregado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar CO2 evitado: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error al agregar el CO2 evitado');
        }
    }

    /**
     * Resetear métricas
     */
    public function resetMetrics(CommunityMetrics $communityMetric): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $communityMetric->update([
                'total_users' => 0,
                'total_co2_avoided' => 0,
                'total_kwh_produced' => 0,
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
