<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TextContentResource;
use App\Models\TextContent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Text Contents",
 *     description="Gestión de contenidos de texto del CMS"
 * )
 */
class TextContentController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TextContent::query()
            ->with(['organization', 'createdBy'])
            ->published()
            ->orderBy('created_at', 'desc');

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('text', 'like', "%{$search}%");
            });
        }

        $textContents = $query->get();
        return TextContentResource::collection($textContents);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'text' => 'required|string',
            'language' => 'required|string|in:es,en,ca,eu,gl',
            'organization_id' => 'nullable|exists:organizations,id',
        ]);

        $validated['created_by_user_id'] = auth()->id();

        $textContent = TextContent::create($validated);
        $textContent->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new TextContentResource($textContent),
            'message' => 'Contenido de texto creado exitosamente'
        ], 201);
    }

    public function show(TextContent $textContent): JsonResponse
    {
        if (!$textContent->isPublished()) {
            return response()->json(['message' => 'Contenido no encontrado'], 404);
        }

        $textContent->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new TextContentResource($textContent)
        ]);
    }

    public function update(Request $request, TextContent $textContent): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'text' => 'sometimes|string',
            'language' => 'sometimes|string|in:es,en,ca,eu,gl',
        ]);

        $validated['updated_by_user_id'] = auth()->id();

        $textContent->update($validated);
        $textContent->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new TextContentResource($textContent),
            'message' => 'Contenido actualizado exitosamente'
        ]);
    }

    public function destroy(TextContent $textContent): JsonResponse
    {
        $textContent->delete();

        return response()->json([
            'message' => 'Contenido eliminado exitosamente'
        ]);
    }
}