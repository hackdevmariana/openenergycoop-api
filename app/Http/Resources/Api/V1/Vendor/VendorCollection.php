<?php

namespace App\Http\Resources\Api\V1\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VendorCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'has_more_pages' => $this->hasMorePages(),
                'links' => [
                    'first' => $this->url(1),
                    'last' => $this->url($this->lastPage()),
                    'prev' => $this->previousPageUrl(),
                    'next' => $this->nextPageUrl(),
                ],
            ],
            'summary' => [
                'total_vendors' => $this->total(),
                'active_vendors' => $this->collection->where('is_active', true)->count(),
                'verified_vendors' => $this->collection->where('is_verified', true)->count(),
                'preferred_vendors' => $this->collection->where('is_preferred', true)->count(),
                'blacklisted_vendors' => $this->collection->where('is_blacklisted', true)->count(),
                'high_risk_vendors' => $this->collection->whereIn('risk_level', ['high', 'extreme'])->count(),
                'compliant_vendors' => $this->collection->where('compliance_status', 'compliant')->count(),
                'vendors_needing_audit' => $this->collection->where('compliance_status', 'needs_audit')->count(),
                'vendors_by_type' => $this->collection->groupBy('vendor_type')->map->count(),
                'vendors_by_risk_level' => $this->collection->groupBy('risk_level')->map->count(),
                'vendors_by_compliance_status' => $this->collection->groupBy('compliance_status')->map->count(),
                'vendors_by_country' => $this->collection->groupBy('country')->map->count(),
                'average_rating' => $this->collection->whereNotNull('rating')->avg('rating'),
                'total_credit_limit' => $this->collection->whereNotNull('credit_limit')->sum('credit_limit'),
                'total_current_balance' => $this->collection->whereNotNull('current_balance')->sum('current_balance'),
                'vendors_expiring_contract_soon' => $this->collection->filter(function ($vendor) {
                    if (!$vendor->contract_end_date) return false;
                    $daysUntil = now()->diffInDays($vendor->contract_end_date, false);
                    return $daysUntil >= 0 && $daysUntil <= 90;
                })->count(),
                'vendors_with_overdue_audit' => $this->collection->filter(function ($vendor) {
                    if (!$vendor->next_audit_date) return false;
                    return now()->gt($vendor->next_audit_date);
                })->count(),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Vendors retrieved successfully',
            'filters_applied' => $this->getAppliedFilters($request),
            'sorting_applied' => $this->getAppliedSorting($request),
        ];
    }

    /**
     * Get the filters that were applied to the collection.
     */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];
        
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        
        if ($request->filled('vendor_type')) {
            $filters['vendor_type'] = $request->vendor_type;
        }
        
        if ($request->filled('industry')) {
            $filters['industry'] = $request->industry;
        }
        
        if ($request->filled('country')) {
            $filters['country'] = $request->country;
        }
        
        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }
        
        if ($request->filled('risk_level')) {
            $filters['risk_level'] = $request->risk_level;
        }
        
        if ($request->filled('compliance_status')) {
            $filters['compliance_status'] = $request->compliance_status;
        }
        
        if ($request->filled('is_active')) {
            $filters['is_active'] = $request->boolean('is_active');
        }
        
        if ($request->filled('is_verified')) {
            $filters['is_verified'] = $request->boolean('is_verified');
        }
        
        if ($request->filled('is_preferred')) {
            $filters['is_preferred'] = $request->boolean('is_preferred');
        }
        
        if ($request->filled('is_blacklisted')) {
            $filters['is_blacklisted'] = $request->boolean('is_blacklisted');
        }
        
        if ($request->filled('rating_min')) {
            $filters['rating_min'] = $request->rating_min;
        }
        
        if ($request->filled('rating_max')) {
            $filters['rating_max'] = $request->rating_max;
        }
        
        if ($request->filled('credit_limit_min')) {
            $filters['credit_limit_min'] = $request->credit_limit_min;
        }
        
        if ($request->filled('credit_limit_max')) {
            $filters['credit_limit_max'] = $request->credit_limit_max;
        }
        
        if ($request->filled('contract_expires_before')) {
            $filters['contract_expires_before'] = $request->contract_expires_before;
        }
        
        if ($request->filled('audit_due_before')) {
            $filters['audit_due_before'] = $request->audit_due_before;
        }
        
        if ($request->filled('created_after')) {
            $filters['created_after'] = $request->created_after;
        }
        
        if ($request->filled('created_before')) {
            $filters['created_before'] = $request->created_before;
        }
        
        if ($request->filled('approved_after')) {
            $filters['approved_after'] = $request->approved_after;
        }
        
        if ($request->filled('approved_before')) {
            $filters['approved_before'] = $request->approved_before;
        }
        
        return $filters;
    }

    /**
     * Get the sorting that was applied to the collection.
     */
    private function getAppliedSorting(Request $request): array
    {
        $sorting = [];
        
        if ($request->filled('sort_by')) {
            $sorting['sort_by'] = $request->sort_by;
        }
        
        if ($request->filled('sort_direction')) {
            $sorting['sort_direction'] = $request->sort_direction;
        }
        
        return $sorting;
    }
}
