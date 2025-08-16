<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Image\StoreImageRequest;
use App\Http\Requests\Api\V1\Image\UpdateImageRequest;
use App\Http\Resources\Api\V1\ImageResource;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Images",
 *     description="Gestión de imágenes del sistema CMS"
 * )
 */
class ImageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/images",
     *     tags={"Images"},
     *     summary="Listar todas las imágenes",
     *     description="Obtiene una lista paginada de todas las imágenes públicas",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de imágenes obtenida exitosamente"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Image::query()
            ->with(['category', 'organization', 'uploadedBy'])
            ->active()
            ->public();

        // Filtros
        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        // Paginación
        $perPage = min($request->get('per_page', 15), 100);
        $images = $query->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return ImageResource::collection($images);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/images",
     *     tags={"Images"},
     *     summary="Crear nueva imagen",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Imagen creada exitosamente"
     *     )
     * )
     */
    public function store(StoreImageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Procesar la subida del archivo
        if ($request->hasFile('path')) {
            $file = $request->file('path');
            $path = $file->store('images', 'public');
            
            $validated['path'] = $path;
            $validated['filename'] = $file->getClientOriginalName();
            $validated['mime_type'] = $file->getMimeType();
            $validated['file_size'] = $file->getSize();
            $validated['url'] = asset('storage/' . $path);
            
            // Obtener dimensiones si es una imagen
            if (str_starts_with($file->getMimeType(), 'image/')) {
                try {
                    $imageInfo = @getimagesize($file->getPathname());
                    if ($imageInfo && is_array($imageInfo) && count($imageInfo) >= 2) {
                        $validated['width'] = $imageInfo[0];
                        $validated['height'] = $imageInfo[1];
                    }
                } catch (\Exception $e) {
                    // Ignore errors when getting image dimensions
                    // This can happen with fake test files
                }
            }
        }

        $validated['uploaded_by_user_id'] = auth()->id();
        $validated['published_at'] = now();

        $image = Image::create($validated);
        $image->load(['category', 'organization', 'uploadedBy']);

        return response()->json([
            'data' => new ImageResource($image),
            'message' => 'Imagen creada exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/images/{id}",
     *     tags={"Images"},
     *     summary="Obtener imagen específica",
     *     @OA\Response(
     *         response=200,
     *         description="Imagen obtenida exitosamente"
     *     )
     * )
     */
    public function show(Image $image): JsonResponse
    {
        // Solo mostrar imágenes públicas y activas
        if (!$image->is_public || $image->status !== 'active') {
            return response()->json([
                'message' => 'Imagen no encontrada'
            ], 404);
        }

        $image->load(['category', 'organization', 'uploadedBy']);
        
        // Incrementar contador de vistas
        $image->incrementViews();

        return response()->json([
            'data' => new ImageResource($image)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/images/{id}",
     *     tags={"Images"},
     *     summary="Actualizar imagen",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Imagen actualizada exitosamente"
     *     )
     * )
     */
    public function update(UpdateImageRequest $request, Image $image): JsonResponse
    {
        $image->update($request->validated());
        $image->load(['category', 'organization', 'uploadedBy']);

        return response()->json([
            'data' => new ImageResource($image),
            'message' => 'Imagen actualizada exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/images/{id}",
     *     tags={"Images"},
     *     summary="Eliminar imagen",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Imagen eliminada exitosamente"
     *     )
     * )
     */
    public function destroy(Image $image): JsonResponse
    {
        $image->softDelete();

        return response()->json([
            'message' => 'Imagen eliminada exitosamente'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/images/{id}/download",
     *     tags={"Images"},
     *     summary="Descargar imagen",
     *     @OA\Response(
     *         response=200,
     *         description="Archivo de imagen"
     *     )
     * )
     */
    public function download(Image $image)
    {
        if (!$image->is_public || $image->status !== 'active') {
            return response()->json([
                'message' => 'Imagen no encontrada'
            ], 404);
        }

        $image->incrementDownloads();

        return response()->download(
            storage_path('app/public/' . $image->path),
            $image->filename
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/images/featured",
     *     tags={"Images"},
     *     summary="Obtener imágenes destacadas",
     *     @OA\Response(
     *         response=200,
     *         description="Imágenes destacadas obtenidas exitosamente"
     *     )
     * )
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 50);

        $images = Image::query()
            ->with(['category', 'organization', 'uploadedBy'])
            ->active()
            ->public()
            ->featured()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => ImageResource::collection($images)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/images/stats",
     *     tags={"Images"},
     *     summary="Obtener estadísticas de imágenes",
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente"
     *     )
     * )
     */
    public function stats(): JsonResponse
    {
        $stats = Image::getUsageStats();
        return response()->json($stats);
    }
}