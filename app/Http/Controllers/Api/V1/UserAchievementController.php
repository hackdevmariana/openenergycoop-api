<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserAchievementResource;
use App\Models\UserAchievement;
use App\Models\Achievement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="User Achievements",
 *     description="API Endpoints para la gestión de logros de usuario"
 * )
 */
class UserAchievementController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user-achievements",
     *     summary="Listar logros de usuarios",
     *     description="Retorna una lista paginada de logros obtenidos por usuarios",
     *     operationId="getUserAchievements",
     *     tags={"User Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filtrar por usuario específico",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="achievement_type",
     *         in="query",
     *         description="Filtrar por tipo de achievement",
     *         required=false,
     *         @OA\Schema(type="string", enum={"energy", "participation", "community", "milestone"})
     *     ),
     *     @OA\Parameter(
     *         name="reward_granted",
     *         in="query",
     *         description="Filtrar por recompensa otorgada",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de logros obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserAchievement")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = UserAchievement::with(['user', 'achievement']);

        // Filtrar por usuario específico
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filtrar por tipo de achievement
        if ($request->filled('achievement_type')) {
            $query->whereHas('achievement', function ($q) use ($request) {
                $q->where('type', $request->achievement_type);
            });
        }

        // Filtrar por recompensa otorgada
        if ($request->has('reward_granted')) {
            if ($request->boolean('reward_granted')) {
                $query->rewardGranted();
            } else {
                $query->pendingReward();
            }
        }

        // Ordenar por fecha de obtención más reciente
        $query->orderedByEarnedDate();

        $userAchievements = $query->paginate($request->integer('per_page', 15));

        return UserAchievementResource::collection($userAchievements);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-achievements/{id}",
     *     summary="Obtener un logro de usuario específico",
     *     description="Retorna los detalles de un logro de usuario específico",
     *     operationId="getUserAchievement",
     *     tags={"User Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del logro de usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logro obtenido exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/UserAchievement")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Logro no encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function show(UserAchievement $userAchievement): UserAchievementResource
    {
        $userAchievement->load(['user', 'achievement']);
        
        return new UserAchievementResource($userAchievement);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-achievements/me",
     *     summary="Obtener mis logros",
     *     description="Retorna todos los logros obtenidos por el usuario autenticado",
     *     operationId="getMyUserAchievements",
     *     tags={"User Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mis logros obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserAchievement")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function me(Request $request): AnonymousResourceCollection
    {
        $userAchievements = UserAchievement::byUser($request->user()->id)
            ->with(['achievement'])
            ->orderedByEarnedDate()
            ->paginate($request->integer('per_page', 15));
        
        return UserAchievementResource::collection($userAchievements);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-achievements/me/recent",
     *     summary="Obtener mis logros recientes",
     *     description="Retorna los logros más recientes del usuario autenticado",
     *     operationId="getMyRecentUserAchievements",
     *     tags={"User Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de logros a retornar",
     *         required=false,
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logros recientes obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserAchievement"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function recentMe(Request $request): AnonymousResourceCollection
    {
        $limit = $request->integer('limit', 5);
        
        $recentAchievements = UserAchievement::getRecentByUser($request->user()->id, $limit);
        
        return UserAchievementResource::collection($recentAchievements);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-achievements/me/statistics",
     *     summary="Obtener mis estadísticas de logros",
     *     description="Retorna estadísticas de logros del usuario autenticado",
     *     operationId="getMyUserAchievementStatistics",
     *     tags={"User Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_achievements", type="integer", example=15),
     *                 @OA\Property(property="rewards_granted", type="integer", example=12),
     *                 @OA\Property(property="pending_rewards", type="integer", example=3),
     *                 @OA\Property(
     *                     property="by_type",
     *                     type="object",
     *                     @OA\Property(property="energy", type="integer", example=8),
     *                     @OA\Property(property="participation", type="integer", example=4),
     *                     @OA\Property(property="community", type="integer", example=2),
     *                     @OA\Property(property="milestone", type="integer", example=1)
     *                 ),
     *                 @OA\Property(property="total_points_earned", type="integer", example=2450)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function statisticsMe(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        // Estadísticas básicas
        $stats = UserAchievement::getUserStats($userId);
        
        // Estadísticas por tipo
        $byType = UserAchievement::byUser($userId)
            ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
            ->selectRaw('achievements.type, COUNT(*) as count')
            ->groupBy('achievements.type')
            ->pluck('count', 'type')
            ->toArray();
        
        // Total de puntos ganados
        $totalPoints = UserAchievement::byUser($userId)
            ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
            ->sum('achievements.points_reward');
        
        $stats['by_type'] = [
            'energy' => $byType['energy'] ?? 0,
            'participation' => $byType['participation'] ?? 0,
            'community' => $byType['community'] ?? 0,
            'milestone' => $byType['milestone'] ?? 0,
        ];
        $stats['total_points_earned'] = $totalPoints;
        
        return response()->json(['data' => $stats]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user-achievements/{id}/grant-reward",
     *     summary="Otorgar recompensa por logro",
     *     description="Marca la recompensa como otorgada para un logro específico",
     *     operationId="grantUserAchievementReward",
     *     tags={"User Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del logro de usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recompensa otorgada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Recompensa otorgada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserAchievement")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="La recompensa ya fue otorgada"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Logro no encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso denegado"
     *     )
     * )
     */
    public function grantReward(UserAchievement $userAchievement): JsonResponse
    {
        // Solo administradores pueden otorgar recompensas
        $this->authorize('update', $userAchievement);
        
        if ($userAchievement->reward_granted) {
            return response()->json([
                'message' => 'La recompensa ya fue otorgada',
                'error' => 'reward_already_granted'
            ], 400);
        }

        $userAchievement->grantReward();
        $userAchievement->load(['user', 'achievement']);

        return response()->json([
            'message' => 'Recompensa otorgada exitosamente',
            'data' => new UserAchievementResource($userAchievement)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-achievements/leaderboard",
     *     summary="Obtener ranking de usuarios con más logros",
     *     description="Retorna los usuarios que han obtenido más logros",
     *     operationId="getUserAchievementLeaderboard",
     *     tags={"User Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de usuarios a retornar",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ranking obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="user_id", type="integer", example=5),
     *                     @OA\Property(property="user_name", type="string", example="María García"),
     *                     @OA\Property(property="achievements_count", type="integer", example=25),
     *                     @OA\Property(property="total_points", type="integer", example=3500)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 10);

        $leaderboard = UserAchievement::select('user_id')
            ->selectRaw('COUNT(*) as achievements_count')
            ->selectRaw('SUM(achievements.points_reward) as total_points')
            ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
            ->join('users', 'user_achievements.user_id', '=', 'users.id')
            ->groupBy('user_id')
            ->orderByDesc('achievements_count')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->with('user:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'user_name' => $item->user->name,
                    'achievements_count' => $item->achievements_count,
                    'total_points' => $item->total_points,
                ];
            });

        return response()->json(['data' => $leaderboard]);
    }
}
