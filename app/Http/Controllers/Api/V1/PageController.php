<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Page\StorePageRequest;
use App\Http\Requests\Api\V1\Page\UpdatePageRequest;
use App\Http\Resources\Api\V1\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Pages",
 *     description="Gestión de páginas del sistema CMS"
 * )
 */
class PageController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/pages",
     *     tags={"Pages"},
     *     summary="Listar páginas",
     *     description="Obtiene una lista de páginas publicadas",
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Filtrar por idioma",
     *         required=false,
     *         @OA\Schema(type="string", enum={"es", "en", "ca", "eu", "gl"}, example="es")
     *     ),
     *     @OA\Parameter(
     *         name="template",
     *         in="query",
     *         description="Filtrar por template",
     *         required=false,
     *         @OA\Schema(type="string", example="home")
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="Filtrar por página padre",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de páginas obtenida exitosamente"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Page::query()
            ->with(['parent', 'children', 'organization', 'components'])
            ->where('is_draft', false)
            ->orderBy('sort_order')
            ->orderBy('title');

        // Filtros
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('template')) {
            $query->where('template', $request->template);
        }

        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        $pages = $query->get();

        return PageResource::collection($pages);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/pages",
     *     tags={"Pages"},
     *     summary="Crear nueva página",
     *     description="Crea una nueva página en el sistema",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "slug"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Acerca de Nosotros"),
     *             @OA\Property(property="slug", type="string", maxLength=255, example="acerca-de-nosotros"),
     *             @OA\Property(property="route", type="string", maxLength=255, example="/about"),
     *             @OA\Property(property="language", type="string", enum={"es", "en", "ca", "eu", "gl"}, example="es"),
     *             @OA\Property(property="template", type="string", example="default"),
     *             @OA\Property(property="meta_data", type="object", example={"title": "Acerca de Nosotros", "description": "Conoce más sobre nuestra empresa"}),
     *             @OA\Property(property="parent_id", type="integer", example=1),
     *             @OA\Property(property="sort_order", type="integer", example=1),
     *             @OA\Property(property="requires_auth", type="boolean", example=false),
     *             @OA\Property(property="allowed_roles", type="array", @OA\Items(type="string"), example={"admin", "editor"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Página creada exitosamente"
     *     )
     * )
     */
    public function store(StorePageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $page = Page::create($validated);
        $page->load(['parent', 'children', 'organization', 'components']);

        return response()->json([
            'data' => new PageResource($page),
            'message' => 'Página creada exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pages/{id}",
     *     tags={"Pages"},
     *     summary="Obtener página específica",
     *     description="Obtiene los detalles de una página específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID, slug o ruta de la página",
     *         required=true,
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="include_components",
     *         in="query",
     *         description="Incluir componentes de la página",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Página obtenida exitosamente"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Page::query()
            ->with(['parent', 'children', 'organization']);

        // Incluir componentes si se solicita
        if ($request->boolean('include_components')) {
            $query->with('components.componentable');
        }

        // Buscar por ID, slug o route
        $page = $query->where(function ($query) use ($id) {
            $query->where('id', $id)
                  ->orWhere('slug', $id)
                  ->orWhere('route', $id);
        })
        ->where('is_draft', false)
        ->firstOrFail();

        return response()->json([
            'data' => new PageResource($page)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/pages/{id}",
     *     tags={"Pages"},
     *     summary="Actualizar página",
     *     description="Actualiza una página existente",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la página",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255),
     *             @OA\Property(property="slug", type="string", maxLength=255),
     *             @OA\Property(property="route", type="string", maxLength=255),
     *             @OA\Property(property="template", type="string"),
     *             @OA\Property(property="meta_data", type="object"),
     *             @OA\Property(property="sort_order", type="integer"),
     *             @OA\Property(property="requires_auth", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Página actualizada exitosamente"
     *     )
     * )
     */
    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by_user_id'] = auth()->id();

        $page->update($validated);
        $page->load(['parent', 'children', 'organization', 'components']);

        return response()->json([
            'data' => new PageResource($page),
            'message' => 'Página actualizada exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/pages/{id}",
     *     tags={"Pages"},
     *     summary="Eliminar página",
     *     description="Elimina una página del sistema",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la página",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Página eliminada exitosamente"
     *     )
     * )
     */
    public function destroy(Page $page): JsonResponse
    {
        // Verificar si tiene páginas hijas
        if ($page->children()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una página que tiene páginas hijas'
            ], 422);
        }

        $page->delete();

        return response()->json([
            'message' => 'Página eliminada exitosamente'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pages/by-route/{route}",
     *     tags={"Pages"},
     *     summary="Obtener página por ruta",
     *     description="Obtiene una página específica por su ruta",
     *     @OA\Parameter(
     *         name="route",
     *         in="path",
     *         description="Ruta de la página",
     *         required=true,
     *         @OA\Schema(type="string", example="about")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Página obtenida exitosamente"
     *     )
     * )
     */
    public function byRoute(string $route): JsonResponse
    {
        $page = Page::query()
            ->with(['parent', 'children', 'organization', 'components.componentable'])
            ->where('route', '/' . ltrim($route, '/'))
            ->where('is_draft', false)
            ->firstOrFail();

        return response()->json([
            'data' => new PageResource($page)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pages/hierarchy",
     *     tags={"Pages"},
     *     summary="Obtener jerarquía de páginas",
     *     description="Obtiene la estructura jerárquica completa de páginas",
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Filtrar por idioma",
     *         required=false,
     *         @OA\Schema(type="string", enum={"es", "en", "ca", "eu", "gl"}, example="es")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jerarquía de páginas obtenida"
     *     )
     * )
     */
    public function hierarchy(Request $request): JsonResponse
    {
        $query = Page::query()
            ->with(['children.children'])
            ->where('is_draft', false)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('title');

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        $pages = $query->get();

        return response()->json([
            'data' => PageResource::collection($pages)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pages/search",
     *     tags={"Pages"},
     *     summary="Buscar páginas",
     *     description="Busca páginas por título y contenido",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Término de búsqueda",
     *         required=true,
     *         @OA\Schema(type="string", example="energía")
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Filtrar por idioma",
     *         required=false,
     *         @OA\Schema(type="string", enum={"es", "en", "ca", "eu", "gl"}, example="es")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resultados de búsqueda obtenidos"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:3',
            'language' => 'nullable|string|in:es,en,ca,eu,gl'
        ]);

        $search = $request->q;
        
        $query = Page::query()
            ->with(['parent', 'organization'])
            ->where('is_draft', false)
            ->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('search_keywords', 'like', "%{$search}%");
            });

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        $pages = $query->orderByDesc('last_reviewed_at')
            ->orderBy('title')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => PageResource::collection($pages),
            'query' => $search,
            'total' => $pages->count()
        ]);
    }
}