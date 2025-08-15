<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserProfile\UpdateUserProfileRequest;
use App\Http\Resources\Api\V1\UserProfileResource;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="User Profiles",
 *     description="API Endpoints para la gestión de perfiles de usuario"
 * )
 */
class UserProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user-profiles",
     *     summary="Listar perfiles de usuario",
     *     description="Retorna una lista paginada de perfiles de usuario",
     *     operationId="getUserProfiles",
     *     tags={"User Profiles"},
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
     *         name="organization_id",
     *         in="query",
     *         description="Filtrar por organización",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="show_in_rankings",
     *         in="query",
     *         description="Solo mostrar perfiles visibles en rankings",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="municipality_id",
     *         in="query",
     *         description="Filtrar por municipio",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de perfiles obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserProfile")),
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
        $query = UserProfile::with(['user', 'organization']);

        // Filtrar por organización
        if ($request->filled('organization_id')) {
            $query->byOrganization($request->organization_id);
        }

        // Filtrar por visibilidad en rankings
        if ($request->has('show_in_rankings')) {
            if ($request->boolean('show_in_rankings')) {
                $query->inRankings();
            } else {
                $query->where('show_in_rankings', false);
            }
        }

        // Filtrar por municipio
        if ($request->filled('municipality_id')) {
            $query->byMunicipality($request->municipality_id);
        }

        // Ordenar por puntos por defecto
        $query->orderedByPoints();

        $profiles = $query->paginate($request->integer('per_page', 15));

        return UserProfileResource::collection($profiles);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-profiles/{id}",
     *     summary="Obtener un perfil de usuario específico",
     *     description="Retorna los detalles de un perfil de usuario específico",
     *     operationId="getUserProfile",
     *     tags={"User Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del perfil de usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil obtenido exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfile")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Perfil no encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function show(UserProfile $userProfile): UserProfileResource
    {
        $userProfile->load(['user', 'organization']);
        
        return new UserProfileResource($userProfile);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-profiles/me",
     *     summary="Obtener mi perfil de usuario",
     *     description="Retorna el perfil del usuario autenticado",
     *     operationId="getMyUserProfile",
     *     tags={"User Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Mi perfil obtenido exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfile")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Perfil no encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function me(Request $request): UserProfileResource
    {
        $profile = UserProfile::where('user_id', $request->user()->id)
            ->with(['user', 'organization'])
            ->firstOrFail();
        
        return new UserProfileResource($profile);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user-profiles/me",
     *     summary="Actualizar mi perfil de usuario",
     *     description="Actualiza el perfil del usuario autenticado",
     *     operationId="updateMyUserProfile",
     *     tags={"User Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil actualizado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfile")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Perfil no encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function updateMe(UpdateUserProfileRequest $request): UserProfileResource
    {
        $profile = UserProfile::where('user_id', $request->user()->id)->firstOrFail();
        
        $profile->update($request->validated());
        $profile->load(['user', 'organization']);
        
        return new UserProfileResource($profile);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-profiles/rankings/organization/{organizationId}",
     *     summary="Obtener ranking de usuarios por organización",
     *     description="Retorna el ranking de usuarios de una organización específica ordenado por puntos",
     *     operationId="getOrganizationRanking",
     *     tags={"User Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="organizationId",
     *         in="path",
     *         description="ID de la organización",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de usuarios a retornar",
     *         required=false,
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ranking obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserProfile"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function organizationRanking(Request $request, int $organizationId): AnonymousResourceCollection
    {
        $limit = $request->integer('limit', 50);

        $profiles = UserProfile::byOrganization($organizationId)
            ->inRankings()
            ->with(['user'])
            ->orderedByPoints()
            ->limit($limit)
            ->get();

        return UserProfileResource::collection($profiles);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-profiles/rankings/municipality/{municipalityId}",
     *     summary="Obtener ranking de usuarios por municipio",
     *     description="Retorna el ranking de usuarios de un municipio específico ordenado por puntos",
     *     operationId="getMunicipalityRanking",
     *     tags={"User Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="municipalityId",
     *         in="path",
     *         description="ID del municipio",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de usuarios a retornar",
     *         required=false,
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ranking obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserProfile"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function municipalityRanking(Request $request, string $municipalityId): AnonymousResourceCollection
    {
        $limit = $request->integer('limit', 50);

        $profiles = UserProfile::byMunicipality($municipalityId)
            ->inRankings()
            ->with(['user', 'organization'])
            ->orderedByPoints()
            ->limit($limit)
            ->get();

        return UserProfileResource::collection($profiles);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user-profiles/statistics",
     *     summary="Obtener estadísticas generales de perfiles",
     *     description="Retorna estadísticas generales sobre los perfiles de usuario",
     *     operationId="getUserProfileStatistics",
     *     tags={"User Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_profiles", type="integer", example=1250),
     *                 @OA\Property(property="completed_profiles", type="integer", example=980),
     *                 @OA\Property(property="profiles_in_rankings", type="integer", example=1100),
     *                 @OA\Property(property="total_points", type="integer", example=125000),
     *                 @OA\Property(property="total_kwh_produced", type="number", format="float", example=45678.90),
     *                 @OA\Property(property="total_co2_avoided", type="number", format="float", example=23456.78),
     *                 @OA\Property(property="average_points_per_user", type="number", format="float", example=100.0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_profiles' => UserProfile::count(),
            'completed_profiles' => UserProfile::completed()->count(),
            'profiles_in_rankings' => UserProfile::inRankings()->count(),
            'total_points' => UserProfile::sum('points_total'),
            'total_kwh_produced' => UserProfile::sum('kwh_produced_total'),
            'total_co2_avoided' => UserProfile::sum('co2_avoided_total'),
            'average_points_per_user' => UserProfile::avg('points_total'),
        ];

        return response()->json(['data' => $stats]);
    }
}
