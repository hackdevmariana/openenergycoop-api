<?php

namespace App\Http\Controllers;

use App\Models\EnergyChallenge;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnergyChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = EnergyChallenge::query();

        // Filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('reward_type')) {
            $query->where('reward_type', $request->reward_type);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $now = now();
            
            switch ($status) {
                case 'active':
                    $query->where('starts_at', '<=', $now)
                          ->where('ends_at', '>=', $now)
                          ->where('is_active', true);
                    break;
                case 'upcoming':
                    $query->where('starts_at', '>', $now);
                    break;
                case 'completed':
                    $query->where('ends_at', '<', $now);
                    break;
                case 'draft':
                    $query->where('starts_at', '>', $now);
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $challenges = $query->paginate(12);

        return view('energy-challenges.index', compact('challenges'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('energy-challenges.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:individual,colectivo',
            'goal_kwh' => 'required|numeric|min:0.01',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'reward_type' => 'required|in:symbolic,energy_donation,badge',
            'reward_details' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $challenge = EnergyChallenge::create($request->validated());

            DB::commit();

            return redirect()->route('energy-challenges.show', $challenge)
                ->with('success', 'Desafío creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear desafío energético: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al crear el desafío. Por favor, inténtalo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EnergyChallenge $energyChallenge): View
    {
        $energyChallenge->load(['userProgress.user', 'participants']);
        
        return view('energy-challenges.show', compact('energyChallenge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EnergyChallenge $energyChallenge): View
    {
        return view('energy-challenges.edit', compact('energyChallenge'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EnergyChallenge $energyChallenge): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:individual,colectivo',
            'goal_kwh' => 'required|numeric|min:0.01',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'reward_type' => 'required|in:symbolic,energy_donation,badge',
            'reward_details' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $energyChallenge->update($request->validated());

            DB::commit();

            return redirect()->route('energy-challenges.show', $energyChallenge)
                ->with('success', 'Desafío actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar desafío energético: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al actualizar el desafío. Por favor, inténtalo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EnergyChallenge $energyChallenge): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $energyChallenge->delete();

            DB::commit();

            return redirect()->route('energy-challenges.index')
                ->with('success', 'Desafío eliminado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar desafío energético: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al eliminar el desafío. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Activar o desactivar un desafío
     */
    public function toggleStatus(EnergyChallenge $energyChallenge): RedirectResponse
    {
        try {
            $energyChallenge->update([
                'is_active' => !$energyChallenge->is_active
            ]);

            $status = $energyChallenge->is_active ? 'activado' : 'desactivado';
            
            return redirect()->back()
                ->with('success', "Desafío {$status} exitosamente.");

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado del desafío: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al cambiar el estado del desafío.');
        }
    }

    /**
     * Duplicar un desafío
     */
    public function duplicate(EnergyChallenge $energyChallenge): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $newChallenge = $energyChallenge->replicate();
            $newChallenge->title = $energyChallenge->title . ' (Copia)';
            $newChallenge->starts_at = now()->addDays(7);
            $newChallenge->ends_at = now()->addDays(37);
            $newChallenge->is_active = false;
            $newChallenge->save();

            DB::commit();

            return redirect()->route('energy-challenges.edit', $newChallenge)
                ->with('success', 'Desafío duplicado exitosamente. Puedes editarlo ahora.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al duplicar desafío: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al duplicar el desafío.');
        }
    }

    /**
     * Obtener estadísticas del desafío
     */
    public function statistics(EnergyChallenge $energyChallenge): View
    {
        $energyChallenge->load(['userProgress.user']);
        
        $stats = [
            'total_participants' => $energyChallenge->total_participants,
            'average_progress' => $energyChallenge->average_progress,
            'progress_percentage' => $energyChallenge->progress_percentage,
            'days_remaining' => $energyChallenge->hasEnded() ? 0 : now()->diffInDays($energyChallenge->ends_at),
            'top_participants' => $energyChallenge->userProgress()
                ->with('user')
                ->orderBy('progress_kwh', 'desc')
                ->limit(10)
                ->get(),
        ];

        return view('energy-challenges.statistics', compact('energyChallenge', 'stats'));
    }
}
