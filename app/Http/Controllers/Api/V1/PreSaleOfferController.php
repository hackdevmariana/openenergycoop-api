<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\PreSaleOffer\StorePreSaleOfferRequest;
use App\Http\Requests\Api\V1\PreSaleOffer\UpdatePreSaleOfferRequest;
use App\Http\Resources\Api\V1\PreSaleOffer\PreSaleOfferResource;
use App\Http\Resources\Api\V1\PreSaleOffer\PreSaleOfferCollection;
use App\Models\PreSaleOffer;
use App\Models\Organization;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Pre-Sale Offer Management
 *
 * APIs for managing pre-sale offers and early bird promotions
 */
class PreSaleOfferController extends Controller
{
    /**
     * Display a listing of pre-sale offers.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in title, description, product_name. Example: "Early Bird"
     * @queryParam status string Filter by offer status. Example: "active"
     * @queryParam type string Filter by offer type. Example: "discount"
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam product_id integer Filter by product. Example: 1
     * @queryParam is_featured boolean Filter by featured status. Example: true
     * @queryParam discount_min float Minimum discount percentage. Example: 10.0
     * @queryParam discount_max float Maximum discount percentage. Example: 50.0
     * @queryParam price_min float Minimum price. Example: 100.0
     * @queryParam price_max float Maximum price. Example: 1000.0
     * @queryParam sort_by string Sort field. Example: "created_at"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "desc"
     * @queryParam created_at_from date Filter by creation date from. Example: "2024-01-01"
     * @queryParam created_at_to date Filter by creation date to. Example: "2024-12-31"
     * @queryParam is_limited_time boolean Filter by limited time offers. Example: true
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Early Bird Special",
     *       "description": "Get 20% off on pre-orders",
     *       "type": "discount",
     *       "discount_percentage": 20.0,
     *       "status": "active",
     *       "is_featured": true,
     *       "created_at": "2024-01-15T10:00:00Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "total": 25,
     *     "per_page": 15
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PreSaleOffer::with(['organization', 'product', 'createdBy']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('product_name', 'like', "%{$search}%")
                      ->orWhere('terms_conditions', 'like', "%{$search}%");
                });
            }

            // Filtros por estado
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtros por tipo
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Filtros por organización
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Filtros por producto
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            // Filtros por estado destacado
            if ($request->has('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            // Filtros por descuento
            if ($request->filled('discount_min')) {
                $query->where('discount_percentage', '>=', $request->discount_min);
            }
            if ($request->filled('discount_max')) {
                $query->where('discount_percentage', '<=', $request->discount_max);
            }

            // Filtros por precio
            if ($request->filled('price_min')) {
                $query->where('price', '>=', $request->price_min);
            }
            if ($request->filled('price_max')) {
                $query->where('price', '<=', $request->price_max);
            }

            // Filtros por fecha de creación
            if ($request->filled('created_at_from')) {
                $query->where('created_at', '>=', $request->created_at_from);
            }
            if ($request->filled('created_at_to')) {
                $query->where('created_at', '<=', $request->created_at_to . ' 23:59:59');
            }

            // Filtros por tiempo limitado
            if ($request->has('is_limited_time')) {
                $query->where('is_limited_time', $request->boolean('is_limited_time'));
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $allowedSortFields = ['title', 'type', 'discount_percentage', 'price', 'status', 'created_at'];
            
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100); // Limitar entre 1 y 100

            $preSaleOffers = $query->paginate($perPage);

            return response()->json([
                'data' => PreSaleOfferCollection::make($preSaleOffers),
                'message' => 'Pre-sale offers retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving pre-sale offers: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving pre-sale offers',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created pre-sale offer.
     *
     * @authenticated
     * @bodyParam title string required The offer title. Example: "Early Bird Special"
     * @bodyParam description text The offer description. Example: "Get 20% off on pre-orders"
     * @bodyParam type string required The offer type. Example: "discount"
     * @bodyParam discount_percentage float The discount percentage. Example: 20.0
     * @bodyParam fixed_discount_amount float The fixed discount amount. Example: 50.0
     * @bodyParam original_price float The original price. Example: 200.0
     * @bodyParam price float The offer price. Example: 160.0
     * @bodyParam currency string The currency code. Example: "USD"
     * @bodyParam status string required The offer status. Example: "active"
     * @bodyParam is_featured boolean Whether the offer is featured. Example: true
     * @bodyParam is_limited_time boolean Whether it's a limited time offer. Example: true
     * @bodyParam start_date date The start date. Example: "2024-01-01"
     * @bodyParam end_date date The end date. Example: "2024-03-31"
     * @bodyParam max_quantity integer The maximum quantity available. Example: 100
     * @bodyParam min_quantity integer The minimum quantity required. Example: 1
     * @bodyParam product_id integer The product ID. Example: 1
     * @bodyParam product_name string The product name. Example: "Premium Widget"
     * @bodyParam terms_conditions text The terms and conditions. Example: "Valid until March 31st"
     * @bodyParam organization_id integer The organization ID. Example: 1
     * @bodyParam tags array The tags. Example: ["early-bird", "pre-sale", "discount"]
     * @bodyParam requirements array The requirements. Example: ["pre-order", "payment-in-advance"]
     * @bodyParam benefits array The benefits. Example: ["exclusive-access", "limited-edition"]
     * @bodyParam restrictions array The restrictions. Example: ["one-per-customer", "non-transferable"]
     *
     * @response 201 {
     *   "message": "Pre-sale offer created successfully",
     *   "data": {
     *     "id": 1,
     *     "title": "Early Bird Special",
     *     "type": "discount",
     *     "discount_percentage": 20.0,
     *     "status": "active"
     *   }
     * }
     */
    public function store(StorePreSaleOfferRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $preSaleOffer = PreSaleOffer::create($request->validated());

            // Cargar relaciones para la respuesta
            $preSaleOffer->load(['organization', 'product', 'createdBy']);

            DB::commit();

            return response()->json([
                'message' => 'Pre-sale offer created successfully',
                'data' => new PreSaleOfferResource($preSaleOffer)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating pre-sale offer: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error creating pre-sale offer',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified pre-sale offer.
     *
     * @authenticated
     * @urlParam preSaleOffer integer required The pre-sale offer ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "title": "Early Bird Special",
     *     "description": "Get 20% off on pre-orders",
     *     "type": "discount",
     *     "discount_percentage": 20.0,
     *     "status": "active",
     *     "created_at": "2024-01-15T10:00:00Z",
     *     "links": {
     *       "self": "http://localhost/api/v1/pre-sale-offers/1",
     *       "edit": "http://localhost/api/v1/pre-sale-offers/1",
     *       "delete": "http://localhost/api/v1/pre-sale-offers/1"
     *     }
     *   }
     * }
     */
    public function show(PreSaleOffer $preSaleOffer): JsonResponse
    {
        try {
            $preSaleOffer->load(['organization', 'product', 'createdBy']);

            return response()->json([
                'data' => new PreSaleOfferResource($preSaleOffer)
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving pre-sale offer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving pre-sale offer',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified pre-sale offer.
     *
     * @authenticated
     * @urlParam preSaleOffer integer required The pre-sale offer ID. Example: 1
     * @bodyParam title string The offer title. Example: "Updated Early Bird Special"
     * @bodyParam description text The offer description. Example: "Get 25% off on pre-orders"
     * @bodyParam type string The offer type. Example: "discount"
     * @bodyParam discount_percentage float The discount percentage. Example: 25.0
     * @bodyParam fixed_discount_amount float The fixed discount amount. Example: 60.0
     * @bodyParam original_price float The original price. Example: 250.0
     * @bodyParam price float The offer price. Example: 187.5
     * @bodyParam status string The offer status. Example: "inactive"
     * @bodyParam is_featured boolean Whether the offer is featured. Example: false
     * @bodyParam is_limited_time boolean Whether it's a limited time offer. Example: false
     * @bodyParam start_date date The start date. Example: "2024-02-01"
     * @bodyParam end_date date The end date. Example: "2024-04-30"
     * @bodyParam max_quantity integer The maximum quantity available. Example: 150
     * @bodyParam min_quantity integer The minimum quantity required. Example: 2
     * @bodyParam terms_conditions text The terms and conditions. Example: "Valid until April 30th"
     * @bodyParam tags array The tags. Example: ["updated", "early-bird", "pre-sale"]
     * @bodyParam requirements array The requirements. Example: ["pre-order", "payment-in-advance", "min-quantity-2"]
     * @bodyParam benefits array The benefits. Example: ["exclusive-access", "limited-edition", "priority-shipping"]
     * @bodyParam restrictions array The restrictions. Example: ["one-per-customer", "non-transferable", "while-supplies-last"]
     *
     * @response 200 {
     *   "message": "Pre-sale offer updated successfully",
     *   "data": {
     *     "id": 1,
     *     "title": "Updated Early Bird Special",
     *     "type": "discount",
     *     "discount_percentage": 25.0,
     *     "status": "inactive"
     *   }
     * }
     */
    public function update(UpdatePreSaleOfferRequest $request, PreSaleOffer $preSaleOffer): JsonResponse
    {
        try {
            DB::beginTransaction();

            $preSaleOffer->update($request->validated());

            // Cargar relaciones para la respuesta
            $preSaleOffer->load(['organization', 'product', 'createdBy']);

            DB::commit();

            return response()->json([
                'message' => 'Pre-sale offer updated successfully',
                'data' => new PreSaleOfferResource($preSaleOffer)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating pre-sale offer: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating pre-sale offer',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified pre-sale offer.
     *
     * @authenticated
     * @urlParam preSaleOffer integer required The pre-sale offer ID. Example: 1
     *
     * @response 200 {
     *   "message": "Pre-sale offer deleted successfully"
     * }
     */
    public function destroy(PreSaleOffer $preSaleOffer): JsonResponse
    {
        try {
            DB::beginTransaction();

            $preSaleOffer->delete();

            DB::commit();

            return response()->json([
                'message' => 'Pre-sale offer deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting pre-sale offer: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error deleting pre-sale offer',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get active pre-sale offers.
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam product_id integer Filter by product. Example: 1
     * @queryParam type string Filter by offer type. Example: "discount"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Early Bird Special",
     *       "type": "discount",
     *       "discount_percentage": 20.0,
     *       "status": "active"
     *     }
     *   ]
     * }
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $query = PreSaleOffer::where('status', 'active')
                                ->where(function ($q) {
                                    $q->whereNull('end_date')
                                      ->orWhere('end_date', '>=', now());
                                })
                                ->where(function ($q) {
                                    $q->whereNull('start_date')
                                      ->orWhere('start_date', '<=', now());
                                })
                                ->with(['organization', 'product']);

            // Filtros adicionales
            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Ordenar por descuento (mayor primero) y luego por fecha de creación
            $query->orderBy('discount_percentage', 'desc')
                  ->orderBy('created_at', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $preSaleOffers = $query->paginate($perPage);

            return response()->json([
                'data' => PreSaleOfferCollection::make($preSaleOffers),
                'message' => 'Active pre-sale offers retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving active pre-sale offers: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving active pre-sale offers',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get featured pre-sale offers.
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Early Bird Special",
     *       "type": "discount",
     *       "discount_percentage": 20.0,
     *       "is_featured": true
     *     }
     *   ]
     * }
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $query = PreSaleOffer::where('is_featured', true)
                                ->where('status', 'active')
                                ->with(['organization', 'product']);

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Ordenar por descuento (mayor primero) y luego por fecha de creación
            $query->orderBy('discount_percentage', 'desc')
                  ->orderBy('created_at', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $preSaleOffers = $query->paginate($perPage);

            return response()->json([
                'data' => PreSaleOfferCollection::make($preSaleOffers),
                'message' => 'Featured pre-sale offers retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving featured pre-sale offers: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving featured pre-sale offers',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pre-sale offers by type.
     *
     * @queryParam type string required The offer type. Example: "discount"
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Early Bird Special",
     *       "type": "discount",
     *       "discount_percentage": 20.0
     *     }
     *   ]
     * }
     */
    public function byType(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|string|in:discount,fixed_amount,free_shipping,buy_one_get_one,other'
            ]);

            $query = PreSaleOffer::where('type', $request->type)
                                ->where('status', 'active')
                                ->with(['organization', 'product']);

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Ordenar por descuento (mayor primero)
            $query->orderBy('discount_percentage', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $preSaleOffers = $query->paginate($perPage);

            return response()->json([
                'data' => PreSaleOfferCollection::make($preSaleOffers),
                'message' => "Pre-sale offers of type '{$request->type}' retrieved successfully"
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving pre-sale offers by type: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving pre-sale offers by type',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pre-sale offers by product.
     *
     * @queryParam product_id integer required The product ID. Example: 1
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Early Bird Special",
     *       "type": "discount",
     *       "discount_percentage": 20.0,
     *       "product_name": "Premium Widget"
     *     }
     *   ]
     * }
     */
    public function byProduct(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $query = PreSaleOffer::where('product_id', $request->product_id)
                                ->where('status', 'active')
                                ->with(['organization', 'product']);

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Ordenar por descuento (mayor primero)
            $query->orderBy('discount_percentage', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $preSaleOffers = $query->paginate($perPage);

            return response()->json([
                'data' => PreSaleOfferCollection::make($preSaleOffers),
                'message' => 'Product pre-sale offers retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving product pre-sale offers: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving product pre-sale offers',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get pre-sale offer statistics.
     *
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam period string The period for statistics. Example: "month"
     * @queryParam product_id integer Filter by product. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "total_offers": 150,
     *     "active_offers": 120,
     *     "expired_offers": 20,
     *     "offers_by_type": {
     *       "discount": 80,
     *       "fixed_amount": 40,
     *       "free_shipping": 20,
     *       "other": 10
     *     },
     *     "offers_by_status": {
     *       "active": 120,
     *       "inactive": 20,
     *       "expired": 10
     *     },
     *     "total_discount_value": 5000.00,
     *     "average_discount_percentage": 25.5,
     *     "featured_offers": 30
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->get('organization_id');
            $period = $request->get('period', 'month');
            $productId = $request->get('product_id');

            $query = PreSaleOffer::query();

            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }

            if ($productId) {
                $query->where('product_id', $productId);
            }

            $totalOffers = $query->count();
            $activeOffers = (clone $query)->where('status', 'active')->count();
            $expiredOffers = (clone $query)->where('end_date', '<', now())->count();

            // Estadísticas por tipo
            $offersByType = (clone $query)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            // Estadísticas por estado
            $offersByStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Estadísticas de descuentos
            $totalDiscountValue = (clone $query)->sum('discount_percentage');
            $averageDiscountPercentage = $totalOffers > 0 ? $totalDiscountValue / $totalOffers : 0;

            // Ofertas destacadas
            $featuredOffers = (clone $query)->where('is_featured', true)->count();

            return response()->json([
                'data' => [
                    'total_offers' => $totalOffers,
                    'active_offers' => $activeOffers,
                    'expired_offers' => $expiredOffers,
                    'offers_by_type' => $offersByType,
                    'offers_by_status' => $offersByStatus,
                    'total_discount_value' => round($totalDiscountValue, 2),
                    'average_discount_percentage' => round($averageDiscountPercentage, 1),
                    'featured_offers' => $featuredOffers
                ],
                'period' => $period,
                'message' => 'Pre-sale offer statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving pre-sale offer statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving pre-sale offer statistics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Activate a pre-sale offer.
     *
     * @authenticated
     * @urlParam preSaleOffer integer required The pre-sale offer ID. Example: 1
     *
     * @response 200 {
     *   "message": "Pre-sale offer activated successfully"
     * }
     */
    public function activate(PreSaleOffer $preSaleOffer): JsonResponse
    {
        try {
            if ($preSaleOffer->status === 'active') {
                return response()->json([
                    'message' => 'Pre-sale offer is already active'
                ], 422);
            }

            DB::beginTransaction();

            $preSaleOffer->update([
                'status' => 'active',
                'activated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pre-sale offer activated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error activating pre-sale offer: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error activating pre-sale offer',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Deactivate a pre-sale offer.
     *
     * @authenticated
     * @urlParam preSaleOffer integer required The pre-sale offer ID. Example: 1
     *
     * @response 200 {
     *   "message": "Pre-sale offer deactivated successfully"
     * }
     */
    public function deactivate(PreSaleOffer $preSaleOffer): JsonResponse
    {
        try {
            if ($preSaleOffer->status === 'inactive') {
                return response()->json([
                    'message' => 'Pre-sale offer is already inactive'
                ], 422);
            }

            DB::beginTransaction();

            $preSaleOffer->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pre-sale offer deactivated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deactivating pre-sale offer: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error deactivating pre-sale offer',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Toggle featured status of a pre-sale offer.
     *
     * @authenticated
     * @urlParam preSaleOffer integer required The pre-sale offer ID. Example: 1
     * @bodyParam is_featured boolean Whether the offer should be featured. Example: true
     *
     * @response 200 {
     *   "message": "Pre-sale offer featured status updated successfully"
     * }
     */
    public function toggleFeatured(Request $request, PreSaleOffer $preSaleOffer): JsonResponse
    {
        try {
            $request->validate([
                'is_featured' => 'required|boolean'
            ]);

            DB::beginTransaction();

            $preSaleOffer->update([
                'is_featured' => $request->is_featured,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pre-sale offer featured status updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating pre-sale offer featured status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating pre-sale offer featured status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate a pre-sale offer.
     *
     * @authenticated
     * @urlParam preSaleOffer integer required The pre-sale offer ID. Example: 1
     * @bodyParam title string The new offer title. Example: "Copy of Early Bird Special"
     * @bodyParam product_id integer The new product ID. Example: 2
     *
     * @response 200 {
     *   "message": "Pre-sale offer duplicated successfully",
     *   "data": {
     *     "id": 2,
     *     "title": "Copy of Early Bird Special",
     *     "type": "discount",
     *     "status": "inactive"
     *   }
     * }
     */
    public function duplicate(Request $request, PreSaleOffer $preSaleOffer): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'nullable|string|max:255',
                'product_id' => 'nullable|exists:products,id',
            ]);

            DB::beginTransaction();

            $newPreSaleOffer = $preSaleOffer->replicate();
            $newPreSaleOffer->title = $request->title ?? $preSaleOffer->title . ' (Copy)';
            $newPreSaleOffer->product_id = $request->product_id ?? $preSaleOffer->product_id;
            $newPreSaleOffer->status = 'inactive';
            $newPreSaleOffer->activated_at = null;
            $newPreSaleOffer->deactivated_at = null;
            $newPreSaleOffer->save();

            // Cargar relaciones para la respuesta
            $newPreSaleOffer->load(['organization', 'product', 'createdBy']);

            DB::commit();

            return response()->json([
                'message' => 'Pre-sale offer duplicated successfully',
                'data' => new PreSaleOfferResource($newPreSaleOffer)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating pre-sale offer: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error duplicating pre-sale offer',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Validate a pre-sale offer.
     *
     * @bodyParam offer_id integer required The pre-sale offer ID. Example: 1
     * @bodyParam quantity integer The quantity requested. Example: 2
     * @bodyParam customer_id integer The customer ID. Example: 1
     *
     * @response 200 {
     *   "valid": true,
     *   "offer": {
     *     "id": 1,
     *     "title": "Early Bird Special",
     *     "type": "discount",
     *     "discount_percentage": 20.0,
     *     "final_price": 160.00
     *   }
     * }
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'offer_id' => 'required|exists:pre_sale_offers,id',
                'quantity' => 'nullable|integer|min:1',
                'customer_id' => 'nullable|exists:users,id',
            ]);

            $preSaleOffer = PreSaleOffer::findOrFail($request->offer_id);

            // Validar estado activo
            if ($preSaleOffer->status !== 'active') {
                return response()->json([
                    'valid' => false,
                    'message' => 'Pre-sale offer is not active'
                ]);
            }

            // Validar fechas
            if ($preSaleOffer->start_date && now() < $preSaleOffer->start_date) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Pre-sale offer has not started yet'
                ]);
            }

            if ($preSaleOffer->end_date && now() > $preSaleOffer->end_date) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Pre-sale offer has expired'
                ]);
            }

            // Validar cantidad mínima
            if ($preSaleOffer->min_quantity && $request->quantity < $preSaleOffer->min_quantity) {
                return response()->json([
                    'valid' => false,
                    'message' => "Minimum quantity of {$preSaleOffer->min_quantity} required"
                ]);
            }

            // Validar cantidad máxima
            if ($preSaleOffer->max_quantity && $request->quantity > $preSaleOffer->max_quantity) {
                return response()->json([
                    'valid' => false,
                    'message' => "Maximum quantity of {$preSaleOffer->max_quantity} allowed"
                ]);
            }

            // Calcular precio final
            $quantity = $request->quantity ?? 1;
            $finalPrice = $this->calculateFinalPrice($preSaleOffer, $quantity);

            return response()->json([
                'valid' => true,
                'offer' => [
                    'id' => $preSaleOffer->id,
                    'title' => $preSaleOffer->title,
                    'type' => $preSaleOffer->type,
                    'discount_percentage' => $preSaleOffer->discount_percentage,
                    'original_price' => $preSaleOffer->original_price,
                    'final_price' => $finalPrice,
                    'quantity' => $quantity,
                    'total_savings' => ($preSaleOffer->original_price * $quantity) - $finalPrice,
                ],
                'message' => 'Pre-sale offer is valid'
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating pre-sale offer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error validating pre-sale offer',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Calculate final price based on offer type and quantity.
     */
    private function calculateFinalPrice(PreSaleOffer $preSaleOffer, int $quantity): float
    {
        $basePrice = $preSaleOffer->original_price ?? 0;
        $offerPrice = $preSaleOffer->price ?? $basePrice;

        switch ($preSaleOffer->type) {
            case 'discount':
                if ($preSaleOffer->discount_percentage) {
                    $discountAmount = ($basePrice * $preSaleOffer->discount_percentage) / 100;
                    $finalPrice = $basePrice - $discountAmount;
                } else {
                    $finalPrice = $offerPrice;
                }
                break;
            case 'fixed_amount':
                if ($preSaleOffer->fixed_discount_amount) {
                    $finalPrice = max(0, $basePrice - $preSaleOffer->fixed_discount_amount);
                } else {
                    $finalPrice = $offerPrice;
                }
                break;
            case 'free_shipping':
                $finalPrice = $basePrice; // Shipping cost would be handled separately
                break;
            case 'buy_one_get_one':
                // BOGO logic: pay for half the quantity
                $finalPrice = $basePrice * ceil($quantity / 2);
                break;
            default:
                $finalPrice = $offerPrice;
        }

        return round($finalPrice * $quantity, 2);
    }
}
