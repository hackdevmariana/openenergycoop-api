<?php

namespace App\Http\Resources\Api\V1\Affiliate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AffiliateCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => AffiliateResource::collection($this->collection),
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
            'api_info' => [
                'version' => 'v1',
                'documentation' => route('api.documentation'),
                'rate_limit' => '1000 requests per hour',
            ],
        ];
    }
}
