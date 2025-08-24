<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Comment\StoreCommentRequest;
use App\Http\Requests\Api\V1\Comment\UpdateCommentRequest;
use App\Http\Resources\Api\V1\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Comments",
 *     description="Sistema de comentarios para artículos y páginas"
 * )
 */
class CommentController extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/comments",
     *     tags={"Comments"},
     *     summary="Listar comentarios",
     *     description="Obtiene una lista paginada de comentarios",
     *     @OA\Response(response=200, description="Lista de comentarios")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Comment::query()
            ->with(['user', 'commentable', 'parent'])
            ->approved()
            ->orderByDesc('is_pinned')
            ->orderBy('created_at');

        // Filtros
        if ($request->filled('commentable_type') && $request->filled('commentable_id')) {
            $query->where('commentable_type', $request->commentable_type)
                  ->where('commentable_id', $request->commentable_id);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null') {
                $query->rootComments();
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Incluir respuestas si se solicita
        if ($request->boolean('include_replies')) {
            $query->with(['replies.user', 'replies.replies.user']);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $comments = $query->paginate($perPage);

        return CommentResource::collection($comments);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comments",
     *     tags={"Comments"},
     *     summary="Crear nuevo comentario",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=201, description="Comentario creado")
     * )
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Si el usuario está autenticado, usar sus datos
        if (auth()->check()) {
            $validated['user_id'] = auth()->id();
            unset($validated['author_name'], $validated['author_email']);
        } else {
            $validated['ip_address'] = $request->ip();
            $validated['user_agent'] = $request->userAgent();
        }

        // Estado inicial según configuración
        $validated['status'] = config('comments.auto_approve', false) ? 'approved' : 'pending';

        $comment = Comment::create($validated);
        $comment->load(['user', 'commentable', 'parent']);

        $message = $comment->status === 'pending' 
            ? 'Comentario enviado y pendiente de moderación'
            : 'Comentario publicado exitosamente';

        return response()->json([
            'data' => new CommentResource($comment),
            'message' => $message
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comments/{id}",
     *     tags={"Comments"},
     *     summary="Obtener comentario específico",
     *     @OA\Response(response=200, description="Comentario obtenido")
     * )
     */
    public function show(Request $request, Comment $comment): JsonResponse
    {
        // Solo mostrar comentarios aprobados para usuarios no autenticados
        if (!auth()->check() && !$comment->isApproved()) {
            return response()->json(['message' => 'Comentario no encontrado'], 404);
        }

        $relations = ['user', 'commentable', 'parent'];
        
        if ($request->boolean('include_thread')) {
            $relations[] = 'replies.user';
            $relations[] = 'replies.replies.user';
        }

        $comment->load($relations);

        return response()->json([
            'data' => new CommentResource($comment)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comments/{id}",
     *     tags={"Comments"},
     *     summary="Actualizar comentario",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Comentario actualizado")
     * )
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        // Verificar permisos
        if (!auth()->check() || (auth()->id() !== $comment->user_id && !auth()->user()->can('manage comments'))) {
            return response()->json(['message' => 'No tienes permisos para editar este comentario'], 403);
        }

        $validated = $request->validated();
        
        // Si se edita el contenido, resetear estado a pendiente
        if (isset($validated['content']) && $validated['content'] !== $comment->content) {
            $validated['status'] = 'pending';
            $validated['approved_at'] = null;
            $validated['approved_by_user_id'] = null;
        }

        $comment->update($validated);
        $comment->load(['user', 'commentable', 'parent']);

        return response()->json([
            'data' => new CommentResource($comment),
            'message' => 'Comentario actualizado exitosamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comments/{id}",
     *     tags={"Comments"},
     *     summary="Eliminar comentario",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Comentario eliminado")
     * )
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Verificar permisos
        if (!auth()->check() || (auth()->id() !== $comment->user_id && !auth()->user()->can('manage comments'))) {
            return response()->json(['message' => 'No tienes permisos para eliminar este comentario'], 403);
        }

        // Si tiene respuestas, no permitir eliminación
        if ($comment->hasReplies()) {
            return response()->json([
                'message' => 'No se puede eliminar un comentario que tiene respuestas'
            ], 422);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comentario eliminado exitosamente'
        ]);
    }

    // Métodos adicionales para moderación
    public function like(Comment $comment): JsonResponse
    {
        if (!$comment->isApproved()) {
            return response()->json(['message' => 'No se puede dar like a este comentario'], 422);
        }

        $comment->like();
        return response()->json([
            'data' => new CommentResource($comment->fresh()),
            'message' => 'Like registrado exitosamente'
        ]);
    }

    public function approve(Comment $comment): JsonResponse
    {
        if (!auth()->user()->can('manage comments')) {
            return response()->json(['message' => 'No tienes permisos para aprobar comentarios'], 403);
        }

        if (!$comment->canBeApproved()) {
            return response()->json(['message' => 'Este comentario no puede ser aprobado'], 422);
        }

        $comment->approve(auth()->user());
        return response()->json([
            'data' => new CommentResource($comment->fresh()),
            'message' => 'Comentario aprobado exitosamente'
        ]);
    }

    public function thread(Comment $comment): JsonResponse
    {
        $rootComment = $comment->getThreadRoot();
        
        $rootComment->load([
            'user',
            'replies.user',
            'replies.replies.user',
            'replies.replies.replies.user'
        ]);

        return response()->json([
            'data' => new CommentResource($rootComment),
            'thread_depth' => $comment->getDepth(),
            'total_replies' => $rootComment->getAllReplies()->count()
        ]);
    }
}