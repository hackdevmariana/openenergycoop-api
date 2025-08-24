<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CarbonCredit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Carbon Credits",
 *     description="Gestión de créditos de carbono"
 * )
 */
class CarbonCreditController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CarbonCredit::with(['user', 'provider']);

        // Filtros
        if ($request->filled('credit_type')) {
            $query->where('credit_type', $request->credit_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('project_country')) {
            $query->where('project_country', $request->project_country);
        }

        if ($request->filled('project_type')) {
            $query->where('project_type', 'like', "%{$request->project_type}%");
        }

        if ($request->filled('vintage_year')) {
            $query->whereYear('vintage_year', $request->vintage_year);
        }

        // Filtro por créditos disponibles
        if ($request->filled('available_only') && $request->boolean('available_only')) {
            $query->where('available_credits', '>', 0);
        }

        // Filtro por adicionalidad
        if ($request->filled('additionality_demonstrated')) {
            $query->where('additionality_demonstrated', $request->boolean('additionality_demonstrated'));
        }

        // Búsqueda por texto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('credit_id', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('registry_id', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // Filtros de precio
        if ($request->filled('min_price')) {
            $query->where('current_market_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('current_market_price', '<=', $request->max_price);
        }

        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $credits = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $credits,
            'message' => 'Créditos de carbono obtenidos exitosamente'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'provider_id' => 'nullable|exists:providers,id',
            'credit_id' => 'required|string|unique:carbon_credits,credit_id',
            'credit_type' => 'required|in:vcs,gold_standard,cdm,vcu,cer,rgu,custom',
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string',
            'project_type' => 'required|string|max:255',
            'project_country' => 'required|string|max:255',
            'project_location' => 'required|string|max:255',
            'total_credits' => 'required|numeric|min:0',
            'available_credits' => 'required|numeric|min:0',
            'vintage_year' => 'required|date',
            'credit_period_start' => 'required|date',
            'credit_period_end' => 'required|date|after:credit_period_start',
            'purchase_price_per_credit' => 'nullable|numeric|min:0',
            'current_market_price' => 'nullable|numeric|min:0',
            'additionality_demonstrated' => 'nullable|boolean',
            'methodology' => 'nullable|string|max:255',
            'verifier_name' => 'nullable|string|max:255',
        ]);

        $credit = CarbonCredit::create($validatedData);
        $credit->load(['user', 'provider']);

        return response()->json([
            'success' => true,
            'data' => $credit,
            'message' => 'Crédito de carbono creado exitosamente'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CarbonCredit $carbonCredit): JsonResponse
    {
        $carbonCredit->load(['user', 'provider']);

        return response()->json([
            'success' => true,
            'data' => $carbonCredit,
            'message' => 'Crédito de carbono obtenido exitosamente'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CarbonCredit $carbonCredit): JsonResponse
    {
        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'provider_id' => 'nullable|exists:providers,id',
            'credit_id' => [
                'sometimes',
                'string',
                Rule::unique('carbon_credits')->ignore($carbonCredit->id)
            ],
            'credit_type' => 'sometimes|in:vcs,gold_standard,cdm,vcu,cer,rgu,custom',
            'project_name' => 'sometimes|string|max:255',
            'project_description' => 'nullable|string',
            'project_type' => 'sometimes|string|max:255',
            'project_country' => 'sometimes|string|max:255',
            'project_location' => 'sometimes|string|max:255',
            'total_credits' => 'sometimes|numeric|min:0',
            'available_credits' => 'sometimes|numeric|min:0',
            'retired_credits' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:pending,verified,issued,available,retired,cancelled,expired',
            'vintage_year' => 'sometimes|date',
            'credit_period_start' => 'sometimes|date',
            'credit_period_end' => 'sometimes|date|after:credit_period_start',
            'purchase_price_per_credit' => 'nullable|numeric|min:0',
            'current_market_price' => 'nullable|numeric|min:0',
            'additionality_demonstrated' => 'nullable|boolean',
            'methodology' => 'nullable|string|max:255',
            'verifier_name' => 'nullable|string|max:255',
        ]);

        $carbonCredit->update($validatedData);
        $carbonCredit->load(['user', 'provider']);

        return response()->json([
            'success' => true,
            'data' => $carbonCredit,
            'message' => 'Crédito de carbono actualizado exitosamente'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CarbonCredit $carbonCredit): JsonResponse
    {
        if ($carbonCredit->status === 'verified' || $carbonCredit->status === 'available') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un crédito verificado o disponible'
            ], 422);
        }

        $carbonCredit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Crédito de carbono eliminado exitosamente'
        ]);
    }

    /**
     * Verificar crédito de carbono
     */
    public function verify(CarbonCredit $carbonCredit): JsonResponse
    {
        if ($carbonCredit->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden verificar créditos pendientes'
            ], 422);
        }

        $carbonCredit->update([
            'status' => 'verified',
            'verification_date' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => $carbonCredit,
            'message' => 'Crédito de carbono verificado exitosamente'
        ]);
    }

    /**
     * Retirar créditos de carbono
     */
    public function retire(Request $request, CarbonCredit $carbonCredit): JsonResponse
    {
        $validatedData = $request->validate([
            'credits_to_retire' => 'required|numeric|min:0.01',
            'retirement_reason' => 'required|string|max:1000'
        ]);

        if ($carbonCredit->available_credits < $validatedData['credits_to_retire']) {
            return response()->json([
                'success' => false,
                'message' => 'No hay suficientes créditos disponibles para retirar'
            ], 422);
        }

        $carbonCredit->update([
            'available_credits' => $carbonCredit->available_credits - $validatedData['credits_to_retire'],
            'retired_credits' => $carbonCredit->retired_credits + $validatedData['credits_to_retire'],
            'retirement_reason' => $validatedData['retirement_reason'],
            'retirement_date' => now(),
            'retired_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'data' => $carbonCredit,
            'message' => 'Créditos retirados exitosamente'
        ]);
    }

    /**
     * Transferir créditos de carbono
     */
    public function transfer(Request $request, CarbonCredit $carbonCredit): JsonResponse
    {
        $validatedData = $request->validate([
            'recipient_user_id' => 'required|exists:users,id',
            'credits_to_transfer' => 'required|numeric|min:0.01',
            'transfer_price' => 'nullable|numeric|min:0',
            'transfer_notes' => 'nullable|string|max:500'
        ]);

        if ($carbonCredit->available_credits < $validatedData['credits_to_transfer']) {
            return response()->json([
                'success' => false,
                'message' => 'No hay suficientes créditos disponibles para transferir'
            ], 422);
        }

        // Crear un nuevo registro para el destinatario
        $newCredit = $carbonCredit->replicate();
        $newCredit->user_id = $validatedData['recipient_user_id'];
        $newCredit->credit_id = $carbonCredit->credit_id . '-T' . time();
        $newCredit->total_credits = $validatedData['credits_to_transfer'];
        $newCredit->available_credits = $validatedData['credits_to_transfer'];
        $newCredit->retired_credits = 0;
        $newCredit->transferred_credits = 0;
        $newCredit->original_owner_id = $carbonCredit->user_id;
        $newCredit->last_transfer_date = now();
        $newCredit->save();

        // Actualizar el crédito original
        $carbonCredit->update([
            'available_credits' => $carbonCredit->available_credits - $validatedData['credits_to_transfer'],
            'transferred_credits' => $carbonCredit->transferred_credits + $validatedData['credits_to_transfer'],
            'last_transfer_date' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'original_credit' => $carbonCredit,
                'new_credit' => $newCredit
            ],
            'message' => 'Créditos transferidos exitosamente'
        ]);
    }

    /**
     * Obtener créditos disponibles para compra
     */
    public function marketplace(Request $request): JsonResponse
    {
        $query = CarbonCredit::where('status', 'available')
            ->where('available_credits', '>', 0)
            ->with(['user', 'provider']);

        // Filtros específicos del marketplace
        if ($request->filled('credit_type')) {
            $query->where('credit_type', $request->credit_type);
        }

        if ($request->filled('project_country')) {
            $query->where('project_country', $request->project_country);
        }

        if ($request->filled('min_price')) {
            $query->where('current_market_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('current_market_price', '<=', $request->max_price);
        }

        if ($request->filled('additionality_only') && $request->boolean('additionality_only')) {
            $query->where('additionality_demonstrated', true);
        }

        $credits = $query->orderBy('current_market_price', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $credits,
            'message' => 'Créditos disponibles en marketplace obtenidos exitosamente'
        ]);
    }

    /**
     * Obtener mis créditos de carbono
     */
    public function myCredits(Request $request): JsonResponse
    {
        $credits = CarbonCredit::where('user_id', Auth::id())
            ->with(['provider'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $credits,
            'message' => 'Créditos del usuario obtenidos exitosamente'
        ]);
    }

    /**
     * Obtener estadísticas de créditos de carbono
     */
    public function analytics(): JsonResponse
    {
        $analytics = [
            'total_credits' => CarbonCredit::count(),
            'credits_by_status' => CarbonCredit::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'credits_by_type' => CarbonCredit::select('credit_type', DB::raw('count(*) as count'))
                ->groupBy('credit_type')
                ->pluck('count', 'credit_type'),
            'total_available_credits' => CarbonCredit::sum('available_credits'),
            'total_retired_credits' => CarbonCredit::sum('retired_credits'),
            'credits_by_country' => CarbonCredit::select('project_country', DB::raw('count(*) as count'))
                ->groupBy('project_country')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'project_country'),
            'average_market_price' => CarbonCredit::where('status', 'available')
                ->whereNotNull('current_market_price')
                ->avg('current_market_price'),
            'verified_credits_percentage' => (CarbonCredit::where('status', 'verified')->count() / 
                                           max(CarbonCredit::count(), 1)) * 100,
            'additionality_percentage' => (CarbonCredit::where('additionality_demonstrated', true)->count() / 
                                        max(CarbonCredit::count(), 1)) * 100
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
            'message' => 'Análisis de créditos de carbono obtenido exitosamente'
        ]);
    }

    /**
     * Obtener trazabilidad de un crédito
     */
    public function traceability(CarbonCredit $carbonCredit): JsonResponse
    {
        $traceability = [
            'credit_info' => [
                'credit_id' => $carbonCredit->credit_id,
                'registry_id' => $carbonCredit->registry_id,
                'serial_number' => $carbonCredit->serial_number,
                'blockchain_hash' => $carbonCredit->blockchain_hash
            ],
            'project_info' => [
                'project_name' => $carbonCredit->project_name,
                'project_type' => $carbonCredit->project_type,
                'project_location' => $carbonCredit->project_location,
                'project_country' => $carbonCredit->project_country
            ],
            'verification_info' => [
                'verifier_name' => $carbonCredit->verifier_name,
                'verification_date' => $carbonCredit->verification_date,
                'additionality_demonstrated' => $carbonCredit->additionality_demonstrated,
                'methodology' => $carbonCredit->methodology
            ],
            'ownership_chain' => [
                'current_owner' => $carbonCredit->user->name ?? 'N/A',
                'original_owner' => $carbonCredit->originalOwner->name ?? 'N/A',
                'last_transfer_date' => $carbonCredit->last_transfer_date,
                'transaction_history' => $carbonCredit->transaction_history
            ],
            'impact_metrics' => [
                'total_credits' => $carbonCredit->total_credits,
                'available_credits' => $carbonCredit->available_credits,
                'retired_credits' => $carbonCredit->retired_credits,
                'actual_co2_reduced' => $carbonCredit->actual_co2_reduced,
                'retirement_reason' => $carbonCredit->retirement_reason
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $traceability,
            'message' => 'Trazabilidad del crédito obtenida exitosamente'
        ]);
    }
}