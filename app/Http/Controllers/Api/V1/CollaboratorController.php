<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
class CollaboratorController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Collaborator::query()
            ->with(['organization'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $collaborators = $query->get();
        return CollaboratorResource::collection($collaborators);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'role' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'personal_website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'languages' => 'nullable|array',
            'start_date' => 'nullable|date',
            'sort_order' => 'nullable|integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $collaborator = Collaborator::create($validated);
        $collaborator->load(['organization']);

        return response()->json([
            'data' => new CollaboratorResource($collaborator),
            'message' => 'Colaborador creado exitosamente'
        ], 201);
    }

    public function show(Collaborator $collaborator): JsonResponse
    {
        if (!$collaborator->is_active) {
            return response()->json(['message' => 'Colaborador no encontrado'], 404);
        }

        $collaborator->load(['organization']);

        return response()->json([
            'data' => new CollaboratorResource($collaborator)
        ]);
    }

    public function update(Request $request, Collaborator $collaborator): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'role' => 'sometimes|string|max:255',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',
            'skills' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $collaborator->update($validated);
        $collaborator->load(['organization']);

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

    public function featured(Request $request): JsonResponse
    {
        $query = Collaborator::query()
            ->with(['organization'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order');

        $limit = min($request->get('limit', 6), 12);
        $collaborators = $query->limit($limit)->get();

        return response()->json([
            'data' => CollaboratorResource::collection($collaborators),
            'total' => $collaborators->count()
        ]);
    }

    public function byDepartment(Request $request, string $department): JsonResponse
    {
        $query = Collaborator::query()
            ->with(['organization'])
            ->where('is_active', true)
            ->where('department', $department)
            ->orderBy('sort_order');

        $collaborators = $query->get();

        return response()->json([
            'data' => CollaboratorResource::collection($collaborators),
            'department' => $department,
            'total' => $collaborators->count()
        ]);
    }
}