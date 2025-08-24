<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FaqTopic\StoreFaqTopicRequest;
use App\Http\Requests\Api\V1\FaqTopic\UpdateFaqTopicRequest;
use App\Http\Resources\Api\V1\FaqTopicResource;
use App\Models\FaqTopic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="FAQ Topics",
 *     description="Gestión de temas de preguntas frecuentes"
 * )
 */
class FaqTopicController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = FaqTopic::query()
            ->with(['organization', 'faqs'])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        if ($request->boolean('with_faqs_count')) {
            $query->withCount('faqs');
        }

        $topics = $query->get();
        return FaqTopicResource::collection($topics);
    }

    public function store(StoreFaqTopicRequest $request): JsonResponse
    {
        $topic = FaqTopic::create($request->validated());
        $topic->load(['organization', 'faqs']);

        return response()->json([
            'data' => new FaqTopicResource($topic),
            'message' => 'Tema de FAQ creado exitosamente'
        ], 201);
    }

    public function show(Request $request, FaqTopic $faqTopic): JsonResponse
    {
        if (!$faqTopic->is_active) {
            return response()->json(['message' => 'Tema de FAQ no encontrado'], 404);
        }

        $relations = ['organization'];
        
        if ($request->boolean('include_faqs')) {
            $relations[] = 'faqs';
        }

        $faqTopic->load($relations);

        return response()->json([
            'data' => new FaqTopicResource($faqTopic)
        ]);
    }

    public function update(UpdateFaqTopicRequest $request, FaqTopic $faqTopic): JsonResponse
    {
        $faqTopic->update($request->validated());
        $faqTopic->load(['organization', 'faqs']);

        return response()->json([
            'data' => new FaqTopicResource($faqTopic),
            'message' => 'Tema de FAQ actualizado exitosamente'
        ]);
    }

    public function destroy(FaqTopic $faqTopic): JsonResponse
    {
        // Verificar si tiene FAQs asociadas
        if ($faqTopic->faqs()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un tema que tiene FAQs asociadas'
            ], 422);
        }

        $faqTopic->delete();

        return response()->json([
            'message' => 'Tema de FAQ eliminado exitosamente'
        ]);
    }

    public function faqs(Request $request, FaqTopic $faqTopic): JsonResponse
    {
        $query = $faqTopic->faqs()
            ->with(['organization', 'createdBy'])
            ->published()
            ->orderBy('position');

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        $faqs = $query->get();

        return response()->json([
            'topic' => new FaqTopicResource($faqTopic),
            'faqs' => $faqs->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'position' => $faq->position,
                    'is_featured' => $faq->is_featured,
                    'created_at' => $faq->created_at,
                ];
            }),
            'total_faqs' => $faqs->count()
        ]);
    }
}