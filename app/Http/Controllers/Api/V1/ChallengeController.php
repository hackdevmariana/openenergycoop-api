<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ChallengeResource;
use App\Models\Challenge;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Challenges',
    description: 'Endpoints para gestión de desafíos energéticos'
)]
class ChallengeController extends \App\Http\Controllers\Controller
{
    #[OA\Get(
        path: '/api/v1/challenges',
        summary: 'Listar desafíos',
        description: 'Obtiene una lista paginada de desafíos con filtros opcionales',
        tags: ['Challenges'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'query',
                description: 'Filtrar por tipo de desafío',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['individual', 'team', 'organization'])
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'Filtrar por estado temporal',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['current', 'upcoming', 'past'])
            ),
            new OA\Parameter(
                name: 'organization_id',
                in: 'query',
                description: 'Filtrar por ID de organización',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'is_active',
                in: 'query',
                description: 'Filtrar por estado activo',
                required: false,
                schema: new OA\Schema(type: 'boolean')
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
                description: 'Lista de desafíos obtenida exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Challenge')),
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
        $this->authorize('viewAny', Challenge::class);

        $query = Challenge::with(['organization'])
                          ->withCount(['teamProgress', 'teamProgress as completed_teams_count' => function ($q) {
                              $q->whereNotNull('completed_at');
                          }]);

        // Filtros
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filtro por estado temporal
        if ($request->has('status')) {
            switch ($request->status) {
                case 'current':
                    $query->current();
                    break;
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'past':
                    $query->past();
                    break;
            }
        }

        $challenges = $query->orderBy('start_date', 'desc')
                           ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => ChallengeResource::collection($challenges->items()),
            'links' => [
                'first' => $challenges->url(1),
                'last' => $challenges->url($challenges->lastPage()),
                'prev' => $challenges->previousPageUrl(),
                'next' => $challenges->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $challenges->currentPage(),
                'from' => $challenges->firstItem(),
                'last_page' => $challenges->lastPage(),
                'per_page' => $challenges->perPage(),
                'to' => $challenges->lastItem(),
                'total' => $challenges->total(),
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/v1/challenges/{id}',
        summary: 'Mostrar desafío',
        description: 'Obtiene los detalles de un desafío específico',
        tags: ['Challenges'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del desafío',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalles del desafío obtenidos exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Challenge')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Desafío no encontrado')
        ]
    )]
    public function show(Challenge $challenge): JsonResponse
    {
        $this->authorize('view', $challenge);

        return response()->json([
            'data' => new ChallengeResource($challenge->load([
                'organization',
                'teamProgress.team',
                'teamProgress' => function ($query) {
                    $query->orderBy('progress_kwh', 'desc');
                }
            ]))
        ]);
    }

    #[OA\Get(
        path: '/api/v1/challenges/{id}/leaderboard',
        summary: 'Ranking del desafío',
        description: 'Obtiene el ranking de equipos para un desafío específico',
        tags: ['Challenges'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID del desafío',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Número máximo de equipos en el ranking',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ranking obtenido exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'rank', type: 'integer', example: 1),
                                    new OA\Property(property: 'team_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'team_name', type: 'string', example: 'Green Warriors'),
                                    new OA\Property(property: 'progress_kwh', type: 'number', format: 'float', example: 1250.50),
                                    new OA\Property(property: 'progress_percentage', type: 'number', format: 'float', example: 62.53),
                                    new OA\Property(property: 'is_completed', type: 'boolean', example: false),
                                    new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', nullable: true)
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Desafío no encontrado')
        ]
    )]
    public function leaderboard(Request $request, Challenge $challenge): JsonResponse
    {
        $this->authorize('view', $challenge);

        $limit = $request->get('limit', 10);
        $ranking = $challenge->getTeamRanking($limit);

        $data = $ranking->map(function ($progress, $index) {
            return [
                'rank' => $index + 1,
                'team_id' => $progress->team->id,
                'team_name' => $progress->team->name,
                'team_slug' => $progress->team->slug,
                'team_logo' => $progress->team->logo_path,
                'progress_kwh' => $progress->progress_kwh,
                'progress_percentage' => $progress->progress_percentage,
                'is_completed' => $progress->isCompleted(),
                'completed_at' => $progress->completed_at,
                'members_count' => $progress->team->activeMemberships()->count(),
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }

    #[OA\Get(
        path: '/api/v1/challenges/current',
        summary: 'Desafíos actuales',
        description: 'Obtiene los desafíos que están activos actualmente',
        tags: ['Challenges'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'query',
                description: 'Filtrar por tipo de desafío',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['individual', 'team', 'organization'])
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Número máximo de desafíos',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Desafíos actuales obtenidos exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Challenge'))
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado')
        ]
    )]
    public function current(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Challenge::class);

        $query = Challenge::current()
                         ->active()
                         ->with(['organization'])
                         ->withCount(['teamProgress', 'teamProgress as completed_teams_count' => function ($q) {
                             $q->whereNotNull('completed_at');
                         }]);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $challenges = $query->orderBy('end_date', 'asc')
                           ->limit($request->get('limit', 10))
                           ->get();

        return response()->json([
            'data' => ChallengeResource::collection($challenges)
        ]);
    }

    #[OA\Get(
        path: '/api/v1/challenges/recommendations/{teamId}',
        summary: 'Desafíos recomendados para equipo',
        description: 'Obtiene desafíos recomendados para un equipo específico',
        tags: ['Challenges'],
        parameters: [
            new OA\Parameter(
                name: 'teamId',
                in: 'path',
                description: 'ID del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
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
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Challenge'))
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 404, description: 'Equipo no encontrado')
        ]
    )]
    public function recommendations(Request $request, Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        $limit = $request->get('limit', 5);
        $recommendations = Challenge::getRecommendedForTeam($team, $limit);

        return response()->json([
            'data' => ChallengeResource::collection($recommendations)
        ]);
    }

    #[OA\Get(
        path: '/api/v1/challenges/statistics',
        summary: 'Estadísticas de desafíos',
        description: 'Obtiene estadísticas generales de los desafíos',
        tags: ['Challenges'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estadísticas obtenidas exitosamente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total_challenges', type: 'integer', example: 25),
                                new OA\Property(property: 'active_challenges', type: 'integer', example: 8),
                                new OA\Property(property: 'current_challenges', type: 'integer', example: 3),
                                new OA\Property(property: 'completed_challenges', type: 'integer', example: 12),
                                new OA\Property(property: 'total_participants', type: 'integer', example: 150),
                                new OA\Property(property: 'total_kwh_target', type: 'number', format: 'float', example: 50000.00),
                                new OA\Property(property: 'total_kwh_achieved', type: 'number', format: 'float', example: 32500.75),
                                new OA\Property(property: 'completion_rate', type: 'number', format: 'float', example: 65.00),
                                new OA\Property(
                                    property: 'by_type',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'individual', type: 'integer', example: 10),
                                        new OA\Property(property: 'team', type: 'integer', example: 12),
                                        new OA\Property(property: 'organization', type: 'integer', example: 3)
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado')
        ]
    )]
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', Challenge::class);

        $totalChallenges = Challenge::count();
        $activeChallenges = Challenge::active()->count();
        $currentChallenges = Challenge::current()->count();
        $completedChallenges = Challenge::whereHas('teamProgress', function ($query) {
            $query->whereNotNull('completed_at');
        })->distinct()->count();

        $totalParticipants = \App\Models\TeamChallengeProgress::distinct('team_id')->count();
        $totalKwhTarget = Challenge::active()->sum('target_kwh');
        $totalKwhAchieved = \App\Models\TeamChallengeProgress::sum('progress_kwh');
        
        $completionRate = $totalKwhTarget > 0 ? ($totalKwhAchieved / $totalKwhTarget) * 100 : 0;

        $byType = Challenge::selectRaw('type, COUNT(*) as count')
                          ->groupBy('type')
                          ->pluck('count', 'type')
                          ->toArray();

        return response()->json([
            'data' => [
                'total_challenges' => $totalChallenges,
                'active_challenges' => $activeChallenges,
                'current_challenges' => $currentChallenges,
                'completed_challenges' => $completedChallenges,
                'total_participants' => $totalParticipants,
                'total_kwh_target' => round($totalKwhTarget, 2),
                'total_kwh_achieved' => round($totalKwhAchieved, 2),
                'completion_rate' => round($completionRate, 2),
                'by_type' => [
                    'individual' => $byType['individual'] ?? 0,
                    'team' => $byType['team'] ?? 0,
                    'organization' => $byType['organization'] ?? 0,
                ]
            ]
        ]);
    }
}
