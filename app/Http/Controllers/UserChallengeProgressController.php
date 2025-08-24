<?php

namespace App\Http\Controllers;

use App\Models\UserChallengeProgress;
use App\Models\EnergyChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserChallengeProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = UserChallengeProgress::with(['user', 'challenge']);

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('challenge_id')) {
            $query->where('challenge_id', $request->challenge_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereNotNull('completed_at');
            } else {
                $query->whereNull('completed_at');
            }
        }

        if ($request->filled('progress_range')) {
            $min = $request->get('min_progress');
            $max = $request->get('max_progress');
            
            if ($min !== null) {
                $query->where('progress_kwh', '>=', $min);
            }
            if ($max !== null) {
                $query->where('progress_kwh', '<=', $max);
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $progressList = $query->paginate(15);

        return view('user-challenge-progress.index', compact('progressList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $users = User::all();
        $challenges = EnergyChallenge::active()->get();
        
        return view('user-challenge-progress.create', compact('users', 'challenges'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'challenge_id' => 'required|exists:energy_challenges,id',
            'progress_kwh' => 'required|numeric|min:0',
            'completed_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Verificar que no exista ya un progreso para este usuario y desafío
            $existingProgress = UserChallengeProgress::where('user_id', $request->user_id)
                ->where('challenge_id', $request->challenge_id)
                ->first();

            if ($existingProgress) {
                return redirect()->back()
                    ->with('error', 'Ya existe un progreso para este usuario en este desafío.')
                    ->withInput();
            }

            $progress = UserChallengeProgress::create($request->validated());

            DB::commit();

            return redirect()->route('user-challenge-progress.show', $progress)
                ->with('success', 'Progreso creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear progreso: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al crear el progreso. Por favor, inténtalo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserChallengeProgress $userChallengeProgress): View
    {
        $userChallengeProgress->load(['user', 'challenge']);
        
        return view('user-challenge-progress.show', compact('userChallengeProgress'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserChallengeProgress $userChallengeProgress): View
    {
        $users = User::all();
        $challenges = EnergyChallenge::all();
        
        return view('user-challenge-progress.edit', compact('userChallengeProgress', 'users', 'challenges'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserChallengeProgress $userChallengeProgress): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'challenge_id' => 'required|exists:energy_challenges,id',
            'progress_kwh' => 'required|numeric|min:0',
            'completed_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $userChallengeProgress->update($request->validated());

            DB::commit();

            return redirect()->route('user-challenge-progress.show', $userChallengeProgress)
                ->with('success', 'Progreso actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar progreso: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al actualizar el progreso. Por favor, inténtalo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserChallengeProgress $userChallengeProgress): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $userChallengeProgress->delete();

            DB::commit();

            return redirect()->route('user-challenge-progress.index')
                ->with('success', 'Progreso eliminado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar progreso: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al eliminar el progreso. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Actualizar progreso de un usuario
     */
    public function updateProgress(Request $request, UserChallengeProgress $userChallengeProgress): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'additional_kwh' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $userChallengeProgress->updateProgress($request->additional_kwh);

            return redirect()->back()
                ->with('success', 'Progreso actualizado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar progreso: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al actualizar el progreso.');
        }
    }

    /**
     * Completar un desafío
     */
    public function complete(UserChallengeProgress $userChallengeProgress): RedirectResponse
    {
        try {
            $userChallengeProgress->complete();

            return redirect()->back()
                ->with('success', 'Desafío completado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al completar desafío: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al completar el desafío.');
        }
    }

    /**
     * Reiniciar progreso
     */
    public function reset(UserChallengeProgress $userChallengeProgress): RedirectResponse
    {
        try {
            $userChallengeProgress->reset();

            return redirect()->back()
                ->with('success', 'Progreso reiniciado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al reiniciar progreso: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al reiniciar el progreso.');
        }
    }

    /**
     * Progreso del usuario autenticado
     */
    public function myProgress(Request $request): View
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Usuario no autenticado');
        }

        $query = UserChallengeProgress::where('user_id', $user->id)
            ->with(['challenge']);

        // Filtros
        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereNotNull('completed_at');
            } else {
                $query->whereNull('completed_at');
            }
        }

        $myProgress = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('user-challenge-progress.my-progress', compact('myProgress'));
    }

    /**
     * Unirse a un desafío
     */
    public function joinChallenge(Request $request, EnergyChallenge $challenge): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Usuario no autenticado');
        }

        try {
            DB::beginTransaction();

            // Verificar que no esté ya participando
            $existingProgress = UserChallengeProgress::where('user_id', $user->id)
                ->where('challenge_id', $challenge->id)
                ->first();

            if ($existingProgress) {
                return redirect()->back()
                    ->with('error', 'Ya estás participando en este desafío.');
            }

            // Verificar que el desafío esté activo
            if (!$challenge->isActive()) {
                return redirect()->back()
                    ->with('error', 'Este desafío no está disponible actualmente.');
            }

            $progress = UserChallengeProgress::create([
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'progress_kwh' => 0,
            ]);

            DB::commit();

            return redirect()->route('user-challenge-progress.show', $progress)
                ->with('success', 'Te has unido al desafío exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al unirse al desafío: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al unirse al desafío.');
        }
    }
}
