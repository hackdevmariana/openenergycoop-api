<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyParticipation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnergyParticipationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergyParticipation::with(['user']);

            // Filtros
            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            if ($request->has('plan_code')) {
                $query->byPlanCode($request->plan_code);
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('monthly')) {
                if ($request->monthly === 'true') {
                    $query->monthly();
                } elseif ($request->monthly === 'false') {
                    $query->oneTime();
                }
            }

            if ($request->has('with_end_date')) {
                if ($request->with_end_date === 'true') {
                    $query->withEndDate();
                } elseif ($request->with_end_date === 'false') {
                    $query->withoutEndDate();
                }
            }

            if ($request->has('expiring_soon')) {
                $days = $request->get('expiring_soon', 30);
                $query->expiringSoon($days);
            }

            if ($request->has('expired')) {
                if ($request->expired === 'true') {
                    $query->expired();
                }
            }

            if ($request->has('min_fidelity_years')) {
                $query->where('fidelity_years', '>=', $request->min_fidelity_years);
            }

            if ($request->has('max_fidelity_years')) {
                $query->where('fidelity_years', '<=', $request->max_fidelity_years);
            }

            if ($request->has('min_monthly_amount')) {
                $query->where('monthly_amount', '>=', $request->min_monthly_amount);
            }

            if ($request->has('max_monthly_amount')) {
                $query->where('monthly_amount', '<=', $request->max_monthly_amount);
            }

            if ($request->has('min_one_time_amount')) {
                $query->where('one_time_amount', '>=', $request->min_one_time_amount);
            }

            if ($request->has('max_one_time_amount')) {
                $query->where('one_time_amount', '<=', $request->max_one_time_amount);
            }

            // Búsqueda
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('plan_code', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $participations = $query->paginate($perPage);

            // Transformar datos para incluir accessors
            $participations->getCollection()->transform(function ($participation) {
                return [
                    'id' => $participation->id,
                    'user' => $participation->user ? [
                        'id' => $participation->user->id,
                        'name' => $participation->user->name,
                        'email' => $participation->user->email,
                    ] : null,
                    'plan_code' => $participation->plan_code,
                    'plan_label' => $participation->plan_label,
                    'monthly_amount' => $participation->monthly_amount,
                    'one_time_amount' => $participation->one_time_amount,
                    'total_amount' => $participation->total_amount,
                    'total_months' => $participation->total_months,
                    'start_date' => $participation->start_date,
                    'end_date' => $participation->end_date,
                    'status' => $participation->status,
                    'status_label' => $participation->status_label,
                    'status_color' => $participation->status_color,
                    'fidelity_years' => $participation->fidelity_years,
                    'energy_rights_daily' => $participation->energy_rights_daily,
                    'energy_rights_total_kwh' => $participation->energy_rights_total_kwh,
                    'total_contributions' => $participation->total_contributions,
                    'remaining_amount' => $participation->remaining_amount,
                    'completion_percentage' => $participation->completion_percentage,
                    'is_monthly' => $participation->is_monthly,
                    'is_one_time' => $participation->is_one_time,
                    'is_expired' => $participation->is_expired,
                    'is_expiring_soon' => $participation->is_expiring_soon,
                    'days_until_expiration' => $participation->days_until_expiration,
                    'expiration_status' => $participation->expiration_status,
                    'notes' => $participation->notes,
                    'created_at' => $participation->created_at,
                    'updated_at' => $participation->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $participations,
                'message' => 'Participaciones energéticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener participaciones energéticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener participaciones energéticas'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'plan_code' => 'required|string|in:raiz,hogar,independencia_22,independencia_25,independencia_30',
                'monthly_amount' => 'nullable|numeric|min:0',
                'one_time_amount' => 'nullable|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'required|string|in:active,suspended,cancelled,completed',
                'fidelity_years' => 'nullable|numeric|min:0',
                'energy_rights_daily' => 'nullable|numeric|min:0',
                'energy_rights_total_kwh' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validar que al menos uno de los montos esté presente
            if (!$request->monthly_amount && !$request->one_time_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar al menos un monto mensual o una aportación única'
                ], 422);
            }

            // Validar que no se especifiquen ambos montos
            if ($request->monthly_amount && $request->one_time_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede especificar tanto monto mensual como aportación única'
                ], 422);
            }

            DB::beginTransaction();

            $participation = EnergyParticipation::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $participation->getParticipationSummary(),
                'message' => 'Participación energética creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear participación energética: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear participación energética'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EnergyParticipation $energyParticipation): JsonResponse
    {
        try {
            $energyParticipation->load(['user', 'contributions']);

            return response()->json([
                'success' => true,
                'data' => $energyParticipation->getParticipationSummary(),
                'message' => 'Participación energética obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener participación energética: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener participación energética'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EnergyParticipation $energyParticipation): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|exists:users,id',
                'plan_code' => 'sometimes|string|in:raiz,hogar,independencia_22,independencia_25,independencia_30',
                'monthly_amount' => 'nullable|numeric|min:0',
                'one_time_amount' => 'nullable|numeric|min:0',
                'start_date' => 'sometimes|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'sometimes|string|in:active,suspended,cancelled,completed',
                'fidelity_years' => 'nullable|numeric|min:0',
                'energy_rights_daily' => 'nullable|numeric|min:0',
                'energy_rights_total_kwh' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validar que al menos uno de los montos esté presente
            $monthlyAmount = $request->has('monthly_amount') ? $request->monthly_amount : $energyParticipation->monthly_amount;
            $oneTimeAmount = $request->has('one_time_amount') ? $request->one_time_amount : $energyParticipation->one_time_amount;

            if (!$monthlyAmount && !$oneTimeAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar al menos un monto mensual o una aportación única'
                ], 422);
            }

            // Validar que no se especifiquen ambos montos
            if ($monthlyAmount && $oneTimeAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede especificar tanto monto mensual como aportación única'
                ], 422);
            }

            DB::beginTransaction();

            $energyParticipation->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $energyParticipation->getParticipationSummary(),
                'message' => 'Participación energética actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar participación energética: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar participación energética'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EnergyParticipation $energyParticipation): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyParticipation->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Participación energética eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar participación energética: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar participación energética'
            ], 500);
        }
    }

    /**
     * Obtener resumen del sistema
     */
    public function systemSummary(): JsonResponse
    {
        try {
            $summary = EnergyParticipation::getSystemSummary();

            return response()->json([
                'success' => true,
                'data' => $summary,
                'message' => 'Resumen del sistema obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener resumen del sistema: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen del sistema'
            ], 500);
        }
    }

    /**
     * Suspender participación
     */
    public function suspend(EnergyParticipation $energyParticipation): JsonResponse
    {
        try {
            if (!$energyParticipation->canBeSuspended()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La participación no puede ser suspendida en su estado actual'
                ], 422);
            }

            DB::beginTransaction();

            $energyParticipation->suspend();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $energyParticipation->getParticipationSummary(),
                'message' => 'Participación suspendida exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al suspender participación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al suspender participación'
            ], 500);
        }
    }

    /**
     * Cancelar participación
     */
    public function cancel(EnergyParticipation $energyParticipation): JsonResponse
    {
        try {
            if (!$energyParticipation->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La participación no puede ser cancelada en su estado actual'
                ], 422);
            }

            DB::beginTransaction();

            $energyParticipation->cancel();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $energyParticipation->getParticipationSummary(),
                'message' => 'Participación cancelada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar participación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar participación'
            ], 500);
        }
    }

    /**
     * Completar participación
     */
    public function complete(EnergyParticipation $energyParticipation): JsonResponse
    {
        try {
            if (!$energyParticipation->canBeCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La participación no puede ser completada en su estado actual'
                ], 422);
            }

            DB::beginTransaction();

            $energyParticipation->complete();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $energyParticipation->getParticipationSummary(),
                'message' => 'Participación completada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al completar participación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al completar participación'
            ], 500);
        }
    }

    /**
     * Activar participación
     */
    public function activate(EnergyParticipation $energyParticipation): JsonResponse
    {
        try {
            if (!$energyParticipation->isSuspended()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden activar participaciones suspendidas'
                ], 422);
            }

            DB::beginTransaction();

            $energyParticipation->activate();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $energyParticipation->getParticipationSummary(),
                'message' => 'Participación activada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al activar participación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al activar participación'
            ], 500);
        }
    }

    /**
     * Obtener participaciones por plan
     */
    public function byPlan(string $planCode): JsonResponse
    {
        try {
            $participations = EnergyParticipation::getParticipationsByPlan($planCode);

            return response()->json([
                'success' => true,
                'data' => $participations,
                'message' => "Participaciones del plan {$planCode} obtenidas exitosamente"
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener participaciones por plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener participaciones por plan'
            ], 500);
        }
    }

    /**
     * Obtener participaciones por estado
     */
    public function byStatus(string $status): JsonResponse
    {
        try {
            $participations = EnergyParticipation::getParticipationsByStatus($status);

            return response()->json([
                'success' => true,
                'data' => $participations,
                'message' => "Participaciones con estado {$status} obtenidas exitosamente"
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener participaciones por estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener participaciones por estado'
            ], 500);
        }
    }

    /**
     * Obtener participaciones que expiran pronto
     */
    public function expiring(int $days = 30): JsonResponse
    {
        try {
            $participations = EnergyParticipation::getExpiringParticipations($days);

            return response()->json([
                'success' => true,
                'data' => $participations,
                'message' => "Participaciones que expiran en {$days} días obtenidas exitosamente"
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener participaciones que expiran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener participaciones que expiran'
            ], 500);
        }
    }

    /**
     * Obtener participaciones activas
     */
    public function active(): JsonResponse
    {
        try {
            $participations = EnergyParticipation::getActiveParticipations();

            return response()->json([
                'success' => true,
                'data' => $participations,
                'message' => 'Participaciones activas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener participaciones activas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener participaciones activas'
            ], 500);
        }
    }
}