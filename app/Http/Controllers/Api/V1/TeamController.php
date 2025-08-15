<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeamResource;
use App\Http\Requests\Api\V1\Team\StoreTeamRequest;
use App\Http\Requests\Api\V1\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Teams',
    description: 'Endpoints para gestión de equipos'
)]
class TeamController extends Controller
{
    #[OA\Get(
        path: '/api/v1/teams',
        summary: 'Listar equipos',
        description: 'Obtiene una lista paginada de equipos con filtros opcionales',
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'organization_id',
                in: 'query',
                description: 'Filtrar por ID de organización',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'is_open',
                in: 'query',
                description: 'Filtrar por equipos abiertos (true) o cerrados (false)',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'has_space',
                in: 'query',
                description: 'Filtrar equipos con espacio disponible',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Buscar en nombre y descripción',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Número de elementos por página',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de equipos obtenida exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Team')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Team::class);

        $query = Team::with(['organization', 'createdBy'])
                    ->withCount('activeMemberships');

        // Filtros
        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->has('is_open')) {
            $query->where('is_open', $request->boolean('is_open'));
        }

        if ($request->boolean('has_space')) {
            $query->where(function ($q) {
                $q->whereNull('max_members')
                  ->orWhereColumn('max_members', '>', function ($subQuery) {
                      $subQuery->selectRaw('COUNT(*)')
                               ->from('team_memberships')
                               ->whereColumn('team_id', 'teams.id')
                               ->whereNull('left_at');
                  });
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $teams = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => TeamResource::collection($teams->items()),
            'links' => [
                'first' => $teams->url(1),
                'last' => $teams->url($teams->lastPage()),
                'prev' => $teams->previousPageUrl(),
                'next' => $teams->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $teams->currentPage(),
                'from' => $teams->firstItem(),
                'last_page' => $teams->lastPage(),
                'per_page' => $teams->perPage(),
                'to' => $teams->lastItem(),
                'total' => $teams->total(),
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/v1/teams',
        summary: 'Crear equipo',
        description: 'Crea un nuevo equipo',
        tags: ['Teams'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreTeamRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Equipo creado exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Team')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'No autorizado'),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $this->authorize('create', Team::class);

        $team = Team::create($request->validated());

        return response()->json([
            'data' => new TeamResource($team->load(['organization', 'createdBy']))
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/teams/{id}',
        summary: 'Mostrar equipo',
        description: 'Obtiene los detalles de un equipo específico',
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalles del equipo obtenidos exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Team')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Equipo no encontrado')
        ]
    )]
    public function show(Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        return response()->json([
            'data' => new TeamResource($team->load([
                'organization', 
                'createdBy', 
                'activeMemberships.user',
                'challengeProgress.challenge'
            ]))
        ]);
    }

    #[OA\Put(
        path: '/api/v1/teams/{id}',
        summary: 'Actualizar equipo',
        description: 'Actualiza los datos de un equipo existente',
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateTeamRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Equipo actualizado exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Team')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'No autorizado'),
            new OA\Response(response: 404, description: 'Equipo no encontrado'),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $team->update($request->validated());

        return response()->json([
            'data' => new TeamResource($team->load(['organization', 'createdBy']))
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/teams/{id}',
        summary: 'Eliminar equipo',
        description: 'Elimina un equipo existente',
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Equipo eliminado exitosamente'
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'No autorizado'),
            new OA\Response(response: 404, description: 'Equipo no encontrado')
        ]
    )]
    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $team->delete();

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: '/api/v1/teams/{id}/join',
        summary: 'Unirse a equipo',
        description: 'Permite al usuario autenticado unirse a un equipo abierto',
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario unido al equipo exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/TeamMembership')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'No se puede unir al equipo'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Equipo no encontrado')
        ]
    )]
    public function join(Team $team): JsonResponse
    {
        $user = auth()->user();

        if (!$team->canJoin($user)) {
            return response()->json([
                'message' => 'No puedes unirte a este equipo'
            ], 400);
        }

        $membership = $team->addMember($user);

        return response()->json([
            'message' => 'Te has unido al equipo exitosamente',
            'data' => $membership->load(['team', 'user'])
        ]);
    }

    #[OA\Post(
        path: '/api/v1/teams/{id}/leave',
        summary: 'Salir del equipo',
        description: 'Permite al usuario autenticado salir de un equipo',
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario salió del equipo exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'No eres miembro del equipo'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Equipo no encontrado')
        ]
    )]
    public function leave(Team $team): JsonResponse
    {
        $user = auth()->user();

        if (!$team->hasMember($user)) {
            return response()->json([
                'message' => 'No eres miembro de este equipo'
            ], 400);
        }

        $team->removeMember($user);

        return response()->json([
            'message' => 'Has salido del equipo exitosamente'
        ]);
    }

    #[OA\Get(
        path: '/api/v1/teams/{id}/members',
        summary: 'Listar miembros del equipo',
        description: 'Obtiene la lista de miembros activos de un equipo',
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'role',
                in: 'query',
                description: 'Filtrar por rol',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['admin', 'moderator', 'member'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de miembros obtenida exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TeamMembership'))
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Equipo no encontrado')
        ]
    )]
    public function members(Request $request, Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        $query = $team->activeMemberships()->with('user');

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $members = $query->orderBy('joined_at', 'desc')->get();

        return response()->json([
            'data' => $members
        ]);
    }

    #[OA\Get(
        path: '/api/v1/teams/recommendations',
        summary: 'Equipos recomendados',
        description: 'Obtiene equipos recomendados para el usuario autenticado',
        tags: ['Teams'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Número máximo de recomendaciones',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 5)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recomendaciones obtenidas exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Team'))
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado')
        ]
    )]
    public function recommendations(Request $request): JsonResponse
    {
        $user = auth()->user();
        $limit = $request->get('limit', 5);

        $recommendations = Team::getRecommendedForUser($user, $limit);

        return response()->json([
            'data' => TeamResource::collection($recommendations)
        ]);
    }

    #[OA\Get(
        path: '/api/v1/teams/my-teams',
        summary: 'Mis equipos',
        description: 'Obtiene los equipos del usuario autenticado',
        tags: ['Teams'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Equipos del usuario obtenidos exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Team'))
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado')
        ]
    )]
    public function myTeams(): JsonResponse
    {
        $user = auth()->user();

        $teams = Team::whereHas('activeMemberships', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['organization', 'createdBy'])
          ->withCount('activeMemberships')
          ->get();

        return response()->json([
            'data' => TeamResource::collection($teams)
        ]);
    }
}
