<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Hero\StoreHeroRequest;
use App\Http\Requests\Api\V1\Hero\UpdateHeroRequest;
use App\Http\Resources\Api\V1\HeroResource;
use App\Models\Hero;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Heroes",
 *     description="Gestión de banners principales y heroes del sitio web"
 * )
 */
class HeroController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Hero::query()
            ->with(['organization', 'createdBy', 'updatedBy'])
            ->published();

        if ($request->has('active')) {
            if ($request->boolean('active')) {
                $query->active();
            } else {
                $query->where('active', false);
            }
        }

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        if ($request->boolean('for_slideshow')) {
            $query->forSlideshow();
        } else {
            $query->byPriority();
        }

        $heroes = $query->get();
        return HeroResource::collection($heroes);
    }

    public function store(StoreHeroRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $hero = Hero::create($validated);
        $hero->load(['organization', 'createdBy', 'updatedBy']);

        return response()->json([
            'data' => new HeroResource($hero),
            'message' => 'Hero creado exitosamente'
        ], 201);
    }

    public function show(Hero $hero): JsonResponse
    {
        if (!$hero->isPublished() || !$hero->active) {
            return response()->json(['message' => 'Hero no encontrado'], 404);
        }

        $hero->load(['organization', 'createdBy', 'updatedBy']);

        return response()->json([
            'data' => new HeroResource($hero)
        ]);
    }

    public function update(UpdateHeroRequest $request, Hero $hero): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by_user_id'] = auth()->id();

        $hero->update($validated);
        $hero->load(['organization', 'createdBy', 'updatedBy']);

        return response()->json([
            'data' => new HeroResource($hero),
            'message' => 'Hero actualizado exitosamente'
        ]);
    }

    public function destroy(Hero $hero): JsonResponse
    {
        $hero->delete();

        return response()->json([
            'message' => 'Hero eliminado exitosamente'
        ]);
    }

    public function slideshow(Request $request): JsonResponse
    {
        $query = Hero::query()
            ->with(['organization'])
            ->forSlideshow();

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        $limit = min($request->get('limit', 5), 20);
        $heroes = $query->limit($limit)->get();

        return response()->json([
            'data' => HeroResource::collection($heroes),
            'total' => $heroes->count(),
            'slideshow_ready' => true
        ]);
    }

    public function duplicate(Hero $hero): JsonResponse
    {
        $duplicate = $hero->duplicate();
        $duplicate->load(['organization', 'createdBy', 'updatedBy']);

        return response()->json([
            'data' => new HeroResource($duplicate),
            'message' => 'Hero duplicado exitosamente'
        ], 201);
    }

    public function active(Request $request): JsonResponse
    {
        $query = Hero::query()
            ->with(['organization'])
            ->active()
            ->published()
            ->inExhibitionPeriod()
            ->byPriority();

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        $heroes = $query->get();

        return response()->json([
            'data' => HeroResource::collection($heroes),
            'total' => $heroes->count()
        ]);
    }
}