<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeamMembershipResource;
use App\Models\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Team Memberships',
    description: 'Gestión de membresías de equipos'
)]
class TeamMembershipController extends Controller
{
    #[OA\Get(
        path: '/api/v1/team-memberships',
        tags: ['Team Memberships'],
        summary: 'Listar membresías de equipos',
        description: 'Obtiene todas las membresías de equipos con filtros opcionales',
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
                name: 'user_id',
                in: 'query',
                description: 'Filtrar por usuario',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'role',
                in: 'query',
                description: 'Filtrar por rol',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['leader', 'member', 'moderator'])
            ),
            new OA\Parameter(
                name: 'is_active',
                in: 'query',
                description: 'Filtrar por estado activo',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de membresías obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TeamMembershipResource'))
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TeamMembership::query()
            ->with(['team', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $memberships = $query->get();
        return TeamMembershipResource::collection($memberships);
    }

    #[OA\Post(
        path: '/api/v1/team-memberships',
        tags: ['Team Memberships'],
        summary: 'Crear nueva membresía de equipo',
        description: 'Registra un nuevo miembro en un equipo',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['team_id', 'user_id', 'role'],
                properties: [
                    new OA\Property(property: 'team_id', type: 'integer', example: 1),
                    new OA\Property(property: 'user_id', type: 'integer', example: 2),
                    new OA\Property(property: 'role', type: 'string', enum: ['member', 'leader', 'admin'], example: 'member')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Membresía creada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/TeamMembershipResource'),
                        new OA\Property(property: 'message', type: 'string', example: 'Membresía creada exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'El usuario ya es miembro del equipo o errores de validación')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:member,leader,admin',
        ]);

        // Verificar que el usuario no esté ya en el equipo
        $existing = TeamMembership::where('team_id', $validated['team_id'])
            ->where('user_id', $validated['user_id'])
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'El usuario ya es miembro de este equipo'
            ], 422);
        }

        $membership = TeamMembership::create($validated);
        $membership->load(['team', 'user']);

        return response()->json([
            'data' => new TeamMembershipResource($membership),
            'message' => 'Membresía creada exitosamente'
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/team-memberships/{team_membership}',
        tags: ['Team Memberships'],
        summary: 'Ver membresía específica',
        description: 'Obtiene los detalles de una membresía de equipo específica',
        parameters: [
            new OA\Parameter(
                name: 'team_membership',
                in: 'path',
                description: 'ID de la membresía',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Membresía obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/TeamMembershipResource')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Membresía no encontrada')
        ]
    )]
    public function show(TeamMembership $teamMembership): JsonResponse
    {
        $teamMembership->load(['team', 'user']);

        return response()->json([
            'data' => new TeamMembershipResource($teamMembership)
        ]);
    }

    #[OA\Put(
        path: '/api/v1/team-memberships/{team_membership}',
        tags: ['Team Memberships'],
        summary: 'Actualizar membresía de equipo',
        description: 'Actualiza el rol o estado de una membresía de equipo',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'team_membership',
                in: 'path',
                description: 'ID de la membresía',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'role', type: 'string', enum: ['member', 'leader', 'admin'], example: 'leader'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Membresía actualizada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/TeamMembershipResource'),
                        new OA\Property(property: 'message', type: 'string', example: 'Membresía actualizada exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Membresía no encontrada'),
            new OA\Response(response: 422, description: 'Errores de validación')
        ]
    )]
    public function update(Request $request, TeamMembership $teamMembership): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'sometimes|string|in:member,leader,admin',
            'is_active' => 'boolean',
        ]);

        $teamMembership->update($validated);

        return response()->json([
            'data' => new TeamMembershipResource($teamMembership->fresh()),
            'message' => 'Membresía actualizada exitosamente'
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/team-memberships/{team_membership}',
        tags: ['Team Memberships'],
        summary: 'Eliminar membresía de equipo',
        description: 'Elimina permanentemente una membresía de equipo',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'team_membership',
                in: 'path',
                description: 'ID de la membresía',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Membresía eliminada exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Membresía eliminada exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Membresía no encontrada')
        ]
    )]
    public function destroy(TeamMembership $teamMembership): JsonResponse
    {
        $teamMembership->delete();

        return response()->json([
            'message' => 'Membresía eliminada exitosamente'
        ]);
    }

    #[OA\Post(
        path: '/api/v1/team-memberships/leave',
        tags: ['Team Memberships'],
        summary: 'Abandonar equipo',
        description: 'Permite al usuario autenticado abandonar un equipo',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['team_id'],
                properties: [
                    new OA\Property(property: 'team_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Has abandonado el equipo exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Has abandonado el equipo exitosamente')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'No eres miembro de este equipo o errores de validación')
        ]
    )]
    public function leave(Request $request): JsonResponse
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id'
        ]);

        $membership = TeamMembership::where('team_id', $request->team_id)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$membership) {
            return response()->json([
                'message' => 'No eres miembro de este equipo'
            ], 422);
        }

        $membership->update(['is_active' => false, 'left_at' => now()]);

        return response()->json([
            'message' => 'Has abandonado el equipo exitosamente'
        ]);
    }
}