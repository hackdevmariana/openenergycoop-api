<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Affiliate\StoreAffiliateRequest;
use App\Http\Requests\Api\V1\Affiliate\UpdateAffiliateRequest;
use App\Http\Resources\Api\V1\Affiliate\AffiliateResource;
use App\Http\Resources\Api\V1\Affiliate\AffiliateCollection;
use App\Models\Affiliate;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @group Affiliate Management
 *
 * APIs for managing affiliate relationships and partnerships
 */
class AffiliateController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of affiliates.
     *
     * @authenticated
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam search string Search in name, email, company_name, website. Example: "energy"
     * @queryParam status string Filter by status. Example: "active"
     * @queryParam type string Filter by affiliate type. Example: "partner"
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam sort_by string Sort field. Example: "name"
     * @queryParam sort_direction string Sort direction (asc/desc). Example: "asc"
     * @queryParam is_verified boolean Filter by verification status. Example: true
     * @queryParam commission_rate_min float Minimum commission rate. Example: 5.0
     * @queryParam commission_rate_max float Maximum commission rate. Example: 15.0
     * @queryParam performance_rating_min integer Minimum performance rating. Example: 3
     * @queryParam performance_rating_max integer Maximum performance rating. Example: 5
     * @queryParam created_at_from date Filter by creation date from. Example: "2024-01-01"
     * @queryParam created_at_to date Filter by creation date to. Example: "2024-12-31"
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Energy Partner Corp",
     *       "email": "partner@energycorp.com",
     *       "company_name": "Energy Partner Corporation",
     *       "website": "https://energycorp.com",
     *       "status": "active",
     *       "type": "partner",
     *       "commission_rate": 10.5,
     *       "performance_rating": 4,
     *       "is_verified": true,
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
            $query = Affiliate::with(['organization', 'user']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('website', 'like', "%{$search}%")
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

            // Filtros por verificación
            if ($request->has('is_verified')) {
                $query->where('is_verified', $request->boolean('is_verified'));
            }

            // Filtros por tasa de comisión
            if ($request->filled('commission_rate_min')) {
                $query->where('commission_rate', '>=', $request->commission_rate_min);
            }
            if ($request->filled('commission_rate_max')) {
                $query->where('commission_rate', '<=', $request->commission_rate_max);
            }

            // Filtros por rating de rendimiento
            if ($request->filled('performance_rating_min')) {
                $query->where('performance_rating', '>=', $request->performance_rating_min);
            }
            if ($request->filled('performance_rating_max')) {
                $query->where('performance_rating', '<=', $request->performance_rating_max);
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
            $allowedSortFields = ['name', 'email', 'company_name', 'status', 'type', 'commission_rate', 'performance_rating', 'created_at'];
            
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100); // Limitar entre 1 y 100

            $affiliates = $query->paginate($perPage);

            return response()->json([
                'data' => AffiliateCollection::make($affiliates),
                'message' => 'Affiliates retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving affiliates: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving affiliates',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created affiliate.
     *
     * @authenticated
     * @bodyParam name string required The affiliate name. Example: "Energy Partner Corp"
     * @bodyParam email string required The affiliate email. Example: "partner@energycorp.com"
     * @bodyParam company_name string The company name. Example: "Energy Partner Corporation"
     * @bodyParam website string The website URL. Example: "https://energycorp.com"
     * @bodyParam phone string The phone number. Example: "+1-555-0123"
     * @bodyParam address string The address. Example: "123 Energy Street"
     * @bodyParam city string The city. Example: "Energy City"
     * @bodyParam state string The state/province. Example: "Energy State"
     * @bodyParam country string The country. Example: "United States"
     * @bodyParam postal_code string The postal code. Example: "12345"
     * @bodyParam description text The description. Example: "Leading energy partner"
     * @bodyParam type string required The affiliate type. Example: "partner"
     * @bodyParam status string required The affiliate status. Example: "active"
     * @bodyParam commission_rate float The commission rate percentage. Example: 10.5
     * @bodyParam payment_terms string The payment terms. Example: "Net 30"
     * @bodyParam contract_start_date date The contract start date. Example: "2024-01-01"
     * @bodyParam contract_end_date date The contract end date. Example: "2024-12-31"
     * @bodyParam organization_id integer The organization ID. Example: 1
     * @bodyParam user_id integer The user ID. Example: 1
     * @bodyParam is_verified boolean Whether the affiliate is verified. Example: false
     * @bodyParam notes text Internal notes. Example: "New partner"
     * @bodyParam tags array The tags. Example: ["energy", "partner"]
     *
     * @response 201 {
     *   "message": "Affiliate created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Energy Partner Corp",
     *     "email": "partner@energycorp.com",
     *     "company_name": "Energy Partner Corporation",
     *     "status": "active",
     *     "type": "partner"
     *   }
     * }
     */
    public function store(StoreAffiliateRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $affiliate = Affiliate::create($request->validated());

            // Cargar relaciones para la respuesta
            $affiliate->load(['organization', 'user']);

            DB::commit();

            return response()->json([
                'message' => 'Affiliate created successfully',
                'data' => new AffiliateResource($affiliate)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating affiliate: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error creating affiliate',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified affiliate.
     *
     * @authenticated
     * @urlParam affiliate integer required The affiliate ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Energy Partner Corp",
     *     "email": "partner@energycorp.com",
     *     "company_name": "Energy Partner Corporation",
     *     "website": "https://energycorp.com",
     *     "status": "active",
     *     "type": "partner",
     *     "commission_rate": 10.5,
     *     "performance_rating": 4,
     *     "is_verified": true,
     *     "created_at": "2024-01-15T10:00:00Z",
     *     "links": {
     *       "self": "http://localhost/api/v1/affiliates/1",
     *       "edit": "http://localhost/api/v1/affiliates/1",
     *       "delete": "http://localhost/api/v1/affiliates/1"
     *     }
     *   }
     * }
     */
    public function show(Affiliate $affiliate): JsonResponse
    {
        try {
            $affiliate->load(['organization', 'user']);

            return response()->json([
                'data' => new AffiliateResource($affiliate)
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving affiliate: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving affiliate',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified affiliate.
     *
     * @authenticated
     * @urlParam affiliate integer required The affiliate ID. Example: 1
     * @bodyParam name string The affiliate name. Example: "Updated Energy Partner Corp"
     * @bodyParam email string The affiliate email. Example: "updated@energycorp.com"
     * @bodyParam company_name string The company name. Example: "Updated Energy Partner Corporation"
     * @bodyParam website string The website URL. Example: "https://updated.energycorp.com"
     * @bodyParam phone string The phone number. Example: "+1-555-9999"
     * @bodyParam address string The address. Example: "456 Updated Street"
     * @bodyParam city string The city. Example: "Updated City"
     * @bodyParam state string The state/province. Example: "Updated State"
     * @bodyParam country string The country. Example: "Canada"
     * @bodyParam postal_code string The postal code. Example: "A1B2C3"
     * @bodyParam description text The description. Example: "Updated energy partner"
     * @bodyParam type string The affiliate type. Example: "reseller"
     * @bodyParam status string The affiliate status. Example: "inactive"
     * @bodyParam commission_rate float The commission rate percentage. Example: 12.0
     * @bodyParam payment_terms string The payment terms. Example: "Net 45"
     * @bodyParam contract_start_date date The contract start date. Example: "2024-02-01"
     * @bodyParam contract_end_date date The contract end date. Example: "2025-01-31"
     * @bodyParam organization_id integer The organization ID. Example: 2
     * @bodyParam user_id integer The user ID. Example: 2
     * @bodyParam is_verified boolean Whether the affiliate is verified. Example: true
     * @bodyParam notes text Internal notes. Example: "Updated partner information"
     * @bodyParam tags array The tags. Example: ["energy", "reseller", "verified"]
     *
     * @response 200 {
     *   "message": "Affiliate updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Updated Energy Partner Corp",
     *     "email": "updated@energycorp.com",
     *     "company_name": "Updated Energy Partner Corporation",
     *     "status": "inactive",
     *     "type": "reseller"
     *   }
     * }
     */
    public function update(UpdateAffiliateRequest $request, Affiliate $affiliate): JsonResponse
    {
        try {
            DB::beginTransaction();

            $affiliate->update($request->validated());

            // Cargar relaciones para la respuesta
            $affiliate->load(['organization', 'user']);

            DB::commit();

            return response()->json([
                'message' => 'Affiliate updated successfully',
                'data' => new AffiliateResource($affiliate)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating affiliate: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating affiliate',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified affiliate.
     *
     * @authenticated
     * @urlParam affiliate integer required The affiliate ID. Example: 1
     *
     * @response 200 {
     *   "message": "Affiliate deleted successfully"
     * }
     */
    public function destroy(Affiliate $affiliate): JsonResponse
    {
        try {
            DB::beginTransaction();

            $affiliate->delete();

            DB::commit();

            return response()->json([
                'message' => 'Affiliate deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting affiliate: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error deleting affiliate',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get active affiliates.
     *
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam type string Filter by affiliate type. Example: "partner"
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Energy Partner Corp",
     *       "company_name": "Energy Partner Corporation",
     *       "website": "https://energycorp.com",
     *       "type": "partner",
     *       "commission_rate": 10.5
     *     }
     *   ]
     * }
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $query = Affiliate::where('status', 'active')
                             ->where('is_verified', true)
                             ->with(['organization']);

            // Filtros adicionales
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Ordenar por nombre
            $query->orderBy('name', 'asc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $affiliates = $query->paginate($perPage);

            return response()->json([
                'data' => AffiliateCollection::make($affiliates),
                'message' => 'Active affiliates retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving active affiliates: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving active affiliates',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get affiliates by type.
     *
     * @queryParam type string required The affiliate type. Example: "partner"
     * @queryParam page integer The page number for pagination. Example: 1
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Energy Partner Corp",
     *       "company_name": "Energy Partner Corporation",
     *       "type": "partner",
     *       "status": "active"
     *     }
     *   ]
     * }
     */
    public function byType(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|string|in:partner,reseller,distributor,consultant,other'
            ]);

            $query = Affiliate::where('type', $request->type)
                             ->with(['organization']);

            if ($request->filled('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Ordenar por nombre
            $query->orderBy('name', 'asc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100);

            $affiliates = $query->paginate($perPage);

            return response()->json([
                'data' => AffiliateCollection::make($affiliates),
                'message' => "Affiliates of type '{$request->type}' retrieved successfully"
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving affiliates by type: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving affiliates by type',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get top performing affiliates.
     *
     * @queryParam limit integer Number of affiliates to return. Example: 10
     * @queryParam period string The period for performance calculation. Example: "month"
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Top Energy Partner",
     *       "company_name": "Top Energy Corporation",
     *       "performance_rating": 5,
     *       "commission_rate": 12.0,
     *       "total_revenue": 50000.00
     *     }
     *   ],
     *   "period": "month",
     *   "total_affiliates": 25
     * }
     */
    public function topPerformers(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 10), 50);
            $period = $request->get('period', 'month');
            $organizationId = $request->get('organization_id');

            $query = Affiliate::where('status', 'active')
                             ->where('is_verified', true);

            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }

            // Ordenar por rating de rendimiento y luego por tasa de comisión
            $affiliates = $query->orderBy('performance_rating', 'desc')
                               ->orderBy('commission_rate', 'desc')
                               ->limit($limit)
                               ->get();

            return response()->json([
                'data' => AffiliateResource::collection($affiliates),
                'period' => $period,
                'total_affiliates' => Affiliate::where('status', 'active')->count(),
                'message' => 'Top performing affiliates retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving top performers: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving top performers',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get affiliate statistics.
     *
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam period string The period for statistics. Example: "year"
     *
     * @response 200 {
     *   "data": {
     *     "total_affiliates": 150,
     *     "active_affiliates": 120,
     *     "verified_affiliates": 95,
     *     "affiliates_by_type": {
     *       "partner": 45,
     *       "reseller": 30,
     *       "distributor": 25,
     *       "consultant": 20,
     *       "other": 30
     *     },
     *     "affiliates_by_status": {
     *       "active": 120,
     *       "inactive": 20,
     *       "pending": 10
     *     },
     *     "average_commission_rate": 12.5,
     *     "average_performance_rating": 4.2,
     *     "monthly_growth": 5.2
     *   }
     * }
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->get('organization_id');
            $period = $request->get('period', 'year');

            $query = Affiliate::query();

            if ($organizationId) {
                $query->where('organization_id', $organizationId);
            }

            $totalAffiliates = $query->count();
            $activeAffiliates = (clone $query)->where('status', 'active')->count();
            $verifiedAffiliates = (clone $query)->where('is_verified', true)->count();

            // Estadísticas por tipo
            $affiliatesByType = (clone $query)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            // Estadísticas por estado
            $affiliatesByStatus = (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Promedios
            $averageCommissionRate = (clone $query)->avg('commission_rate');
            $averagePerformanceRating = (clone $query)->avg('performance_rating');

            // Crecimiento mensual (simulado)
            $monthlyGrowth = $this->calculateMonthlyGrowth($query, $period);

            return response()->json([
                'data' => [
                    'total_affiliates' => $totalAffiliates,
                    'active_affiliates' => $activeAffiliates,
                    'verified_affiliates' => $verifiedAffiliates,
                    'affiliates_by_type' => $affiliatesByType,
                    'affiliates_by_status' => $affiliatesByStatus,
                    'average_commission_rate' => round($averageCommissionRate, 2),
                    'average_performance_rating' => round($averagePerformanceRating, 1),
                    'monthly_growth' => round($monthlyGrowth, 1)
                ],
                'period' => $period,
                'message' => 'Affiliate statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving affiliate statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving affiliate statistics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Verify an affiliate.
     *
     * @authenticated
     * @urlParam affiliate integer required The affiliate ID. Example: 1
     * @bodyParam verification_notes string Notes about the verification. Example: "Documents verified"
     *
     * @response 200 {
     *   "message": "Affiliate verified successfully"
     * }
     */
    public function verify(Request $request, Affiliate $affiliate): JsonResponse
    {
        try {
            $request->validate([
                'verification_notes' => 'nullable|string|max:1000'
            ]);

            if ($affiliate->is_verified) {
                return response()->json([
                    'message' => 'Affiliate is already verified'
                ], 422);
            }

            DB::beginTransaction();

            $affiliate->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verification_notes' => $request->verification_notes
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Affiliate verified successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verifying affiliate: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error verifying affiliate',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update affiliate performance rating.
     *
     * @authenticated
     * @urlParam affiliate integer required The affiliate ID. Example: 1
     * @bodyParam performance_rating integer required The performance rating (1-5). Example: 4
     * @bodyParam rating_notes string Notes about the rating. Example: "Excellent performance"
     *
     * @response 200 {
     *   "message": "Performance rating updated successfully"
     * }
     */
    public function updatePerformanceRating(Request $request, Affiliate $affiliate): JsonResponse
    {
        try {
            $request->validate([
                'performance_rating' => 'required|integer|between:1,5',
                'rating_notes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            $affiliate->update([
                'performance_rating' => $request->performance_rating,
                'rating_notes' => $request->rating_notes,
                'rating_updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Performance rating updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating performance rating: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating performance rating',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update affiliate commission rate.
     *
     * @authenticated
     * @urlParam affiliate integer required The affiliate ID. Example: 1
     * @bodyParam commission_rate float required The new commission rate percentage. Example: 12.5
     * @bodyParam rate_change_reason string Reason for the rate change. Example: "Performance improvement"
     *
     * @response 200 {
     *   "message": "Commission rate updated successfully"
     * }
     */
    public function updateCommissionRate(Request $request, Affiliate $affiliate): JsonResponse
    {
        try {
            $request->validate([
                'commission_rate' => 'required|numeric|between:0,100',
                'rate_change_reason' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            $affiliate->update([
                'commission_rate' => $request->commission_rate,
                'rate_change_reason' => $request->rate_change_reason,
                'rate_updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Commission rate updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating commission rate: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error updating commission rate',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duplicate an affiliate.
     *
     * @authenticated
     * @urlParam affiliate integer required The affiliate ID. Example: 1
     * @bodyParam name string The new affiliate name. Example: "Copy of Energy Partner Corp"
     * @bodyParam email string The new affiliate email. Example: "copy@energycorp.com"
     *
     * @response 200 {
     *   "message": "Affiliate duplicated successfully",
     *   "data": {
     *     "id": 2,
     *     "name": "Copy of Energy Partner Corp",
     *     "email": "copy@energycorp.com"
     *   }
     * }
     */
    public function duplicate(Request $request, Affiliate $affiliate): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:affiliates,email'
            ]);

            DB::beginTransaction();

            $newAffiliate = $affiliate->replicate();
            $newAffiliate->name = $request->name ?? $affiliate->name . ' (Copy)';
            $newAffiliate->email = $request->email ?? 'copy_' . $affiliate->email;
            $newAffiliate->status = 'pending';
            $newAffiliate->is_verified = false;
            $newAffiliate->verified_at = null;
            $newAffiliate->performance_rating = null;
            $newAffiliate->rating_notes = null;
            $newAffiliate->rating_updated_at = null;
            $newAffiliate->rate_change_reason = null;
            $newAffiliate->rate_updated_at = null;
            $newAffiliate->save();

            // Cargar relaciones para la respuesta
            $newAffiliate->load(['organization', 'user']);

            DB::commit();

            return response()->json([
                'message' => 'Affiliate duplicated successfully',
                'data' => new AffiliateResource($newAffiliate)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating affiliate: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error duplicating affiliate',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Calculate monthly growth percentage.
     */
    private function calculateMonthlyGrowth($query, string $period): float
    {
        // Simulación simple del crecimiento mensual
        // En un entorno real, esto se calcularía basándose en datos históricos
        $currentMonth = (clone $query)->whereMonth('created_at', now()->month)->count();
        $lastMonth = (clone $query)->whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth == 0) {
            return $currentMonth > 0 ? 100.0 : 0.0;
        }
        
        return (($currentMonth - $lastMonth) / $lastMonth) * 100;
    }
}
