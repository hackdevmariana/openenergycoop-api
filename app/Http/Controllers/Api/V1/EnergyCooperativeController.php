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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
