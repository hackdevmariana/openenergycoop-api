<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergySharing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Intercambios de Energía",
    description: "API para gestión de intercambios P2P de energía entre usuarios"
)]
class EnergySharingController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EnergySharing::query()->with(['providerUser', 'consumerUser', 'energyCooperative']);

        // Filtrar por usuario (ver solo intercambios donde participa)
        if (!$request->user()->hasRole('admin')) {
            $user = $request->user();
            $query->where(function($q) use ($user) {
                $q->where('provider_user_id', $user->id)
                  ->orWhere('consumer_user_id', $user->id);
            });
        }

        // Filtros adicionales
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sharing_type')) {
            $query->where('sharing_type', $request->sharing_type);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $sharings = $query->paginate($perPage);

        return response()->json($sharings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consumer_user_id' => 'required|exists:users,id|different:provider_user_id',
            'sharing_code' => 'required|string|max:255|unique:energy_sharings,sharing_code',
            'title' => 'required|string|max:255',
            'sharing_type' => 'required|in:direct,community,marketplace,emergency,scheduled,real_time',
            'energy_amount_kwh' => 'required|numeric|min:0.01',
            'is_renewable' => 'boolean',
            'sharing_start_datetime' => 'required|date|after:now',
            'sharing_end_datetime' => 'required|date|after:sharing_start_datetime',
            'proposal_expiry_datetime' => 'required|date|after:now|before:sharing_start_datetime',
            'duration_hours' => 'required|integer|min:1',
            'price_per_kwh' => 'required|numeric|min:0',
            'payment_method' => 'required|in:credits,bank_transfer,energy_tokens,barter,loyalty_points',
            'allows_partial_delivery' => 'boolean',
            'certified_green_energy' => 'boolean',
            'real_time_tracking' => 'boolean',
        ]);

        $validated['provider_user_id'] = $request->user()->id;
        $validated['status'] = 'proposed';
        $validated['total_amount'] = $validated['energy_amount_kwh'] * $validated['price_per_kwh'];
        $validated['energy_delivered_kwh'] = 0;
        $validated['energy_remaining_kwh'] = $validated['energy_amount_kwh'];
        $validated['proposed_at'] = now();

        $sharing = EnergySharing::create($validated);
        $sharing->load(['providerUser', 'consumerUser']);

        return response()->json([
            'message' => 'Propuesta de intercambio de energía creada exitosamente',
            'data' => $sharing
        ], 201);
    }

    public function show(Request $request, EnergySharing $energySharing): JsonResponse
    {
        $energySharing->load(['providerUser', 'consumerUser', 'energyCooperative']);

        return response()->json([
            'data' => $energySharing
        ]);
    }

    public function update(Request $request, EnergySharing $energySharing): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'energy_amount_kwh' => 'sometimes|numeric|min:0.01',
            'price_per_kwh' => 'sometimes|numeric|min:0',
        ]);

        $energySharing->update($validated);

        return response()->json([
            'message' => 'Intercambio de energía actualizado exitosamente',
            'data' => $energySharing
        ]);
    }

    public function destroy(Request $request, EnergySharing $energySharing): JsonResponse
    {
        $energySharing->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);

        return response()->json([
            'message' => 'Intercambio de energía cancelado exitosamente'
        ]);
    }

    public function accept(Request $request, EnergySharing $energySharing): JsonResponse
    {
        if ($energySharing->consumer_user_id !== $request->user()->id) {
            return response()->json(['error' => 'Solo el consumidor puede aceptar el intercambio'], 403);
        }

        $energySharing->update([
            'status' => 'accepted',
            'accepted_at' => now()
        ]);

        return response()->json([
            'message' => 'Intercambio de energía aceptado exitosamente',
            'data' => $energySharing
        ]);
    }

    public function complete(Request $request, EnergySharing $energySharing): JsonResponse
    {
        $validated = $request->validate([
            'energy_delivered_kwh' => 'required|numeric|min:0|max:' . $energySharing->energy_amount_kwh,
            'quality_score' => 'nullable|numeric|between:1,5',
            'delivery_efficiency' => 'nullable|numeric|between:0,100',
        ]);

        $energySharing->update([
            'status' => 'completed',
            'completed_at' => now(),
            'energy_delivered_kwh' => $validated['energy_delivered_kwh'],
            'energy_remaining_kwh' => $energySharing->energy_amount_kwh - $validated['energy_delivered_kwh'],
            'quality_score' => $validated['quality_score'] ?? null,
            'delivery_efficiency' => $validated['delivery_efficiency'] ?? null,
            'payment_status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Intercambio de energía completado exitosamente',
            'data' => $energySharing
        ]);
    }

    public function mySharings(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = EnergySharing::where('provider_user_id', $user->id)
            ->orWhere('consumer_user_id', $user->id);

        $sharings = $query->with(['providerUser', 'consumerUser'])->get();

        return response()->json([
            'data' => $sharings,
            'summary' => [
                'total_sharings' => $sharings->count(),
                'as_provider' => $sharings->where('provider_user_id', $user->id)->count(),
                'as_consumer' => $sharings->where('consumer_user_id', $user->id)->count(),
                'active_sharings' => $sharings->where('status', 'active')->count(),
                'completed_sharings' => $sharings->where('status', 'completed')->count(),
            ]
        ]);
    }

    public function rate(Request $request, EnergySharing $energySharing): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|numeric|between:1,5',
            'feedback' => 'nullable|string|max:1000',
            'would_repeat' => 'boolean'
        ]);

        if ($energySharing->provider_user_id === $request->user()->id) {
            $energySharing->update([
                'consumer_rating' => $validated['rating'],
                'consumer_feedback' => $validated['feedback'],
            ]);
        } elseif ($energySharing->consumer_user_id === $request->user()->id) {
            $energySharing->update([
                'provider_rating' => $validated['rating'],
                'provider_feedback' => $validated['feedback'],
            ]);
        }

        return response()->json([
            'message' => 'Calificación enviada exitosamente'
        ]);
    }
}
