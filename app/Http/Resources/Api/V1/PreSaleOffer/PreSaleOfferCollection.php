<?php

namespace App\Http\Resources\Api\V1\PreSaleOffer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PreSaleOfferCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => PreSaleOfferResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
            'summary' => [
                'total_offers' => $this->total(),
                'active_offers' => $this->collection->where('status', 'active')->count(),
                'featured_offers' => $this->collection->where('is_featured', true)->count(),
                'limited_time_offers' => $this->collection->where('is_limited_time', true)->count(),
                'offers_by_type' => $this->collection->groupBy('type')->map->count(),
                'average_discount' => $this->collection->where('discount_percentage', '>', 0)->avg('discount_percentage'),
                'total_savings_potential' => $this->collection->sum(function ($offer) {
                    return $offer->calculateDiscountAmount() ?? 0;
                }),
            ],
            'api_info' => [
                'version' => 'v1',
                'documentation' => route('api.documentation'),
                'rate_limit' => '1000 requests per hour',
            ],
        ];
    }
}
