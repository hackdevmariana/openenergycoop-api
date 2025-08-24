<?php

namespace App\Http\Controllers;

use App\Models\CommunityMetrics;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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
            if ($request->has('organization_id') && $request->organization_id) {
                $query->byOrganization($request->organization_id);
            }

            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'inactive') {
                    $query->inactive();
                }
            }

            if ($request->has('users_min') || $request->has('users_max')) {
                $minUsers = $request->users_min ?? 0;
                $maxUsers = $request->users_max ?? null;
                $query->byUserCount($minUsers, $maxUsers);
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

            // Datos para filtros
            $organizations = Organization::orderBy('name')->get();

            return view('community-metrics.index', compact('metrics', 'organizations'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas comunitarias: ' . $e->getMessage());
            return view('community-metrics.index')->with('error', 'Error al cargar las métricas comunitarias');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $organizations = Organization::orderBy('name')->get();
        
        return view('community-metrics.create', compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,id|unique:community_metrics,organization_id',
                'total_users' => 'required|integer|min:0',
                'total_kwh_produced' => 'required|numeric|min:0',
                'total_co2_avoided' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $metrics = CommunityMetrics::create($request->all());

            DB::commit();

            return redirect()->route('community-metrics.index')
                ->with('success', 'Métricas comunitarias creadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear métricas comunitarias: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al crear métricas comunitarias')
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
        $organizations = Organization::orderBy('name')->get();
        
        return view('community-metrics.edit', compact('communityMetric', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommunityMetrics $communityMetric): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|required|exists:organizations,id|unique:community_metrics,organization_id,' . $communityMetric->id,
                'total_users' => 'sometimes|required|integer|min:0',
                'total_kwh_produced' => 'sometimes|required|numeric|min:0',
                'total_co2_avoided' => 'sometimes|required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $communityMetric->update($request->all());

            DB::commit();

            return redirect()->route('community-metrics.show', $communityMetric)
                ->with('success', 'Métricas comunitarias actualizadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar métricas comunitarias: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al actualizar métricas comunitarias')
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

            return redirect()->route('community-metrics.index')
                ->with('success', 'Métricas comunitarias eliminadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar métricas comunitarias: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al eliminar métricas comunitarias');
        }
    }

    /**
     * Mostrar métricas por organización
     */
    public function byOrganization($organizationId): View
    {
        try {
            $organization = Organization::findOrFail($organizationId);
            $metrics = CommunityMetrics::byOrganization($organizationId)
                ->with(['organization'])
                ->first();

            if (!$metrics) {
                return redirect()->route('community-metrics.index')
                    ->with('error', 'No se encontraron métricas para esta organización');
            }

            return view('community-metrics.by-organization', compact('organization', 'metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas por organización: ' . $e->getMessage());
            return redirect()->route('community-metrics.index')
                ->with('error', 'Error al obtener métricas de la organización');
        }
    }

    /**
     * Mostrar métricas activas
     */
    public function active(): View
    {
        try {
            $metrics = CommunityMetrics::active()
                ->with(['organization'])
                ->orderBy('total_co2_avoided', 'desc')
                ->paginate(15);

            return view('community-metrics.active', compact('metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas activas: ' . $e->getMessage());
            return redirect()->route('community-metrics.index')
                ->with('error', 'Error al obtener métricas activas');
        }
    }

    /**
     * Mostrar métricas inactivas
     */
    public function inactive(): View
    {
        try {
            $metrics = CommunityMetrics::inactive()
                ->with(['organization'])
                ->orderBy('updated_at', 'desc')
                ->paginate(15);

            return view('community-metrics.inactive', compact('metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas inactivas: ' . $e->getMessage());
            return redirect()->route('community-metrics.index')
                ->with('error', 'Error al obtener métricas inactivas');
        }
    }

    /**
     * Mostrar métricas recientes
     */
    public function recent(Request $request): View
    {
        try {
            $days = $request->get('days', 30);
            
            $metrics = CommunityMetrics::recent($days)
                ->with(['organization'])
                ->orderBy('updated_at', 'desc')
                ->paginate(15);

            return view('community-metrics.recent', compact('metrics', 'days'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas recientes: ' . $e->getMessage());
            return redirect()->route('community-metrics.index')
                ->with('error', 'Error al obtener métricas recientes');
        }
    }

    /**
     * Mostrar métricas de este mes
     */
    public function thisMonth(): View
    {
        try {
            $metrics = CommunityMetrics::thisMonth()
                ->with(['organization'])
                ->orderBy('updated_at', 'desc')
                ->paginate(15);

            return view('community-metrics.this-month', compact('metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de este mes: ' . $e->getMessage());
            return redirect()->route('community-metrics.index')
                ->with('error', 'Error al obtener métricas de este mes');
        }
    }

    /**
     * Mostrar métricas de este año
     */
    public function thisYear(): View
    {
        try {
            $metrics = CommunityMetrics::thisYear()
                ->with(['organization'])
                ->orderBy('updated_at', 'desc')
                ->paginate(15);

            return view('community-metrics.this-year', compact('metrics'));

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de este año: ' . $e->getMessage());
            return redirect()->route('community-metrics.index')
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
                'total_community_impact' => CommunityMetrics::getTotalCommunityImpact(),
                'total_community_production' => CommunityMetrics::getTotalCommunityProduction(),
                'total_community_users' => CommunityMetrics::getTotalCommunityUsers(),
                'top_organizations_by_impact' => CommunityMetrics::getTopOrganizationsByImpact(10),
                'top_organizations_by_production' => CommunityMetrics::getTopOrganizationsByProduction(10),
                'top_organizations_by_users' => CommunityMetrics::getTopOrganizationsByUsers(10),
                'average_metrics' => CommunityMetrics::getAverageMetrics(),
                'formatted_average_metrics' => CommunityMetrics::getFormattedAverageMetrics(),
                'active_organizations' => CommunityMetrics::active()->count(),
                'inactive_organizations' => CommunityMetrics::inactive()->count(),
            ];

            return view('community-metrics.statistics', compact('stats'));

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return redirect()->route('community-metrics.index')
                ->with('error', 'Error al obtener estadísticas');
        }
    }

    /**
     * Agregar usuario a la organización
     */
    public function addUser(CommunityMetrics $communityMetric): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $communityMetric->addUser();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Usuario agregado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar usuario: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al agregar usuario');
        }
    }

    /**
     * Remover usuario de la organización
     */
    public function removeUser(CommunityMetrics $communityMetric): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $communityMetric->removeUser();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Usuario removido exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al remover usuario: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al remover usuario');
        }
    }

    /**
     * Agregar producción de kWh
     */
    public function addKwhProduction(Request $request, CommunityMetrics $communityMetric): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'kwh' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $communityMetric->addKwhProduction($request->kwh);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Producción de kWh agregada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar producción de kWh: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al agregar producción de kWh');
        }
    }

    /**
     * Agregar CO2 evitado
     */
    public function addCo2Avoided(Request $request, CommunityMetrics $communityMetric): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'co2' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $communityMetric->addCo2Avoided($request->co2);

            DB::commit();

            return redirect()->back()
                ->with('success', 'CO2 evitado agregado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar CO2 evitado: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al agregar CO2 evitado');
        }
    }

    /**
     * Reiniciar métricas
     */
    public function resetMetrics(CommunityMetrics $communityMetric): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $communityMetric->resetMetrics();

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
