<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SocialLink\StoreSocialLinkRequest;
use App\Http\Requests\Api\V1\SocialLink\UpdateSocialLinkRequest;
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
            ->with(['organization', 'createdBy'])
            ->active()
            ->published()
            ->ordered();

        if ($request->filled('platform')) {
            $query->byPlatform($request->platform);
        }

        if ($request->boolean('only_verified')) {
            $query->whereNotNull('followers_count')->where('followers_count', '>=', 10000);
        }

        $socialLinks = $query->get();
        return SocialLinkResource::collection($socialLinks);
    }

    public function store(StoreSocialLinkRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $socialLink = SocialLink::create($validated);
        $socialLink->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new SocialLinkResource($socialLink),
            'message' => 'Enlace social creado exitosamente'
        ], 201);
    }

    public function show(SocialLink $socialLink): JsonResponse
    {
        if (!$socialLink->is_active || !$socialLink->isPublished()) {
            return response()->json(['message' => 'Enlace social no encontrado'], 404);
        }

        $socialLink->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new SocialLinkResource($socialLink)
        ]);
    }

    public function update(UpdateSocialLinkRequest $request, SocialLink $socialLink): JsonResponse
    {
        $validated = $request->validated();

        $socialLink->update($validated);
        $socialLink->load(['organization', 'createdBy']);

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
            ->with(['organization', 'createdBy'])
            ->active()
            ->published()
            ->byPlatform($platform)
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
            ->with(['organization', 'createdBy'])
            ->active()
            ->published()
            ->popular();

        $limit = min($request->get('limit', 5), 10);
        $socialLinks = $query->limit($limit)->get();

        return response()->json([
            'data' => SocialLinkResource::collection($socialLinks),
            'total' => $socialLinks->count()
        ]);
    }
}