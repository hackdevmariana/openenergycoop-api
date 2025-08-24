<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeamChallengeProgressResource;
use App\Models\TeamChallengeProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Team Challenge Progress',
    description: 'Gestión del progreso de equipos en desafíos'
)]
class TeamChallengeProgressController extends \App\Http\Controllers\Controller
{
    #[OA\Get(
        path: '/api/v1/team-challenge-progress',
        tags: ['Team Challenge Progress'],
        summary: 'Listar progreso de equipos en desafíos',
        description: 'Obtiene el progreso de todos los equipos en los desafíos',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'team_id',
                in: 'query',
                description: 'Filtrar por equipo',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'challenge_id',
                in: 'query',
                description: 'Filtrar por desafío',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'completed',
                in: 'query',
                description: 'Filtrar por estado de finalización',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de progreso obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TeamChallengeProgressResource'))
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TeamChallengeProgress::query()
            ->with(['team', 'challenge'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('challenge_id')) {
            $query->where('challenge_id', $request->challenge_id);
        }

        if ($request->has('completed')) {
            if ($request->boolean('completed')) {
                $query->completed();
            } else {
                $query->inProgress();
            }
        }

        $progress = $query->get();
        return TeamChallengeProgressResource::collection($progress);
    }

    #[OA\Post(
        path: '/api/v1/team-challenge-progress',
        tags: ['Team Challenge Progress'],
        summary: 'Registrar progreso de equipo en desafío',
        description: 'Registra o actualiza el progreso de un equipo en un desafío específico',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['team_id', 'challenge_id'],
                properties: [
                    new OA\Property(property: 'team_id', type: 'integer', example: 1),
                    new OA\Property(property: 'challenge_id', type: 'integer', example: 1),
                    new OA\Property(property: 'progress_kwh', type: 'number', format: 'float', nullable: true, example: 75.5, description: 'Progress in kWh')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Progreso registrado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/TeamChallengeProgressResource'),
                        new OA\Property(property: 'message', type: 'string', example: 'Progreso registrado exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'challenge_id' => 'required|exists:challenges,id',
            'progress_kwh' => 'nullable|numeric|min:0',
        ]);

        $progress = TeamChallengeProgress::create($validated);
        $progress->load(['team', 'challenge']);

        return response()->json([
            'data' => new TeamChallengeProgressResource($progress),
            'message' => 'Progreso registrado exitosamente'
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/team-challenge-progress/{team_challenge_progress}',
        tags: ['Team Challenge Progress'],
        summary: 'Ver progreso específico de equipo en desafío',
        description: 'Obtiene el progreso detallado de un equipo en un desafío específico',
        parameters: [
            new OA\Parameter(
                name: 'team_challenge_progress',
                in: 'path',
                description: 'ID del progreso del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Progreso obtenido exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/TeamChallengeProgressResource')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Progreso no encontrado')
        ]
    )]
    public function show(TeamChallengeProgress $teamChallengeProgress): JsonResponse
    {
        $teamChallengeProgress->load(['team', 'challenge']);

        return response()->json([
            'data' => new TeamChallengeProgressResource($teamChallengeProgress)
        ]);
    }

    #[OA\Put(
        path: '/api/v1/team-challenge-progress/{team_challenge_progress}',
        tags: ['Team Challenge Progress'],
        summary: 'Actualizar progreso de equipo en desafío',
        description: 'Actualiza el progreso de un equipo en un desafío específico',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'team_challenge_progress',
                in: 'path',
                description: 'ID del progreso del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'progress_kwh', type: 'number', format: 'float', nullable: true, example: 85.0, description: 'Updated progress in kWh')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Progreso actualizado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/TeamChallengeProgressResource'),
                        new OA\Property(property: 'message', type: 'string', example: 'Progreso actualizado exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Progreso no encontrado'),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function update(Request $request, TeamChallengeProgress $teamChallengeProgress): JsonResponse
    {
        $validated = $request->validate([
            'progress_kwh' => 'nullable|numeric|min:0',
        ]);

        $teamChallengeProgress->update($validated);

        return response()->json([
            'data' => new TeamChallengeProgressResource($teamChallengeProgress->fresh()),
            'message' => 'Progreso actualizado exitosamente'
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/team-challenge-progress/{team_challenge_progress}',
        tags: ['Team Challenge Progress'],
        summary: 'Eliminar progreso de equipo en desafío',
        description: 'Elimina el registro de progreso de un equipo en un desafío',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'team_challenge_progress',
                in: 'path',
                description: 'ID del progreso del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Progreso eliminado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Progreso eliminado exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Progreso no encontrado')
        ]
    )]
    public function destroy(TeamChallengeProgress $teamChallengeProgress): JsonResponse
    {
        $teamChallengeProgress->delete();

        return response()->json([
            'message' => 'Progreso eliminado exitosamente'
        ]);
    }

    #[OA\Get(
        path: '/api/v1/team-challenge-progress/leaderboard/{challengeId}',
        tags: ['Team Challenge Progress'],
        summary: 'Obtener tabla de posiciones del desafío',
        description: 'Obtiene la tabla de posiciones de equipos para un desafío específico',
        parameters: [
            new OA\Parameter(
                name: 'challengeId',
                in: 'path',
                description: 'ID del desafío',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Número máximo de equipos a mostrar (máx. 50)',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tabla de posiciones obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TeamChallengeProgressResource')),
                        new OA\Property(property: 'challenge_id', type: 'integer', example: 1),
                        new OA\Property(property: 'total', type: 'integer', example: 5)
                    ]
                )
            )
        ]
    )]
    public function leaderboard(Request $request, int $challengeId): JsonResponse
    {
        $query = TeamChallengeProgress::query()
            ->with(['team'])
            ->where('challenge_id', $challengeId)
            ->orderBy('progress_kwh', 'desc');

        $limit = min($request->get('limit', 10), 50);
        $progress = $query->limit($limit)->get();

        return response()->json([
            'data' => TeamChallengeProgressResource::collection($progress),
            'challenge_id' => $challengeId,
            'total' => $progress->count()
        ]);
    }

    #[OA\Post(
        path: '/api/v1/team-challenge-progress/{teamChallengeProgress}/update-progress',
        tags: ['Team Challenge Progress'],
        summary: 'Actualizar progreso con incremento',
        description: 'Actualiza el progreso de un equipo incrementando el valor actual',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'teamChallengeProgress',
                in: 'path',
                description: 'ID del progreso del equipo',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['increment'],
                properties: [
                    new OA\Property(property: 'increment', type: 'number', format: 'float', minimum: 0, example: 10.5),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Progreso actualizado por actividad completada')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Progreso actualizado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/TeamChallengeProgressResource'),
                        new OA\Property(property: 'message', type: 'string', example: 'Progreso actualizado exitosamente'),
                        new OA\Property(property: 'previous_value', type: 'number', format: 'float', example: 75.0),
                        new OA\Property(property: 'increment', type: 'number', format: 'float', example: 10.5)
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Progreso no encontrado'),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function updateProgress(Request $request, TeamChallengeProgress $teamChallengeProgress): JsonResponse
    {
        $request->validate([
            'increment' => 'required|numeric'
        ]);

        $teamChallengeProgress->updateProgress($request->increment);

        return response()->json([
            'data' => new TeamChallengeProgressResource($teamChallengeProgress->fresh()),
            'message' => 'Progreso actualizado exitosamente'
        ]);
    }
}