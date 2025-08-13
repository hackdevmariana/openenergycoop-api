<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Customer Profiles",
 *     description="API Endpoints para gestión de perfiles de clientes"
 * )
 */
class CustomerProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/customer-profiles",
     *     summary="Listar todos los perfiles de clientes",
     *     tags={"Customer Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="organization_id",
     *         in="query",
     *         description="ID de la organización para filtrar",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="profile_type",
     *         in="query",
     *         description="Tipo de perfil para filtrar",
     *         required=false,
     *         @OA\Schema(type="string", enum={"individual", "tenant", "company", "ownership_change"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de perfiles de clientes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CustomerProfile")),
     *             @OA\Property(property="links", ref="#/components/schemas/Links"),
     *             @OA\Property(property="meta", ref="#/components/schemas/Meta")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=403, description="Prohibido")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        // Verificar permisos del usuario
        $this->authorize('viewAny', CustomerProfile::class);

        // Construir query con filtros
        $query = CustomerProfile::with(['user', 'organization']);

        // Filtrar por organización si se especifica
        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Filtrar por tipo de perfil si se especifica
        if ($request->has('profile_type')) {
            $query->where('profile_type', $request->profile_type);
        }

        // Filtrar por organización del usuario autenticado si no es admin
        if (!auth()->user()->hasRole('admin')) {
            $query->where('organization_id', auth()->user()->organization_id);
        }

        // Paginar resultados
        $customerProfiles = $query->paginate(15);

        return response()->json([
            'data' => $customerProfiles->items(),
            'links' => [
                'first' => $customerProfiles->url(1),
                'last' => $customerProfiles->url($customerProfiles->lastPage()),
                'prev' => $customerProfiles->previousPageUrl(),
                'next' => $customerProfiles->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $customerProfiles->currentPage(),
                'last_page' => $customerProfiles->lastPage(),
                'per_page' => $customerProfiles->perPage(),
                'total' => $customerProfiles->total(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customer-profiles",
     *     summary="Crear un nuevo perfil de cliente",
     *     tags={"Customer Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "organization_id", "profile_type", "legal_id_type", "legal_id_number", "legal_name", "contract_type"},
     *             @OA\Property(property="user_id", type="integer", description="ID del usuario"),
     *             @OA\Property(property="organization_id", type="integer", description="ID de la organización"),
     *             @OA\Property(property="profile_type", type="string", enum={"individual", "tenant", "company", "ownership_change"}),
     *             @OA\Property(property="legal_id_type", type="string", enum={"dni", "nie", "passport", "cif"}),
     *             @OA\Property(property="legal_id_number", type="string", description="Número del documento legal"),
     *             @OA\Property(property="legal_name", type="string", description="Nombre legal completo"),
     *             @OA\Property(property="contract_type", type="string", enum={"own", "tenant", "company", "ownership_change"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Perfil de cliente creado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/CustomerProfile")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Datos de entrada inválidos"),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // Verificar permisos del usuario
        $this->authorize('create', CustomerProfile::class);

        // Validar datos de entrada
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
            'profile_type' => ['required', Rule::in(['individual', 'tenant', 'company', 'ownership_change'])],
            'legal_id_type' => ['required', Rule::in(['dni', 'nie', 'passport', 'cif'])],
            'legal_id_number' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'contract_type' => ['required', Rule::in(['own', 'tenant', 'company', 'ownership_change'])],
        ]);

        // Verificar que el usuario no tenga ya un perfil en esta organización
        $existingProfile = CustomerProfile::where('user_id', $validated['user_id'])
            ->where('organization_id', $validated['organization_id'])
            ->first();

        if ($existingProfile) {
            return response()->json([
                'message' => 'El usuario ya tiene un perfil en esta organización',
                'errors' => ['user_id' => ['Usuario duplicado en esta organización']]
            ], 422);
        }

        // Crear el perfil de cliente
        $customerProfile = CustomerProfile::create($validated);

        // Cargar relaciones para la respuesta
        $customerProfile->load(['user', 'organization']);

        return response()->json([
            'message' => 'Perfil de cliente creado exitosamente',
            'data' => $customerProfile
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer-profiles/{id}",
     *     summary="Obtener un perfil de cliente específico",
     *     tags={"Customer Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del perfil de cliente",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil de cliente encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/CustomerProfile")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Perfil de cliente no encontrado"),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=403, description="Prohibido")
     * )
     */
    public function show(CustomerProfile $customerProfile): JsonResponse
    {
        // Verificar permisos del usuario
        $this->authorize('view', $customerProfile);

        // Cargar relaciones
        $customerProfile->load(['user', 'organization', 'contactInfo', 'legalDocuments']);

        return response()->json([
            'data' => $customerProfile
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/customer-profiles/{id}",
     *     summary="Actualizar un perfil de cliente",
     *     tags={"Customer Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del perfil de cliente",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="profile_type", type="string", enum={"individual", "tenant", "company", "ownership_change"}),
     *             @OA\Property(property="legal_id_type", type="string", enum={"dni", "nie", "passport", "cif"}),
     *             @OA\Property(property="legal_id_number", type="string"),
     *             @OA\Property(property="legal_name", type="string"),
     *             @OA\Property(property="contract_type", type="string", enum={"own", "tenant", "company", "ownership_change"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil de cliente actualizado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/CustomerProfile")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Datos de entrada inválidos"),
     *     @OA\Response(response=404, description="Perfil de cliente no encontrado"),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, CustomerProfile $customerProfile): JsonResponse
    {
        // Verificar permisos del usuario
        $this->authorize('update', $customerProfile);

        // Validar datos de entrada
        $validated = $request->validate([
            'profile_type' => ['sometimes', Rule::in(['individual', 'tenant', 'company', 'ownership_change'])],
            'legal_id_type' => ['sometimes', Rule::in(['dni', 'nie', 'passport', 'cif'])],
            'legal_id_number' => 'sometimes|string|max:255',
            'legal_name' => 'sometimes|string|max:255',
            'contract_type' => ['sometimes', Rule::in(['own', 'tenant', 'company', 'ownership_change'])],
        ]);

        // Actualizar el perfil
        $customerProfile->update($validated);

        // Cargar relaciones para la respuesta
        $customerProfile->load(['user', 'organization']);

        return response()->json([
            'message' => 'Perfil de cliente actualizado exitosamente',
            'data' => $customerProfile
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/customer-profiles/{id}",
     *     summary="Eliminar un perfil de cliente",
     *     tags={"Customer Profiles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del perfil de cliente",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil de cliente eliminado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Perfil de cliente no encontrado"),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=403, description="Prohibido")
     * )
     */
    public function destroy(CustomerProfile $customerProfile): JsonResponse
    {
        // Verificar permisos del usuario
        $this->authorize('delete', $customerProfile);

        // Eliminar el perfil (esto también eliminará registros relacionados por cascade)
        $customerProfile->delete();

        return response()->json([
            'message' => 'Perfil de cliente eliminado exitosamente'
        ]);
    }
}
