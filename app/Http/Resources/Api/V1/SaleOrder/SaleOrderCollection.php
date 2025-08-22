<?php

namespace App\Http\Resources\Api\V1\SaleOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SaleOrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => SaleOrderResource::collection($this->collection),
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
                'total_orders' => $this->total(),
                'total_revenue' => $this->collection->sum('total'),
                'average_order_value' => $this->collection->avg('total'),
                'pending_orders' => $this->collection->where('status', 'pending')->count(),
                'urgent_orders' => $this->collection->where('is_urgent', true)->count(),
            ],
            'api_info' => [
                'version' => 'v1',
                'documentation' => route('api.documentation'),
                'rate_limit' => '1000 requests per hour',
            ],
        ];
    }
}
