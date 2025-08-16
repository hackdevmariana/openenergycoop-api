<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Article\StoreArticleRequest;
use App\Http\Requests\Api\V1\Article\UpdateArticleRequest;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Articles",
 *     description="Gestión de artículos del sistema CMS"
 * )
 */
class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/articles",
     *     tags={"Articles"},
     *     summary="Listar artículos",
     *     description="Obtiene una lista paginada de artículos publicados",
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filtrar por categoría",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="featured",
     *         in="query",
     *         description="Solo artículos destacados",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar en título y contenido",
     *         required=false,
     *         @OA\Schema(type="string", example="energía")
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Filtrar por idioma",
     *         required=false,
     *         @OA\Schema(type="string", enum={"es", "en", "ca", "eu", "gl"}, example="es")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página (máximo 50)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50, example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de artículos obtenida exitosamente"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Article::query()
            ->with(['category', 'author', 'organization'])
            ->published()
            ->orderByDesc('featured')
            ->orderByDesc('published_at');

        // Filtros
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('text', 'like', "%{$search}%");
            });
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        $perPage = min($request->get('per_page', 10), 50);
        $articles = $query->paginate($perPage);

        return ArticleResource::collection($articles);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/articles",
     *     tags={"Articles"},
     *     summary="Crear nuevo artículo",
     *     description="Crea un nuevo artículo en el sistema",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Energía Solar en España"),
     *             @OA\Property(property="slug", type="string", maxLength=255, example="energia-solar-espana"),
     *             @OA\Property(property="excerpt", type="string", maxLength=500, example="Resumen del artículo..."),
     *             @OA\Property(property="content", type="string", example="Contenido completo del artículo..."),
     *             @OA\Property(property="featured_image", type="string", example="images/article-featured.jpg"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"energía", "solar", "renovable"}),
     *             @OA\Property(property="language", type="string", enum={"es", "en", "ca", "eu", "gl"}, example="es"),
     *             @OA\Property(property="is_featured", type="boolean", example=false),
     *             @OA\Property(property="meta_title", type="string", maxLength=60, example="Energía Solar - Guía Completa"),
     *             @OA\Property(property="meta_description", type="string", maxLength=160, example="Todo sobre energía solar en España"),
     *             @OA\Property(property="published_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Artículo creado exitosamente"
     *     )
     * )
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['author_id'] = auth()->id();

        $article = Article::create($validated);
        $article->load(['category', 'author', 'organization']);

        return response()->json([
            'data' => new ArticleResource($article),
            'message' => 'Artículo creado exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Obtener artículo específico",
     *     description="Obtiene los detalles de un artículo específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID o slug del artículo",
     *         required=true,
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo obtenido exitosamente"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        // Buscar por ID o slug
        $article = Article::query()
            ->with(['category', 'author', 'organization', 'comments.user'])
            ->published()
            ->where(function ($query) use ($id) {
                $query->where('id', $id)->orWhere('slug', $id);
            })
            ->firstOrFail();

        // Incrementar contador de vistas
        $article->increment('number_of_views');

        return response()->json([
            'data' => new ArticleResource($article)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Actualizar artículo",
     *     description="Actualiza un artículo existente",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del artículo",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="excerpt", type="string", maxLength=500),
     *             @OA\Property(property="featured_image", type="string"),
     *             @OA\Property(property="is_featured", type="boolean"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo actualizado exitosamente"
     *     )
     * )
     */
    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $article->update($request->validated());
        $article->load(['category', 'author', 'organization']);

        return response()->json([
            'data' => new ArticleResource($article),
            'message' => 'Artículo actualizado exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Eliminar artículo",
     *     description="Elimina un artículo del sistema",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del artículo",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo eliminado exitosamente"
     *     )
     * )
     */
    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json([
            'message' => 'Artículo eliminado exitosamente'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles/featured",
     *     tags={"Articles"},
     *     summary="Obtener artículos destacados",
     *     description="Obtiene los artículos marcados como destacados",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de artículos",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=20, example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículos destacados obtenidos"
     *     )
     * )
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 5), 20);

        $articles = Article::query()
            ->with(['category', 'author', 'organization'])
            ->published()
            ->where('featured', true)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => ArticleResource::collection($articles)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles/recent",
     *     tags={"Articles"},
     *     summary="Obtener artículos recientes",
     *     description="Obtiene los artículos más recientes",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de artículos",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=20, example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículos recientes obtenidos"
     *     )
     * )
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 20);

        $articles = Article::query()
            ->with(['category', 'author', 'organization'])
            ->published()
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => ArticleResource::collection($articles)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles/popular",
     *     tags={"Articles"},
     *     summary="Obtener artículos populares",
     *     description="Obtiene los artículos más vistos",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de artículos",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=20, example=10)
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período de tiempo",
     *         required=false,
     *         @OA\Schema(type="string", enum={"week", "month", "year", "all"}, example="month")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículos populares obtenidos"
     *     )
     * )
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 20);
        $period = $request->get('period', 'month');

        $query = Article::query()
            ->with(['category', 'author', 'organization'])
            ->published()
            ->orderByDesc('number_of_views');

        // Filtrar por período si no es 'all'
        if ($period !== 'all') {
            $date = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
                default => now()->subMonth()
            };
            $query->where('published_at', '>=', $date);
        }

        $articles = $query->limit($limit)->get();

        return response()->json([
            'data' => ArticleResource::collection($articles)
        ]);
    }
}