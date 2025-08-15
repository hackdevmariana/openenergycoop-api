<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SocialLinkResource;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Social Links",
 *     description="Gestión de enlaces a redes sociales"
 * )
 */
class SocialLinkController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SocialLink::query()
            ->with(['organization'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('platform');

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        $socialLinks = $query->get();
        return SocialLinkResource::collection($socialLinks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:facebook,twitter,instagram,linkedin,youtube,tiktok,telegram,whatsapp,other',
            'url' => 'required|url',
            'username' => 'nullable|string|max:100',
            'followers_count' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $socialLink = SocialLink::create($validated);
        $socialLink->load(['organization']);

        return response()->json([
            'data' => new SocialLinkResource($socialLink),
            'message' => 'Enlace social creado exitosamente'
        ], 201);
    }

    public function show(SocialLink $socialLink): JsonResponse
    {
        if (!$socialLink->is_active) {
            return response()->json(['message' => 'Enlace social no encontrado'], 404);
        }

        $socialLink->load(['organization']);

        return response()->json([
            'data' => new SocialLinkResource($socialLink)
        ]);
    }

    public function update(Request $request, SocialLink $socialLink): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'sometimes|url',
            'username' => 'nullable|string|max:100',
            'followers_count' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $socialLink->update($validated);
        $socialLink->load(['organization']);

        return response()->json([
            'data' => new SocialLinkResource($socialLink),
            'message' => 'Enlace social actualizado exitosamente'
        ]);
    }

    public function destroy(SocialLink $socialLink): JsonResponse
    {
        $socialLink->delete();

        return response()->json([
            'message' => 'Enlace social eliminado exitosamente'
        ]);
    }

    public function byPlatform(Request $request, string $platform): JsonResponse
    {
        $socialLink = SocialLink::query()
            ->with(['organization'])
            ->where('is_active', true)
            ->where('platform', $platform)
            ->first();

        if (!$socialLink) {
            return response()->json(['message' => 'Enlace social no encontrado'], 404);
        }

        return response()->json([
            'data' => new SocialLinkResource($socialLink)
        ]);
    }

    public function popular(Request $request): JsonResponse
    {
        $query = SocialLink::query()
            ->with(['organization'])
            ->where('is_active', true)
            ->whereNotNull('followers_count')
            ->orderBy('followers_count', 'desc');

        $limit = min($request->get('limit', 5), 10);
        $socialLinks = $query->limit($limit)->get();

        return response()->json([
            'data' => SocialLinkResource::collection($socialLinks),
            'total' => $socialLinks->count()
        ]);
    }
}