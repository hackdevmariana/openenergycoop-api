<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Suscripciones de Usuario",
    description: "API para gestión de suscripciones de usuarios a servicios energéticos"
)]
class UserSubscriptionController extends Controller
{
    #[OA\Get(
        path: "/api/v1/user-subscriptions",
        operationId: "getUserSubscriptions",
        description: "Obtener lista de suscripciones de usuarios con filtros",
        summary: "Listar suscripciones",
        security: [["sanctum" => []]],
        tags: ["Suscripciones de Usuario"]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = UserSubscription::query()->with(['user', 'energyCooperative', 'provider']);

        // Filtro para ver solo las suscripciones del usuario autenticado (por defecto)
        if (!$request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }

        // Filtros adicionales
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('billing_frequency')) {
            $query->where('billing_frequency', $request->billing_frequency);
        }

        if ($request->filled('subscription_type')) {
            $query->where('subscription_type', $request->subscription_type);
        }

        if ($request->filled('auto_renewal')) {
            $query->where('auto_renewal', $request->boolean('auto_renewal'));
        }

        $perPage = min($request->get('per_page', 15), 100);
        $subscriptions = $query->paginate($perPage);

        return response()->json($subscriptions);
    }

    #[OA\Post(
        path: "/api/v1/user-subscriptions",
        operationId: "createUserSubscription",
        description: "Crear una nueva suscripción de usuario",
        summary: "Crear suscripción",
        security: [["sanctum" => []]],
        tags: ["Suscripciones de Usuario"]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'energy_cooperative_id' => 'nullable|exists:energy_cooperatives,id',
            'provider_id' => 'nullable|exists:providers,id',
            'subscription_type' => 'required|string|max:255',
            'plan_name' => 'required|string|max:255',
            'plan_description' => 'nullable|string',
            'service_category' => 'required|string|max:255',
            'status' => 'nullable|in:pending,active,paused,cancelled,expired,suspended,terminated',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'trial_end_date' => 'nullable|date',
            'billing_frequency' => 'required|in:weekly,monthly,quarterly,semi_annual,annual,one_time',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'discount_amount' => 'nullable|numeric|min:0',
            'promo_code' => 'nullable|string|max:255',
            'energy_allowance_kwh' => 'nullable|numeric|min:0',
            'overage_rate_per_kwh' => 'nullable|numeric|min:0',
            'includes_renewable_energy' => 'boolean',
            'renewable_percentage' => 'nullable|numeric|between:0,100',
            'auto_renewal' => 'boolean',
            'renewal_reminder_days' => 'nullable|integer|min:1',
        ]);

        $validated['user_id'] = $request->user()->id;

        $subscription = UserSubscription::create($validated);
        $subscription->load(['user', 'energyCooperative', 'provider']);

        return response()->json([
            'message' => 'Suscripción creada exitosamente',
            'data' => $subscription
        ], 201);
    }

    #[OA\Get(
        path: "/api/v1/user-subscriptions/{userSubscription}",
        operationId: "getUserSubscription",
        description: "Obtener una suscripción específica",
        summary: "Ver suscripción",
        security: [["sanctum" => []]],
        tags: ["Suscripciones de Usuario"]
    )]
    public function show(Request $request, UserSubscription $userSubscription): JsonResponse
    {
        // Verificar que el usuario puede acceder a esta suscripción
        if (!$request->user()->hasRole('admin') && $userSubscription->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $userSubscription->load(['user', 'energyCooperative', 'provider']);

        return response()->json([
            'data' => $userSubscription
        ]);
    }

    #[OA\Put(
        path: "/api/v1/user-subscriptions/{userSubscription}",
        operationId: "updateUserSubscription",
        description: "Actualizar una suscripción",
        summary: "Actualizar suscripción",
        security: [["sanctum" => []]],
        tags: ["Suscripciones de Usuario"]
    )]
    public function update(Request $request, UserSubscription $userSubscription): JsonResponse
    {
        // Verificar que el usuario puede modificar esta suscripción
        if (!$request->user()->hasRole('admin') && $userSubscription->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'plan_name' => 'sometimes|string|max:255',
            'plan_description' => 'nullable|string',
            'status' => 'sometimes|in:pending,active,paused,cancelled,expired,suspended,terminated',
            'end_date' => 'nullable|date|after:start_date',
            'billing_frequency' => 'sometimes|in:weekly,monthly,quarterly,semi_annual,annual,one_time',
            'price' => 'sometimes|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'discount_amount' => 'nullable|numeric|min:0',
            'energy_allowance_kwh' => 'nullable|numeric|min:0',
            'overage_rate_per_kwh' => 'nullable|numeric|min:0',
            'includes_renewable_energy' => 'boolean',
            'renewable_percentage' => 'nullable|numeric|between:0,100',
            'auto_renewal' => 'boolean',
            'renewal_reminder_days' => 'nullable|integer|min:1',
        ]);

        $userSubscription->update($validated);
        $userSubscription->load(['user', 'energyCooperative', 'provider']);

        return response()->json([
            'message' => 'Suscripción actualizada exitosamente',
            'data' => $userSubscription
        ]);
    }

    #[OA\Delete(
        path: "/api/v1/user-subscriptions/{userSubscription}",
        operationId: "deleteUserSubscription",
        description: "Cancelar una suscripción",
        summary: "Cancelar suscripción",
        security: [["sanctum" => []]],
        tags: ["Suscripciones de Usuario"]
    )]
    public function destroy(Request $request, UserSubscription $userSubscription): JsonResponse
    {
        // Verificar que el usuario puede cancelar esta suscripción
        if (!$request->user()->hasRole('admin') && $userSubscription->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // No eliminar realmente, sino cambiar estado a cancelado
        $userSubscription->update([
            'status' => 'cancelled',
            'cancellation_date' => now(),
            'cancellation_reason' => $request->input('reason', 'Cancelado por el usuario')
        ]);

        return response()->json([
            'message' => 'Suscripción cancelada exitosamente'
        ]);
    }

    #[OA\Get(
        path: "/api/v1/user-subscriptions/my-subscriptions",
        operationId: "getMySubscriptions",
        description: "Obtener suscripciones del usuario autenticado",
        summary: "Mis suscripciones",
        security: [["sanctum" => []]],
        tags: ["Suscripciones de Usuario"]
    )]
    public function mySubscriptions(Request $request): JsonResponse
    {
        $query = $request->user()->userSubscriptions()->with(['energyCooperative', 'provider']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->get();

        return response()->json([
            'data' => $subscriptions,
            'summary' => [
                'total_subscriptions' => $subscriptions->count(),
                'active_subscriptions' => $subscriptions->where('status', 'active')->count(),
                'pending_subscriptions' => $subscriptions->where('status', 'pending')->count(),
                'total_monthly_cost' => $subscriptions->where('status', 'active')
                    ->where('billing_frequency', 'monthly')
                    ->sum('price')
            ]
        ]);
    }

    #[OA\Post(
        path: "/api/v1/user-subscriptions/{userSubscription}/pause",
        operationId: "pauseUserSubscription",
        description: "Pausar una suscripción temporalmente",
        summary: "Pausar suscripción",
        security: [["sanctum" => []]],
        tags: ["Suscripciones de Usuario"]
    )]
    public function pause(Request $request, UserSubscription $userSubscription): JsonResponse
    {
        if (!$request->user()->hasRole('admin') && $userSubscription->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if (!$userSubscription->isActive()) {
            return response()->json(['error' => 'Solo se pueden pausar suscripciones activas'], 400);
        }

        $userSubscription->update([
            'status' => 'paused',
            'paused_at' => now()
        ]);

        return response()->json([
            'message' => 'Suscripción pausada exitosamente',
            'data' => $userSubscription
        ]);
    }

    #[OA\Post(
        path: "/api/v1/user-subscriptions/{userSubscription}/resume",
        operationId: "resumeUserSubscription",
        description: "Reanudar una suscripción pausada",
        summary: "Reanudar suscripción",
        security: [["sanctum" => []]],
        tags: ["Suscripciones de Usuario"]
    )]
    public function resume(Request $request, UserSubscription $userSubscription): JsonResponse
    {
        if (!$request->user()->hasRole('admin') && $userSubscription->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($userSubscription->status !== 'paused') {
            return response()->json(['error' => 'Solo se pueden reanudar suscripciones pausadas'], 400);
        }

        $userSubscription->update([
            'status' => 'active',
            'paused_at' => null
        ]);

        return response()->json([
            'message' => 'Suscripción reanudada exitosamente',
            'data' => $userSubscription
        ]);
    }

    #[OA\Get(
        path: "/api/v1/user-subscriptions/{userSubscription}/usage",
        operationId: "getUserSubscriptionUsage",
        description: "Obtener estadísticas de uso de una suscripción",
        summary: "Estadísticas de uso",
        security: [["sanctum" => []]],
        tags: ["Suscripciones de Usuario"]
    )]
    public function usage(Request $request, UserSubscription $userSubscription): JsonResponse
    {
        if (!$request->user()->hasRole('admin') && $userSubscription->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $usage = [
            'current_period' => [
                'usage_kwh' => $userSubscription->current_period_usage_kwh,
                'allowance_kwh' => $userSubscription->energy_allowance_kwh,
                'remaining_kwh' => $userSubscription->getRemainingAllowance(),
                'cost' => $userSubscription->current_period_cost,
                'overage_amount' => $userSubscription->getOverageAmount(),
                'has_overage' => $userSubscription->hasOverage()
            ],
            'total' => [
                'usage_kwh' => $userSubscription->total_usage_kwh,
                'cost_paid' => $userSubscription->total_cost_paid,
                'billing_cycles' => $userSubscription->billing_cycles_completed,
                'loyalty_points' => $userSubscription->loyalty_points
            ],
            'renewable_energy' => [
                'included' => $userSubscription->includes_renewable_energy,
                'percentage' => $userSubscription->renewable_percentage
            ]
        ];

        return response()->json([
            'subscription' => $userSubscription->only(['id', 'plan_name', 'status']),
            'usage' => $usage,
            'generated_at' => now()->toISOString()
        ]);
    }
}