<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeamMembershipResource;
use App\Models\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamMembershipController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TeamMembership::query()
            ->with(['team', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $memberships = $query->get();
        return TeamMembershipResource::collection($memberships);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:member,leader,admin',
        ]);

        // Verificar que el usuario no esté ya en el equipo
        $existing = TeamMembership::where('team_id', $validated['team_id'])
            ->where('user_id', $validated['user_id'])
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'El usuario ya es miembro de este equipo'
            ], 422);
        }

        $membership = TeamMembership::create($validated);
        $membership->load(['team', 'user']);

        return response()->json([
            'data' => new TeamMembershipResource($membership),
            'message' => 'Membresía creada exitosamente'
        ], 201);
    }

    public function show(TeamMembership $teamMembership): JsonResponse
    {
        $teamMembership->load(['team', 'user']);

        return response()->json([
            'data' => new TeamMembershipResource($teamMembership)
        ]);
    }

    public function update(Request $request, TeamMembership $teamMembership): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'sometimes|string|in:member,leader,admin',
            'is_active' => 'boolean',
        ]);

        $teamMembership->update($validated);

        return response()->json([
            'data' => new TeamMembershipResource($teamMembership->fresh()),
            'message' => 'Membresía actualizada exitosamente'
        ]);
    }

    public function destroy(TeamMembership $teamMembership): JsonResponse
    {
        $teamMembership->delete();

        return response()->json([
            'message' => 'Membresía eliminada exitosamente'
        ]);
    }

    public function leave(Request $request): JsonResponse
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id'
        ]);

        $membership = TeamMembership::where('team_id', $request->team_id)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$membership) {
            return response()->json([
                'message' => 'No eres miembro de este equipo'
            ], 422);
        }

        $membership->update(['is_active' => false, 'left_at' => now()]);

        return response()->json([
            'message' => 'Has abandonado el equipo exitosamente'
        ]);
    }
}