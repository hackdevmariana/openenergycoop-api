<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserChallengeProgress;
use App\Models\EnergyChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserChallengeProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = UserChallengeProgress::with(['user', 'challenge']);

            // Filtros
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('challenge_id')) {
                $query->where('challenge_id', $request->challenge_id);
            }

            if ($request->filled('status')) {
                if ($request->status === 'completed') {
                    $query->whereNotNull('completed_at');
                } else {
                    $query->whereNull('completed_at');
                }
            }

            if ($request->filled('progress_range')) {
                $min = $request->get('min_progress');
                $max = $request->get('max_progress');
                
                if ($min !== null) {
                    $query->where('progress_kwh', '>=', $min);
                }
                if ($max !== null) {
                    $query->where('progress_kwh', '<=', $max);
                }
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $progressList = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $progressList,
                'message' => 'Progresos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API UserChallengeProgress index: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los progresos'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'challenge_id' => 'required|exists:energy_challenges,id',
            'progress_kwh' => 'required|numeric|min:0',
            'completed_at' => 'nullable|date',
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

            // Verificar que no exista ya un progreso para este usuario y desafío
            $existingProgress = UserChallengeProgress::where('user_id', $request->user_id)
                ->where('challenge_id', $request->challenge_id)
                ->first();

            if ($existingProgress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un progreso para este usuario en este desafío'
                ], 409);
            }

            $progress = UserChallengeProgress::create($request->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $progress->load(['user', 'challenge']),
                'message' => 'Progreso creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en API UserChallengeProgress store: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el progreso'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserChallengeProgress $userChallengeProgress): JsonResponse
    {
        try {
            $userChallengeProgress->load(['user', 'challenge']);
            
            return response()->json([
                'success' => true,
                'data' => $userChallengeProgress,
                'message' => 'Progreso obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API UserChallengeProgress show: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el progreso'
            ], 500);
            }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserChallengeProgress $userChallengeProgress): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|required|exists:users,id',
            'challenge_id' => 'sometimes|required|exists:energy_challenges,id',
            'progress_kwh' => 'sometimes|required|numeric|min:0',
            'completed_at' => 'nullable|date',
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

            $userChallengeProgress->update($request->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $userChallengeProgress->fresh()->load(['user', 'challenge']),
                'message' => 'Progreso actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en API UserChallengeProgress update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el progreso'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserChallengeProgress $userChallengeProgress): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userChallengeProgress->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progreso eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en API UserChallengeProgress destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el progreso'
            ], 500);
        }
    }

    /**
     * Actualizar progreso de un usuario
     */
    public function updateProgress(Request $request, UserChallengeProgress $userChallengeProgress): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'additional_kwh' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userChallengeProgress->updateProgress($request->additional_kwh);

            return response()->json([
                'success' => true,
                'data' => $userChallengeProgress->fresh()->load(['user', 'challenge']),
                'message' => 'Progreso actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API UserChallengeProgress updateProgress: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el progreso'
            ], 500);
        }
    }

    /**
     * Completar un desafío
     */
    public function complete(UserChallengeProgress $userChallengeProgress): JsonResponse
    {
        try {
            $userChallengeProgress->complete();

            return response()->json([
                'success' => true,
                'data' => $userChallengeProgress->fresh()->load(['user', 'challenge']),
                'message' => 'Desafío completado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API UserChallengeProgress complete: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al completar el desafío'
            ], 500);
        }
    }

    /**
     * Reiniciar progreso
     */
    public function reset(UserChallengeProgress $userChallengeProgress): JsonResponse
    {
        try {
            $userChallengeProgress->reset();

            return response()->json([
                'success' => true,
                'data' => $userChallengeProgress->fresh()->load(['user', 'challenge']),
                'message' => 'Progreso reiniciado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API UserChallengeProgress reset: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al reiniciar el progreso'
            ], 500);
        }
    }

    /**
     * Progreso del usuario autenticado
     */
    public function myProgress(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $query = UserChallengeProgress::where('user_id', $user->id)
                ->with(['challenge']);

            // Filtros
            if ($request->filled('status')) {
                if ($request->status === 'completed') {
                    $query->whereNotNull('completed_at');
                } else {
                    $query->whereNull('completed_at');
                }
            }

            $myProgress = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => $myProgress,
                'message' => 'Mi progreso obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en API UserChallengeProgress myProgress: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mi progreso'
            ], 500);
        }
    }

    /**
     * Unirse a un desafío
     */
    public function joinChallenge(Request $request, EnergyChallenge $challenge): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            DB::beginTransaction();

            // Verificar que no esté ya participando
            $existingProgress = UserChallengeProgress::where('user_id', $user->id)
                ->where('challenge_id', $challenge->id)
                ->first();

            if ($existingProgress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya estás participando en este desafío'
                ], 409);
            }

            // Verificar que el desafío esté activo
            if (!$challenge->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este desafío no está disponible actualmente'
                ], 400);
            }

            $progress = UserChallengeProgress::create([
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'progress_kwh' => 0,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $progress->load(['user', 'challenge']),
                'message' => 'Te has unido al desafío exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en API UserChallengeProgress joinChallenge: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al unirse al desafío'
            ], 500);
        }
    }
}
