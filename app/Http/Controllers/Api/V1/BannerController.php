<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Banner\StoreBannerRequest;
use App\Http\Requests\Api\V1\Banner\UpdateBannerRequest;
use App\Http\Resources\Api\V1\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Banners",
 *     description="Gestión de banners promocionales del sitio web"
 * )
 */
class BannerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Banner::query()
            ->with(['organization', 'createdBy'])
            ->published()
            ->orderBy('position', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('type')) {
            $query->where('banner_type', $request->type);
        }

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        $banners = $query->get();
        return BannerResource::collection($banners);
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $banner = Banner::create($validated);
        $banner->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new BannerResource($banner),
            'message' => 'Banner creado exitosamente'
        ], 201);
    }

    public function show(Banner $banner): JsonResponse
    {
        if (!$banner->isPublished() || !$banner->active) {
            return response()->json(['message' => 'Banner no encontrado'], 404);
        }

        $banner->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new BannerResource($banner)
        ]);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by_user_id'] = auth()->id();

        $banner->update($validated);
        $banner->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new BannerResource($banner),
            'message' => 'Banner actualizado exitosamente'
        ]);
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $banner->delete();

        return response()->json([
            'message' => 'Banner eliminado exitosamente'
        ]);
    }

    public function byPosition(Request $request, string $position): JsonResponse
    {
        $query = Banner::query()
            ->with(['organization'])
            ->published()
            ->where('active', true)
            ->where('position', $position)
            ->orderBy('position', 'desc');



        $banners = $query->get();

        return response()->json([
            'data' => BannerResource::collection($banners),
            'position' => $position,
            'total' => $banners->count()
        ]);
    }

    public function active(Request $request): JsonResponse
    {
        $query = Banner::query()
            ->with(['organization'])
            ->published()
            ->where('active', true)
            ->orderBy('position', 'desc');



        if ($request->filled('type')) {
            $query->where('banner_type', $request->type);
        }

        $banners = $query->get();

        return response()->json([
            'data' => BannerResource::collection($banners),
            'total' => $banners->count()
        ]);
    }
}