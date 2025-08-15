<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AchievementResource;
use App\Models\Achievement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Achievements",
 *     description="API Endpoints para la gestión de logros y achievements"
 * )
 */
class AchievementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/achievements",
     *     summary="Listar todos los achievements",
     *     description="Retorna una lista paginada de todos los achievements disponibles",
     *     operationId="getAchievements",
     *     tags={"Achievements"},
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
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo de achievement",
     *         required=false,
     *         @OA\Schema(type="string", enum={"energy", "participation", "community", "milestone"})
     *     ),
     *     @OA\Parameter(
     *         name="active_only",
     *         in="query",
     *         description="Solo mostrar achievements activos",
     *         required=false,
     *         @OA\Schema(type="boolean", default=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de achievements obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Achievement"))
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
        $query = Achievement::query();

        // Filtrar por tipo si se proporciona
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filtrar solo activos por defecto
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        // Cargar conteo de usuarios que han obtenido cada achievement
        $query->withCount('userAchievements');

        // Ordenar
        $query->ordered();

        $achievements = $query->paginate($request->integer('per_page', 15));

        return AchievementResource::collection($achievements);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/achievements/{id}",
     *     summary="Obtener un achievement específico",
     *     description="Retorna los detalles de un achievement específico",
     *     operationId="getAchievement",
     *     tags={"Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del achievement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Achievement obtenido exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Achievement")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Achievement no encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function show(Achievement $achievement): AchievementResource
    {
        $achievement->loadCount('userAchievements');
        
        return new AchievementResource($achievement);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/achievements/types",
     *     summary="Obtener tipos de achievements disponibles",
     *     description="Retorna una lista de los tipos de achievements disponibles en el sistema",
     *     operationId="getAchievementTypes",
     *     tags={"Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Tipos de achievements obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="energy", type="string", example="Energía"),
     *                 @OA\Property(property="participation", type="string", example="Participación"),
     *                 @OA\Property(property="community", type="string", example="Comunidad"),
     *                 @OA\Property(property="milestone", type="string", example="Hito")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'data' => [
                'energy' => 'Energía',
                'participation' => 'Participación',
                'community' => 'Comunidad',
                'milestone' => 'Hito',
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/achievements/leaderboard",
     *     summary="Obtener ranking de achievements más populares",
     *     description="Retorna los achievements más obtenidos por los usuarios",
     *     operationId="getAchievementLeaderboard",
     *     tags={"Achievements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de achievements a retornar",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ranking obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Achievement"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function leaderboard(Request $request): AnonymousResourceCollection
    {
        $limit = $request->integer('limit', 10);

        $achievements = Achievement::active()
            ->withCount('userAchievements')
            ->orderBy('user_achievements_count', 'desc')
            ->limit($limit)
            ->get();

        return AchievementResource::collection($achievements);
    }
}
