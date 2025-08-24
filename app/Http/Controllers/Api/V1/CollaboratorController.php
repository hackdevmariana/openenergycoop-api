<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Collaborator\StoreCollaboratorRequest;
use App\Http\Requests\Api\V1\Collaborator\UpdateCollaboratorRequest;
use App\Http\Resources\Api\V1\CollaboratorResource;
use App\Models\Collaborator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Collaborators",
 *     description="Gestión de colaboradores y equipo"
 * )
 */
class CollaboratorController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Collaborator::query()
            ->with(['organization', 'createdBy'])
            ->published()
            ->orderedByPriority();

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        } else {
            // Por defecto, solo mostrar activos si no se especifica el filtro
            $query->active();
        }

        $collaborators = $query->get();
        return CollaboratorResource::collection($collaborators);
    }

    public function store(StoreCollaboratorRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $collaborator = Collaborator::create($validated);
        $collaborator->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new CollaboratorResource($collaborator),
            'message' => 'Colaborador creado exitosamente'
        ], 201);
    }

    public function show(Collaborator $collaborator): JsonResponse
    {
        if (!$collaborator->isPublished() || !$collaborator->is_active) {
            return response()->json(['message' => 'Colaborador no encontrado'], 404);
        }

        $collaborator->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new CollaboratorResource($collaborator)
        ]);
    }

    public function update(UpdateCollaboratorRequest $request, Collaborator $collaborator): JsonResponse
    {
        $validated = $request->validated();

        $collaborator->update($validated);
        $collaborator->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new CollaboratorResource($collaborator),
            'message' => 'Colaborador actualizado exitosamente'
        ]);
    }

    public function destroy(Collaborator $collaborator): JsonResponse
    {
        $collaborator->delete();

        return response()->json([
            'message' => 'Colaborador eliminado exitosamente'
        ]);
    }

    public function byType(Request $request, string $type): JsonResponse
    {
        $query = Collaborator::query()
            ->with(['organization', 'createdBy'])
            ->published()
            ->active()
            ->byType($type)
            ->orderedByPriority();

        $collaborators = $query->get();

        return response()->json([
            'data' => CollaboratorResource::collection($collaborators),
            'type' => $type,
            'total' => $collaborators->count()
        ]);
    }

    public function active(Request $request): JsonResponse
    {
        $query = Collaborator::query()
            ->with(['organization', 'createdBy'])
            ->published()
            ->active()
            ->orderedByPriority();

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        $limit = min($request->get('limit', 10), 50);
        $collaborators = $query->limit($limit)->get();

        return response()->json([
            'data' => CollaboratorResource::collection($collaborators),
            'total' => $collaborators->count()
        ]);
    }
}