<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
            ->with(['organization'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        $contacts = $query->get();
        return ContactResource::collection($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'type' => 'required|string|in:general,support,sales,media,technical',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'language' => 'required|string|in:es,en,ca,eu,gl',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $contact = Contact::create($validated);
        $contact->load(['organization']);

        return response()->json([
            'data' => new ContactResource($contact),
            'message' => 'Contacto creado exitosamente'
        ], 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        if (!$contact->is_active) {
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }

        $contact->load(['organization']);

        return response()->json([
            'data' => new ContactResource($contact)
        ]);
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $contact->update($validated);
        $contact->load(['organization']);

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
            ->with(['organization'])
            ->where('is_active', true)
            ->where('type', $type)
            ->orderBy('sort_order');

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        $contacts = $query->get();

        return response()->json([
            'data' => ContactResource::collection($contacts),
            'type' => $type,
            'total' => $contacts->count()
        ]);
    }
}