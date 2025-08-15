<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NewsletterSubscriptionResource;
use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Newsletter Subscriptions",
 *     description="Gestión de suscripciones al newsletter"
 * )
 */
class NewsletterSubscriptionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        // Solo administradores pueden listar suscripciones
        if (!auth()->user()->can('manage newsletters')) {
            abort(403, 'No tienes permisos para listar suscripciones');
        }

        $query = NewsletterSubscription::query()
            ->with(['organization'])
            ->orderBy('created_at', 'desc');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $subscriptions = $query->paginate($perPage);

        return NewsletterSubscriptionResource::collection($subscriptions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletter_subscriptions,email',
            'name' => 'nullable|string|max:255',
            'language' => 'nullable|string|in:es,en,ca,eu,gl',
            'interests' => 'nullable|array',
            'source' => 'nullable|string|max:100',
            'gdpr_consent' => 'required|boolean',
        ]);

        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['subscribed_at'] = now();

        $subscription = NewsletterSubscription::create($validated);
        $subscription->load(['organization']);

        return response()->json([
            'data' => new NewsletterSubscriptionResource($subscription),
            'message' => 'Suscripción creada exitosamente'
        ], 201);
    }

    public function show(NewsletterSubscription $newsletterSubscription): JsonResponse
    {
        // Solo administradores o el propio suscriptor pueden ver detalles
        if (!auth()->user()->can('manage newsletters')) {
            abort(403, 'No tienes permisos para ver esta suscripción');
        }

        $newsletterSubscription->load(['organization']);

        return response()->json([
            'data' => new NewsletterSubscriptionResource($newsletterSubscription)
        ]);
    }

    public function update(Request $request, NewsletterSubscription $newsletterSubscription): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'language' => 'nullable|string|in:es,en,ca,eu,gl',
            'interests' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $newsletterSubscription->update($validated);
        $newsletterSubscription->load(['organization']);

        return response()->json([
            'data' => new NewsletterSubscriptionResource($newsletterSubscription),
            'message' => 'Suscripción actualizada exitosamente'
        ]);
    }

    public function destroy(NewsletterSubscription $newsletterSubscription): JsonResponse
    {
        $newsletterSubscription->delete();

        return response()->json([
            'message' => 'Suscripción eliminada exitosamente'
        ]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'nullable|string'
        ]);

        $subscription = NewsletterSubscription::where('email', $request->email)->first();

        if (!$subscription) {
            return response()->json(['message' => 'Suscripción no encontrada'], 404);
        }

        $subscription->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
            'unsubscribe_reason' => $request->reason
        ]);

        return response()->json([
            'message' => 'Te has desuscrito exitosamente del newsletter'
        ]);
    }

    public function resubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $subscription = NewsletterSubscription::where('email', $request->email)->first();

        if (!$subscription) {
            return response()->json(['message' => 'Suscripción no encontrada'], 404);
        }

        $subscription->update([
            'is_active' => true,
            'unsubscribed_at' => null,
            'unsubscribe_reason' => null,
            'resubscribed_at' => now()
        ]);

        return response()->json([
            'message' => 'Te has suscrito nuevamente al newsletter'
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        if (!auth()->user()->can('manage newsletters')) {
            abort(403, 'No tienes permisos para ver estadísticas');
        }

        $stats = [
            'total_subscriptions' => NewsletterSubscription::count(),
            'active_subscriptions' => NewsletterSubscription::where('is_active', true)->count(),
            'unsubscribed' => NewsletterSubscription::where('is_active', false)->count(),
            'this_month' => NewsletterSubscription::where('created_at', '>=', now()->startOfMonth())->count(),
            'by_language' => NewsletterSubscription::where('is_active', true)
                ->selectRaw('language, COUNT(*) as count')
                ->groupBy('language')
                ->get(),
            'by_source' => NewsletterSubscription::where('is_active', true)
                ->selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'stats' => $stats,
            'generated_at' => now()->toISOString()
        ]);
    }
}