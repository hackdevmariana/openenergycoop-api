<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EnergyRightPreSale;
use App\Models\User;
use App\Models\EnergyInstallation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnergyRightPreSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EnergyRightPreSale::with(['user', 'installation']);

            // Filtros
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('zone_name')) {
                $query->where('zone_name', 'like', '%' . $request->zone_name . '%');
            }

            if ($request->filled('postal_code')) {
                $query->where('postal_code', 'like', '%' . $request->postal_code . '%');
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('energy_installation_id')) {
                $query->where('energy_installation_id', $request->energy_installation_id);
            }

            if ($request->filled('with_installation')) {
                $query->whereNotNull('energy_installation_id');
            }

            if ($request->filled('without_installation')) {
                $query->whereNull('energy_installation_id');
            }

            if ($request->filled('active')) {
                $query->active();
            }

            if ($request->filled('expired')) {
                $query->expired();
            }

            if ($request->filled('expiring_soon')) {
                $days = $request->get('expiring_soon_days', 30);
                $query->expiringSoon($days);
            }

            if ($request->filled('min_kwh')) {
                $query->where('kwh_per_month_reserved', '>=', $request->min_kwh);
            }

            if ($request->filled('max_kwh')) {
                $query->where('kwh_per_month_reserved', '<=', $request->max_kwh);
            }

            if ($request->filled('min_price')) {
                $query->where('price_per_kwh', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price_per_kwh', '<=', $request->max_price);
            }

            // Búsqueda general
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('zone_name', 'like', "%{$search}%")
                      ->orWhere('postal_code', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('installation', function ($installationQuery) use ($search) {
                          $installationQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Ordenación
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $preSales = $query->paginate($perPage);

            // Transformar datos
            $preSales->getCollection()->transform(function ($preSale) {
                return [
                    'id' => $preSale->id,
                    'user' => $preSale->user ? [
                        'id' => $preSale->user->id,
                        'name' => $preSale->user->name,
                        'email' => $preSale->user->email,
                    ] : null,
                    'installation' => $preSale->installation ? [
                        'id' => $preSale->installation->id,
                        'name' => $preSale->installation->name,
                        'postal_code' => $preSale->installation->postal_code,
                    ] : null,
                    'zone_name' => $preSale->zone_name,
                    'postal_code' => $preSale->postal_code,
                    'full_zone_name' => $preSale->full_zone_name,
                    'kwh_per_month_reserved' => $preSale->kwh_per_month_reserved,
                    'price_per_kwh' => $preSale->price_per_kwh,
                    'total_value' => $preSale->total_value,
                    'total_value_formatted' => $preSale->total_value_formatted,
                    'status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'signed_at' => $preSale->signed_at,
                    'expires_at' => $preSale->expires_at,
                    'is_expired' => $preSale->is_expired,
                    'is_expiring_soon' => $preSale->is_expiring_soon,
                    'days_until_expiration' => $preSale->days_until_expiration,
                    'expiration_status' => $preSale->expiration_status,
                    'notes' => $preSale->notes,
                    'created_at' => $preSale->created_at,
                    'updated_at' => $preSale->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Preventas de derechos energéticos obtenidas exitosamente',
                'data' => $preSales,
                'meta' => [
                    'total_presales' => EnergyRightPreSale::count(),
                    'system_summary' => EnergyRightPreSale::getSystemSummary(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener preventas de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preventas de derechos energéticos',
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
                'user_id' => 'required|exists:users,id',
                'energy_installation_id' => 'nullable|exists:energy_installations,id',
                'zone_name' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'kwh_per_month_reserved' => 'required|numeric|min:0.01',
                'price_per_kwh' => 'required|numeric|min:0.0001',
                'status' => 'nullable|in:pending,confirmed,cancelled',
                'signed_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after:now',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validaciones de negocio
            if (!$request->filled('energy_installation_id') && (!$request->filled('zone_name') || !$request->filled('postal_code'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se debe especificar una instalación o una zona con código postal',
                    'errors' => [
                        'energy_installation_id' => ['Este campo es requerido si no se especifica zona'],
                        'zone_name' => ['Este campo es requerido si no se especifica instalación'],
                        'postal_code' => ['Este campo es requerido si no se especifica instalación']
                    ]
                ], 422);
            }

            DB::beginTransaction();

            $preSaleData = $request->only([
                'user_id', 'energy_installation_id', 'zone_name', 'postal_code',
                'kwh_per_month_reserved', 'price_per_kwh', 'status', 'signed_at', 'expires_at', 'notes'
            ]);

            // Establecer estado por defecto
            if (!isset($preSaleData['status'])) {
                $preSaleData['status'] = EnergyRightPreSale::STATUS_PENDING;
            }

            // Si se confirma, establecer fecha de firma
            if ($preSaleData['status'] === EnergyRightPreSale::STATUS_CONFIRMED && !$preSaleData['signed_at']) {
                $preSaleData['signed_at'] = now();
            }

            $preSale = EnergyRightPreSale::create($preSaleData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Preventa de derechos energéticos creada exitosamente',
                'data' => [
                    'id' => $preSale->id,
                    'user' => $preSale->user ? [
                        'id' => $preSale->user->id,
                        'name' => $preSale->user->name,
                        'email' => $preSale->user->email,
                    ] : null,
                    'installation' => $preSale->installation ? [
                        'id' => $preSale->installation->id,
                        'name' => $preSale->installation->name,
                        'postal_code' => $preSale->installation->postal_code,
                    ] : null,
                    'zone_name' => $preSale->zone_name,
                    'postal_code' => $preSale->postal_code,
                    'full_zone_name' => $preSale->full_zone_name,
                    'kwh_per_month_reserved' => $preSale->kwh_per_month_reserved,
                    'price_per_kwh' => $preSale->price_per_kwh,
                    'total_value' => $preSale->total_value,
                    'total_value_formatted' => $preSale->total_value_formatted,
                    'status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'signed_at' => $preSale->signed_at,
                    'expires_at' => $preSale->expires_at,
                    'is_expired' => $preSale->is_expired,
                    'is_expiring_soon' => $preSale->is_expiring_soon,
                    'days_until_expiration' => $preSale->days_until_expiration,
                    'expiration_status' => $preSale->expiration_status,
                    'notes' => $preSale->notes,
                    'created_at' => $preSale->created_at,
                    'updated_at' => $preSale->updated_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear preventa de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la preventa de derechos energéticos',
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
            $preSale = EnergyRightPreSale::with(['user', 'installation'])->find($id);

            if (!$preSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preventa de derechos energéticos no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Preventa de derechos energéticos obtenida exitosamente',
                'data' => [
                    'id' => $preSale->id,
                    'user' => $preSale->user ? [
                        'id' => $preSale->user->id,
                        'name' => $preSale->user->name,
                        'email' => $preSale->user->email,
                    ] : null,
                    'installation' => $preSale->installation ? [
                        'id' => $preSale->installation->id,
                        'name' => $preSale->installation->name,
                        'postal_code' => $preSale->installation->postal_code,
                    ] : null,
                    'zone_name' => $preSale->zone_name,
                    'postal_code' => $preSale->postal_code,
                    'full_zone_name' => $preSale->full_zone_name,
                    'kwh_per_month_reserved' => $preSale->kwh_per_month_reserved,
                    'price_per_kwh' => $preSale->price_per_kwh,
                    'total_value' => $preSale->total_value,
                    'total_value_formatted' => $preSale->total_value_formatted,
                    'status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'signed_at' => $preSale->signed_at,
                    'expires_at' => $preSale->expires_at,
                    'is_expired' => $preSale->is_expired,
                    'is_expiring_soon' => $preSale->is_expiring_soon,
                    'days_until_expiration' => $preSale->days_until_expiration,
                    'expiration_status' => $preSale->expiration_status,
                    'notes' => $preSale->notes,
                    'pre_sale_summary' => $preSale->getPreSaleSummary(),
                    'created_at' => $preSale->created_at,
                    'updated_at' => $preSale->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener preventa de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la preventa de derechos energéticos',
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
            $preSale = EnergyRightPreSale::find($id);

            if (!$preSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preventa de derechos energéticos no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|required|exists:users,id',
                'energy_installation_id' => 'sometimes|nullable|exists:energy_installations,id',
                'zone_name' => 'sometimes|nullable|string|max:255',
                'postal_code' => 'sometimes|nullable|string|max:10',
                'kwh_per_month_reserved' => 'sometimes|required|numeric|min:0.01',
                'price_per_kwh' => 'sometimes|required|numeric|min:0.0001',
                'status' => 'sometimes|required|in:pending,confirmed,cancelled',
                'signed_at' => 'sometimes|nullable|date',
                'expires_at' => 'sometimes|nullable|date',
                'notes' => 'sometimes|nullable|string',
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
                'user_id', 'energy_installation_id', 'zone_name', 'postal_code',
                'kwh_per_month_reserved', 'price_per_kwh', 'status', 'signed_at', 'expires_at', 'notes'
            ]);

            // Si se confirma, establecer fecha de firma si no existe
            if (isset($updateData['status']) && $updateData['status'] === EnergyRightPreSale::STATUS_CONFIRMED && !$updateData['signed_at']) {
                $updateData['signed_at'] = now();
            }

            $preSale->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Preventa de derechos energéticos actualizada exitosamente',
                'data' => [
                    'id' => $preSale->id,
                    'user' => $preSale->user ? [
                        'id' => $preSale->user->id,
                        'name' => $preSale->user->name,
                        'email' => $preSale->user->email,
                    ] : null,
                    'installation' => $preSale->installation ? [
                        'id' => $preSale->installation->id,
                        'name' => $preSale->installation->name,
                        'postal_code' => $preSale->installation->postal_code,
                    ] : null,
                    'zone_name' => $preSale->zone_name,
                    'postal_code' => $preSale->postal_code,
                    'full_zone_name' => $preSale->full_zone_name,
                    'kwh_per_month_reserved' => $preSale->kwh_per_month_reserved,
                    'price_per_kwh' => $preSale->price_per_kwh,
                    'total_value' => $preSale->total_value,
                    'total_value_formatted' => $preSale->total_value_formatted,
                    'status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'signed_at' => $preSale->signed_at,
                    'expires_at' => $preSale->expires_at,
                    'is_expired' => $preSale->is_expired,
                    'is_expiring_soon' => $preSale->is_expiring_soon,
                    'days_until_expiration' => $preSale->days_until_expiration,
                    'expiration_status' => $preSale->expiration_status,
                    'notes' => $preSale->notes,
                    'created_at' => $preSale->created_at,
                    'updated_at' => $preSale->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar preventa de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la preventa de derechos energéticos',
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
            $preSale = EnergyRightPreSale::find($id);

            if (!$preSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preventa de derechos energéticos no encontrada'
                ], 404);
            }

            DB::beginTransaction();

            $userInfo = $preSale->user ? "Usuario: {$preSale->user->name}" : "Usuario ID: {$preSale->user_id}";
            $locationInfo = $preSale->installation ? "Instalación: {$preSale->installation->name}" : "Zona: {$preSale->full_zone_name}";

            $preSale->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Preventa de derechos energéticos eliminada exitosamente - {$userInfo} - {$locationInfo}"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar preventa de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la preventa de derechos energéticos',
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
            $summary = EnergyRightPreSale::getSystemSummary();

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
     * Confirm a pre-sale
     */
    public function confirm(string $id): JsonResponse
    {
        try {
            $preSale = EnergyRightPreSale::find($id);

            if (!$preSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preventa de derechos energéticos no encontrada'
                ], 404);
            }

            if (!$preSale->canBeConfirmed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La preventa no puede ser confirmada en su estado actual',
                    'data' => [
                        'current_status' => $preSale->status,
                        'is_expired' => $preSale->is_expired,
                    ]
                ], 422);
            }

            $oldStatus = $preSale->status;
            $preSale->confirm();

            return response()->json([
                'success' => true,
                'message' => 'Preventa de derechos energéticos confirmada exitosamente',
                'data' => [
                    'id' => $preSale->id,
                    'old_status' => $oldStatus,
                    'new_status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'signed_at' => $preSale->signed_at,
                    'updated_at' => $preSale->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al confirmar preventa de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar la preventa de derechos energéticos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Cancel a pre-sale
     */
    public function cancel(string $id): JsonResponse
    {
        try {
            $preSale = EnergyRightPreSale::find($id);

            if (!$preSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preventa de derechos energéticos no encontrada'
                ], 404);
            }

            if (!$preSale->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La preventa no puede ser cancelada en su estado actual',
                    'data' => [
                        'current_status' => $preSale->status,
                    ]
                ], 422);
            }

            $oldStatus = $preSale->status;
            $preSale->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Preventa de derechos energéticos cancelada exitosamente',
                'data' => [
                    'id' => $preSale->id,
                    'old_status' => $oldStatus,
                    'new_status' => $preSale->status,
                    'status_label' => $preSale->status_label,
                    'status_color' => $preSale->status_color,
                    'updated_at' => $preSale->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cancelar preventa de derechos energéticos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la preventa de derechos energéticos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get pre-sales by zone
     */
    public function byZone(string $zoneName): JsonResponse
    {
        try {
            $preSales = EnergyRightPreSale::getPreSalesByZone($zoneName);

            return response()->json([
                'success' => true,
                'message' => "Preventas de la zona '{$zoneName}' obtenidas exitosamente",
                'data' => $preSales,
                'meta' => [
                    'zone_name' => $zoneName,
                    'total_presales' => $preSales->count(),
                    'by_status' => [
                        'pending' => $preSales->where('status', 'pending')->count(),
                        'confirmed' => $preSales->where('status', 'confirmed')->count(),
                        'cancelled' => $preSales->where('status', 'cancelled')->count(),
                    ],
                    'total_kwh_reserved' => $preSales->where('status', 'confirmed')->sum('kwh_per_month_reserved'),
                    'total_value_reserved' => $preSales->where('status', 'confirmed')->sum('total_value'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener preventas por zona: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preventas por zona',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get pre-sales by status
     */
    public function byStatus(string $status): JsonResponse
    {
        try {
            $preSales = EnergyRightPreSale::getPreSalesByStatus($status);

            return response()->json([
                'success' => true,
                'message' => "Preventas con estado '{$status}' obtenidas exitosamente",
                'data' => $preSales,
                'meta' => [
                    'status' => $status,
                    'status_label' => EnergyRightPreSale::getStatuses()[$status] ?? 'Desconocido',
                    'total_presales' => $preSales->count(),
                    'total_kwh_reserved' => $preSales->sum('kwh_per_month_reserved'),
                    'total_value_reserved' => $preSales->sum('total_value'),
                    'average_price_per_kwh' => $preSales->avg('price_per_kwh'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener preventas por estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preventas por estado',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get expiring pre-sales
     */
    public function expiring(int $days = 30): JsonResponse
    {
        try {
            $preSales = EnergyRightPreSale::getExpiringPreSales($days);

            return response()->json([
                'success' => true,
                'message' => "Preventas que expiran en {$days} días obtenidas exitosamente",
                'data' => $preSales,
                'meta' => [
                    'days' => $days,
                    'total_expiring' => $preSales->count(),
                    'by_status' => [
                        'pending' => $preSales->where('status', 'pending')->count(),
                        'confirmed' => $preSales->where('status', 'confirmed')->count(),
                        'cancelled' => $preSales->where('status', 'cancelled')->count(),
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener preventas que expiran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preventas que expiran',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get active pre-sales
     */
    public function active(): JsonResponse
    {
        try {
            $preSales = EnergyRightPreSale::getActivePreSales();

            return response()->json([
                'success' => true,
                'message' => 'Preventas activas obtenidas exitosamente',
                'data' => $preSales,
                'meta' => [
                    'total_active' => $preSales->count(),
                    'total_kwh_reserved' => $preSales->sum('kwh_per_month_reserved'),
                    'total_value_reserved' => $preSales->sum('total_value'),
                    'average_price_per_kwh' => $preSales->avg('price_per_kwh'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener preventas activas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preventas activas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
