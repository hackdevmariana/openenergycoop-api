<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Category\StoreCategoryRequest;
use App\Http\Requests\Api\V1\Category\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="Gestión de categorías del sistema CMS"
 * )
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     tags={"Categories"},
     *     summary="Listar todas las categorías",
     *     @OA\Response(response=200, description="Lista de categorías")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Category::query()
            ->with(['parent', 'children', 'organization'])
            ->active();

        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->get();
        return CategoryResource::collection($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     tags={"Categories"},
     *     summary="Crear nueva categoría",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=201, description="Categoría creada")
     * )
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $category = Category::create($validated);
        $category->load(['parent', 'children', 'organization']);

        return response()->json([
            'data' => new CategoryResource($category),
            'message' => 'Categoría creada exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{id}",
     *     tags={"Categories"},
     *     summary="Obtener categoría específica",
     *     @OA\Response(response=200, description="Categoría obtenida")
     * )
     */
    public function show(Category $category): JsonResponse
    {
        if (!$category->is_active) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $category->load(['parent', 'children', 'organization', 'articles', 'images']);
        return response()->json(['data' => new CategoryResource($category)]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/categories/{id}",
     *     tags={"Categories"},
     *     summary="Actualizar categoría",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Categoría actualizada")
     * )
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());
        $category->load(['parent', 'children', 'organization']);

        return response()->json([
            'data' => new CategoryResource($category),
            'message' => 'Categoría actualizada exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{id}",
     *     tags={"Categories"},
     *     summary="Eliminar categoría",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Categoría eliminada")
     * )
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->children()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una categoría que tiene subcategorías'
            ], 422);
        }

        if ($category->articles()->count() > 0 || $category->images()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una categoría que tiene contenido asociado'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Categoría eliminada exitosamente']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/tree",
     *     tags={"Categories"},
     *     summary="Obtener árbol de categorías",
     *     @OA\Response(response=200, description="Árbol de categorías")
     * )
     */
    public function tree(Request $request): JsonResponse
    {
        $query = Category::query()
            ->with(['children.children', 'organization'])
            ->active()
            ->whereNull('parent_id');

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->get();
        return response()->json(['data' => CategoryResource::collection($categories)]);
    }
}