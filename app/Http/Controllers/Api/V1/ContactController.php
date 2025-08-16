<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Contact\StoreContactRequest;
use App\Http\Requests\Api\V1\Contact\UpdateContactRequest;
use App\Http\Resources\Api\V1\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Contacts",
 *     description="Gestión de información de contacto"
 * )
 */
class ContactController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Contact::query()
            ->with(['organization', 'createdBy'])
            ->published()
            ->orderBy('is_primary', 'desc')
            ->orderBy('contact_type')
            ->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->boolean('primary_only')) {
            $query->primary();
        }

        if ($request->boolean('with_location')) {
            $query->withLocation();
        }

        $contacts = $query->get();
        return ContactResource::collection($contacts);
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $contact = Contact::create($validated);
        $contact->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new ContactResource($contact),
            'message' => 'Contacto creado exitosamente'
        ], 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        if (!$contact->isPublished()) {
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }

        $contact->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new ContactResource($contact)
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse
    {
        $validated = $request->validated();

        $contact->update($validated);
        $contact->load(['organization', 'createdBy']);

        return response()->json([
            'data' => new ContactResource($contact),
            'message' => 'Contacto actualizado exitosamente'
        ]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json([
            'message' => 'Contacto eliminado exitosamente'
        ]);
    }

    public function byType(Request $request, string $type): JsonResponse
    {
        $query = Contact::query()
            ->with(['organization', 'createdBy'])
            ->published()
            ->byType($type)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->boolean('primary_only')) {
            $query->primary();
        }

        $contacts = $query->get();

        return response()->json([
            'data' => ContactResource::collection($contacts),
            'type' => $type,
            'total' => $contacts->count()
        ]);
    }
}