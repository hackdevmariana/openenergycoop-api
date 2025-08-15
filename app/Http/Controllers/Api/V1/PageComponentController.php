<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PageComponentResource;
use App\Models\PageComponent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Page Components",
 *     description="Gestión de componentes de página del CMS"
 * )
 */
class PageComponentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PageComponent::query()
            ->with(['page', 'componentable', 'organization'])
            ->orderBy('position');

        if ($request->filled('page_id')) {
            $query->where('page_id', $request->page_id);
        }

        if ($request->filled('componentable_type')) {
            $query->where('componentable_type', $request->componentable_type);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->has('is_draft')) {
            $query->where('is_draft', $request->boolean('is_draft'));
        }

        $components = $query->get();
        return PageComponentResource::collection($components);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'componentable_type' => 'required|string',
            'componentable_id' => 'required|integer',
            'position' => 'nullable|integer',
            'parent_id' => 'nullable|exists:page_components,id',
            'language' => 'required|string|in:es,en,ca,eu,gl',
            'settings' => 'nullable|json',
            'visibility_rules' => 'nullable|json',
        ]);

        $component = PageComponent::create($validated);
        $component->load(['page', 'componentable', 'organization']);

        return response()->json([
            'data' => new PageComponentResource($component),
            'message' => 'Componente de página creado exitosamente'
        ], 201);
    }

    public function show(PageComponent $pageComponent): JsonResponse
    {
        $pageComponent->load(['page', 'componentable', 'organization', 'parent', 'children']);

        return response()->json([
            'data' => new PageComponentResource($pageComponent)
        ]);
    }

    public function update(Request $request, PageComponent $pageComponent): JsonResponse
    {
        $validated = $request->validate([
            'position' => 'nullable|integer',
            'settings' => 'nullable|json',
            'visibility_rules' => 'nullable|json',
            'is_draft' => 'nullable|boolean',
            'cache_enabled' => 'nullable|boolean',
        ]);

        $pageComponent->update($validated);
        $pageComponent->load(['page', 'componentable', 'organization']);

        return response()->json([
            'data' => new PageComponentResource($pageComponent),
            'message' => 'Componente actualizado exitosamente'
        ]);
    }

    public function destroy(PageComponent $pageComponent): JsonResponse
    {
        // Verificar si tiene componentes hijos
        if ($pageComponent->children()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un componente que tiene componentes hijos'
            ], 422);
        }

        $pageComponent->delete();

        return response()->json([
            'message' => 'Componente eliminado exitosamente'
        ]);
    }

    public function reorder(Request $request, PageComponent $pageComponent): JsonResponse
    {
        $request->validate([
            'position' => 'required|integer|min:1'
        ]);

        $pageComponent->update(['position' => $request->position]);

        return response()->json([
            'data' => new PageComponentResource($pageComponent->fresh()),
            'message' => 'Componente reordenado exitosamente'
        ]);
    }

    public function forPage(Request $request, int $pageId): JsonResponse
    {
        $query = PageComponent::query()
            ->with(['componentable', 'children.componentable'])
            ->where('page_id', $pageId)
            ->where('is_draft', false)
            ->whereNull('parent_id')
            ->orderBy('position');

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        $components = $query->get();

        return response()->json([
            'data' => PageComponentResource::collection($components),
            'page_id' => $pageId,
            'total' => $components->count()
        ]);
    }
}