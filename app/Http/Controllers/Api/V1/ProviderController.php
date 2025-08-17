<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\StoreProviderRequest;
use App\Http\Requests\Api\V1\Provider\UpdateProviderRequest;
use App\Http\Resources\Api\V1\ProviderResource;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Providers",
 *     description="API Endpoints para la gestión de proveedores energéticos"
 * )
 */
class ProviderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/providers",
     *     summary="Listar todos los proveedores energéticos",
     *     description="Retorna una lista paginada de proveedores con sus certificaciones",
     *     operationId="getProviders",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Término de búsqueda",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo de proveedor",
     *         required=false,
     *         @OA\Schema(type="string", enum={"renewable", "traditional", "hybrid"})
     *     ),
     *     @OA\Parameter(
     *         name="rating_min",
     *         in="query",
     *         description="Rating mínimo",
     *         required=false,
     *         @OA\Schema(type="number", minimum=0, maximum=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de proveedores",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProviderResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Provider::query()->with(['company', 'tags']);

        // Búsqueda por nombre o descripción
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filtro por rating mínimo
        if ($request->filled('rating_min')) {
            $query->where('rating', '>=', $request->input('rating_min'));
        }

        // Ordenamiento
        $query->orderBy('rating', 'desc')
              ->orderBy('name', 'asc');

        $perPage = min($request->input('per_page', 15), 100);
        $providers = $query->paginate($perPage);

        return ProviderResource::collection($providers);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/providers/top-rated",
     *     summary="Obtener proveedores mejor valorados",
     *     description="Retorna los proveedores con mejor rating",
     *     operationId="getTopRatedProviders",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de proveedores",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proveedores mejor valorados",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProviderResource"))
     *         )
     *     )
     * )
     */
    public function topRated(Request $request): AnonymousResourceCollection
    {
        $limit = min($request->input('limit', 10), 50);
        
        $providers = Provider::with(['company', 'tags'])
            ->where('rating', '>=', 4.0)
            ->orderBy('rating', 'desc')
            ->orderBy('total_products', 'desc')
            ->limit($limit)
            ->get();

        return ProviderResource::collection($providers);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/providers/renewable",
     *     summary="Obtener proveedores de energía renovable",
     *     description="Retorna solo proveedores de energías renovables",
     *     operationId="getRenewableProviders",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Proveedores de energía renovable",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProviderResource"))
     *         )
     *     )
     * )
     */
    public function renewable(): AnonymousResourceCollection
    {
        $providers = Provider::with(['company', 'tags'])
            ->where('type', 'renewable')
            ->where('is_active', true)
            ->orderBy('rating', 'desc')
            ->get();

        return ProviderResource::collection($providers);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/providers/{provider}/certifications",
     *     summary="Obtener certificaciones de un proveedor",
     *     description="Retorna las certificaciones y acreditaciones del proveedor",
     *     operationId="getProviderCertifications",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         description="ID del proveedor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificaciones del proveedor",
     *         @OA\JsonContent(
     *             @OA\Property(property="certifications", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Proveedor no encontrado")
     * )
     */
    public function certifications(Provider $provider): JsonResponse
    {
        return response()->json([
            'certifications' => $provider->certifications ?? [],
            'verification_status' => $provider->verification_status,
            'last_verified_at' => $provider->last_verified_at?->toISOString(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/providers/{provider}/statistics",
     *     summary="Obtener estadísticas de un proveedor",
     *     description="Retorna estadísticas detalladas del proveedor",
     *     operationId="getProviderStatistics",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         description="ID del proveedor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas del proveedor",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_products", type="integer"),
     *             @OA\Property(property="average_rating", type="number"),
     *             @OA\Property(property="total_sales", type="number"),
     *             @OA\Property(property="sustainability_score", type="number")
     *         )
     *     )
     * )
     */
    public function statistics(Provider $provider): JsonResponse
    {
        $stats = [
            'total_products' => $provider->total_products ?? 0,
            'average_rating' => round($provider->rating, 2),
            'total_sales' => $provider->products()->sum('total_sales') ?? 0,
            'sustainability_score' => $provider->sustainability_score ?? 0,
            'certification_count' => count($provider->certifications ?? []),
            'is_verified' => $provider->verification_status === 'verified',
        ];

        return response()->json($stats);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/providers",
     *     summary="Crear un nuevo proveedor",
     *     description="Crea un nuevo proveedor energético",
     *     operationId="storeProvider",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreProviderRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proveedor creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/ProviderResource")
     *     ),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(StoreProviderRequest $request): ProviderResource
    {
        $provider = Provider::create($request->validated());

        if ($request->filled('tag_ids')) {
            $provider->tags()->sync($request->input('tag_ids'));
        }

        return new ProviderResource($provider->load(['company', 'tags']));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/providers/{provider}",
     *     summary="Obtener un proveedor específico",
     *     description="Retorna los detalles de un proveedor específico",
     *     operationId="showProvider",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         description="ID del proveedor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del proveedor",
     *         @OA\JsonContent(ref="#/components/schemas/ProviderResource")
     *     ),
     *     @OA\Response(response=404, description="Proveedor no encontrado")
     * )
     */
    public function show(Provider $provider): ProviderResource
    {
        return new ProviderResource($provider->load(['company', 'tags', 'products']));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/providers/{provider}",
     *     summary="Actualizar un proveedor",
     *     description="Actualiza los datos de un proveedor existente",
     *     operationId="updateProvider",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         description="ID del proveedor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProviderRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proveedor actualizado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/ProviderResource")
     *     ),
     *     @OA\Response(response=404, description="Proveedor no encontrado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(UpdateProviderRequest $request, Provider $provider): ProviderResource
    {
        $provider->update($request->validated());

        if ($request->filled('tag_ids')) {
            $provider->tags()->sync($request->input('tag_ids'));
        }

        return new ProviderResource($provider->load(['company', 'tags']));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/providers/{provider}",
     *     summary="Eliminar un proveedor",
     *     description="Elimina un proveedor específico",
     *     operationId="destroyProvider",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         description="ID del proveedor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Proveedor eliminado exitosamente"
     *     ),
     *     @OA\Response(response=404, description="Proveedor no encontrado")
     * )
     */
    public function destroy(Provider $provider): JsonResponse
    {
        $provider->delete();

        return response()->json(null, 204);
    }
}
