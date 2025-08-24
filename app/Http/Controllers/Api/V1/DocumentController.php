<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Document\StoreDocumentRequest;
use App\Http\Requests\Api\V1\Document\UpdateDocumentRequest;
use App\Http\Resources\Api\V1\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Documents",
 *     description="Gestión de documentos del sistema"
 * )
 */
class DocumentController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Document::query()
            ->with(['category', 'organization', 'uploadedBy'])
            ->published()
            ->where('visible', true)
            ->orderBy('uploaded_at', 'desc');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('search_keywords', $search);
            });
        }

        if ($request->filled('type')) {
            $query->where('file_type', $request->type);
        }

        // Removido filtro de featured ya que no existe en la migración

        $perPage = min($request->get('per_page', 15), 50);
        $documents = $query->paginate($perPage);

        return DocumentResource::collection($documents);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['uploaded_by'] = auth()->id();
        $validated['uploaded_at'] = now();

        $document = Document::create($validated);
        $document->load(['category', 'organization', 'uploadedBy']);

        return response()->json([
            'data' => new DocumentResource($document),
            'message' => 'Documento creado exitosamente'
        ], 201);
    }

    public function show(Document $document): JsonResponse
    {
        if (!$document->isPublished() || !$document->visible) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        $document->load(['category', 'organization', 'uploadedBy']);

        // Incrementar contador de descargas
        $document->increment('download_count');

        return response()->json([
            'data' => new DocumentResource($document)
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): JsonResponse
    {
        $document->update($request->validated());
        $document->load(['category', 'organization', 'uploadedBy']);

        return response()->json([
            'data' => new DocumentResource($document),
            'message' => 'Documento actualizado exitosamente'
        ]);
    }

    public function destroy(Document $document): JsonResponse
    {
        $document->delete();

        return response()->json([
            'message' => 'Documento eliminado exitosamente'
        ]);
    }

    public function download(Document $document): JsonResponse
    {
        if (!$document->isPublished() || !$document->visible) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        // Incrementar contador de descargas
        $document->increment('download_count');

        return response()->json([
            'download_url' => $document->getDownloadUrl(),
            'filename' => $document->original_filename,
            'file_size' => $document->file_size,
            'file_type' => $document->file_type,
            'message' => 'URL de descarga generada'
        ]);
    }

    public function mostDownloaded(Request $request): JsonResponse
    {
        $query = Document::query()
            ->with(['category', 'organization'])
            ->published()
            ->where('visible', true)
            ->where('download_count', '>', 0)
            ->orderBy('download_count', 'desc');

        $limit = min($request->get('limit', 10), 20);
        $documents = $query->limit($limit)->get();

        return response()->json([
            'data' => DocumentResource::collection($documents),
            'total' => $documents->count()
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $query = Document::query()
            ->with(['category', 'organization'])
            ->published()
            ->where('visible', true)
            ->orderBy('uploaded_at', 'desc');

        $limit = min($request->get('limit', 10), 20);
        $documents = $query->limit($limit)->get();

        return response()->json([
            'data' => DocumentResource::collection($documents),
            'total' => $documents->count()
        ]);
    }

    public function popular(Request $request): JsonResponse
    {
        $query = Document::query()
            ->with(['category', 'organization'])
            ->published()
            ->where('visible', true)
            ->orderBy('download_count', 'desc');

        $limit = min($request->get('limit', 10), 20);
        $documents = $query->limit($limit)->get();

        return response()->json([
            'data' => DocumentResource::collection($documents),
            'total' => $documents->count()
        ]);
    }
}