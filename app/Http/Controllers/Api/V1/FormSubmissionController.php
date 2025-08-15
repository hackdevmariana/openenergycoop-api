<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FormSubmissionResource;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FormSubmissionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        if (!auth()->user()->can('manage forms')) {
            abort(403, 'No tienes permisos para ver envíos de formularios');
        }

        $query = FormSubmission::query()
            ->with(['user', 'assignedTo', 'processedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('form_type')) {
            $query->byFormType($request->form_type);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $submissions = $query->paginate($perPage);

        return FormSubmissionResource::collection($submissions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_name' => 'required|string|max:255',
            'form_type' => 'required|string|max:100',
            'form_data' => 'required|array',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'source_page' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id() ?? null;
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['referrer'] = $request->header('referer');

        $submission = FormSubmission::create($validated);

        return response()->json([
            'data' => new FormSubmissionResource($submission),
            'message' => 'Formulario enviado exitosamente'
        ], 201);
    }

    public function show(FormSubmission $formSubmission): JsonResponse
    {
        if (!auth()->user()->can('manage forms')) {
            abort(403, 'No tienes permisos para ver este envío');
        }

        $formSubmission->load(['user', 'assignedTo', 'processedBy']);

        return response()->json([
            'data' => new FormSubmissionResource($formSubmission)
        ]);
    }

    public function update(Request $request, FormSubmission $formSubmission): JsonResponse
    {
        if (!auth()->user()->can('manage forms')) {
            abort(403, 'No tienes permisos para actualizar este envío');
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,reviewed,processed,rejected',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'internal_notes' => 'nullable|string',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'processed') {
            $validated['processed_at'] = now();
            $validated['processed_by_user_id'] = auth()->id();
        }

        $formSubmission->update($validated);

        return response()->json([
            'data' => new FormSubmissionResource($formSubmission->fresh()),
            'message' => 'Envío actualizado exitosamente'
        ]);
    }

    public function destroy(FormSubmission $formSubmission): JsonResponse
    {
        if (!auth()->user()->can('manage forms')) {
            abort(403, 'No tienes permisos para eliminar este envío');
        }

        $formSubmission->delete();

        return response()->json([
            'message' => 'Envío eliminado exitosamente'
        ]);
    }
}