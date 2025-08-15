<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Messages",
 *     description="Gestión de mensajes y comunicación"
 * )
 */
class MessageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Message::query()
            ->with(['sender', 'recipient', 'organization'])
            ->orderBy('created_at', 'desc');

        // Solo mensajes del usuario autenticado
        $query->where(function ($q) {
            $q->where('sender_id', auth()->id())
              ->orWhere('recipient_id', auth()->id());
        });

        if ($request->has('unread')) {
            if ($request->boolean('unread')) {
                $query->whereNull('read_at');
            } else {
                $query->whereNotNull('read_at');
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $messages = $query->paginate($perPage);

        return MessageResource::collection($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|string|in:message,notification,alert,reminder',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
        ]);

        $validated['sender_id'] = auth()->id();

        $message = Message::create($validated);
        $message->load(['sender', 'recipient', 'organization']);

        return response()->json([
            'data' => new MessageResource($message),
            'message' => 'Mensaje enviado exitosamente'
        ], 201);
    }

    public function show(Message $message): JsonResponse
    {
        // Solo el remitente o destinatario pueden ver el mensaje
        if (!in_array(auth()->id(), [$message->sender_id, $message->recipient_id])) {
            abort(403, 'No tienes permisos para ver este mensaje');
        }

        // Marcar como leído si es el destinatario
        if (auth()->id() === $message->recipient_id && !$message->read_at) {
            $message->update(['read_at' => now()]);
        }

        $message->load(['sender', 'recipient', 'organization']);

        return response()->json([
            'data' => new MessageResource($message)
        ]);
    }

    public function destroy(Message $message): JsonResponse
    {
        // Solo el remitente o destinatario pueden eliminar el mensaje
        if (!in_array(auth()->id(), [$message->sender_id, $message->recipient_id])) {
            abort(403, 'No tienes permisos para eliminar este mensaje');
        }

        $message->delete();

        return response()->json([
            'message' => 'Mensaje eliminado exitosamente'
        ]);
    }

    public function markAsRead(Message $message): JsonResponse
    {
        if (auth()->id() !== $message->recipient_id) {
            abort(403, 'Solo el destinatario puede marcar el mensaje como leído');
        }

        $message->update(['read_at' => now()]);

        return response()->json([
            'data' => new MessageResource($message->fresh()),
            'message' => 'Mensaje marcado como leído'
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        $query = Message::query()
            ->with(['sender', 'organization'])
            ->where('recipient_id', auth()->id())
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');

        $messages = $query->get();

        return response()->json([
            'data' => MessageResource::collection($messages),
            'total_unread' => $messages->count()
        ]);
    }
}