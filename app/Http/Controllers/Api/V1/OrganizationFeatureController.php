<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrganizationFeatureResource;
use App\Models\OrganizationFeature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationFeatureController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = OrganizationFeature::query()
            ->with(['organization'])
            ->orderBy('name');

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->has('is_enabled')) {
            $query->where('is_enabled', $request->boolean('is_enabled'));
        }

        $features = $query->get();
        return OrganizationFeatureResource::collection($features);
    }

    public function store(Request $request): JsonResponse
    {
        if (!auth()->user()->can('manage organizations')) {
            abort(403, 'No tienes permisos para crear características organizacionales');
        }

        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'is_enabled' => 'boolean',
        ]);

        $feature = OrganizationFeature::create($validated);

        return response()->json([
            'data' => new OrganizationFeatureResource($feature),
            'message' => 'Característica creada exitosamente'
        ], 201);
    }

    public function show(OrganizationFeature $organizationFeature): JsonResponse
    {
        $organizationFeature->load(['organization']);

        return response()->json([
            'data' => new OrganizationFeatureResource($organizationFeature)
        ]);
    }

    public function update(Request $request, OrganizationFeature $organizationFeature): JsonResponse
    {
        if (!auth()->user()->can('manage organizations')) {
            abort(403, 'No tienes permisos para actualizar características organizacionales');
        }

        $validated = $request->validate([
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'is_enabled' => 'boolean',
        ]);

        $organizationFeature->update($validated);

        return response()->json([
            'data' => new OrganizationFeatureResource($organizationFeature),
            'message' => 'Característica actualizada exitosamente'
        ]);
    }

    public function destroy(OrganizationFeature $organizationFeature): JsonResponse
    {
        if (!auth()->user()->can('manage organizations')) {
            abort(403, 'No tienes permisos para eliminar características organizacionales');
        }

        $organizationFeature->delete();

        return response()->json([
            'message' => 'Característica eliminada exitosamente'
        ]);
    }
}