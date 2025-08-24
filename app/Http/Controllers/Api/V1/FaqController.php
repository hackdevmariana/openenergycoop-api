<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Faq\StoreFaqRequest;
use App\Http\Requests\Api\V1\Faq\UpdateFaqRequest;
use App\Http\Resources\Api\V1\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="FAQs",
 *     description="Gestión de preguntas frecuentes"
 * )
 */
class FaqController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Faq::query()
            ->with(['topic', 'organization', 'createdBy'])
            ->published()
            ->orderedByPosition();

        if ($request->filled('topic_id')) {
            $query->byTopic($request->topic_id);
        }

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
            });
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $faqs = $query->get();
        return FaqResource::collection($faqs);
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $faq = Faq::create($validated);
        $faq->load(['topic', 'organization', 'createdBy']);

        return response()->json([
            'data' => new FaqResource($faq),
            'message' => 'FAQ creada exitosamente'
        ], 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        if (!$faq->isPublished()) {
            return response()->json(['message' => 'FAQ no encontrada'], 404);
        }

        $faq->load(['topic', 'organization', 'createdBy']);

        return response()->json([
            'data' => new FaqResource($faq)
        ]);
    }

    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by_user_id'] = auth()->id();

        $faq->update($validated);
        $faq->load(['topic', 'organization', 'createdBy']);

        return response()->json([
            'data' => new FaqResource($faq),
            'message' => 'FAQ actualizada exitosamente'
        ]);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json([
            'message' => 'FAQ eliminada exitosamente'
        ]);
    }

    public function featured(Request $request): JsonResponse
    {
        $query = Faq::query()
            ->with(['topic', 'organization'])
            ->published()
            ->featured()
            ->orderBy('position');

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        $limit = min($request->get('limit', 10), 20);
        $faqs = $query->limit($limit)->get();

        return response()->json([
            'data' => FaqResource::collection($faqs),
            'total' => $faqs->count()
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:3',
            'language' => 'nullable|string|in:es,en,ca,eu,gl'
        ]);

        $search = $request->q;
        
        $query = Faq::query()
            ->with(['topic', 'organization'])
            ->published()
            ->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        $faqs = $query->orderBy('position')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => FaqResource::collection($faqs),
            'query' => $search,
            'total' => $faqs->count()
        ]);
    }
}