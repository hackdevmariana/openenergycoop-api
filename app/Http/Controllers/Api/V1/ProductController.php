<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints para la gestión de productos energéticos"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Listar todos los productos energéticos",
     *     description="Retorna una lista paginada de productos con cálculos de sostenibilidad",
     *     operationId="getProducts",
     *     tags={"Products"},
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
     *         description="Filtrar por tipo de producto",
     *         required=false,
     *         @OA\Schema(type="string", enum={"solar", "wind", "hydro", "biomass", "geothermal"})
     *     ),
     *     @OA\Parameter(
     *         name="provider_id",
     *         in="query",
     *         description="Filtrar por proveedor",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="price_min",
     *         in="query",
     *         description="Precio mínimo",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="price_max",
     *         in="query",
     *         description="Precio máximo",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="sustainability_min",
     *         in="query",
     *         description="Puntuación de sostenibilidad mínima",
     *         required=false,
     *         @OA\Schema(type="number", minimum=0, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProductResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()->with(['provider', 'tags']);

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

        // Filtro por proveedor
        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->input('provider_id'));
        }

        // Filtro por rango de precios
        if ($request->filled('price_min')) {
            $query->where('unit_price', '>=', $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('unit_price', '<=', $request->input('price_max'));
        }

        // Filtro por sostenibilidad
        if ($request->filled('sustainability_min')) {
            $query->where('sustainability_score', '>=', $request->input('sustainability_min'));
        }

        // Solo productos activos por defecto
        $query->where('is_active', true);

        // Ordenamiento por defecto: sostenibilidad y precio
        $query->orderBy('sustainability_score', 'desc')
              ->orderBy('unit_price', 'asc')
              ->orderBy('name', 'asc');

        $perPage = min($request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/sustainable",
     *     summary="Obtener productos más sostenibles",
     *     description="Retorna los productos con mayor puntuación de sostenibilidad",
     *     operationId="getSustainableProducts",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de productos",
     *         required=false,
     *         @OA\Schema(type="integer", default=20, maximum=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Productos más sostenibles",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProductResource"))
     *         )
     *     )
     * )
     */
    public function sustainable(Request $request): AnonymousResourceCollection
    {
        $limit = min($request->input('limit', 20), 50);
        
        $products = Product::with(['provider', 'tags'])
            ->where('sustainability_score', '>=', 80)
            ->where('is_active', true)
            ->orderBy('sustainability_score', 'desc')
            ->orderBy('unit_price', 'asc')
            ->limit($limit)
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/recommendations/{user}",
     *     summary="Obtener recomendaciones de productos para un usuario",
     *     description="Retorna productos recomendados basados en el perfil del usuario",
     *     operationId="getProductRecommendations",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID del usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de recomendaciones",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Productos recomendados",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProductResource")),
     *             @OA\Property(property="recommendation_reasons", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function recommendations(Request $request, int $userId): JsonResponse
    {
        $limit = min($request->input('limit', 10), 20);
        
        // Lógica de recomendación básica basada en sostenibilidad y precio
        $products = Product::with(['provider', 'tags'])
            ->where('is_active', true)
            ->where('sustainability_score', '>=', 70)
            ->orderBy('sustainability_score', 'desc')
            ->orderBy('unit_price', 'asc')
            ->limit($limit)
            ->get();

        $reasons = [
            'Alta puntuación de sostenibilidad',
            'Relación calidad-precio favorable',
            'Proveedor verificado',
            'Producto popular en tu región'
        ];

        return response()->json([
            'data' => ProductResource::collection($products),
            'recommendation_reasons' => $reasons
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{product}/pricing",
     *     summary="Calcular precios de un producto",
     *     description="Retorna cálculos detallados de precios para diferentes cantidades",
     *     operationId="getProductPricing",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="quantity",
     *         in="query",
     *         description="Cantidad a calcular",
     *         required=false,
     *         @OA\Schema(type="number", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="months",
     *         in="query",
     *         description="Número de meses para proyección",
     *         required=false,
     *         @OA\Schema(type="integer", default=12)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cálculos de precios",
     *         @OA\JsonContent(
     *             @OA\Property(property="unit_price", type="number"),
     *             @OA\Property(property="total_price", type="number"),
     *             @OA\Property(property="monthly_cost", type="number"),
     *             @OA\Property(property="annual_cost", type="number"),
     *             @OA\Property(property="savings_projection", type="object")
     *         )
     *     )
     * )
     */
    public function pricing(Request $request, Product $product): JsonResponse
    {
        $quantity = $request->input('quantity', 1);
        $months = $request->input('months', 12);

        $unitPrice = $product->unit_price;
        $totalPrice = $unitPrice * $quantity;
        $monthlyCost = $totalPrice / 12;
        $annualCost = $totalPrice;

        // Cálculo de ahorros proyectados
        $savingsProjection = [
            'traditional_cost' => $totalPrice * 1.3, // Asumiendo 30% más caro lo tradicional
            'projected_savings' => $totalPrice * 0.3,
            'payback_period_months' => 24, // Periodo de retorno estimado
            'co2_reduction_kg' => $quantity * ($product->co2_reduction ?? 100) // Reducción de CO2
        ];

        return response()->json([
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'monthly_cost' => round($monthlyCost, 2),
            'annual_cost' => $annualCost,
            'currency' => $product->currency ?? 'EUR',
            'unit' => $product->unit,
            'savings_projection' => $savingsProjection,
            'sustainability_impact' => [
                'score' => $product->sustainability_score,
                'co2_reduction' => $savingsProjection['co2_reduction_kg'],
                'renewable_percentage' => $product->renewable_percentage ?? 100
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{product}/sustainability",
     *     summary="Obtener detalles de sostenibilidad de un producto",
     *     description="Retorna información detallada sobre el impacto ambiental",
     *     operationId="getProductSustainability",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de sostenibilidad",
     *         @OA\JsonContent(
     *             @OA\Property(property="sustainability_score", type="number"),
     *             @OA\Property(property="environmental_impact", type="object"),
     *             @OA\Property(property="certifications", type="array")
     *         )
     *     )
     * )
     */
    public function sustainability(Product $product): JsonResponse
    {
        $environmentalImpact = [
            'co2_reduction_per_unit' => $product->co2_reduction ?? 0,
            'renewable_percentage' => $product->renewable_percentage ?? 0,
            'energy_efficiency_rating' => $product->energy_efficiency ?? 'A',
            'lifecycle_carbon_footprint' => $product->carbon_footprint ?? 0,
            'water_usage_reduction' => $product->water_saving ?? 0,
        ];

        $certifications = $product->certifications ?? [];

        return response()->json([
            'sustainability_score' => $product->sustainability_score,
            'environmental_impact' => $environmentalImpact,
            'certifications' => $certifications,
            'sustainability_rating' => $this->getSustainabilityRating($product->sustainability_score),
            'comparison_vs_traditional' => [
                'co2_reduction_percentage' => 70,
                'cost_efficiency' => 85,
                'environmental_benefit' => 90
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Crear un nuevo producto",
     *     description="Crea un nuevo producto energético",
     *     operationId="storeProduct",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreProductRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/ProductResource")
     *     ),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(StoreProductRequest $request): ProductResource
    {
        $validated = $request->validated();
        
        // Calcular puntuación de sostenibilidad automáticamente
        $validated['sustainability_score'] = $this->calculateSustainabilityScore($validated);
        
        $product = Product::create($validated);

        if ($request->filled('tag_ids')) {
            $product->tags()->sync($request->input('tag_ids'));
        }

        return new ProductResource($product->load(['provider', 'tags']));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{product}",
     *     summary="Obtener un producto específico",
     *     description="Retorna los detalles de un producto específico",
     *     operationId="showProduct",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del producto",
     *         @OA\JsonContent(ref="#/components/schemas/ProductResource")
     *     ),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['provider', 'tags']));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/products/{product}",
     *     summary="Actualizar un producto",
     *     description="Actualiza los datos de un producto existente",
     *     operationId="updateProduct",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProductRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/ProductResource")
     *     ),
     *     @OA\Response(response=404, description="Producto no encontrado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $validated = $request->validated();
        
        // Recalcular puntuación de sostenibilidad si hay cambios relevantes
        if (isset($validated['renewable_percentage']) || isset($validated['co2_reduction'])) {
            $validated['sustainability_score'] = $this->calculateSustainabilityScore($validated);
        }
        
        $product->update($validated);

        if ($request->filled('tag_ids')) {
            $product->tags()->sync($request->input('tag_ids'));
        }

        return new ProductResource($product->load(['provider', 'tags']));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/products/{product}",
     *     summary="Eliminar un producto",
     *     description="Elimina un producto específico",
     *     operationId="destroyProduct",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Producto eliminado exitosamente"
     *     ),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }

    /**
     * Calcula la puntuación de sostenibilidad basada en varios factores
     */
    private function calculateSustainabilityScore(array $data): int
    {
        $score = 0;
        
        // Porcentaje renovable (hasta 40 puntos)
        $renewablePercentage = $data['renewable_percentage'] ?? 0;
        $score += ($renewablePercentage / 100) * 40;
        
        // Reducción de CO2 (hasta 30 puntos)
        $co2Reduction = $data['co2_reduction'] ?? 0;
        $score += min(($co2Reduction / 500) * 30, 30); // Máximo 500kg = 30 puntos
        
        // Eficiencia energética (hasta 20 puntos)
        $efficiency = $data['energy_efficiency'] ?? 'C';
        $efficiencyMap = ['A++' => 20, 'A+' => 18, 'A' => 16, 'B' => 12, 'C' => 8, 'D' => 4];
        $score += $efficiencyMap[$efficiency] ?? 8;
        
        // Certificaciones (hasta 10 puntos)
        $certifications = $data['certifications'] ?? [];
        $score += min(count($certifications) * 2, 10);
        
        return min(100, round($score));
    }

    /**
     * Obtiene la clasificación de sostenibilidad basada en la puntuación
     */
    private function getSustainabilityRating(int $score): string
    {
        if ($score >= 90) return 'Excelente';
        if ($score >= 80) return 'Muy Bueno';
        if ($score >= 70) return 'Bueno';
        if ($score >= 60) return 'Regular';
        return 'Mejorable';
    }
}
