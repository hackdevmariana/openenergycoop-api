<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeamChallengeProgressResource;
use App\Models\TeamChallengeProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamChallengeProgressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TeamChallengeProgress::query()
            ->with(['team', 'challenge'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('challenge_id')) {
            $query->where('challenge_id', $request->challenge_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $progress = $query->get();
        return TeamChallengeProgressResource::collection($progress);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'challenge_id' => 'required|exists:challenges,id',
            'current_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $progress = TeamChallengeProgress::create($validated);
        $progress->load(['team', 'challenge']);

        return response()->json([
            'data' => new TeamChallengeProgressResource($progress),
            'message' => 'Progreso registrado exitosamente'
        ], 201);
    }

    public function show(TeamChallengeProgress $teamChallengeProgress): JsonResponse
    {
        $teamChallengeProgress->load(['team', 'challenge']);

        return response()->json([
            'data' => new TeamChallengeProgressResource($teamChallengeProgress)
        ]);
    }

    public function update(Request $request, TeamChallengeProgress $teamChallengeProgress): JsonResponse
    {
        $validated = $request->validate([
            'current_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:active,completed,failed,paused',
        ]);

        $teamChallengeProgress->update($validated);

        return response()->json([
            'data' => new TeamChallengeProgressResource($teamChallengeProgress->fresh()),
            'message' => 'Progreso actualizado exitosamente'
        ]);
    }

    public function destroy(TeamChallengeProgress $teamChallengeProgress): JsonResponse
    {
        $teamChallengeProgress->delete();

        return response()->json([
            'message' => 'Progreso eliminado exitosamente'
        ]);
    }

    public function leaderboard(Request $request, int $challengeId): JsonResponse
    {
        $query = TeamChallengeProgress::query()
            ->with(['team'])
            ->where('challenge_id', $challengeId)
            ->where('status', 'active')
            ->orderBy('current_value', 'desc');

        $limit = min($request->get('limit', 10), 50);
        $progress = $query->limit($limit)->get();

        return response()->json([
            'data' => TeamChallengeProgressResource::collection($progress),
            'challenge_id' => $challengeId,
            'total' => $progress->count()
        ]);
    }

    public function updateProgress(Request $request, TeamChallengeProgress $teamChallengeProgress): JsonResponse
    {
        $request->validate([
            'increment' => 'required|numeric'
        ]);

        $newValue = $teamChallengeProgress->current_value + $request->increment;
        $teamChallengeProgress->update(['current_value' => max(0, $newValue)]);

        return response()->json([
            'data' => new TeamChallengeProgressResource($teamChallengeProgress->fresh()),
            'message' => 'Progreso actualizado exitosamente'
        ]);
    }
}