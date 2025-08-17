<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyContract;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Energy Contracts",
 *     description="Gestión de contratos energéticos"
 * )
 */
class EnergyContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = EnergyContract::with(['user', 'provider', 'product']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        // Búsqueda por texto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $contracts = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $contracts,
            'message' => 'Contratos energéticos obtenidos exitosamente'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'provider_id' => 'required|exists:providers,id',
            'product_id' => 'required|exists:products,id',
            'contract_number' => 'required|string|unique:energy_contracts,contract_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:supply,generation,storage,hybrid',
            'status' => 'nullable|in:draft,pending,active,suspended,terminated,expired',
            'total_value' => 'required|numeric|min:0',
            'monthly_payment' => 'nullable|numeric|min:0',
            'contracted_power' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'green_energy_percentage' => 'nullable|numeric|min:0|max:100',
            'carbon_neutral' => 'nullable|boolean',
        ]);

        $contract = EnergyContract::create($validatedData);
        $contract->load(['user', 'provider', 'product']);

        return response()->json([
            'success' => true,
            'data' => $contract,
            'message' => 'Contrato energético creado exitosamente'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(EnergyContract $energyContract): JsonResponse
    {
        $energyContract->load(['user', 'provider', 'product']);

        return response()->json([
            'success' => true,
            'data' => $energyContract,
            'message' => 'Contrato energético obtenido exitosamente'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EnergyContract $energyContract): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'provider_id' => 'sometimes|exists:providers,id',
            'product_id' => 'sometimes|exists:products,id',
            'contract_number' => [
                'sometimes',
                'string',
                Rule::unique('energy_contracts')->ignore($energyContract->id)
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:supply,generation,storage,hybrid',
            'status' => 'sometimes|in:draft,pending,active,suspended,terminated,expired',
            'total_value' => 'sometimes|numeric|min:0',
            'monthly_payment' => 'nullable|numeric|min:0',
            'contracted_power' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'green_energy_percentage' => 'nullable|numeric|min:0|max:100',
            'carbon_neutral' => 'nullable|boolean',
        ]);

        $energyContract->update($validatedData);
        $energyContract->load(['user', 'provider', 'product']);

        return response()->json([
            'success' => true,
            'data' => $energyContract,
            'message' => 'Contrato energético actualizado exitosamente'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EnergyContract $energyContract): JsonResponse
    {
        // Verificar que el contrato no esté activo
        if ($energyContract->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un contrato activo.'
            ], 422);
        }

        $energyContract->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contrato energético eliminado exitosamente'
        ]);
    }

    /**
     * Aprobar contrato
     */
    public function approve(EnergyContract $energyContract): JsonResponse
    {
        if ($energyContract->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden aprobar contratos pendientes'
            ], 422);
        }

        $energyContract->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'activation_date' => now()
        ]);

        $energyContract->load(['user', 'provider', 'product']);

        return response()->json([
            'success' => true,
            'data' => $energyContract,
            'message' => 'Contrato aprobado exitosamente'
        ]);
    }

    /**
     * Suspender contrato
     */
    public function suspend(Request $request, EnergyContract $energyContract): JsonResponse
    {
        if ($energyContract->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden suspender contratos activos'
            ], 422);
        }

        $energyContract->update(['status' => 'suspended']);
        $energyContract->load(['user', 'provider', 'product']);

        return response()->json([
            'success' => true,
            'data' => $energyContract,
            'message' => 'Contrato suspendido exitosamente'
        ]);
    }

    /**
     * Mis contratos
     */
    public function myContracts(Request $request): JsonResponse
    {
        $contracts = EnergyContract::where('user_id', Auth::id())
            ->with(['provider', 'product'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $contracts,
            'message' => 'Contratos del usuario obtenidos exitosamente'
        ]);
    }
}
