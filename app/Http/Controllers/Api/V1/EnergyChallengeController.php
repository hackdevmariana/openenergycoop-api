<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyChallenge;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnergyChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergyChallenge::query();

            // Filtros
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('reward_type')) {
                $query->where('reward_type', $request->reward_type);
            }

            if ($request->filled('status')) {
                $status = $request->status;
                $now = now();
                
                switch ($status) {
                    case 'active':
                        $query->where('starts_at', '<=', $now)
                              ->where('ends_at', '>=', $now)
                              ->where('is_active', true);
                        break;
                    case 'upcoming':
                        $query->where('starts_at', '>', $now);
                        break;
                    case 'completed':
                        $query->where('ends_at', '<', $now);
                        break;
                    case 'draft':
                        $query->where('starts_at', '>', $now);
                        break;
                }
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $challenges = $query->paginate($request->get('per_page', 12));

            return response()->json([
                'success' => true,
                'data' => $challenges,
                'message' => 'Desafíos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API EnergyChallenge index: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los desafíos'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:individual,colectivo',
            'goal_kwh' => 'required|numeric|min:0.01',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'reward_type' => 'required|in:symbolic,energy_donation,badge',
            'reward_details' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $challenge = EnergyChallenge::create($request->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $challenge,
                'message' => 'Desafío creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en API EnergyChallenge store: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el desafío'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EnergyChallenge $energyChallenge): JsonResponse
    {
        try {
            $energyChallenge->load(['userProgress.user', 'participants']);
            
            return response()->json([
                'success' => true,
                'data' => $energyChallenge,
                'message' => 'Desafío obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API EnergyChallenge show: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el desafío'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EnergyChallenge $energyChallenge): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:individual,colectivo',
            'goal_kwh' => 'sometimes|required|numeric|min:0.01',
            'starts_at' => 'sometimes|required|date',
            'ends_at' => 'sometimes|required|date|after:starts_at',
            'reward_type' => 'sometimes|required|in:symbolic,energy_donation,badge',
            'reward_details' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $energyChallenge->update($request->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $energyChallenge->fresh(),
                'message' => 'Desafío actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en API EnergyChallenge update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el desafío'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EnergyChallenge $energyChallenge): JsonResponse
    {
        try {
            DB::beginTransaction();

            $energyChallenge->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Desafío eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en API EnergyChallenge destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el desafío'
            ], 500);
        }
    }

    /**
     * Obtener desafíos activos
     */
    public function active(): JsonResponse
    {
        try {
            $challenges = EnergyChallenge::active()->paginate(12);

            return response()->json([
                'success' => true,
                'data' => $challenges,
                'message' => 'Desafíos activos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API EnergyChallenge active: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener desafíos activos'
            ], 500);
        }
    }

    /**
     * Obtener desafíos próximos
     */
    public function upcoming(): JsonResponse
    {
        try {
            $challenges = EnergyChallenge::upcoming()->paginate(12);

            return response()->json([
                'success' => true,
                'data' => $challenges,
                'message' => 'Desafíos próximos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API EnergyChallenge upcoming: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener desafíos próximos'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas del desafío
     */
    public function statistics(EnergyChallenge $energyChallenge): JsonResponse
    {
        try {
            $energyChallenge->load(['userProgress.user']);
            
            $stats = [
                'total_participants' => $energyChallenge->total_participants,
                'average_progress' => $energyChallenge->average_progress,
                'progress_percentage' => $energyChallenge->progress_percentage,
                'days_remaining' => $energyChallenge->hasEnded() ? 0 : now()->diffInDays($energyChallenge->ends_at),
                'top_participants' => $energyChallenge->userProgress()
                    ->with('user')
                    ->orderBy('progress_kwh', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API EnergyChallenge statistics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }
}
