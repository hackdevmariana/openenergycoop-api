<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Vendor\StoreVendorRequest;
use App\Http\Requests\Api\V1\Vendor\UpdateVendorRequest;
use App\Http\Resources\Api\V1\Vendor\VendorCollection;
use App\Http\Resources\Api\V1\Vendor\VendorResource;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Vendors",
 *     description="API Endpoints para gestión de proveedores"
 * )
 */
class VendorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/vendors",
     *     summary="Listar proveedores",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="page", in="query", description="Número de página", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Elementos por página", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", description="Término de búsqueda", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", description="Campo de ordenamiento", @OA\Schema(type="string")),
     *     @OA\Parameter(name="order", in="query", description="Orden (asc/desc)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="vendor_type", in="query", description="Filtrar por tipo", @OA\Schema(type="string")),
     *     @OA\Parameter(name="industry", in="query", description="Filtrar por industria", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Filtrar por estado", @OA\Schema(type="string")),
     *     @OA\Parameter(name="risk_level", in="query", description="Filtrar por nivel de riesgo", @OA\Schema(type="string")),
     *     @OA\Parameter(name="is_active", in="query", description="Filtrar por estado activo", @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="Lista de proveedores obtenida exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Vendor::query()
                ->with(['createdBy', 'approvedBy', 'country', 'state', 'city']);

            // Filtros
            if ($request->filled('vendor_type')) {
                $query->byType($request->vendor_type);
            }

            if ($request->filled('industry')) {
                $query->byIndustry($request->industry);
            }

            if ($request->filled('status')) {
                $query->byStatus($request->status);
            }

            if ($request->filled('risk_level')) {
                $query->byRiskLevel($request->risk_level);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('compliance_status')) {
                $query->byComplianceStatus($request->compliance_status);
            }

            if ($request->filled('country')) {
                $query->byLocation($request->country);
            }

            // Búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('legal_name', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortField = $request->get('sort', 'name');
            $sortOrder = $request->get('order', 'asc');
            $query->orderBy($sortField, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $vendors = $query->paginate($perPage);

            Log::info('Vendors listados', [
                'user_id' => auth()->id(),
                'filters' => $request->only(['vendor_type', 'industry', 'status', 'risk_level', 'is_active']),
                'total' => $vendors->total()
            ]);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al listar Vendors', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al listar los proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendors",
     *     summary="Crear nuevo proveedor",
     *     tags={"Vendors"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreVendorRequest")
     *     ),
     *     @OA\Response(response=201, description="Proveedor creado exitosamente"),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function store(StoreVendorRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $vendor = Vendor::create($request->validated());

            DB::commit();

            Log::info('Vendor creado', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'name' => $vendor->name
            ]);

            return response()->json([
                'message' => 'Proveedor creado exitosamente',
                'data' => new VendorResource($vendor)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear Vendor', [
                'user_id' => auth()->id(),
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al crear el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/{id}",
     *     summary="Obtener proveedor específico",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proveedor", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Proveedor obtenido exitosamente"),
     *     @OA\Response(response=404, description="Proveedor no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function show(Vendor $vendor): JsonResponse
    {
        try {
            $vendor->load(['createdBy', 'approvedBy', 'country', 'state', 'city']);

            Log::info('Vendor consultado', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id
            ]);

            return response()->json([
                'data' => new VendorResource($vendor)
            ]);
        } catch (\Exception $e) {
            Log::error('Error al consultar Vendor', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al consultar el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendors/{id}",
     *     summary="Actualizar proveedor",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proveedor", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateVendorRequest")
     *     ),
     *     @OA\Response(response=200, description="Proveedor actualizado exitosamente"),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=404, description="Proveedor no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        try {
            DB::beginTransaction();

            $vendor->update($request->validated());

            DB::commit();

            Log::info('Vendor actualizado', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'changes' => $request->validated()
            ]);

            return response()->json([
                'message' => 'Proveedor actualizado exitosamente',
                'data' => new VendorResource($vendor)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al actualizar Vendor', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al actualizar el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vendors/{id}",
     *     summary="Eliminar proveedor",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proveedor", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Proveedor eliminado exitosamente"),
     *     @OA\Response(response=404, description="Proveedor no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        try {
            DB::beginTransaction();

            $vendor->delete();

            DB::commit();

            Log::info('Vendor eliminado', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'name' => $vendor->name
            ]);

            return response()->json([
                'message' => 'Proveedor eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al eliminar Vendor', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al eliminar el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/statistics",
     *     summary="Obtener estadísticas de proveedores",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Estadísticas obtenidas exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_vendors' => Vendor::count(),
                'active_vendors' => Vendor::where('is_active', true)->count(),
                'inactive_vendors' => Vendor::where('is_active', false)->count(),
                'verified_vendors' => Vendor::verified()->count(),
                'preferred_vendors' => Vendor::preferred()->count(),
                'blacklisted_vendors' => Vendor::blacklisted()->count(),
                'approved_vendors' => Vendor::approved()->count(),
                'pending_approval_vendors' => Vendor::pendingApproval()->count(),
                'high_risk_vendors' => Vendor::highRisk()->count(),
                'compliant_vendors' => Vendor::compliant()->count(),
                'non_compliant_vendors' => Vendor::nonCompliant()->count(),
                'needs_audit_vendors' => Vendor::needsAudit()->count(),
            ];

            Log::info('Estadísticas de Vendors consultadas', [
                'user_id' => auth()->id()
            ]);

            return response()->json(['data' => $stats]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de Vendors', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener las estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/vendor-types",
     *     summary="Obtener tipos de proveedor disponibles",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Tipos obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function vendorTypes(): JsonResponse
    {
        try {
            $types = collect(Vendor::getVendorTypes())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => Vendor::byType($value)->count()
                ];
            })->values();

            return response()->json(['data' => $types]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de Vendors', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los tipos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/statuses",
     *     summary="Obtener estados disponibles",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Estados obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function statuses(): JsonResponse
    {
        try {
            $statuses = collect(Vendor::getStatuses())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => Vendor::byStatus($value)->count()
                ];
            })->values();

            return response()->json(['data' => $statuses]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estados de Vendors', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los estados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/risk-levels",
     *     summary="Obtener niveles de riesgo disponibles",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Niveles de riesgo obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function riskLevels(): JsonResponse
    {
        try {
            $riskLevels = collect(Vendor::getRiskLevels())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => Vendor::byRiskLevel($value)->count()
                ];
            })->values();

            return response()->json(['data' => $riskLevels]);
        } catch (\Exception $e) {
            Log::error('Error al obtener niveles de riesgo', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los niveles de riesgo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/compliance-statuses",
     *     summary="Obtener estados de cumplimiento disponibles",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Estados de cumplimiento obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function complianceStatuses(): JsonResponse
    {
        try {
            $complianceStatuses = collect(Vendor::getComplianceStatuses())->map(function ($label, $value) {
                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => Vendor::where('compliance_status', $value)->count()
                ];
            })->values();

            return response()->json(['data' => $complianceStatuses]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estados de cumplimiento', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los estados de cumplimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/vendors/{id}/toggle-active",
     *     summary="Alternar estado activo del proveedor",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proveedor", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Estado alternado exitosamente"),
     *     @OA\Response(response=404, description="Proveedor no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function toggleActive(Vendor $vendor): JsonResponse
    {
        try {
            DB::beginTransaction();

            $vendor->update(['is_active' => !$vendor->is_active]);

            DB::commit();

            Log::info('Estado activo de Vendor alternado', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'new_status' => $vendor->is_active
            ]);

            return response()->json([
                'message' => 'Estado activo alternado exitosamente',
                'data' => new VendorResource($vendor)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al alternar estado activo', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al alternar el estado activo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/vendors/{id}/toggle-verified",
     *     summary="Alternar estado verificado del proveedor",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proveedor", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Estado verificado alternado exitosamente"),
     *     @OA\Response(response=404, description="Proveedor no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function toggleVerified(Vendor $vendor): JsonResponse
    {
        try {
            DB::beginTransaction();

            $vendor->update(['is_verified' => !$vendor->is_verified]);

            DB::commit();

            Log::info('Estado verificado de Vendor alternado', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'new_status' => $vendor->is_verified
            ]);

            return response()->json([
                'message' => 'Estado verificado alternado exitosamente',
                'data' => new VendorResource($vendor)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al alternar estado verificado', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al alternar el estado verificado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/vendors/{id}/toggle-preferred",
     *     summary="Alternar estado preferido del proveedor",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proveedor", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Estado preferido alternado exitosamente"),
     *     @OA\Response(response=404, description="Proveedor no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function togglePreferred(Vendor $vendor): JsonResponse
    {
        try {
            DB::beginTransaction();

            $vendor->update(['is_preferred' => !$vendor->is_preferred]);

            DB::commit();

            Log::info('Estado preferido de Vendor alternado', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'new_status' => $vendor->is_preferred
            ]);

            return response()->json([
                'message' => 'Estado preferido alternado exitosamente',
                'data' => new VendorResource($vendor)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al alternar estado preferido', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al alternar el estado preferido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendors/{id}/duplicate",
     *     summary="Duplicar proveedor",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proveedor", @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Proveedor duplicado exitosamente"),
     *     @OA\Response(response=404, description="Proveedor no encontrado"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function duplicate(Vendor $vendor): JsonResponse
    {
        try {
            DB::beginTransaction();

            $duplicate = $vendor->replicate();
            $duplicate->name = $duplicate->name . ' (Copia)';
            $duplicate->is_active = false;
            $duplicate->is_verified = false;
            $duplicate->is_preferred = false;
            $duplicate->approved_at = null;
            $duplicate->approved_by = null;
            $duplicate->save();

            DB::commit();

            Log::info('Vendor duplicado', [
                'user_id' => auth()->id(),
                'original_id' => $vendor->id,
                'duplicate_id' => $duplicate->id
            ]);

            return response()->json([
                'message' => 'Proveedor duplicado exitosamente',
                'data' => new VendorResource($duplicate)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al duplicar Vendor', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al duplicar el proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/active",
     *     summary="Obtener proveedores activos",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Proveedores activos obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function active(): JsonResponse
    {
        try {
            $vendors = Vendor::active()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores activos', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores activos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/verified",
     *     summary="Obtener proveedores verificados",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Proveedores verificados obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function verified(): JsonResponse
    {
        try {
            $vendors = Vendor::verified()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores verificados', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores verificados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/preferred",
     *     summary="Obtener proveedores preferidos",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Proveedores preferidos obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function preferred(): JsonResponse
    {
        try {
            $vendors = Vendor::preferred()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores preferidos', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores preferidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/high-risk",
     *     summary="Obtener proveedores de alto riesgo",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Proveedores de alto riesgo obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function highRisk(): JsonResponse
    {
        try {
            $vendors = Vendor::highRisk()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores de alto riesgo', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores de alto riesgo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/compliant",
     *     summary="Obtener proveedores que cumplen",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Proveedores que cumplen obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function compliant(): JsonResponse
    {
        try {
            $vendors = Vendor::compliant()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores que cumplen', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores que cumplen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/needs-audit",
     *     summary="Obtener proveedores que necesitan auditoría",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Proveedores obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function needsAudit(): JsonResponse
    {
        try {
            $vendors = Vendor::needsAudit()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores que necesitan auditoría', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores que necesitan auditoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/contract-expiring",
     *     summary="Obtener proveedores con contratos próximos a vencer",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Proveedores obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function contractExpiring(): JsonResponse
    {
        try {
            $vendors = Vendor::byContractStatus('expiring_soon')
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores con contratos próximos a vencer', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores con contratos próximos a vencer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/by-type/{type}",
     *     summary="Obtener proveedores por tipo",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="type", in="path", required=true, description="Tipo de proveedor", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Proveedores obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byType(string $type): JsonResponse
    {
        try {
            $vendors = Vendor::byType($type)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores por tipo', [
                'user_id' => auth()->id(),
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores por tipo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/by-industry/{industry}",
     *     summary="Obtener proveedores por industria",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="industry", in="path", required=true, description="Industria", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Proveedores obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byIndustry(string $industry): JsonResponse
    {
        try {
            $vendors = Vendor::byIndustry($industry)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores por industria', [
                'user_id' => auth()->id(),
                'industry' => $industry,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores por industria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/by-risk-level/{riskLevel}",
     *     summary="Obtener proveedores por nivel de riesgo",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="riskLevel", in="path", required=true, description="Nivel de riesgo", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Proveedores obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byRiskLevel(string $riskLevel): JsonResponse
    {
        try {
            $vendors = Vendor::byRiskLevel($riskLevel)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores por nivel de riesgo', [
                'user_id' => auth()->id(),
                'risk_level' => $riskLevel,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores por nivel de riesgo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/by-compliance-status/{complianceStatus}",
     *     summary="Obtener proveedores por estado de cumplimiento",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="complianceStatus", in="path", required=true, description="Estado de cumplimiento", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Proveedores obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byComplianceStatus(string $complianceStatus): JsonResponse
    {
        try {
            $vendors = Vendor::where('compliance_status', $complianceStatus)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores por estado de cumplimiento', [
                'user_id' => auth()->id(),
                'compliance_status' => $complianceStatus,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores por estado de cumplimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/by-location/{country}",
     *     summary="Obtener proveedores por ubicación",
     *     tags={"Vendors"},
     *     @OA\Parameter(name="country", in="path", required=true, description="País", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Proveedores obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function byLocation(string $country): JsonResponse
    {
        try {
            $vendors = Vendor::byLocation($country)
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores por ubicación', [
                'user_id' => auth()->id(),
                'country' => $country,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores por ubicación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendors/high-rating",
     *     summary="Obtener proveedores con alta calificación",
     *     tags={"Vendors"},
     *     @OA\Response(response=200, description="Proveedores obtenidos exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function highRating(): JsonResponse
    {
        try {
            $vendors = Vendor::highRating()
                ->with(['createdBy', 'approvedBy'])
                ->paginate(15);

            return response()->json(new VendorCollection($vendors));
        } catch (\Exception $e) {
            Log::error('Error al obtener proveedores con alta calificación', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al obtener los proveedores con alta calificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
