<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Message\StoreMessageRequest;
use App\Http\Requests\Api\V1\Message\UpdateMessageRequest;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Messages",
 *     description="Gestión de mensajes y formularios de contacto"
 * )
 */
class MessageController extends Controller
{
    // Public endpoint for contact form submissions
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Message::query()
            ->with(['repliedBy', 'assignedTo', 'organization'])
            ->orderByPriority();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by type
        if ($request->filled('message_type')) {
            $query->byType($request->message_type);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        // Filter unread (pending status)
        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        // Search by email or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->get('per_page', 20), 50);
        $messages = $query->paginate($perPage);

        return MessageResource::collection($messages);
    }

    // Public endpoint - no authentication required
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Capture client information
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();

        $message = Message::create($validated);

        return response()->json([
            'data' => new MessageResource($message),
            'message' => 'Mensaje enviado exitosamente. Te contactaremos pronto.'
        ], 201);
    }

    public function show(Message $message): JsonResponse
    {
        $message->load(['repliedBy', 'assignedTo', 'organization']);

        // Mark as read if not already read
        if (!$message->isRead()) {
            $message->markAsRead();
        }

        return response()->json([
            'data' => new MessageResource($message)
        ]);
    }

    public function update(UpdateMessageRequest $request, Message $message): JsonResponse
    {
        $validated = $request->validated();

        $message->update($validated);
        $message->load(['repliedBy', 'assignedTo', 'organization']);

        return response()->json([
            'data' => new MessageResource($message),
            'message' => 'Mensaje actualizado exitosamente'
        ]);
    }

    public function destroy(Message $message): JsonResponse
    {
        $message->delete();

        return response()->json([
            'message' => 'Mensaje eliminado exitosamente'
        ]);
    }

    public function markAsRead(Message $message): JsonResponse
    {
        $message->markAsRead();

        return response()->json([
            'data' => new MessageResource($message->fresh()),
            'message' => 'Mensaje marcado como leído'
        ]);
    }

    public function markAsReplied(Message $message): JsonResponse
    {
        $message->markAsReplied(auth()->id());

        return response()->json([
            'data' => new MessageResource($message->fresh()),
            'message' => 'Mensaje marcado como respondido'
        ]);
    }

    public function markAsSpam(Message $message): JsonResponse
    {
        $message->markAsSpam();

        return response()->json([
            'data' => new MessageResource($message->fresh()),
            'message' => 'Mensaje marcado como spam'
        ]);
    }

    public function archive(Message $message): JsonResponse
    {
        $message->archive();

        return response()->json([
            'data' => new MessageResource($message->fresh()),
            'message' => 'Mensaje archivado'
        ]);
    }

    public function assign(Request $request, Message $message): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $message->assignTo($validated['user_id']);

        return response()->json([
            'data' => new MessageResource($message->fresh(['assignedTo'])),
            'message' => 'Mensaje asignado exitosamente'
        ]);
    }

    public function unassign(Message $message): JsonResponse
    {
        $message->unassign();

        return response()->json([
            'data' => new MessageResource($message->fresh()),
            'message' => 'Asignación removida'
        ]);
    }

    public function pending(Request $request): JsonResponse
    {
        $query = Message::query()
            ->with(['assignedTo', 'organization'])
            ->pending()
            ->orderByPriority();

        $messages = $query->get();

        return response()->json([
            'data' => MessageResource::collection($messages),
            'total_pending' => $messages->count()
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        $query = Message::query()
            ->with(['assignedTo', 'organization'])
            ->unread()
            ->orderByPriority();

        $messages = $query->get();

        return response()->json([
            'data' => MessageResource::collection($messages),
            'total_unread' => $messages->count()
        ]);
    }

    public function assigned(Request $request): JsonResponse
    {
        $userId = $request->get('user_id', auth()->id());
        
        $query = Message::query()
            ->with(['repliedBy', 'organization'])
            ->assignedTo($userId)
            ->whereNotIn('status', ['archived', 'spam'])
            ->orderByPriority();

        $messages = $query->get();

        return response()->json([
            'data' => MessageResource::collection($messages),
            'total_assigned' => $messages->count()
        ]);
    }

    public function byEmail(Request $request, string $email): JsonResponse
    {
        $query = Message::query()
            ->with(['repliedBy', 'assignedTo', 'organization'])
            ->byEmail($email)
            ->orderBy('created_at', 'desc');

        $messages = $query->get();

        return response()->json([
            'data' => MessageResource::collection($messages),
            'total' => $messages->count(),
            'email' => $email
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $organizationId = $request->get('organization_id');
        
        $query = Message::query();
        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $stats = [
            'total' => $query->count(),
            'pending' => $query->clone()->pending()->count(),
            'read' => $query->clone()->read()->count(),
            'replied' => $query->clone()->replied()->count(),
            'archived' => $query->clone()->archived()->count(),
            'spam' => $query->clone()->spam()->count(),
            'urgent' => $query->clone()->byPriority('urgent')->count(),
            'high_priority' => $query->clone()->byPriority('high')->count(),
            'assigned' => $query->clone()->whereNotNull('assigned_to_user_id')->count(),
            'unassigned' => $query->clone()->whereNull('assigned_to_user_id')->count(),
        ];

        $typeStats = [];
        foreach (Message::MESSAGE_TYPES as $type => $label) {
            $typeStats[$type] = $query->clone()->byType($type)->count();
        }

        return response()->json([
            'stats' => $stats,
            'by_type' => $typeStats,
            'response_time_avg' => $this->getAverageResponseTime($organizationId),
        ]);
    }

    private function getAverageResponseTime(?int $organizationId = null): ?float
    {
        $query = Message::query()->whereNotNull('replied_at');
        
        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $messages = $query->get();
        
        if ($messages->isEmpty()) {
            return null;
        }

        $totalHours = $messages->sum(function ($message) {
            return $message->getResponseTime();
        });

        return round($totalHours / $messages->count(), 2);
    }
}