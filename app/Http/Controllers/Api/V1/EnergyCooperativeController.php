<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyCooperative;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Cooperativas Energéticas",
    description: "API para gestión de cooperativas energéticas y comunidades"
)]
class EnergyCooperativeController extends Controller
{
    #[OA\Get(
        path: "/api/v1/energy-cooperatives",
        operationId: "getEnergyCooperatives",
        description: "Obtener lista de cooperativas energéticas con filtros y paginación",
        summary: "Listar cooperativas energéticas",
        security: [["sanctum" => []]],
        tags: ["Cooperativas Energéticas"]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = EnergyCooperative::query()->with(['founder', 'administrator']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $perPage = min($request->get('per_page', 15), 100);
        $cooperatives = $query->paginate($perPage);

        return response()->json($cooperatives);
    }

    #[OA\Post(
        path: "/api/v1/energy-cooperatives",
        operationId: "createEnergyCooperative",
        description: "Crear una nueva cooperativa energética",
        summary: "Crear cooperativa energética",
        security: [["sanctum" => []]],
        tags: ["Cooperativas Energéticas"]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:energy_cooperatives,code',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,active,suspended,inactive,dissolved',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'founder_id' => 'nullable|exists:users,id',
            'administrator_id' => 'nullable|exists:users,id',
            'max_members' => 'nullable|integer|min:1',
            'open_enrollment' => 'boolean',
            'allows_energy_sharing' => 'boolean',
            'allows_trading' => 'boolean',
        ]);

        $cooperative = EnergyCooperative::create($validated);
        $cooperative->load(['founder', 'administrator']);

        return response()->json([
            'message' => 'Cooperativa energética creada exitosamente',
            'data' => $cooperative
        ], 201);
    }

    #[OA\Get(
        path: "/api/v1/energy-cooperatives/{energyCooperative}",
        operationId: "getEnergyCooperative",
        description: "Obtener una cooperativa energética específica",
        summary: "Ver cooperativa energética",
        security: [["sanctum" => []]],
        tags: ["Cooperativas Energéticas"]
    )]
    public function show(EnergyCooperative $energyCooperative): JsonResponse
    {
        $energyCooperative->load(['founder', 'administrator']);

        return response()->json([
            'data' => $energyCooperative
        ]);
    }

    public function update(Request $request, EnergyCooperative $energyCooperative): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,active,suspended,inactive,dissolved',
            'max_members' => 'nullable|integer|min:1',
            'open_enrollment' => 'boolean',
            'allows_energy_sharing' => 'boolean',
            'allows_trading' => 'boolean',
        ]);

        $energyCooperative->update($validated);
        $energyCooperative->load(['founder', 'administrator']);

        return response()->json([
            'message' => 'Cooperativa energética actualizada exitosamente',
            'data' => $energyCooperative
        ]);
    }

    public function destroy(EnergyCooperative $energyCooperative): JsonResponse
    {
        // Verificar si tiene miembros activos
        $activeMembersCount = $energyCooperative->userSubscriptions()
            ->where('status', 'active')
            ->count();

        if ($activeMembersCount > 0) {
            return response()->json([
                'error' => 'No se puede eliminar la cooperativa: tiene miembros activos',
                'active_members_count' => $activeMembersCount
            ], 409);
        }

        $energyCooperative->delete();

        return response()->json([
            'message' => 'Cooperativa energética eliminada exitosamente'
        ]);
    }

    public function members(Request $request, EnergyCooperative $energyCooperative): JsonResponse
    {
        $query = $energyCooperative->userSubscriptions()->with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $members = $query->paginate(20);

        return response()->json([
            'data' => $members,
            'cooperative' => $energyCooperative->only(['id', 'name', 'code', 'current_members'])
        ]);
    }

    public function analytics(EnergyCooperative $energyCooperative): JsonResponse
    {
        $membersStats = [
            'total_members' => $energyCooperative->current_members ?? 0,
            'max_members' => $energyCooperative->max_members,
            'active_subscriptions' => $energyCooperative->userSubscriptions()->where('status', 'active')->count(),
            'pending_subscriptions' => $energyCooperative->userSubscriptions()->where('status', 'pending')->count(),
            'occupancy_percentage' => $energyCooperative->max_members > 0 
                ? round(($energyCooperative->current_members / $energyCooperative->max_members) * 100, 2)
                : null
        ];

        $energyStats = [
            'total_capacity_kw' => $energyCooperative->total_capacity_kw,
            'available_capacity_kw' => $energyCooperative->available_capacity_kw,
            'used_capacity_kw' => $energyCooperative->total_capacity_kw - $energyCooperative->available_capacity_kw,
            'capacity_utilization_percentage' => $energyCooperative->total_capacity_kw > 0
                ? round((($energyCooperative->total_capacity_kw - $energyCooperative->available_capacity_kw) / $energyCooperative->total_capacity_kw) * 100, 2)
                : 0,
            'total_energy_shared_kwh' => $energyCooperative->total_energy_shared_kwh,
            'active_energy_sharings' => $energyCooperative->energySharings()->where('status', 'active')->count(),
        ];

        $sustainabilityStats = [
            'total_co2_reduction_kg' => $energyCooperative->total_co2_reduction_kg,
            'total_projects' => $energyCooperative->total_projects,
            'average_member_satisfaction' => $energyCooperative->average_member_satisfaction,
        ];

        return response()->json([
            'cooperative' => $energyCooperative->only(['id', 'name', 'code', 'status']),
            'members_stats' => $membersStats,
            'energy_stats' => $energyStats,
            'sustainability_stats' => $sustainabilityStats,
            'generated_at' => now()->toISOString()
        ]);
    }

    public function join(Request $request, EnergyCooperative $energyCooperative): JsonResponse
    {
        $user = $request->user();

        // Verificar si ya es miembro
        $existingSubscription = $energyCooperative->userSubscriptions()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existingSubscription) {
            return response()->json([
                'error' => 'Ya eres miembro o tienes una solicitud pendiente en esta cooperativa'
            ], 409);
        }

        $validated = $request->validate([
            'subscription_type' => 'required|string|max:255',
            'plan_name' => 'required|string|max:255',
            'billing_frequency' => 'nullable|in:monthly,quarterly,annual'
        ]);

        $subscription = $energyCooperative->userSubscriptions()->create([
            'user_id' => $user->id,
            'subscription_type' => $validated['subscription_type'],
            'plan_name' => $validated['plan_name'],
            'service_category' => 'cooperative_membership',
            'status' => 'pending',
            'start_date' => now(),
            'billing_frequency' => $validated['billing_frequency'] ?? 'monthly',
            'price' => $energyCooperative->membership_fee ?? 0,
            'currency' => $energyCooperative->currency ?? 'EUR',
        ]);

        $subscription->load(['user', 'energyCooperative']);

        return response()->json([
            'message' => 'Solicitud de membresía enviada exitosamente',
            'data' => $subscription
        ], 201);
    }
}
