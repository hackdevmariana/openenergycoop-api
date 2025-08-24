<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\StoreUserRequest;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Gestión avanzada de usuarios del sistema"
 * )
 */
class UserController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        // Solo administradores pueden listar usuarios
        if (!auth('sanctum')->user()->can('manage users')) {
            abort(403, 'No tienes permisos para listar usuarios');
        }

        $query = User::query()
            ->with(['roles', 'permissions'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->has('verified')) {
            if ($request->boolean('verified')) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $perPage = min($request->get('per_page', 20), 50);
        $users = $query->paginate($perPage);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        if (!auth('sanctum')->user()->can('manage users')) {
            abort(403, 'No tienes permisos para crear usuarios');
        }

        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        
        if ($request->boolean('email_verified')) {
            $validated['email_verified_at'] = now();
        }

        $user = User::create($validated);

        if ($request->filled('role')) {
            $user->assignRole($request->role);
        }

        $user->load(['roles', 'permissions']);

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Usuario creado exitosamente'
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if (auth('sanctum')->id() !== $user->id && !auth('sanctum')->user()->can('manage users')) {
            abort(403, 'No tienes permisos para ver este usuario');
        }

        $user->load(['roles', 'permissions']);

        $data = ['data' => new UserResource($user)];

        if ($request->boolean('include_stats')) {
            $data['stats'] = [
                'total_devices' => $user->devices()->count(),
                'total_settings' => $user->settings()->count(),
                'total_consents' => $user->consentLogs()->count(),
                'account_age_days' => $user->created_at->diffInDays(now()),
            ];
        }

        return response()->json($data);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if (auth('sanctum')->id() !== $user->id && !auth('sanctum')->user()->can('manage users')) {
            abort(403, 'No tienes permisos para actualizar este usuario');
        }

        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($request->filled('role') && auth('sanctum')->user()->can('manage users')) {
            $user->syncRoles([$request->role]);
        }

        $user->load(['roles', 'permissions']);

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Usuario actualizado exitosamente'
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if (!auth('sanctum')->user()->can('manage users')) {
            abort(403, 'No tienes permisos para eliminar usuarios');
        }

        if (auth('sanctum')->id() === $user->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        $user->load(['roles', 'permissions']);

        return response()->json([
            'data' => new UserResource($user)
        ]);
    }

    public function updateMe(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        
        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
            $rules['current_password'] = 'required|string';
            
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta'
                ], 422);
            }
        }

        $validated = $request->validate($rules);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        $user->load(['roles', 'permissions']);

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Perfil actualizado exitosamente'
        ]);
    }
}