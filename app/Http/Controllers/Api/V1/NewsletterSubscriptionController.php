<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\NewsletterSubscription\StoreNewsletterSubscriptionRequest;
use App\Http\Requests\Api\V1\NewsletterSubscription\UpdateNewsletterSubscriptionRequest;
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
class NewsletterSubscriptionController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        // Solo usuarios autenticados pueden listar suscripciones
        // En producción, se pueden implementar permisos más específicos

        $query = NewsletterSubscription::query()
            ->with(['organization'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by active/confirmed subscriptions
        if ($request->boolean('active_only')) {
            $query->active();
        }

        if ($request->filled('language')) {
            $query->byLanguage($request->language);
        }

        if ($request->filled('source')) {
            $query->bySource($request->source);
        }

        // Filter by tags
        if ($request->filled('tags')) {
            $tags = is_array($request->tags) ? $request->tags : [$request->tags];
            $query->withTags($tags);
        }

        // Search by email or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by engagement
        if ($request->boolean('engaged_only')) {
            $query->engaged();
        }

        // Filter recent subscribers
        if ($request->filled('recent_days')) {
            $query->recentSubscribers((int) $request->recent_days);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $subscriptions = $query->paginate($perPage);

        return NewsletterSubscriptionResource::collection($subscriptions);
    }

    public function store(StoreNewsletterSubscriptionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Capture client info
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['status'] = 'pending'; // Always start as pending

        $subscription = NewsletterSubscription::create($validated);
        $subscription->load(['organization']);

        return response()->json([
            'data' => new NewsletterSubscriptionResource($subscription),
            'message' => 'Suscripción creada exitosamente. Revisa tu email para confirmar.'
        ], 201);
    }

    public function show(NewsletterSubscription $newsletterSubscription): JsonResponse
    {
        // Solo usuarios autenticados pueden ver detalles
        // En producción, se pueden implementar permisos más específicos

        $newsletterSubscription->load(['organization']);

        return response()->json([
            'data' => new NewsletterSubscriptionResource($newsletterSubscription)
        ]);
    }

    public function update(UpdateNewsletterSubscriptionRequest $request, NewsletterSubscription $newsletterSubscription): JsonResponse
    {
        $validated = $request->validated();

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
            'token' => 'nullable|string',
            'reason' => 'nullable|string|max:255'
        ]);

        $query = NewsletterSubscription::where('email', $request->email);
        
        // If token provided, verify it
        if ($request->token) {
            $query->where('unsubscribe_token', $request->token);
        }

        $subscription = $query->first();

        if (!$subscription) {
            return response()->json(['message' => 'Suscripción no encontrada'], 404);
        }

        $subscription->unsubscribe($request->reason);

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

        $subscription->resubscribe();

        return response()->json([
            'message' => 'Te has suscrito nuevamente al newsletter'
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        // Solo usuarios autenticados pueden ver estadísticas
        // En producción, se pueden implementar permisos más específicos

        $organizationId = $request->get('organization_id');
        $query = NewsletterSubscription::query();

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $stats = [
            'total_subscriptions' => $query->count(),
            'pending' => $query->clone()->where('status', 'pending')->count(),
            'confirmed' => $query->clone()->where('status', 'confirmed')->count(),
            'unsubscribed' => $query->clone()->where('status', 'unsubscribed')->count(),
            'bounced' => $query->clone()->where('status', 'bounced')->count(),
            'complained' => $query->clone()->where('status', 'complained')->count(),
            'this_month' => $query->clone()->where('created_at', '>=', now()->startOfMonth())->count(),
            'this_week' => $query->clone()->where('created_at', '>=', now()->startOfWeek())->count(),
            'today' => $query->clone()->whereDate('created_at', today())->count(),
            'engaged_subscribers' => $query->clone()->engaged()->count(),
            'by_language' => $query->clone()->active()
                ->selectRaw('language, COUNT(*) as count')
                ->groupBy('language')
                ->orderBy('count', 'desc')
                ->get(),
            'by_source' => $query->clone()->active()
                ->selectRaw('subscription_source, COUNT(*) as count')
                ->groupBy('subscription_source')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'engagement' => [
                'avg_open_rate' => $query->clone()->active()->avg(
                    \DB::raw('CASE WHEN emails_sent > 0 THEN (emails_opened / emails_sent) * 100 ELSE 0 END')
                ),
                'avg_click_rate' => $query->clone()->active()->avg(
                    \DB::raw('CASE WHEN emails_sent > 0 THEN (links_clicked / emails_sent) * 100 ELSE 0 END')
                ),
                'total_emails_sent' => $query->clone()->sum('emails_sent'),
                'total_emails_opened' => $query->clone()->sum('emails_opened'),
                'total_links_clicked' => $query->clone()->sum('links_clicked'),
            ]
        ];

        return response()->json([
            'stats' => $stats,
            'generated_at' => now()->toISOString()
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $subscription = NewsletterSubscription::where('confirmation_token', $request->token)->first();

        if (!$subscription) {
            return response()->json(['message' => 'Token de confirmación inválido'], 404);
        }

        if ($subscription->isActive()) {
            return response()->json(['message' => 'La suscripción ya está confirmada']);
        }

        $subscription->confirm();
        $subscription->refresh(); // Reload from database

        return response()->json([
            'data' => new NewsletterSubscriptionResource($subscription),
            'message' => 'Suscripción confirmada exitosamente'
        ]);
    }

    public function markBounced(NewsletterSubscription $newsletterSubscription): JsonResponse
    {
        $newsletterSubscription->markAsBounced();

        return response()->json([
            'data' => new NewsletterSubscriptionResource($newsletterSubscription),
            'message' => 'Suscripción marcada como rebotada'
        ]);
    }

    public function markComplaint(NewsletterSubscription $newsletterSubscription): JsonResponse
    {
        $newsletterSubscription->markAsComplaint();

        return response()->json([
            'data' => new NewsletterSubscriptionResource($newsletterSubscription),
            'message' => 'Suscripción marcada como queja/spam'
        ]);
    }

    public function recordEmail(Request $request, NewsletterSubscription $newsletterSubscription): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:sent,opened,clicked'
        ]);

        switch ($request->action) {
            case 'sent':
                $newsletterSubscription->recordEmailSent();
                break;
            case 'opened':
                $newsletterSubscription->recordEmailOpened();
                break;
            case 'clicked':
                $newsletterSubscription->recordLinkClicked();
                break;
        }

        return response()->json([
            'data' => new NewsletterSubscriptionResource($newsletterSubscription),
            'message' => 'Actividad registrada exitosamente'
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        // Solo usuarios autenticados pueden exportar suscripciones
        // En producción, se pueden implementar permisos más específicos

        $request->validate([
            'status' => 'nullable|string|in:pending,confirmed,unsubscribed,bounced,complained',
            'language' => 'nullable|string|in:es,en,ca,eu,gl',
            'format' => 'nullable|string|in:csv,json',
        ]);

        $query = NewsletterSubscription::query()->with(['organization']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('language')) {
            $query->byLanguage($request->language);
        }

        $subscriptions = $query->get();

        $data = $subscriptions->map(function ($subscription) {
            return [
                'id' => $subscription->id,
                'email' => $subscription->email,
                'name' => $subscription->name,
                'status' => $subscription->status,
                'language' => $subscription->language,
                'subscription_source' => $subscription->subscription_source,
                'confirmed_at' => $subscription->confirmed_at?->toISOString(),
                'unsubscribed_at' => $subscription->unsubscribed_at?->toISOString(),
                'emails_sent' => $subscription->emails_sent,
                'emails_opened' => $subscription->emails_opened,
                'links_clicked' => $subscription->links_clicked,
                'open_rate' => $subscription->getOpenRate(),
                'click_rate' => $subscription->getClickRate(),
                'engagement_score' => $subscription->getEngagementScore(),
                'created_at' => $subscription->created_at->toISOString(),
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $data->count(),
            'exported_at' => now()->toISOString()
        ]);
    }
}