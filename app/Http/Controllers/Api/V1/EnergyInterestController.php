<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyInterest;
use App\Models\User;
use App\Models\EnergyZoneSummary;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnergyInterestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergyInterest::with('user');

            // Filtros
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('zone_name')) {
                $query->where('zone_name', 'like', '%' . $request->zone_name . '%');
            }

            if ($request->filled('postal_code')) {
                $query->where('postal_code', 'like', '%' . $request->postal_code . '%');
            }

            if ($request->filled('with_user')) {
                $query->whereNotNull('user_id');
            }

            if ($request->filled('without_user')) {
                $query->whereNull('user_id');
            }

            if ($request->filled('contact_email')) {
                $query->where('contact_email', 'like', '%' . $request->contact_email . '%');
            }

            // Búsqueda general
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('zone_name', 'like', "%{$search}%")
                      ->orWhere('postal_code', 'like', "%{$search}%")
                      ->orWhere('contact_name', 'like', "%{$search}%")
                      ->orWhere('contact_email', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Ordenación
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $interests = $query->paginate($perPage);

            // Transformar datos
            $interests->getCollection()->transform(function ($interest) {
                return [
                    'id' => $interest->id,
                    'user' => $interest->user ? [
                        'id' => $interest->user->id,
                        'name' => $interest->user->name,
                        'email' => $interest->user->email,
                    ] : null,
                    'zone_name' => $interest->zone_name,
                    'postal_code' => $interest->postal_code,
                    'full_zone_name' => $interest->full_zone_name,
                    'type' => $interest->type,
                    'type_label' => $interest->type_label,
                    'estimated_production_kwh_day' => $interest->estimated_production_kwh_day,
                    'requested_kwh_day' => $interest->requested_kwh_day,
                    'contact_name' => $interest->contact_name,
                    'contact_email' => $interest->contact_email,
                    'contact_phone' => $interest->contact_phone,
                    'notes' => $interest->notes,
                    'status' => $interest->status,
                    'status_label' => $interest->status_label,
                    'status_color' => $interest->status_color,
                    'is_consumer' => $interest->isConsumer(),
                    'is_producer' => $interest->isProducer(),
                    'is_mixed' => $interest->isMixed(),
                    'has_user' => $interest->hasUser(),
                    'created_at' => $interest->created_at,
                    'updated_at' => $interest->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Intereses energéticos obtenidos exitosamente',
                'data' => $interests,
                'meta' => [
                    'total_interests' => EnergyInterest::count(),
                    'system_summary' => EnergyInterest::getSystemSummary(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener intereses energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los intereses energéticos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:users,id',
                'zone_name' => 'required|string|max:255',
                'postal_code' => 'required|string|max:10',
                'type' => 'required|in:consumer,producer,mixed',
                'estimated_production_kwh_day' => 'nullable|numeric|min:0',
                'requested_kwh_day' => 'nullable|numeric|min:0',
                'contact_name' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'notes' => 'nullable|string',
                'status' => 'nullable|in:pending,approved,rejected,active',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validaciones de negocio
            if ($request->type === 'producer' || $request->type === 'mixed') {
                if (!$request->filled('estimated_production_kwh_day')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La producción estimada es requerida para productores y mixtos',
                        'errors' => ['estimated_production_kwh_day' => ['Este campo es requerido para productores y mixtos']]
                    ], 422);
                }
            }

            if ($request->type === 'consumer' || $request->type === 'mixed') {
                if (!$request->filled('requested_kwh_day')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La demanda solicitada es requerida para consumidores y mixtos',
                        'errors' => ['requested_kwh_day' => ['Este campo es requerido para consumidores y mixtos']]
                    ], 422);
                }
            }

            // Si no hay user_id, se requieren datos de contacto
            if (!$request->filled('user_id')) {
                if (!$request->filled('contact_name') || !$request->filled('contact_email')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nombre y email de contacto son requeridos cuando no hay usuario',
                        'errors' => [
                            'contact_name' => ['Este campo es requerido cuando no hay usuario'],
                            'contact_email' => ['Este campo es requerido cuando no hay usuario']
                        ]
                    ], 422);
                }
            }

            DB::beginTransaction();

            $interestData = $request->only([
                'user_id', 'zone_name', 'postal_code', 'type',
                'estimated_production_kwh_day', 'requested_kwh_day',
                'contact_name', 'contact_email', 'contact_phone', 'notes', 'status'
            ]);

            // Establecer estado por defecto
            if (!isset($interestData['status'])) {
                $interestData['status'] = EnergyInterest::STATUS_PENDING;
            }

            $interest = EnergyInterest::create($interestData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Interés energético creado exitosamente',
                'data' => [
                    'id' => $interest->id,
                    'user' => $interest->user ? [
                        'id' => $interest->user->id,
                        'name' => $interest->user->name,
                        'email' => $interest->user->email,
                    ] : null,
                    'zone_name' => $interest->zone_name,
                    'postal_code' => $interest->postal_code,
                    'full_zone_name' => $interest->full_zone_name,
                    'type' => $interest->type,
                    'type_label' => $interest->type_label,
                    'estimated_production_kwh_day' => $interest->estimated_production_kwh_day,
                    'requested_kwh_day' => $interest->requested_kwh_day,
                    'contact_name' => $interest->contact_name,
                    'contact_email' => $interest->contact_email,
                    'contact_phone' => $interest->contact_phone,
                    'notes' => $interest->notes,
                    'status' => $interest->status,
                    'status_label' => $interest->status_label,
                    'status_color' => $interest->status_color,
                    'is_consumer' => $interest->isConsumer(),
                    'is_producer' => $interest->isProducer(),
                    'is_mixed' => $interest->isMixed(),
                    'has_user' => $interest->hasUser(),
                    'created_at' => $interest->created_at,
                    'updated_at' => $interest->updated_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear interés energético: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el interés energético',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $interest = EnergyInterest::with('user')->find($id);

            if (!$interest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interés energético no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Interés energético obtenido exitosamente',
                'data' => [
                    'id' => $interest->id,
                    'user' => $interest->user ? [
                        'id' => $interest->user->id,
                        'name' => $interest->user->name,
                        'email' => $interest->user->email,
                    ] : null,
                    'zone_name' => $interest->zone_name,
                    'postal_code' => $interest->postal_code,
                    'full_zone_name' => $interest->full_zone_name,
                    'type' => $interest->type,
                    'type_label' => $interest->type_label,
                    'estimated_production_kwh_day' => $interest->estimated_production_kwh_day,
                    'requested_kwh_day' => $interest->requested_kwh_day,
                    'contact_name' => $interest->contact_name,
                    'contact_email' => $interest->contact_email,
                    'contact_phone' => $interest->contact_phone,
                    'notes' => $interest->notes,
                    'status' => $interest->status,
                    'status_label' => $interest->status_label,
                    'status_color' => $interest->status_color,
                    'is_consumer' => $interest->isConsumer(),
                    'is_producer' => $interest->isProducer(),
                    'is_mixed' => $interest->isMixed(),
                    'has_user' => $interest->hasUser(),
                    'energy_summary' => $interest->getEnergySummary(),
                    'created_at' => $interest->created_at,
                    'updated_at' => $interest->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener interés energético: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el interés energético',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $interest = EnergyInterest::find($id);

            if (!$interest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interés energético no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|nullable|exists:users,id',
                'zone_name' => 'sometimes|required|string|max:255',
                'postal_code' => 'sometimes|required|string|max:10',
                'type' => 'sometimes|required|in:consumer,producer,mixed',
                'estimated_production_kwh_day' => 'sometimes|nullable|numeric|min:0',
                'requested_kwh_day' => 'sometimes|nullable|numeric|min:0',
                'contact_name' => 'sometimes|nullable|string|max:255',
                'contact_email' => 'sometimes|nullable|email|max:255',
                'contact_phone' => 'sometimes|nullable|string|max:20',
                'notes' => 'sometimes|nullable|string',
                'status' => 'sometimes|required|in:pending,approved,rejected,active',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $updateData = $request->only([
                'user_id', 'zone_name', 'postal_code', 'type',
                'estimated_production_kwh_day', 'requested_kwh_day',
                'contact_name', 'contact_email', 'contact_phone', 'notes', 'status'
            ]);

            $interest->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Interés energético actualizado exitosamente',
                'data' => [
                    'id' => $interest->id,
                    'user' => $interest->user ? [
                        'id' => $interest->user->id,
                        'name' => $interest->user->name,
                        'email' => $interest->user->email,
                    ] : null,
                    'zone_name' => $interest->zone_name,
                    'postal_code' => $interest->postal_code,
                    'full_zone_name' => $interest->full_zone_name,
                    'type' => $interest->type,
                    'type_label' => $interest->type_label,
                    'estimated_production_kwh_day' => $interest->estimated_production_kwh_day,
                    'requested_kwh_day' => $interest->requested_kwh_day,
                    'contact_name' => $interest->contact_name,
                    'contact_email' => $interest->contact_email,
                    'contact_phone' => $interest->contact_phone,
                    'notes' => $interest->notes,
                    'status' => $interest->status,
                    'status_label' => $interest->status_label,
                    'status_color' => $interest->status_color,
                    'is_consumer' => $interest->isConsumer(),
                    'is_producer' => $interest->isProducer(),
                    'is_mixed' => $interest->isMixed(),
                    'has_user' => $interest->hasUser(),
                    'created_at' => $interest->created_at,
                    'updated_at' => $interest->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar interés energético: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el interés energético',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $interest = EnergyInterest::find($id);

            if (!$interest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interés energético no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            $contactInfo = $interest->hasUser() 
                ? "Usuario: {$interest->user->name}"
                : "Contacto: {$interest->contact_name} ({$interest->contact_email})";

            $interest->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Interés energético eliminado exitosamente - {$contactInfo}"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar interés energético: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el interés energético',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get system summary statistics
     */
    public function systemSummary(): JsonResponse
    {
        try {
            $summary = EnergyInterest::getSystemSummary();

            return response()->json([
                'success' => true,
                'message' => 'Resumen del sistema obtenido exitosamente',
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener resumen del sistema: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen del sistema',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Approve an interest
     */
    public function approve(string $id): JsonResponse
    {
        try {
            $interest = EnergyInterest::find($id);

            if (!$interest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interés energético no encontrado'
                ], 404);
            }

            $oldStatus = $interest->status;
            $interest->approve();

            return response()->json([
                'success' => true,
                'message' => 'Interés energético aprobado exitosamente',
                'data' => [
                    'id' => $interest->id,
                    'old_status' => $oldStatus,
                    'new_status' => $interest->status,
                    'status_label' => $interest->status_label,
                    'status_color' => $interest->status_color,
                    'updated_at' => $interest->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al aprobar interés energético: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar el interés energético',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Reject an interest
     */
    public function reject(string $id): JsonResponse
    {
        try {
            $interest = EnergyInterest::find($id);

            if (!$interest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interés energético no encontrado'
                ], 404);
            }

            $oldStatus = $interest->status;
            $interest->reject();

            return response()->json([
                'success' => true,
                'message' => 'Interés energético rechazado exitosamente',
                'data' => [
                    'id' => $interest->id,
                    'old_status' => $oldStatus,
                    'new_status' => $interest->status,
                    'status_label' => $interest->status_label,
                    'status_color' => $interest->status_color,
                    'updated_at' => $interest->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al rechazar interés energético: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar el interés energético',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Activate an interest
     */
    public function activate(string $id): JsonResponse
    {
        try {
            $interest = EnergyInterest::find($id);

            if (!$interest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interés energético no encontrado'
                ], 404);
            }

            $oldStatus = $interest->status;
            $interest->activate();

            return response()->json([
                'success' => true,
                'message' => 'Interés energético activado exitosamente',
                'data' => [
                    'id' => $interest->id,
                    'old_status' => $oldStatus,
                    'new_status' => $interest->status,
                    'status_label' => $interest->status_label,
                    'status_color' => $interest->status_color,
                    'updated_at' => $interest->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al activar interés energético: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al activar el interés energético',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get interests by zone
     */
    public function byZone(string $zoneName): JsonResponse
    {
        try {
            $interests = EnergyInterest::getInterestsByZone($zoneName);

            return response()->json([
                'success' => true,
                'message' => "Intereses de la zona '{$zoneName}' obtenidos exitosamente",
                'data' => $interests,
                'meta' => [
                    'zone_name' => $zoneName,
                    'total_interests' => $interests->count(),
                    'by_type' => [
                        'consumer' => $interests->where('type', 'consumer')->count(),
                        'producer' => $interests->where('type', 'producer')->count(),
                        'mixed' => $interests->where('type', 'mixed')->count(),
                    ],
                    'by_status' => [
                        'pending' => $interests->where('status', 'pending')->count(),
                        'approved' => $interests->where('status', 'approved')->count(),
                        'active' => $interests->where('status', 'active')->count(),
                        'rejected' => $interests->where('status', 'rejected')->count(),
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener intereses por zona: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los intereses por zona',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get interests by type
     */
    public function byType(string $type): JsonResponse
    {
        try {
            $interests = EnergyInterest::getInterestsByType($type);

            return response()->json([
                'success' => true,
                'message' => "Intereses de tipo '{$type}' obtenidos exitosamente",
                'data' => $interests,
                'meta' => [
                    'type' => $type,
                    'type_label' => EnergyInterest::getTypes()[$type] ?? 'Desconocido',
                    'total_interests' => $interests->count(),
                    'by_status' => [
                        'pending' => $interests->where('status', 'pending')->count(),
                        'approved' => $interests->where('status', 'approved')->count(),
                        'active' => $interests->where('status', 'active')->count(),
                        'rejected' => $interests->where('status', 'rejected')->count(),
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener intereses por tipo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los intereses por tipo',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
