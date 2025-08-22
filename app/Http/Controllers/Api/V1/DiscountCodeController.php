<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\DiscountCode\StoreDiscountCodeRequest;
use App\Http\Requests\Api\V1\DiscountCode\UpdateDiscountCodeRequest;
use App\Http\Resources\Api\V1\DiscountCode\DiscountCodeResource;
use App\Http\Resources\Api\V1\DiscountCode\DiscountCodeCollection;
use App\Models\DiscountCode;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group Discount Code Management
 *
 * APIs for managing discount codes and promotional offers
 */
class DiscountCodeController extends Controller
{
    /**
     * Display a listing of discount codes.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in code, name, description. Example: "SUMMER20"
     * @queryParam status string Filter by status. Example: "active"
     * @queryParam type string Filter by discount type. Example: "percentage"
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam is_public boolean Filter by public status. Example: true
     * @queryParam discount_min float Minimum discount amount. Example: 10.0
     * @queryParam discount_max float Maximum discount amount. Example: 50.0
     * @queryParam min_order_amount_min float Minimum order amount filter. Example: 100.0
     * @queryParam min_order_amount_max float Maximum order amount filter. Example: 500.0
     * @queryParam sort_by string Sort field. Example: "code"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "asc"
     * @queryParam created_at_from date Filter by creation date from. Example: "2024-01-01"
     * @queryParam created_at_to date Filter by creation date to. Example: "2024-12-31"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "code": "SUMMER20",
     *       "name": "Summer Sale 20% Off",
     *       "description": "Get 20% off on all summer items",
     *       "type": "percentage",
     *       "value": 20.0,
     *       "status": "active",
     *       "is_public": true,
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
            $query = DiscountCode::with(['organization', 'createdBy']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
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

            // Filtros por estado público
            if ($request->has('is_public')) {
                $query->where('is_public', $request->boolean('is_public'));
            }

            // Filtros por valor de descuento
            if ($request->filled('discount_min')) {
                $query->where('value', '>=', $request->discount_min);
            }
            if ($request->filled('discount_max')) {
                $query->where('value', '<=', $request->discount_max);
            }

            // Filtros por monto mínimo de orden
            if ($request->filled('min_order_amount_min')) {
                $query->where('min_order_amount', '>=', $request->min_order_amount_min);
            }
            if ($request->filled('min_order_amount_max')) {
                $query->where('min_order_amount', '<=', $request->min_order_amount_max);
            }

            // Filtros por fecha de creación
            if ($request->filled('created_at_from')) {
                $query->where('created_at', '>=', $request->created_at_from);
            }
            if ($request->filled('created_at_to')) {
                $query->where('created_at', '<=', $request->created_at_to . ' 23:59:59');
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $allowedSortFields = ['code', 'name', 'type', 'value', 'status', 'created_at'];
            
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100); // Limitar entre 1 y 100

            $discountCodes = $query->paginate($perPage);

            return response()->json([
                'data' => DiscountCodeCollection::make($discountCodes),
                'message' => 'Discount codes retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving discount codes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving discount codes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created discount code.
     *
     * @authenticated
     * @bodyParam code string required The discount code. Example: "SUMMER20"
     * @bodyParam name string required The discount name. Example: "Summer Sale 20% Off"
     * @bodyParam description text The discount description. Example: "Get 20% off on all summer items"
     * @bodyParam type string required The discount type. Example: "percentage"
     * @bodyParam value float required The discount value. Example: 20.0
     * @bodyParam max_discount_amount float The maximum discount amount. Example: 100.0
     * @bodyParam min_order_amount float The minimum order amount required. Example: 50.0
     * @bodyParam max_order_amount float The maximum order amount allowed. Example: 1000.0
     * @bodyParam usage_limit integer The maximum number of times this code can be used. Example: 100
     * @bodyParam usage_limit_per_user integer The maximum number of times per user. Example: 1
     * @bodyParam status string required The discount status. Example: "active"
     * @bodyParam is_public boolean Whether the discount is public. Example: true
     * @bodyParam is_first_time_only boolean Whether it's for first-time customers only. Example: false
     * @bodyParam is_new_customer_only boolean Whether it's for new customers only. Example: false
     * @bodyParam is_returning_customer_only boolean Whether it's for returning customers only. Example: false
     * @bodyParam valid_from date The start date of validity. Example: "2024-06-01"
     * @bodyParam valid_until date The end date of validity. Example: "2024-08-31"
     * @bodyParam organization_id integer The organization ID. Example: 1
     * @bodyParam applicable_products array The product IDs this code applies to. Example: [1, 2, 3]
     * @bodyParam applicable_categories array The category IDs this code applies to. Example: [1, 2]
     * @bodyParam excluded_products array The product IDs excluded from this code. Example: [4, 5]
     * @bodyParam excluded_categories array The category IDs excluded from this code. Example: [3]
     * @bodyParam notes text Internal notes. Example: "Summer promotion campaign"
     * @bodyParam tags array The tags. Example: ["summer", "sale", "promotion"]
     *
     * @response 201 {
     *   "message": "Discount code created successfully",
     *   "data": {
     *     "id": 1,
     *     "code": "SUMMER20",
     *     "name": "Summer Sale 20% Off",
     *     "type": "percentage",
     *     "value": 20.0,
     *     "status": "active"
     *   }
     * }
     */
    public function store(StoreDiscountCodeRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $discountCode = DiscountCode::create($request->validated());

            // Cargar relaciones para la respuesta
            $discountCode->load(['organization', 'createdBy']);

            DB::commit();

            return response()->json([
                'message' => 'Discount code created successfully',
                'data' => new DiscountCodeResource($discountCode)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating discount code: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error creating discount code',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified discount code.
     *
     * @authenticated
     * @urlParam discountCode integer required The discount code ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "code": "SUMMER20",
     *     "name": "Summer Sale 20% Off",
     *     "description": "Get 20% off on all summer items",
     *     "type": "percentage",
     *     "value": 20.0,
     *     "status": "active",
     *     "created_at": "2024-01-15T10:00:00Z",
     *     "links": {
     *       "self": "http://localhost/api/v1/discount-codes/1",
     *       "edit": "http://localhost/api/v1/discount-codes/1",
     *       "delete": "http://localhost/api/v1/discount-codes/1"
     *     }
     *   }
     * }
     */
    public function show(DiscountCode $discountCode): JsonResponse
    {
        try {
            $discountCode->load(['organization', 'createdBy']);

            return response()->json([
                'data' => new DiscountCodeResource($discountCode)
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving discount code: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving discount code',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified discount code.
     *
     * @authenticated
     * @urlParam discountCode integer required The discount code ID. Example: 1
     * @bodyParam code string The discount code. Example: "SUMMER25"
     * @bodyParam name string The discount name. Example: "Summer Sale 25% Off"
     * @bodyParam description text The discount description. Example: "Get 25% off on all summer items"
     * @bodyParam type string The discount type. Example: "percentage"
     * @bodyParam value float The discount value. Example: 25.0
     * @bodyParam max_discount_amount float The maximum discount amount. Example: 150.0
     * @bodyParam min_order_amount float The minimum order amount required. Example: 75.0
     * @bodyParam max_order_amount float The maximum order amount allowed. Example: 1500.0
     * @bodyParam usage_limit integer The maximum number of times this code can be used. Example: 150
     * @bodyParam usage_limit_per_user integer The maximum number of times per user. Example: 2
     * @bodyParam status string The discount status. Example: "inactive"
     * @bodyParam is_public boolean Whether the discount is public. Example: false
     * @bodyParam is_first_time_only boolean Whether it's for first-time customers only. Example: true
     * @bodyParam is_new_customer_only boolean Whether it's for new customers only. Example: true
     * @bodyParam is_returning_customer_only boolean Whether it's for returning customers only. Example: false
     * @bodyParam valid_from date The start date of validity. Example: "2024-07-01"
     * @bodyParam valid_until date The end date of validity. Example: "2024-09-30"
     * @bodyParam organization_id integer The organization ID. Example: 2
     * @bodyParam applicable_products array The product IDs this code applies to. Example: [1, 2, 3, 4]
     * @bodyParam applicable_categories array The category IDs this code applies to. Example: [1, 2, 3]
     * @bodyParam excluded_products array The product IDs excluded from this code. Example: [5, 6]
     * @bodyParam excluded_categories array The category IDs excluded from this code. Example: [4]
     * @bodyParam notes text Internal notes. Example: "Updated summer promotion campaign"
     * @bodyParam tags array The tags. Example: ["summer", "sale", "promotion", "updated"]
     *
     * @response 200 {
     *   "message": "Discount code updated successfully",
     *   "data": {
     *     "id": 1,
     *     "code": "SUMMER25",
     *     "name": "Summer Sale 25% Off",
     *     "type": "percentage",
     *     "value": 25.0,
     *     "status": "inactive"
     *   }
     * }
     */
    public function update(UpdateDiscountCodeRequest $request, DiscountCode $discountCode): JsonResponse
    {
        try {
            DB::beginTransaction();

            $discountCode->update($request->validated());

            // Cargar relaciones para la respuesta
            $discountCode->load(['organization', 'createdBy']);

            DB::commit();

            return response()->json([
                'message' => 'Discount code updated successfully',
                'data' => new DiscountCodeResource($discountCode)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating discount code: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating discount code',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified discount code.
     *
     * @authenticated
     * @urlParam discountCode integer required The discount code ID. Example: 1
     *
     * @response 200 {
     *   "message": "Discount code deleted successfully"
     * }
     */
    public function destroy(DiscountCode $discountCode): JsonResponse
    {
        try {
            DB::beginTransaction();

            $discountCode->delete();

            DB::commit();

            return response()->json([
                'message' => 'Discount code deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting discount code: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error deleting discount code',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get active discount codes.
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam type string Filter by discount type. Example: "percentage"
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam min_order_amount float Filter by minimum order amount. Example: 50.0
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "code": "SUMMER20",
     *       "name": "Summer Sale 20% Off",
     *       "type": "percentage",
     *       "value": 20.0,
     *       "min_order_amount": 50.0
     *     }
     *   ]
     * }
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $query = DiscountCode::where('status', 'active')
                                ->where('is_public', true)
                                ->where(function ($q) {
                                    $q->whereNull('valid_until')
                                      ->orWhere('valid_until', '>=', now());
                                })
                                ->where(function ($q) {
                                    $q->whereNull('valid_from')
                                      ->orWhere('valid_from', '<=', now());
                                })
                                ->with(['organization']);

            // Filtros adicionales
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            if ($request->filled('min_order_amount')) {
                $query->where('min_order_amount', '<=', $request->min_order_amount);
            }

            // Ordenar por valor de descuento (mayor primero)
            $query->orderBy('value', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $discountCodes = $query->paginate($perPage);

            return response()->json([
                'data' => DiscountCodeCollection::make($discountCodes),
                'message' => 'Active discount codes retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving active discount codes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving active discount codes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get discount codes by type.
     *
     * @queryParam type string required The discount type. Example: "percentage"
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "code": "SUMMER20",
     *       "name": "Summer Sale 20% Off",
     *       "type": "percentage",
     *       "value": 20.0
     *     }
     *   ]
     * }
     */
    public function byType(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|string|in:percentage,fixed_amount,free_shipping,buy_one_get_one,other'
            ]);

            $query = DiscountCode::where('type', $request->type)
                                ->with(['organization']);

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Ordenar por valor de descuento
            $query->orderBy('value', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $discountCodes = $query->paginate($perPage);

            return response()->json([
                'data' => DiscountCodeCollection::make($discountCodes),
                'message' => "Discount codes of type '{$request->type}' retrieved successfully"
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving discount codes by type: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving discount codes by type',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Validate a discount code.
     *
     * @bodyParam code string required The discount code to validate. Example: "SUMMER20"
     * @bodyParam order_amount float The order amount for validation. Example: 100.0
     * @bodyParam user_id integer The user ID for validation. Example: 1
     * @bodyParam organization_id integer The organization ID. Example: 1
     *
     * @response 200 {
     *   "valid": true,
     *   "discount_code": {
     *     "id": 1,
     *     "code": "SUMMER20",
     *     "name": "Summer Sale 20% Off",
     *     "type": "percentage",
     *     "value": 20.0,
     *     "discount_amount": 20.0
     *   }
     * }
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'order_amount' => 'nullable|numeric|min:0',
                'user_id' => 'nullable|exists:users,id',
                'organization_id' => 'nullable|exists:organizations,id',
            ]);

            $discountCode = DiscountCode::where('code', $request->code)
                                      ->where('status', 'active')
                                      ->first();

            if (!$discountCode) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid discount code'
                ]);
            }

            // Validar fechas
            if ($discountCode->valid_from && now() < $discountCode->valid_from) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Discount code not yet active'
                ]);
            }

            if ($discountCode->valid_until && now() > $discountCode->valid_until) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Discount code has expired'
                ]);
            }

            // Validar monto mínimo de orden
            if ($discountCode->min_order_amount && $request->order_amount < $discountCode->min_order_amount) {
                return response()->json([
                    'valid' => false,
                    'message' => "Minimum order amount of {$discountCode->min_order_amount} required"
                ]);
            }

            // Validar monto máximo de orden
            if ($discountCode->max_order_amount && $request->order_amount > $discountCode->max_order_amount) {
                return response()->json([
                    'valid' => false,
                    'message' => "Maximum order amount of {$discountCode->max_order_amount} exceeded"
                ]);
            }

            // Validar límite de uso
            if ($discountCode->usage_limit && $discountCode->usage_count >= $discountCode->usage_limit) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Discount code usage limit reached'
                ]);
            }

            // Validar límite por usuario
            if ($discountCode->usage_limit_per_user && $request->user_id) {
                $userUsageCount = $discountCode->usageLogs()
                    ->where('user_id', $request->user_id)
                    ->count();
                
                if ($userUsageCount >= $discountCode->usage_limit_per_user) {
                    return response()->json([
                        'valid' => false,
                        'message' => 'Usage limit per user reached'
                    ]);
                }
            }

            // Calcular monto de descuento
            $discountAmount = $this->calculateDiscountAmount($discountCode, $request->order_amount);

            return response()->json([
                'valid' => true,
                'discount_code' => [
                    'id' => $discountCode->id,
                    'code' => $discountCode->code,
                    'name' => $discountCode->name,
                    'type' => $discountCode->type,
                    'value' => $discountCode->value,
                    'discount_amount' => $discountAmount,
                    'min_order_amount' => $discountCode->min_order_amount,
                    'max_discount_amount' => $discountCode->max_discount_amount,
                ],
                'message' => 'Discount code is valid'
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating discount code: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error validating discount code',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get discount code statistics.
     *
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam period string The period for statistics. Example: "month"
     *
     * @response 200 {
     *   "data": {
     *     "total_codes": 150,
     *     "active_codes": 120,
     *     "expired_codes": 20,
     *     "codes_by_type": {
     *       "percentage": 80,
     *       "fixed_amount": 40,
     *       "free_shipping": 20,
     *       "other": 10
     *     },
     *     "codes_by_status": {
     *       "active": 120,
     *       "inactive": 20,
     *       "expired": 10
     *     },
     *     "total_usage": 2500,
     *     "total_discount_amount": 15000.00,
     *     "average_discount_value": 6.0
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->get('organization_id');
            $period = $request->get('period', 'month');

            $query = DiscountCode::query();

            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }

            $totalCodes = $query->count();
            $activeCodes = (clone $query)->where('status', 'active')->count();
            $expiredCodes = (clone $query)->where('valid_until', '<', now())->count();

            // Estadísticas por tipo
            $codesByType = (clone $query)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            // Estadísticas por estado
            $codesByStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Estadísticas de uso
            $totalUsage = (clone $query)->sum('usage_count');
            $totalDiscountAmount = (clone $query)->sum('total_discount_amount');
            $averageDiscountValue = $totalCodes > 0 ? $totalDiscountAmount / $totalCodes : 0;

            return response()->json([
                'data' => [
                    'total_codes' => $totalCodes,
                    'active_codes' => $activeCodes,
                    'expired_codes' => $expiredCodes,
                    'codes_by_type' => $codesByType,
                    'codes_by_status' => $codesByStatus,
                    'total_usage' => $totalUsage,
                    'total_discount_amount' => round($totalDiscountAmount, 2),
                    'average_discount_value' => round($averageDiscountValue, 2)
                ],
                'period' => $period,
                'message' => 'Discount code statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving discount code statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving discount code statistics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Activate a discount code.
     *
     * @authenticated
     * @urlParam discountCode integer required The discount code ID. Example: 1
     *
     * @response 200 {
     *   "message": "Discount code activated successfully"
     * }
     */
    public function activate(DiscountCode $discountCode): JsonResponse
    {
        try {
            if ($discountCode->status === 'active') {
                return response()->json([
                    'message' => 'Discount code is already active'
                ], 422);
            }

            DB::beginTransaction();

            $discountCode->update([
                'status' => 'active',
                'activated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Discount code activated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error activating discount code: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error activating discount code',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Deactivate a discount code.
     *
     * @authenticated
     * @urlParam discountCode integer required The discount code ID. Example: 1
     *
     * @response 200 {
     *   "message": "Discount code deactivated successfully"
     * }
     */
    public function deactivate(DiscountCode $discountCode): JsonResponse
    {
        try {
            if ($discountCode->status === 'inactive') {
                return response()->json([
                    'message' => 'Discount code is already inactive'
                ], 422);
            }

            DB::beginTransaction();

            $discountCode->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Discount code deactivated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deactivating discount code: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error deactivating discount code',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate a discount code.
     *
     * @authenticated
     * @urlParam discountCode integer required The discount code ID. Example: 1
     * @bodyParam code string The new discount code. Example: "WINTER25"
     * @bodyParam name string The new discount name. Example: "Winter Sale 25% Off"
     *
     * @response 200 {
     *   "message": "Discount code duplicated successfully",
     *   "data": {
     *     "id": 2,
     *     "code": "WINTER25",
     *     "name": "Winter Sale 25% Off"
     *   }
     * }
     */
    public function duplicate(Request $request, DiscountCode $discountCode): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'nullable|string|max:50|unique:discount_codes,code',
                'name' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();

            $newDiscountCode = $discountCode->replicate();
            $newDiscountCode->code = $request->code ?? $discountCode->code . '_COPY';
            $newDiscountCode->name = $request->name ?? $discountCode->name . ' (Copy)';
            $newDiscountCode->status = 'inactive';
            $newDiscountCode->usage_count = 0;
            $newDiscountCode->total_discount_amount = 0;
            $newDiscountCode->activated_at = null;
            $newDiscountCode->deactivated_at = null;
            $newDiscountCode->save();

            // Cargar relaciones para la respuesta
            $newDiscountCode->load(['organization', 'createdBy']);

            DB::commit();

            return response()->json([
                'message' => 'Discount code duplicated successfully',
                'data' => new DiscountCodeResource($newDiscountCode)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating discount code: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error duplicating discount code',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Calculate discount amount based on code type and order amount.
     */
    private function calculateDiscountAmount(DiscountCode $discountCode, float $orderAmount): float
    {
        $discountAmount = 0;

        switch ($discountCode->type) {
            case 'percentage':
                $discountAmount = ($orderAmount * $discountCode->value) / 100;
                break;
            case 'fixed_amount':
                $discountAmount = $discountCode->value;
                break;
            case 'free_shipping':
                // Free shipping typically has a fixed value
                $discountAmount = $discountCode->value;
                break;
            case 'buy_one_get_one':
                // BOGO typically has a fixed value
                $discountAmount = $discountCode->value;
                break;
            default:
                $discountAmount = $discountCode->value;
        }

        // Aplicar límite máximo de descuento
        if ($discountCode->max_discount_amount && $discountAmount > $discountCode->max_discount_amount) {
            $discountAmount = $discountCode->max_discount_amount;
        }

        return round($discountAmount, 2);
    }
}
