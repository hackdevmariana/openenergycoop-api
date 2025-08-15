<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SeoMetaDataResource;
use App\Models\SeoMetaData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="SEO Metadata",
 *     description="Gestión de metadatos SEO"
 * )
 */
class SeoMetaDataController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SeoMetaData::query()
            ->with(['seoable', 'organization'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('seoable_type')) {
            $query->where('seoable_type', $request->seoable_type);
        }

        if ($request->filled('seoable_id')) {
            $query->where('seoable_id', $request->seoable_id);
        }

        $seoData = $query->get();
        return SeoMetaDataResource::collection($seoData);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seoable_type' => 'required|string',
            'seoable_id' => 'required|integer',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string',
            'og_title' => 'nullable|string|max:95',
            'og_description' => 'nullable|string|max:297',
            'og_image' => 'nullable|string',
            'og_type' => 'nullable|string|in:website,article,product,profile',
            'twitter_card' => 'nullable|string|in:summary,summary_large_image,app,player',
            'canonical_url' => 'nullable|url',
            'robots' => 'nullable|string|in:index,noindex,follow,nofollow,archive,noarchive',
            'schema_markup' => 'nullable|json',
        ]);

        $seoData = SeoMetaData::create($validated);
        $seoData->load(['seoable', 'organization']);

        return response()->json([
            'data' => new SeoMetaDataResource($seoData),
            'message' => 'Metadatos SEO creados exitosamente'
        ], 201);
    }

    public function show(SeoMetaData $seoMetaData): JsonResponse
    {
        $seoMetaData->load(['seoable', 'organization']);

        return response()->json([
            'data' => new SeoMetaDataResource($seoMetaData)
        ]);
    }

    public function update(Request $request, SeoMetaData $seoMetaData): JsonResponse
    {
        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string',
            'og_title' => 'nullable|string|max:95',
            'og_description' => 'nullable|string|max:297',
            'og_image' => 'nullable|string',
            'canonical_url' => 'nullable|url',
            'robots' => 'nullable|string|in:index,noindex,follow,nofollow,archive,noarchive',
            'schema_markup' => 'nullable|json',
        ]);

        $seoMetaData->update($validated);
        $seoMetaData->load(['seoable', 'organization']);

        return response()->json([
            'data' => new SeoMetaDataResource($seoMetaData),
            'message' => 'Metadatos SEO actualizados exitosamente'
        ]);
    }

    public function destroy(SeoMetaData $seoMetaData): JsonResponse
    {
        $seoMetaData->delete();

        return response()->json([
            'message' => 'Metadatos SEO eliminados exitosamente'
        ]);
    }

    public function forModel(Request $request, string $type, int $id): JsonResponse
    {
        $seoData = SeoMetaData::where('seoable_type', $type)
            ->where('seoable_id', $id)
            ->with(['seoable', 'organization'])
            ->first();

        if (!$seoData) {
            return response()->json(['message' => 'Metadatos SEO no encontrados'], 404);
        }

        return response()->json([
            'data' => new SeoMetaDataResource($seoData)
        ]);
    }
}